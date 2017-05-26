<?php
use Qh\Loader as QhLoader;

class Bootstrap extends Qh\Bootstrap
{
    /**
     * 自定义自动加载
     *
     * @method _initMyLoader
     */
    public function _initTQLoader( \TQ\Dispatcher $dispatcher)
    {
        $loader = QhLoader::getInstance();

        /** 注册自动加载目录 **/
        $loader->registerDirs(
            [
                APP_PATH. "/application/models",
            ]
        );

        /** 注册名称空间 **/
        $loader->registerNamespaces(
            [
                'App\Model'         => APP_PATH. '/application/models/',
                'App\Admin\Model'     => APP_PATH. '/application/modules/Admin/models/',
            ]
        );

        $loader->register();
    }

    // 使用 RESTful style
    /**
     * 定义 Restful 路由关系
     * @param  TQDispatcher $dispatcher [description]
     * @source $router->on($method, $path, $controller, $action, $module);
     * @return mixed
     */
    function _initRESTfulRoute( \TQ\Dispatcher $dispatcher ) {
        $router = new \Qh\Api\RESTfulRouter;

        $router->on(
            'get',
            'api/users',
            'user','list','api'
        );

        $router->on(
            'post',
            'api/users',
            'user','add','api'
        );

        $router->on(
            'put',
            'api/users/:user_id',
            'user','edit','api'
        );

        $router->on(
            'delete',
            'api/users/:user_id',
            'user','remove','api'
        );




    }



}
