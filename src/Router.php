<?php
    namespace Web\Project;

    class Router
    {
        protected $routes = [];

        function add($httpMethod, $path, $controller){
            $this->routes[$httpMethod][$path] = $controller;
        }

        function match($httpMethod, $path){
            if(isset($this->routes[$httpMethod][$path])){
                return $this->routes[$httpMethod][$path];
            }

            return null;
        }

        function dispatch($httpMethod, $path){
            $handler = $this->match($httpMethod, $path);

            if($handler){
                list($controllerName, $action) = explode("@", $handler);

                $controller = __NAMESPACE__."\\Controllers\\$controllerName";

                $controller = new $controller();

                $controller->$action();
            }

            http_response_code(404);
            echo "404 Not Found";
        }
    }