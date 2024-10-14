<?php
    namespace Web\Project;

    /**
     * Třída Router zpracovává směrování HTTP požadavků na odpovídající kontrolery a metody.
     */
    class Router
    {
        /**
         * Shoduje příchozí požadavek s definovanou trasou.
         *
         * @param string $httpMethod HTTP metoda požadavku (GET, POST)
         * @param string $path Cesta požadavku
         * @return array|null Pole obsahující informace o trase a parametry, pokud je shoda nalezena; jinak null.
         */
        function match($httpMethod, $path) {
            // Získá název skriptu pro určení základní cesty
            $scriptName = dirname($_SERVER["SCRIPT_NAME"]);

            // Odebere název skriptu z cesty
            $path = str_replace($scriptName, "", $path);

            // Projde definované trasy
            foreach (WEB_PAGES as $route => $handlers) {
                // Nahradí parametry trasy regex vzory
                $regex = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)}/', '([a-zA-Z0-9-_()]+)', $route);

                // Zkontroluje, zda se cesta požadavku shoduje s regex vzorem
                if (preg_match("#^$regex$#", $path, $matches) && isset($handlers[$httpMethod])) {
                    // Odebere první shodu
                    array_shift($matches);

                    // Vrátí handler a parametry shody
                    return [
                        'route_info' => $handlers[$httpMethod],
                        'params' => $matches
                    ];
                }
            }

            // Vrátí null, pokud žádná shoda
            return null;
        }

        /**
         * Odesílá požadavek na odpovídající kontroler a metodu.
         *
         * @param string $httpMethod HTTP metoda požadavku
         * @param string $path Cesta požadavku
         * @return void
         */
        function dispatch($httpMethod, $path) {
            // Najde shodující se trasu
            $match = $this->match($httpMethod, $path);

            if ($match) {
                $handler = $match['route_info'];
                $params = $match['params'];

                // Indexy pole: title, controller_class_name, function_name
                extract($handler);

                // Zkontroluje, zda Controller existuje
                if (class_exists($controller_class_name)) {
                    // Vytvoří instanci kontroleru
                    $controller = new $controller_class_name();

                    // Zkontroluje, zda metoda existuje v kontroleru
                    if (method_exists($controller, $function_name)) {
                        $queryParams = $_GET;
                        // Zavolá metodu kontroleru s daty
                        $controller->$function_name(["title" => $title, "params" => $params, "queryParams" => $queryParams]);
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