<?php
namespace Qh\Api;

/**
 * RESTful Route API Wrapper
 */
class RESTfulRouter
{
    private $strict;
    private $router;
    private $index = 0;
    private $prefix = '__REST_';
    public function __construct($strict = false) {
        $this->router = \TQ\Dispatcher::getInstance()->getRouter();
        $this->strict = $strict;
    }

    public function on($method, $path, $controller, $action, $module = 'index') {
        $method = trim($method);
        if ( strpos($method, ' ') ) { // multi method
            $methods = preg_split('/\s+/', $method);
            foreach ($methods as $method) {
                $this->register($method, $path, $controller, $action, $module);
            }
        } else {
            $this->register($method, $path, $controller, $action, $module);
        }
    }

    private function register($method, $path, $controller, $action, $module) {
        $this->router->addRoute( $this->prefix . $this->index++,
            new Lib\RESTfulRoute($path, array(
                'controller' => $controller
              , 'action' => $action
              , 'method' => $method
              , 'module' => $module
            ), $this->strict)
        );
    }
}
