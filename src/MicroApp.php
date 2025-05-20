<?php
declare(strict_types=1);

namespace MicroApp;

class MicroApp {
    private array $request = [];
    private array $routes = [];
    private array $routeMiddleware = [];
    private array $beforeMiddlewareQueue = [];
    private array $afterMiddlewareQueue = [];
    private array $middlewareRegistry = [];
    private array $routeMiddlewareBuffer = [];
    private string $basePath = '';
    private array $response = [
        'body' => '',
        'status' => 200,
        'headers' => [],
        'sent' => false,
    ];

    public function __construct(string $basePath = '') {
        $this->basePath = rtrim($basePath, '/');
    }

    public function get(string $route, callable $handler, $before = null, $after = null): void {
        $this->registerRoute('GET', $route, $handler, $before, $after);
    }
    public function post(string $route, callable $handler, $before = null, $after = null): void {
        $this->registerRoute('POST', $route, $handler, $before, $after);
    }
    public function put(string $route, callable $handler, $before = null, $after = null): void {
        $this->registerRoute('PUT', $route, $handler, $before, $after);
    }
    public function delete(string $route, callable $handler, $before = null, $after = null): void {
        $this->registerRoute('DELETE', $route, $handler, $before, $after);
    }
    public function patch(string $route, callable $handler, $before = null, $after = null): void {
        $this->registerRoute('PATCH', $route, $handler, $before, $after);
    }
    private function registerRoute(string $method, string $route, callable $handler, $before = null, $after = null): void {
        $route = $this->normalize($route);
        $this->routes[$method][$route] = $handler;
        if (!isset($this->routeMiddleware[$method][$route])) {
            $this->routeMiddleware[$method][$route] = ['before' => [], 'after' => []];
        }
        $beforeList = is_array($before) ? $before : ($before !== null ? [$before] : []);
        $afterList  = is_array($after)  ? $after  : ($after !== null  ? [$after]  : []);
        $this->routeMiddleware[$method][$route]['before'] = array_unique(array_merge(
            $this->beforeMiddlewareQueue,
            $this->routeMiddlewareBuffer['before'] ?? [],
            $beforeList
        ));
        $this->routeMiddleware[$method][$route]['after'] = array_unique(array_merge(
            $this->routeMiddlewareBuffer['after'] ?? [],
            $afterList,
            $this->afterMiddlewareQueue
        ));
    }

