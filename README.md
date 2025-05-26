# microapp
MicroApp is a minimal PHP 7.4+ microframework for building super-microservices with clean routing and zero dependencies.
Perfect for fast bootstraps, tiny APIs, internal tools, or focused endpoints where simplicity wins.
Designed for developers who value clarity, control, zero framework overhead and long-term maintainability.
Built for microservices that can live for decades without requiring upgrades or major refactors. You’re encouraged to follow future-proof design principles when building on top of it.

## 🌟 Features
- ✅ `GET`, `POST`, `PUT`, `DELETE`, and `PATCH` method support
- ✅ Named route parameters like `/user/{id}`
- ✅ JSON response helper via `$app->jsonResponse()`
- ✅ Centralized response lifecycle with override support
- ✅ PSR-4 structure with Composer autoloading
- ✅ Simple and readable one-file implementation
- ✅ Ready to be used as a Composer package
- ✅ Auto-discovery of controller classes with route definitions inside the class itself
- ✅ Optional CLI available via the `microapp-dev` package
- ✅ Middleware (before/after) for logging, authentication, CSRF protection, and other cross-cutting concerns

## 🚀 Getting Started
- Install via Composer: `composer require samaphp/microapp`
- You can set things up manually (see sections below) or automate it using the `microapp-dev` package from the Developer Tools section.

