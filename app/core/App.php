<?php

class App {
    protected $controller = 'Auth';
    protected $method = 'index';
    protected $params = [];

    public function __construct() {
        $url = $this->parseURL();

        // Check if controller exists
        if(isset($url[0])) {
            $controller_name = ucfirst(str_replace('-', '_', $url[0]));
            if(file_exists('../app/controllers/' . $controller_name . '.php')) {
                $this->controller = $controller_name;
                unset($url[0]);
            }
        }

        require_once '../app/controllers/' . $this->controller . '.php';
        $this->controller = new $this->controller;

        // Check if method exists in controller
        if(isset($url[1])) {
            $method_name = str_replace('-', '_', $url[1]);
            if(method_exists($this->controller, $method_name)) {
                $this->method = $method_name;
                unset($url[1]);
            }
        }

        // Get params
        if(!empty($url)) {
            $this->params = array_values($url);
        }

        // Call callback with array of params
        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    public function parseURL() {
        if(isset($_GET['url'])) {
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            $url = explode('/', $url);
            return $url;
        }
    }
}
