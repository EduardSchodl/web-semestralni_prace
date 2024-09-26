<?php
    namespace Web\Project;

    class Router
    {
        protected $routes = [];

        function add($httpMethod, $path, $controller){
            $this->routes[$httpMethod][$path] = $controller;
        }

        function match($httpMethod, $path){
            // tady zmenit
            $scriptName = dirname($_SERVER["SCRIPT_NAME"]);
            $path = str_replace($scriptName, "", $path);

            if(isset(WEB_PAGES[$path][$httpMethod])){
                return WEB_PAGES[$path][$httpMethod];
            }

            return null;
        }

        function dispatch($httpMethod, $path){
            $handler = $this->match($httpMethod, $path);

            if($handler){
                //array indexes: title, controller_class_name, function_name
                extract($handler);

                if(class_exists($controller_class_name)){
                    $controller = new $controller_class_name();

                    if(method_exists($controller, $function_name)){
                        $controller->$function_name(["title" => $title]);
                    }
                    else{
                        echo "Method $function_name not found in controller $controller_class_name. <br>";
                    }
                }
                else {
                    echo "Controller $controller_class_name not found. <br>";
                }
            }
            else{
                http_response_code(404);
                echo "404 Not Found";
            }
        }
    }