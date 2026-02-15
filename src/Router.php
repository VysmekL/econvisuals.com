<?php
/**
 * Front Controller Router
 *
 * Centrální směrovač pro všechny požadavky.
 * Implementuje skrytou administraci a čisté URL.
 */

namespace App;

class Router
{
    private array $routes = [];
    private string $requestUri;
    private string $requestMethod;

    public function __construct()
    {
        $this->requestUri = $this->normalizeUri($_SERVER['REQUEST_URI']);
        $this->requestMethod = $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Normalizuje URI (odstraní query string, přebytečné lomítka)
     */
    private function normalizeUri(string $uri): string
    {
        // Odstranit query string
        $uri = strtok($uri, '?');

        // Odstranit přebytečná lomítka
        $uri = trim($uri, '/');

        // Sanitizace - odstranit nebezpečné znaky
        $uri = filter_var($uri, FILTER_SANITIZE_URL);

        return '/' . $uri;
    }

    /**
     * Registruje GET routu
     */
    public function get(string $path, callable $callback): void
    {
        $this->addRoute('GET', $path, $callback);
    }

    /**
     * Registruje POST routu
     */
    public function post(string $path, callable $callback): void
    {
        $this->addRoute('POST', $path, $callback);
    }

    /**
     * Přidá routu do routovací tabulky
     */
    private function addRoute(string $method, string $path, callable $callback): void
    {
        $path = $this->normalizeUri($path);
        $this->routes[$method][$path] = $callback;
    }

    /**
     * Spustí router a najde odpovídající routu
     */
    public function run(): void
    {
        $method = $this->requestMethod;
        $uri = $this->requestUri;

        // Pokud existuje přesná shoda
        if (isset($this->routes[$method][$uri])) {
            call_user_func($this->routes[$method][$uri]);
            return;
        }

        // Dynamické routy s parametry (např. /post/slug-nazev)
        foreach ($this->routes[$method] ?? [] as $route => $callback) {
            $pattern = $this->convertRouteToRegex($route);

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // Odstranit celou shodu
                call_user_func_array($callback, $matches);
                return;
            }
        }

        // 404 Not Found
        $this->notFound();
    }

    /**
     * Převede route na regex pattern pro dynamické parametry
     * Například: /post/:slug => /post/([^/]+)
     */
    private function convertRouteToRegex(string $route): string
    {
        // Escapovat forward slashes pro regex
        $route = str_replace('/', '\/', $route);
        // Nahradit :parametr za capturing group
        $route = preg_replace('/:([a-zA-Z0-9_]+)/', '([^\/]+)', $route);
        return '/^' . $route . '$/';
    }

    /**
     * 404 Error Handler
     */
    private function notFound(): void
    {
        http_response_code(404);
        echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found</title>
    <style>
        body {
            font-family: system-ui, -apple-system, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            background: #f5f5f5;
        }
        .error {
            text-align: center;
            padding: 2rem;
        }
        h1 {
            font-size: 4rem;
            margin: 0;
            color: #333;
        }
        p {
            color: #666;
            font-size: 1.2rem;
        }
    </style>
</head>
<body>
    <div class="error">
        <h1>404</h1>
        <p>Page Not Found</p>
    </div>
</body>
</html>';
    }

    /**
     * Redirect na jinou URL
     */
    public static function redirect(string $url, int $code = 302): void
    {
        header("Location: $url", true, $code);
        exit;
    }
}
