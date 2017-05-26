<?php
namespace Qh\Api;

/**
 *
 */
class AdaptiveView implements \TQ\ViewInterface
{
    private $path;
    private $renderers = array();
    private $data = array();

    public function __construct($path = null) {
        if ( isset($path) ) {
            $this->path = $path;
        } else {
            $config = \TQ\Application::app()->getConfig();
            $this->path = $config['application.directory'] .'/views/';
        }
    }

    public function render($file, $data = null) {
        if ( is_array($data) ) {
            $this->data = array_merge($this->data, $data);
        }

        // render according to extname
        $extname = strtolower( pathinfo($file, PATHINFO_EXTENSION) );

        if ( isset($this->renderers[$extname]) ) {
            return call_user_func($this->renderers[$extname], $file, $this->data);
        } else {
            return $this->renderDefault($file, $this->data);
        }
    }

    public function display($file, $data = null) {
        echo $this->render($file, $data);
    }

    public function assign($name, $value = null) {
        $this->data[$name] = $value;
    }

    public function getScriptPath() {
        return $this->path;
    }

    public function setScriptPath($path) {
        $this->path = $path;
    }

    /**
     * [on description]
     * @param  [type] $extname  [description]
     * @param  [type] $renderer [description]
     * @return [type]           [description]
     */
    public function on($extname, $renderer) {
        $extname = strtolower($extname);

        if ( is_callable($renderer) ) {
            $this->renderers[$extname] = $renderer;
        } else {
            throw new Exception('renderer is not callable');
        }
    }

    private function renderDefault($file, $data) {
        $view = new \TQ\View\Simple($this->path);
        return $view->render($file, $data);
    }
}