## 🛠️ Developer Tools
Looking to scaffold `.htaccess`, `index.php`, or generate controllers?  
Use the official companion package:  
➡️ [`samaphp/microapp-dev`](https://github.com/samaphp/microapp-dev)

## 🔀 basePath Support
If your application is served from a subdirectory (e.g., example.com/myapp/), you can pass the base path to MicroApp during initialization:
```
$app = new MicroApp('/myapp');
```

This ensures all routes are matched correctly regardless of where your app is hosted.

    📌 You must also update your .htaccess rewrite rule to reflect the same subdirectory.

## 🟦 .htaccess example
```
<IfModule mod_rewrite.c>
  RewriteEngine On

  # Enable this to support subdirectory installations after injecting basePath value to MicroApp('basePathHere')
  #RewriteBase /basePathHere/

  # Redirect everything except existing files and directories to index.php
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteRule ^ index.php [QSA,L]
</IfModule>
```

## 🟦 index.php Example
The auto generated `index.php` will be like this:
```php
<?php
require __DIR__ . '/vendor/autoload.php';

use MicroApp\MicroApp;

$app = new MicroApp();
$app->loadRoutesFrom(__DIR__ . '/src/Controller', 'App\\Controller');
$app->dispatch();
```

## 🟦 Controller Example
Your controller class should be in the `src/Controller` directory and follow the PSR-4 autoloading standard. For example, if you create a controller named `HomeController.php`, it should look like this:
```php
<?php
namespace App\Controller;

use MicroApp\MicroApp;

class HomeController
{
    public function routes(MicroApp $app): void
    {
        $app->get('/home', [$this, 'index']);
    }

    public function index(): void
    {
        echo 'Hello from HomeController';
    }
}
```
## 🧩 Middleware Support

Middleware are invoked as class instances with `__invoke(MicroApp $app)` support.
They can inspect the request, modify headers, or override the response.

You can register global or route-specific middleware to execute custom logic **before** or **after** your routes.

### 🔹 Global Middleware
You can apply middleware from the `index.php` file:
```php
$app->before('auth'); // Runs before all routes if inserted in index.php or at the beganning of controller routes() method.
$app->after('logger'); // Runs after all routes if inserted in index.php or at the beganning of controller routes() method.
```

### 🔹 Route Middleware
```php
// 'auth' will run before the handler
// 'audit' will run after the handler
$app->get('/admin', [$this, 'admin'], 'auth', 'audit');
// or applying multiple middlewares per route
$app->get('/admin', [$this, 'admin'], ['auth', 'admin'], 'audit');
// You can also define quick inline routes using closures
$this->app->get('/ping', function () {
    $this->app->setResponse('pong');
});
// Or attach a middleware inline from within the same controller
$this->app->get('/ping', function () {
    $this->app->setResponse('pong');
}, $this->ExampleInternalMiddleware());
```
> ⚠️ Best Practice:
> While calling middleware from internal methods is supported (since they are callable), it’s strongly recommended to define middleware as standalone classes under `App\Middleware`. This keeps your codebase clean, modular, and reusable across routes.

### 🟨 Middleware Example
Your middleware class should be placed in the `src/Middleware` directory and follow the PSR-4 autoloading standard. Middleware must implement the `__invoke` method and receive the `MicroApp` instance.

For example, if you create a middleware named `SecretPasswordMiddleware.php`, it should look like this:

```php
<?php
namespace App\Middleware;

use MicroApp\MicroApp;

class SecretPasswordMiddleware
{
    public function __invoke(MicroApp $app): void
    {
        $key = $app->input('secret');

        if ($key !== '123') {
            $app->jsonResponse([
                'error' => ['code' => 401, 'message' => 'Unauthorized']
            ], 401);
        }
    }
}
```

## 🧩 Extending MicroApp Class
You can extend the `MicroApp` class to customize internal behavior — such as centralized error handling:

```php
class MyApp extends MicroApp {
    protected function handleException(\Throwable $e): void {
        error_log('Caught: ' . $e->getMessage());
        self::json(['error' => 'Something went wrong'], 500);
    }
}
```

## 🔽 Accessing Request Input
You can use `MicroApp::input()` to safely retrieve and sanitize input from various sources:

Usage:
```php
$name = MicroApp::input('name'); // GET by default
$email = MicroApp::input('email', 'POST', 'email');
$id = MicroApp::input('user_id', 'BODY', 'int');
$token = MicroApp::input('Authorization', 'HEADER');

MicroApp::input(string $key, string $method = 'GET', string $filter = 'string')
```
Parameters:
- `key`: The input name to fetch.
- `method`: One of 'GET', 'POST', 'JSON', or 'HEADER'.
- `filter`: Sanitization type: 'string', 'int' or 'email'.

## 🟦 Accessing Headers

To retrieve a request header (case-insensitive):

```php
$auth = $app->getRequestHeader('Authorization');
```
To set a response header:

```php
$app->addResponseHeader('X-Custom-Header', 'value');
```

## 🧪 Public API Reference

MicroApp exposes a set of simple, well-defined public methods:

- `before()`/`after()` – Define global or per-controller middleware hooks.
- `setResponse()` – Define a custom response body and can be used for Text/HTML response, status, and headers.
- `jsonResponse()` – Send a JSON response with optional status code.
- `getResponse()` – Retrieve the full response array (body, status, headers).
- `getRequest()` – Get a specific request section and inputs (GET, POST, HEADER, etc.).
- `getRequestHeader()` – Retrieve a specific request header.
- `addResponseHeader()` – Add or override a response header.
- `getAllRoutes()` – Debug utility to list all registered routes.
- `getMiddlewares()` – Debug utility to inspect middleware configuration.

These methods form the MicroApp API surface and are safe to use directly from controllers or custom logic.

## 🛣️ Roadmap
MicroApp aims to remain minimal and dependency-free while gradually improving developer experience and production readiness.

### ✅ Core Roadmap

- ✅ ~**Middleware (before/after)** for logging, authentication, CSRF protection, and other cross-cutting concerns~
- 🔹 **PHP 8+ Compatibility Check** — audit for deprecated functions to ensure forward compatibility
- 🔹 **SQLite Support** for lightweight, embedded persistence (potentially via a dedicated utility package)
- 🔹 **File Storage / Upload Handling** for managing uploaded files and saving them to disk  (potentially via a dedicated utility package)
- 🔹 **Standards-Based Middleware** implement PSR-15 with minimal PSR-7 support (potentially via a dedicated utility package).
- 🔹 **Lazy Route Registration** — defer loading route definitions until needed during dispatch. Reduces memory footprint and improves cold start times
- 🔹 **Middleware chain** middleware chaining. and `middlewareGroup` to allow for `adminOnly` to have multiple middlewares applied
- 🔹 **Code ehancements** Shortcuts for Early Return return `$app->abort(403, 'Forbidden');` or `redirect()`
- 🔹 **Shared request context** Allow injecting state	Use `$app->context['key'] = value`

### 🧠 Under Consideration

These features are being explored and may be added if they fit the minimalist design:

- 🔸 **Route Grouping / Prefixing** for modular organization (e.g., `/api/v1`)
- 🔸 **Route Caching** to speed up route resolution (no external dependencies)
- 🔸 **Service Container or DI** for clean dependency injection in handlers
- 🔸 **Static File Serving / File Upload Handling** — define usage and scope
- 🔸 **Logging Integration** via a simple `log()` method or PSR-3 support

## ⚖️ Tradeoffs

MicroApp intentionally leaves out features that are outside its core responsibility as a router. This keeps the framework lightweight, dependency-free, and easy to reason about.

### ❌ Not handled by design

- **Authentication & Authorization**  
  You are free to implement auth logic using your own classes or middleware.

- **CSRF Protection**  
  CSRF validation should be enforced at the application level, not in the router.

- **Session Management**  
  MicroApp does not handle PHP sessions or session storage by default.

- **Rate Limiting**  
  Should be implemented at the proxy (e.g. NGINX, Cloudflare) or middleware level.

- **Output Rendering (Templates/Views)**  
  MicroApp is designed for APIs and microservices, not traditional HTML applications. Template engines are out of scope — return raw JSON, plain text, or HTML directly as needed.

- **Advanced Database Abstraction / ORM**  
  MicroApp doesn’t include a database layer or ORM. You can use PDO directly or integrate any lightweight ORM as needed.

These tradeoffs allow MicroApp to stay focused, composable, and small — making it ideal for microservices, APIs, and focused backends.

### 🚫 Deferred by Design

The following are intentionally left out of the core to preserve MicroApp’s no-dependency philosophy:

- ❌ **Config File Support** Loading `.env` files or configuration files is out of scope for the core. If needed, you can use a community-maintained package such as `vlucas/phpdotenv`.

### 🧼 Clean principles to follow

- If it's just one line wrapping an existing method, and it’s not used internally, don’t include it in the core.

## 🚧 Disclaimer

- MicroApp is considered stable for use in real projects, though it has not yet reached version `1.0.0`. APIs are unlikely to change significantly, but some refinements may still occur as the ecosystem evolves.
- The codebase has been reviewed with security scanning tools, and fixes have been applied to address identified concerns. Ongoing best-practice audits will continue to ensure safe use in production environments.
