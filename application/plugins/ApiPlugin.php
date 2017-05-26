<?php

use TQ\Registry as Di;

/**
 * API PLUGIN
 */
class ApiPlugin extends TQ\PluginAbstract
{

    public function routerStartup(TQ\RequestAbstract $request, TQ\ResponseAbstract $response )
    {
    }

    public function routerShutdown(TQ\RequestAbstract $request, TQ\ResponseAbstract $response )
    {
    }

    public function dispatchLoopStartup(TQ\RequestAbstract $request, TQ\ResponseAbstract $response)
    {
        $module = $request->getModuleName();
        if (strtolower($module) == "api") {
            $dispatcher = Di::get('dispatcher');
            $dispatcher->disableView(); //关闭视图模板引擎
            $dispatcher->throwException(false); //将抛出由异常转换为错误级别
            $dispatcher->setErrorHandler(array('\\Qh\\Exception\\ErrorHandler','ApiHandler'));
        }
    }

    public function preDispatch(TQ\RequestAbstract $request, TQ\ResponseAbstract $response )
    {
    }

    public function postDispatch(TQ\RequestAbstract $request, TQ\ResponseAbstract $response )
    {
    }

    public function dispatchLoopShutdown(TQ\RequestAbstract $request, TQ\ResponseAbstract $response )
    {
    }

    public function preResponse(TQ\RequestAbstract $request, TQ\ResponseAbstract $response )
    {
    }

}
