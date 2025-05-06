<?php
declare(strict_types=1);

namespace MicroApp;

class MicroApp
{
    private array $routes = [];
    private string $basePath = '';
    private array $middleware = [];

    public function __construct(string $basePath = '')
    {
        $this->basePath = rtrim($basePath, '/');
    }

    /* --------------------------------------------------------------------- */
    /*  ROUTE REGISTRATION                                                   */
    /* --------------------------------------------------------------------- */

    public function get(string $route, callable $handler): void
    {
        $this->routes['GET'][$this->normalize($route)] = $handler;
    }

    public function post(string $route, callable $handler): void
    {
        $this->routes['POST'][$this->normalize($route)] = $handler;
    }

    public function put(string $route, callable $handler): void
    {
        $this->routes['PUT'][$this->normalize($route)] = $handler;
    }

    public function delete(string $route, callable $handler): void
    {
        $this->routes['DELETE'][$this->normalize($route)] = $handler;
    }

    public function patch(string $route, callable $handler): void
    {
        $this->routes['PATCH'][$this->normalize($route)] = $handler;
    }

    public function getAllRoutes(): array
    {
        return $this->routes;
    }

    /* --------------------------------------------------------------------- */
    /*  AUTO‑LOADING ROUTE & MIDDLEWARE CLASSES                              */
    /* --------------------------------------------------------------------- */

    public function loadRoutesFrom(string $directory, string $namespace): void
    {
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory)) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }
            try {
                $class = $this->getClassFromFile($file->getPathname(), $directory, $namespace);
                if (class_exists($class) && method_exists($class, 'routes')) {
                    (new $class())->routes($this);
                }
            } catch (\Throwable $e) {
                $this->handleException($e);
            }
        }
    }

    public function loadMiddlewareFrom(string $directory, string $namespace): void
    {
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory)) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }
            try {
                $class = $this->getClassFromFile($file->getPathname(), $directory, $namespace);
                if (!class_exists($class) || !method_exists($class, 'routes')) {
                    continue;
                }
                foreach ((array)$class::routes() as $pattern) {
                    $this->middleware[$this->normalize($pattern)][] = $class;
                }
            } catch (\Throwable $e) {
                $this->handleException($e);
            }
        }
    }

    /* --------------------------------------------------------------------- */
    /*  REQUEST DISPATCHER                                                   */
    /* --------------------------------------------------------------------- */

    public function dispatch(): void
    {
        try {
            $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
            $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
            $path = (empty($this->basePath) || strpos($path, $this->basePath) !== 0)
                ? $path
                : substr($path, strlen($this->basePath));
            $path = $this->normalize($path);

            foreach ($this->routes[$method] ?? [] as $route => $handler) {
                $params = [];
                if (!$this->match($route, $path, $params)) {
                    continue;
                }

                /* BEFORE middleware */
                foreach ($this->middlewareFor($route) as $mw) {
                    if (method_exists($mw, 'before')) {
                        $ok = (new $mw)->before($method, $path, $params);
                        // if middleware returns FALSE, stop dispatch here
                        if ($ok === false) {
                            return;
                        }
                    }
                }
                /* Main handler */
                $handler(...$params);

                /* AFTER middleware */
                foreach ($this->middlewareFor($route) as $mw) {
                    if (method_exists($mw, 'after')) {
                        (new $mw)->after($method, $path, $params);
                    }
                }
                return;
            }

            http_response_code(404);
            echo '404 Not Found';
        } catch (\Throwable $e) {
            $this->handleException($e);
        }
    }

    /* --------------------------------------------------------------------- */
    /*  INTERNAL HELPERS                                                     */
    /* --------------------------------------------------------------------- */

    private function middlewareFor(string $route): array
    {
        $global = $this->middleware['*']   ?? [];
        $local  = $this->middleware[$route] ?? [];
        return array_merge($global, $local);
    }

    private function getClassFromFile(string $path, string $baseDir, string $namespace): string
    {
        $relative = str_replace([$baseDir, '/', '.php'], ['', '\\', ''], $path);
        return rtrim($namespace . '\\' . ltrim($relative, '\\'), '\\');
    }

    private function handleException(\Throwable $e): void
    {
        $id = substr(md5($e->getFile() . $e->getLine() . $e->getMessage() . microtime()), 0, 12);
        $response = [
            'error' => [
                'error_id' => $id,
                'code' => 500,
                'message' => 'Internal Server Error',
                'trace' => (defined('APP_DEBUG') && APP_DEBUG) ? (string)$e : null,
            ],
        ];
        $log = $response;
        $log['error']['trace'] = (string)$e;
        error_log('[' . date('Y-m-d H:i:s') . '] ' . json_encode($log, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $this->json($response, 500);
    }

    private function normalize(string $path): string
    {
        $clean = '/' . trim($path, '/');
        return $clean === '/' ? '/' : rtrim($clean, '/');
    }

    private function match(string $route, string $path, array &$params): bool
    {
        $routeParts = explode('/', trim($route, '/'));
        $pathParts = explode('/', trim($path, '/'));
        if (count($routeParts) !== count($pathParts)) {
            return false;
        }
        foreach ($routeParts as $i => $part) {
            if (preg_match('/^{(\w+)(?::(\w+))?}$/', $part, $m)) {
                $type = $m[2] ?? 'string';
                if ($type === 'int' && !preg_match('/^\d+$/', $pathParts[$i])) {
                    return false;
                }
                if ($type === 'string' && !preg_match('/^[a-zA-Z0-9\-_]+$/', $pathParts[$i])) {
                    return false;
                }
                if ($type !== 'int' && $type !== 'string') {
                    throw new \InvalidArgumentException("Unsupported route param type: $type");
                }
                $params[] = $pathParts[$i];
            } elseif ($part !== $pathParts[$i]) {
                return false;
            }
        }
        return true;
    }

    /* --------------------------------------------------------------------- */
    /*  INPUT & RESPONSE HELPERS                                             */
    /* --------------------------------------------------------------------- */

    public static function input(string $key, string $method = 'GET', string $filter = 'string'): ?string
    {
        static $json;
        $method = strtoupper($method);
        $sources = [
            'GET' => $_GET,
            'POST' => $_POST,
            'JSON' => $json = $json !== null ? $json : (json_decode(file_get_contents('php://input'), true) ?: []),
            'HEADER' => function_exists('getallheaders') ? getallheaders() : [],
        ];
        $val = $sources[$method][$key] ?? null;
        return $filter === 'int'
            ? (filter_var($val, FILTER_VALIDATE_INT) !== false ? (string)(int)$val : null)
            : ($filter === 'email'
                ? filter_var($val, FILTER_VALIDATE_EMAIL) ?: null
                : ($filter === 'url'
                    ? filter_var($val, FILTER_VALIDATE_URL) ?: null
                    : htmlspecialchars(trim((string)$val), ENT_QUOTES, 'UTF-8')));
    }

    public static function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        // deliberately no exit() so callers can decide control flow
    }
}