    public function before($middleware): void {
        $middlewares = is_array($middleware) ? $middleware : [$middleware];
        if ($this->routeMiddlewareBuffer !== []) {
            foreach ($middlewares as $mw) {
                $this->routeMiddlewareBuffer['before'][] = $mw;
            }
        } else {
            foreach ($middlewares as $mw) {
                $this->beforeMiddlewareQueue[] = $mw;
            }
        }
    }
    public function after($middleware): void {
        $middlewares = is_array($middleware) ? $middleware : [$middleware];
        if ($this->routeMiddlewareBuffer !== []) {
            foreach ($middlewares as $mw) {
                $this->routeMiddlewareBuffer['after'][] = $mw;
            }
        } else {
            foreach ($middlewares as $mw) {
                $this->afterMiddlewareQueue[] = $mw;
            }
        }
    }
    public function loadMiddlewareFrom(string $directory, string $namespace): void {
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory)) as $file) {
            if ($file->getExtension() !== 'php') continue;
            $class = $this->getClassFromFile($file->getPathname(), $directory, $namespace);
            if (class_exists($class)) {
                $shortName = (new \ReflectionClass($class))->getShortName(); // e.g. "AuthMiddleware"
                $name = strtolower(preg_replace('/Middleware$/', '', $shortName)); // → "auth"
                $this->middlewareRegistry[$name] = $class;
            }
        }
    }

    public function getMiddlewares(): array {
        return [
            'registry' => $this->middlewareRegistry,
            'global_before' => $this->beforeMiddlewareQueue,
            'global_after' => $this->afterMiddlewareQueue,
            'routes' => $this->routeMiddleware,
        ];
    }

    public function getAllRoutes(): array
    {
        return $this->routes;
    }

    public function loadRoutesFrom(string $directory, string $namespace): void {
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory)) as $file) {
            if ($file->getExtension() !== 'php') continue;
            $class = $this->getClassFromFile($file->getPathname(), $directory, $namespace);
            if (class_exists($class) && method_exists($class, 'routes')) {
                $this->routeMiddlewareBuffer = ['before' => [], 'after' => []];
                (new $class($this))->routes();
                $this->routeMiddlewareBuffer = [];
            }
        }
    }

    private function getClassFromFile(string $path, string $baseDir, string $namespace): string {
        $relative = str_replace([$baseDir, '/', '.php'], ['', '\\', ''], $path);
        return rtrim($namespace . '\\' . ltrim($relative, '\\'), '\\');
    }

    private function prepareRequest($method): void {
        if (!isset($this->request['GET']) && ($method == 'GET')) $this->request['GET'] = $_GET;
        if (!isset($this->request['POST']) && ($method == 'POST')) $this->request['POST'] = $_POST;
        if (!isset($this->request['SERVER']) && ($method == 'SERVER')) $this->request['SERVER'] = $_SERVER;
        if (!isset($this->request['HEADER']) && ($method == 'HEADER')) $this->request['HEADER'] = function_exists('getallheaders') ? getallheaders() : [];
        if (!isset($this->request['BODY']) && ($method == 'BODY')) {
            $raw = file_get_contents('php://input');
            $this->request['BODY'] = $raw;
        }
    }

    public function dispatch(): void {
        try {
            $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
            $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
            $path = (empty($this->basePath) || strpos($path, $this->basePath) !== 0) ? $path : substr($path, strlen($this->basePath));
            $path = $this->normalize($path);

            $matched = false;
            foreach ($this->routes[$method] ?? [] as $route => $handler) {
                $params = [];
                if ($this->match($route, $path, $params)) {
                    $matched = true;
                    foreach ($this->routeMiddleware[$method][$route]['before'] ?? [] as $mw) {
                        $this->runMiddleware($mw);
                        if ($this->response['sent']) $this->sendResponse();
                    }
                    $handler(...$params);
                    foreach ($this->routeMiddleware[$method][$route]['after'] ?? [] as $mw) {
                        $this->runMiddleware($mw);
                    }
                    break;
                }
            }

            if (!$matched && !$this->response['sent']) {
                $this->jsonResponse(['error' => ['code' => 404, 'message' => 'Not Found']], 404);
            }

            $this->sendResponse();
        } catch (\Throwable $e) {
            $this->handleException($e);
        }
    }

    private function runMiddleware(string $name): void {
        $key = strtolower($name);
        if (isset($this->middlewareRegistry[$key])) {
            (new $this->middlewareRegistry[$key])($this);
        }
        else {
            throw new \InvalidArgumentException("Middleware not found: $name");
        }
    }

    public function getRequest(string $section): array {
        $part = strtoupper($section);
        $this->prepareRequest($section);
        return $this->request[$section] ?? null;
    }

    public function getRequestHeader(string $key): ?string {
        $this->prepareRequest('HEADER');
        foreach ($this->request['HEADER'] ?? [] as $k => $v) {
            if (strcasecmp($k, $key) === 0) {
                return $v;
            }
        }
        return null;
    }

    public function addResponseHeader(string $key, string $value): void {
        $this->response['headers'][$key] = $value;
    }

    public function getResponse(): array {
        return $this->response;
    }

    public function setResponse(string $body, int $status = null, array $headers = [], bool $force = false): void {
        if (($this->response['sent'] ?? false) && !$force) return;
        $this->response['sent'] = true;
        $this->response['body'] = $body;
        if ($status !== NULL) {
            $this->response['status'] = $status;
        }
        $this->response['headers'] = array_merge($this->response['headers'] ?? [], $headers);
    }

    public function jsonResponse(array $data, int $status = null, bool $force = false): void {
        $this->setResponse(json_encode($data), $status, ['Content-Type' => 'application/json'], $force);
    }

    private function sendResponse(): void {
        http_response_code($this->response['status']);
        foreach ($this->response['headers'] as $k => $v) {
            header("$k: $v");
        }
        echo $this->response['body'];
        $this->terminate();
    }

    protected function terminate(): void {
        exit;
    }

    private function handleException(\Throwable $e): void {
        $id = substr(md5($e->getFile() . $e->getLine() . $e->getMessage() . microtime()), 0, 12);
        $response = ['error' => [
            'error_id' => $id,
            'code' => 500,
            'message' => 'Internal Server Error',
            'trace' => (defined('APP_DEBUG') && APP_DEBUG) ? (string)$e : null
        ]];
        $log = $response;
        $log['error']['trace'] = (string)$e;
        error_log("[" . date('Y-m-d H:i:s') . "] " . json_encode($log, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $this->jsonResponse($response, 500);
        $this->sendResponse();
        $this->terminate();
    }

    private function normalize(string $path): string {
        $clean = '/' . trim($path, '/');
        return $clean === '/' ? '/' : rtrim($clean, '/');
    }

    private function match(string $route, string $path, array &$params): bool {
        $routeParts = explode('/', trim($route, '/'));
        $pathParts = explode('/', trim($path, '/'));
        if (count($routeParts) !== count($pathParts)) return false;
        foreach ($routeParts as $i => $part) {
            if (preg_match('/^{(\w+)(?::(\w+))?}$/', $part, $m)) {
                $type = $m[2] ?? 'string';
                if ($type === 'int' && !preg_match('/^\d+$/', $pathParts[$i])) return false;
                if ($type === 'string' && !preg_match('/^[a-zA-Z0-9\-_]+$/', $pathParts[$i])) return false;
                if ($type !== 'int' && $type !== 'string') throw new \InvalidArgumentException("Unsupported route param type: $type");
                $params[] = $pathParts[$i];
            } elseif ($part !== $pathParts[$i]) return false;
        }
        return true;
    }

    public function input(string $key, string $method = 'GET', string $filter = 'string'): ?string {
        $method = strtoupper($method);
        $this->prepareRequest($method);
        $val = $this->request[$method][$key] ?? null;
        return $filter === 'int' ? (filter_var($val, FILTER_VALIDATE_INT) !== false ? (string)(int)$val : null)
            : ($filter === 'email' ? filter_var($val, FILTER_VALIDATE_EMAIL) ?: null
                : ($filter === 'url' ? filter_var($val, FILTER_VALIDATE_URL) ?: null
                    : htmlspecialchars(trim((string)$val), ENT_QUOTES, 'UTF-8')));
    }
}
