<?php
namespace Qh\Mvc\Controller;

use Qh\Util;
use Qh\Mvc\Controllers;
use TQ\Registry as Di;

abstract class WebApp extends Controllers
{
    protected $_layout = 'default.html';
    protected $_session;
    protected $_config;
    protected $_view;
    protected $_smarty;
    protected $_request;
    protected $disableLayout = TRUE;

    public function init()
    {

        $app = \TQ\Application::app();
        // set request
        $this->_request = $app->getDispatcher()->getRequest();
        $module = $this->_request->module;

        /**
         * 定义模板目录到模块目录下面views
         */
        if ( strcmp($module, 'Index') != 0) {
            $this->setViewpath(APPLICATION_MODULES_PATH ."/". $module. "/views");
        } else {
            $this->setViewpath(APPLICATION_VIEWS_PATH);
        }

        //Set session.
        $this->_session     = \TQ\Session::getInstance();
        $this->_config      = Di::get("config");
        $this->_view        = $this->getView();

        // Assign var to views
        $this->_view->module       = $this->_module     = $this->getRequest()->getModuleName();
        $this->_view->controller   = $this->_controller = $this->getRequest()->getControllerName();
        $this->_view->action       = $this->_action     = $this->getRequest()->getActionName();
        // Assign session to views too.
        $this->_view->session = $this->_session;


    }

    /**
     * 输出
     * @method display
     * @param  [type]  $view_path
     * @param  [type]  $tpl_vars
     * @return [type]
     */
    public function display( $view_path = null , array $tpl_vars = null)
    {
        $view = $this->getView();
        $application = $this->_config->get('application');

        $ext = ".".$application->view->ext;
        //$action = Util::uncamelize($view->action);
        $action = $view->action;

        $view_path = isset($view_path)?$view_path:$view->controller."/".$action.$ext;

        $tp_vars = $view->_smarty->tpl_vars;

        if (empty($tp_vars['title'])){
            $view->assign( 'title', $application->name); // TITLE
        } else {
            $view->assign( 'title', $tp_vars['title']->value.' - '.$application->name);
        }

        /* 是否使用 Layout */
        if ($this->disableLayout) {
            $view->display( strtolower($view_path), $tpl_vars = null);
        } else {
            $view->assign( 'CONTENT_FOR_LAYOUT', strtolower($view_path)); // 网页内容
            $view->display( 'layouts/'.$this->_layout, $tpl_vars = null);
        }

        exit();
    }

    /**
     * 渲染页面
     * @method render
     * @param  [type] $view_path
     * @param  [type] $tpl_vars
     * @return [type]
     */
    public function render( $view_path = null , array $tpl_vars = null)
    {

        $view = $this->getView();
        $application = $this->_config->get('application');
        $ext = ".".$application->view->ext;

        $view_file = $view->controller.DS.$view_path.$ext;
        $this->display($view_file, $tpl_vars);
    }

    /**
     * 传递参数给 VIEW
     * @method assign
     * @param  [type] $spec
     * @param  [type] $value
     * @return [type]
     */
    public function assign($spec, $value = null)
    {
        $view = $this->getView();
        $view->assign($spec, $value);
    }


}
