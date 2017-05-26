<?php
namespace Qh;

use TQ\Registry as Di;

class Bootstrap extends \TQ\BootstrapAbstract
{
    /* 注册依赖变量 */
    public function _initDi( \TQ\Dispatcher $dispatcher )
    {
        Di::set('dispatcher',   $dispatcher);
        Di::set('request',      $dispatcher->getRequest());
        Di::set('app',          $dispatcher->getApplication());
        Di::set('router',       $dispatcher->getRouter());
    }

    /**
     * 自动加载载入
     *
     * @method _initLoader
     * @return void
     */
    public function _initLoader( \TQ\Dispatcher $dispatcher )
    {

        \TQ\Loader::import(APP_PATH . "/vendor/autoload.php");
        \TQ\Loader::import(APP_PATH . "/application/function.php");

        // 注册本地类名前缀, 这部分类名将会在本地类库查找
        \TQ\Loader::getInstance()->registerLocalNameSpace(array('Qh', 'Cache', 'Upload', 'Http', 'Util'));
    }

    /**
     * Session
     *
     * @method _initSession
     * @param  TQ\Dispatcher  $dispatcher
     * @return [type]
     */
    public function _initSession( \TQ\Dispatcher $dispatcher )
    {
        \TQ\Session::getInstance()->start();
        define('REQUEST_METHOD', strtoupper($dispatcher->getRequest()->getMethod()));
    }

    public function _initConfig( \TQ\Dispatcher $dispatcher )
    {
        Di::set('config', \TQ\Application::app()->getConfig());
    }

    /**
     * 初始化系统常量.
     * @param  TQ\Dispatcher $dispatcher
     * @return void
     */
    public function _initConst( \TQ\Dispatcher $dispatcher ) {
        define('APPLICATION_VIEWS_PATH',    APPLICATION_PATH . '/views');
        define('APPLICATION_CONFIG_PATH',   APPLICATION_PATH . '/config');
        define('APPLICATION_MODULES_PATH',  APPLICATION_PATH . '/modules'); //定义模块目录
        define('APPLICATION_MODEL_PATH',    APPLICATION_PATH . '/models');
        define('APPLICATION_LIBRARY_PATH',  APP_PATH . '/library'); //自定义库

    }


    /**
     * 载入 phpActiveRecord
     * @method _initDb
     * @param  TQDispatcher $dispatcher
     * @return mixed
     */
    public function _initActiveRecord( \TQ\Dispatcher $dispatcher )
    {

        \ActiveRecord\Config::initialize(function ($cfg) {

            $config = Di::get('config');
            $cfg->set_model_directory(APPLICATION_PATH .DS. 'models');
            $cfg->set_connections(array(
                'development' => $config->db->development,
                'production' =>$config->db->production->master,
                'master' => $config->db->production->master,
                'slave' => $config->db->production->slave
            ));
            $cfg->set_default_connection($config->db->default_connection);
            $cfg->set_logging('true');
            $cfg->set_logger(\Qh\Logger::getLogger());

        });

    }

    public function _initDefaultName( \TQ\Dispatcher $dispatcher )
    {
        $dispatcher->setDefaultModule('Index')->setDefaultController('Index')->setDefaultAction('index');
    }

    public function _initPlugin( \TQ\Dispatcher $dispatcher )
    {
        // 注册一个插件
        $dispatcher->registerPlugin(new \CamelizePlugin());
        $dispatcher->registerPlugin(new \ApiPlugin());
        $dispatcher->registerPlugin(new \LogPlugin());
    }


    /**
     * initialize Module
     * @param  TQDispatcher $dispatcher
     * @return void
     */
    public function _initModules( \TQ\Dispatcher $dispatcher )
    {
        $app = $dispatcher->getApplication();
        $modules = $app->getModules();

        foreach ($modules as $module)
        {
            if ( strcmp($module, 'Index') == 0) continue;
            if (file_exists($app->getAppDirectory() . "/modules/{$module}/_init.php")) {
                require_once $app->getAppDirectory() . "/modules/{$module}/_init.php";
            }
        }
    }

    public function _initRouter( \TQ\Dispatcher $dispatcher )
    {
        $routes = new \TQ\Config\Ini(APPLICATION_PATH . "/configs/routes.ini");
        $dispatcher->getRouter()->addConfig($routes->index);
    }


    public function _initView( \TQ\Dispatcher $dispatcher )
    {

        //在这里注册自己的view控制器，例如 smarty,firekylin
        if (REQUEST_METHOD != 'CLI') {
            $smarty = new \SmartyAdapter(null, Di::get('config')->smarty);
            $dispatcher->setView($smarty);
        }
    }

    /**
     * init Request;
     * @method _initRequest
     * @param  TQDispatcher $dispatcher
     * @return void
     */
    public function _initRequest( \TQ\Dispatcher $dispatcher)
    {
        $dispatcher->setRequest(new Http\Request());
    }



}
