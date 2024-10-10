<?php
    namespace Web\Project;

    class Router
    {
        function match($httpMethod, $path) {
            $scriptName = dirname($_SERVER["SCRIPT_NAME"]);
            $path = str_replace($scriptName, "", $path);

            foreach (WEB_PAGES as $route => $handlers) {
                $regex = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)}/', '([a-zA-Z0-9-_()]+)', $route);

                if (preg_match("#^$regex$#", $path, $matches) && isset($handlers[$httpMethod])) {
                    array_shift($matches);

                    return [
                        'route_info' => $handlers[$httpMethod],
                        'params' => $matches
                    ];
                }
            }

            return null;
        }

        function dispatch($httpMethod, $path) {
            $match = $this->match($httpMethod, $path);

            if ($match) {
                $handler = $match['route_info'];
                $params = $match['params'];

                // array indexes: title, controller_class_name, function_name
                extract($handler);

                if (class_exists($controller_class_name)) {
                    $controller = new $controller_class_name();

                    if (method_exists($controller, $function_name)) {
                        $queryParams = $_GET;
                        $controller->$function_name(["title" => $title, "params" => $params, "queryParams" => $queryParams]);
                        //$controller->$function_name(["title" => $title, "params" => $params]);
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
    }