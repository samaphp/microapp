# microapp
MicroApp is a minimal PHP 7.4+ microframework for building super-microservices with clean routing and zero dependencies.
Perfect for fast bootstraps, tiny APIs, internal tools, or focused endpoints where simplicity wins.
Built for developers who prefer clarity, control, and zero framework overhead.

## 🌟 Features
- ✅ `GET`, `POST`, `PUT`, `DELETE`, and `PATCH` method support
- ✅ Named route parameters like `/user/{id}`
- ✅ JSON response helper: `MicroApp::json(...)`
- ✅ PSR-4 structure with Composer autoloading
- ✅ Simple and readable one-file implementation
- ✅ Ready to be used as a Composer package
- ✅ Auto-discovery of controller classes with route definitions inside the class itself
- ✅ Optional CLI available via the `microapp-dev` package

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

## 🚧 Disclaimer

MicroApp is still in active development and will reach stability by **May 1st, 2025**.

- The codebase will undergo review by security analysis tools to ensure best practices and safeguard production use.

You're welcome to try it today — just note that APIs and folder structure may still slightly change.
