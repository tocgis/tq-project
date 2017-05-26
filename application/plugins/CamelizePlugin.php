<?php
use Qh\Util;

/**
 * 大小驼峰命名规则修正
 */
class CamelizePlugin extends TQ\PluginAbstract
{

    public function routerStartup(TQ\RequestAbstract $request, TQ\ResponseAbstract $response )
    {
    }

    public function routerShutdown(TQ\RequestAbstract $request, TQ\ResponseAbstract $response )
    {
    }

    public function dispatchLoopStartup(TQ\RequestAbstract $request, TQ\ResponseAbstract $response)
    {
        /* 控制器 大驼峰 */
        $controller = $request->getControllerName();
        $c = Util::getInstance()->camelize($controller);
        $request->setControllerName(ucfirst($c));

        /* Action 小驼峰 */
        $action = $request->getActionName();
        $a = Util::getInstance()->camelize($action);
        $request->setActionName($a);
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
