<?php

class App
{
    protected $routes = [];
    protected $groupMiddleware = [];

    protected function addRoute($method, $uri, $action, $middleware = [])
    {
        $middleware = array_merge($this->groupMiddleware, $middleware);

        $this->routes[$method][$uri] = [
            'action' => $action,
            'middleware' => $middleware
        ];
    }

    public function get($uri, $action, $middleware = [])
    {
        $this->addRoute('GET', $uri, $action, $middleware);
    }

    public function post($uri, $action, $middleware = [])
    {
        $this->addRoute('POST', $uri, $action, $middleware);
    }

    public function patch($uri, $action, $middleware = [])
    {
        $this->addRoute('PATCH', $uri, $action, $middleware);
    }

    public function delete($uri, $action, $middleware = [])
    {
        $this->addRoute('DELETE', $uri, $action, $middleware);
    }

    public function group($middleware, $callback)
    {
        $previousMiddleware = $this->groupMiddleware;

        // Set current group middleware
        $this->groupMiddleware = array_merge($this->groupMiddleware, $middleware);

        // Run routes inside group
        $callback($this);

        // Restore previous middleware (important!)
        $this->groupMiddleware = $previousMiddleware;
    }

    /**
     * Run the app
     */
    public function run()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_method'])) { // AJAX _method overide
            $_SERVER['REQUEST_METHOD'] = strtoupper($_POST['_method']);
        }
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        if (!isset($this->routes[$requestMethod])) {
            http_response_code(404);
            echo "404 Not Found";
            return;
        }

        foreach ($this->routes[$requestMethod] as $routeUri => $route) {

            // Convert /users/{id} → regex
            $pattern = preg_replace('#\{[^/]+\}#', '([^/]+)', $routeUri);
            $pattern = "#^" . $pattern . "$#";

            if (!preg_match($pattern, $uri, $matches)) {
                continue;
            }

            array_shift($matches); // remove full match

            $action = $route['action'];
            $middlewareList = $route['middleware'] ?? [];

            // ------------------------
            // RUN MIDDLEWARE (SAFE)
            // ------------------------
            foreach ($middlewareList as $mw) {

                [$class, $paramString] = array_pad(explode(':', $mw, 2), 2, '');
                $params = $this->parseMiddlewareParams($paramString);

                $mwFile = "../app/middleware/$class.php";

                if (!file_exists($mwFile)) {
                    http_response_code(500);
                    echo "Middleware file not found: $class";
                    return;
                }

                require_once $mwFile;

                if (!class_exists($class)) {
                    http_response_code(500);
                    echo "Middleware class not found: $class";
                    return;
                }

                $mwObj = new $class();

                if (!$mwObj->handle($params)) {
                    http_response_code(403);
                    echo "Forbidden";
                    return;
                }
            }

            // ------------------------
            // CONTROLLER
            // ------------------------
            [$controller, $methodName] = explode('@', $action);

            $controllerFile = "../app/controllers/$controller.php";

            if (!file_exists($controllerFile)) {
                http_response_code(500);
                echo "Controller file not found: $controller";
                return;
            }

            require_once $controllerFile;

            if (!class_exists($controller)) {
                http_response_code(500);
                echo "Controller class not found: $controller";
                return;
            }

            $controllerObj = new $controller();

            if (!method_exists($controllerObj, $methodName)) {
                http_response_code(500);
                echo "Controller method not found: $methodName";
                return;
            }

            // pass route params safely
            $controllerObj->$methodName(...$matches);

            return;
        }

        http_response_code(404);
        echo "404 Not Found";
    }

    /**
     * Parse middleware parameters (key=value or CSV)
     */
    protected function parseMiddlewareParams($paramString)
    {
        $params = [];
        $paramString = trim($paramString);
        if (!$paramString) return $params;

        foreach (explode(',', $paramString) as $p) {
            $p = trim($p);
            if (strpos($p, '=') !== false) {
                [$key, $value] = explode('=', $p, 2);
                $params[trim($key)] = trim($value);
            } else {
                $params[] = $p;
            }
        }

        return $params;
    }
}