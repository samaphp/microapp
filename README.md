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
- ✅ Auto-discovery of controller routes using `loadRoutesFrom(...)`
- ✅ Built-in CLI to initialize `.htaccess` and autoload mapping

## ⚙️ Developer Experience
MicroApp ships with CLI tools to help you get started faster:
`php vendor/samaphp/microapp/bin/init.php`

This will:
- ✅ Inject App\\ => src/ into composer.json if missing
- ✅ Copy .htaccess.microapp to .htaccess if not already present
- ✅ Run composer dump-autoload to finalize setup

## 🚀 Getting Started
- Install via Composer: `composer require samaphp/microapp`
- Scaffold your app (autoload controllers + .htaccess + index.php): `php vendor/samaphp/microapp/bin/init.php`

### Extra
- Create your controller: `php vendor/samaphp/microapp/bin/make-controller.php HomeController`

## 🔀 basePath Support
If your application is served from a subdirectory (e.g., example.com/myapp/), you can pass the base path to MicroApp during initialization:
```
$app = new MicroApp('/myapp');
```

This ensures all routes are matched correctly regardless of where your app is hosted.

    📌 You must also update your .htaccess rewrite rule to reflect the same subdirectory.
