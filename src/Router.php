<?php
    namespace Web\Project;

    class Router
    {
        function match($httpMethod, $path) {
            $scriptName = dirname($_SERVER["SCRIPT_NAME"]);
            $path = str_replace($scriptName, "", $path);

            foreach (WEB_PAGES as $route => $handlers) {
                // Convert the route pattern to a regex, replacing {id} or other parameters with a catch group
                $regex = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)}/', '([0-9]+)', $route);

                // Make sure to check for an exact match of the path
                if (preg_match("#^$regex$#", $path, $matches) && isset($handlers[$httpMethod])) {
                    // Remove the first match (which is the entire match) and retain the parameters
                    array_shift($matches);
                    foreach ($matches as $match) {
                        echo $match;
                    }
                    return [
                        'route_info' => $handlers[$httpMethod],
                        'params' => $matches // capture the dynamic parameters
                    ];
                }
            }

            return null;
        }
        /*
        function match($httpMethod, $path){
            // tady zmenit
            $scriptName = dirname($_SERVER["SCRIPT_NAME"]);
            $path = str_replace($scriptName, "", $path);
            echo $path;

            if(isset(WEB_PAGES[$path][$httpMethod])){
                return WEB_PAGES[$path][$httpMethod];
            }

            return null;
        }
*/
        function dispatch($httpMethod, $path) {
            $match = $this->match($httpMethod, $path);

            if ($match) {
                // Extract the route info and parameters
                $handler = $match['route_info'];
                $params = $match['params'];

                // array indexes: title, controller_class_name, function_name
                extract($handler);

                if (class_exists($controller_class_name)) {
                    $controller = new $controller_class_name();

                    if (method_exists($controller, $function_name)) {
                        // Pass parameters along with other data to the controller function
                        $controller->$function_name(["title" => $title, "params" => $params]);
                    } else {
                        echo "Method $function_name not found in controller $controller_class_name. <br>";
                    }
                } else {
                    echo "Controller $controller_class_name not found. <br>";
                }
            } else {
                http_response_code(404);
                echo "404 Not Found";
            }
        }
        /*
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
        */
    }