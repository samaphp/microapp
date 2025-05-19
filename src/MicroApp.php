<?php
declare(strict_types=1);

namespace MicroApp;

class MicroApp {
    private array $request = [];
    private array $routes = [];
    private string $basePath = '';

    private array $response = [
        'body' => '',
        'status' => 200,
        'headers' => [],
    ];

    public function __construct(string $basePath = '') {
        $this->basePath = rtrim($basePath, '/');
    }

    public function get(string $route, callable $handler): void {
        $this->routes['GET'][$this->normalize($route)] = $handler;
    }

    public function post(string $route, callable $handler): void {
        $this->routes['POST'][$this->normalize($route)] = $handler;
    }

    public function put(string $route, callable $handler): void {
        $this->routes['PUT'][$this->normalize($route)] = $handler;
    }

    public function delete(string $route, callable $handler): void {
        $this->routes['DELETE'][$this->normalize($route)] = $handler;
    }

    public function patch(string $route, callable $handler): void {
        $this->routes['PATCH'][$this->normalize($route)] = $handler;
    }

    public function getAllRoutes(): array
    {
        return $this->routes;
    }

    public function loadRoutesFrom(string $directory, string $namespace): void {
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory)) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            try {
                $class = $this->getClassFromFile($file->getPathname(), $directory, $namespace);
                if (class_exists($class) && method_exists($class, 'routes')) {
                    (new $class($this))->routes();
                }
            } catch (\Throwable $e) {
                $this->handleException($e);
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
            $matched = FALSE;
            foreach ($this->routes[$method] ?? [] as $route => $handler) {
                $params = [];
                if ($this->match($route, $path, $params)) {
                    $handler(...$params);
                    $matched = TRUE;
                    break;
                }
            }
            if (!$matched) {
                $this->jsonResponse(['error' => ['code' => 404, 'message' => 'Not Found' ]], 404);
            }
            $this->sendResponse();
        } catch (\Throwable $e) {
            $this->handleException($e);
        }
    }

    public function setResponse(string $body, int $status = NULL, array $headers = []): void {
        $this->response['body'] = $body;
        if ($status !== NULL) {
            $this->response['status'] = $status;
        }
        $this->response['headers'] = array_merge($this->response['headers'] ?? [], $headers);
    }

    private function sendResponse(): void {
        http_response_code($this->response['status']);
        foreach ($this->response['headers'] as $k => $v) {
            header("$k: $v");
        }
        echo $this->response['body'];
        exit;
    }

    public function jsonResponse(array $data, int $status = NULL): void {
        $this->setResponse(json_encode($data), $status, ['Content-Type' => 'application/json']);
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
