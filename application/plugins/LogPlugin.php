<?php

use TQ\Registry as Di;
use Qh\Logger;

/**
 * 日志 PLUGIN
 */
class LogPlugin extends TQ\PluginAbstract
{

    public function routerStartup(TQ\RequestAbstract $request, TQ\ResponseAbstract $response )
    {
    }

    public function routerShutdown(TQ\RequestAbstract $request, TQ\ResponseAbstract $response )
    {
        if ($request->getModuleName() != 'Index' && $request->getControllerName() != 'Test') {
            Logger::startLogging();
            Logger::getLogger()->log("[{$request->getRequestUri()}]");

            Logger::getLogger()->logRequest($request);

            if (\TQ\ENVIRON == "develop") {
                $data = file_get_contents('php://input');
                Logger::getLogger()->log('Body: (');
                Logger::getLogger()->log($data);
                Logger::getLogger()->log(')'.PHP_EOL);
            }

        }
    }

    /**
     * 分发循环开始之前被触发
     * @param  TQRequestAbstract  $request  [description]
     * @param  TQResponseAbstract $response [description]
     */
    public function dispatchLoopStartup(TQ\RequestAbstract $request, TQ\ResponseAbstract $response)
    {
    }

    /**
     * 分发之前触发
     */
    public function preDispatch(TQ\RequestAbstract $request, TQ\ResponseAbstract $response )
    {
    }

    public function preResponse(TQ\RequestAbstract $request, TQ\ResponseAbstract $response )
    {
    }

    /**
     * 分发结束之后触发
     * @param  TQRequestAbstract  $request  [description]
     * @param  TQResponseAbstract $response [description]
     * @return [type]
     */
    public function postDispatch(TQ\RequestAbstract $request, TQ\ResponseAbstract $response )
    {
    }

    public function dispatchLoopShutdown(TQ\RequestAbstract $request, TQ\ResponseAbstract $response )
    {
        Logger::stopLogging();
    }


}
