<?php
namespace App;

class ErrorController extends \TQ\ControllerAbstract
{

    public function errorAction($exception)
    {

        \TQ\Dispatcher::getInstance()->enableView(); //关闭视图模板引擎

        $env = \TQ\Application::app()->environ();
        switch ($exception->getCode()) {
            case \TQ\ERR\AUTOLOAD_FAILED:
            case \TQ\ERR\NOTFOUND\MODULE:
            case \TQ\ERR\NOTFOUND\CONTROLLER:
            case \TQ\ERR\NOTFOUND\ACTION:
                header('HTTP/1.0 404 Not Found');
                if ($env != 'develop' && $env != 'simon_develop') die('404 NOT FOUND!');
                break;
            default:
                header("HTTP/1.0 500 Internal Server Error");
                break;
        }

        $this->getView()->e = $exception;
        $this->getView()->e_class = get_class($exception);
        $this->getView()->e_string_trace = $exception->getTraceAsString();

        $this->_view->assign("e",$exception);
        $this->_view->assign("message", $exception->getMessage());
        $this->_view->assign("line",$exception->getLine());
        $this->_view->assign("File",$exception->getFile());
        $this->_view->assign("e_class",get_class($exception));
        $this->_view->assign("e_string_trace",$exception->getTraceAsString() );
        $this->_view->assign("debug_print_backtrace",debug_backtrace() );
        $this->_view->assign("modules",$this->getModuleName() );

        $params = $this->getRequest()->getParams();
        unset($params['exception']);
        $params = array_merge(
            array(),
            $params,
            $this->getRequest()->getPost(),
            $this->getRequest()->getQuery()
        );
        $this->_view->assign("params",print_r($params,true));

    }

}
