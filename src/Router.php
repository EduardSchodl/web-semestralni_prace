<?php
    namespace Web\Project;

    class Router
    {
        protected $routes = [];

        function add($httpMethod, $path, $controller){
            $this->routes[$httpMethod][$path] = $controller;
        }

        function match($httpMethod, $path){
            $scriptName = dirname($_SERVER["SCRIPT_NAME"]);
            $path = str_replace($scriptName, "", $path);

            if(isset($this->routes[$httpMethod][$path])){
                return $this->routes[$httpMethod][$path];
            }

            return null;
        }

        function dispatch($httpMethod, $path){
            $handler = $this->match($httpMethod, $path);

            if($handler){
                list($controllerName, $action) = explode("@", $handler);

                $controllerClass = __NAMESPACE__."\\Controllers\\$controllerName";
                if(class_exists($controllerClass)){
                    $controller = new $controllerClass();

                    if(method_exists($controller, $action)){
                        $controller->$action();
                    }
                    else{
                        echo "Method $action not found in controller $controllerClass. <br>";
                    }
                }
                else {
                    echo "Controller $controllerClass not found. <br>";
                }
            }

            http_response_code(404);
            echo "404 Not Found";
        }
    }