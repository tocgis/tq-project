<?php
namespace Qh\Mvc\Controller;

use TQ\Registry as Di;
use Qh\Http\Api\Response;
use Qh\Api\ControllerApi;
use Qh\Logger;

abstract class RestApi extends ControllerApi
{
    private   $response_type  = 'json';   //返回数据格式
    protected $response     = false;    //返回数据
    protected $debug        = false;    //当无结果数据时，也会输出内容
    protected $_lang;
    protected $_u;                      // 接口请求传递回 token 之后获得的用户数据
    protected $_api_auth = true;

    /**
     * 初始化 REST 路由
     * 修改操作 和 绑定参数
     * @method init
     */
    protected function init()
    {
        $dispatcher = Di::get('dispatcher');
        $dispatcher->disableView(); //关闭视图模板引擎
        $dispatcher->throwException(false);
        $dispatcher->setErrorHandler(array('\\Qh\\Exception\\ErrorHandler','ApiHandler'));

        $this->_action = $action = $this->_request->getActionName();

        $app = \TQ\Application::app();

        $this->_environ = $app->environ(); // 当前环境
        $this->_lang    = Di::get('lang');

        //对应REST_Action
        $method = $this->method = $this->_request->getMethod();

        $rest_action =  $action.'_'.strtolower($method);

        $this->uri   = '/'. strtolower($this->_request->getControllerName())
                          .'/'.strtolower($this->_request->getActionName());
        $this->uri  = $this->_request->getRequestUri();

        /*检查该action操作是否存在，存在则修改为REST接口*/
        if (method_exists($this, $rest_action . 'Action')) {

            /*存在对应的操作*/
            $this->_request->setActionName($rest_action);
        }
        elseif (!method_exists($this, $action . 'Action')) {

            /*action和REST_action 都不存在*/
            $response = Response::getInstance();
            $response->response['message'] = array(
                'code' => 404,
                'content' => $this->_request->getControllerName().'Controller: '.$this->_action.'Action Not Found!',
            );
            Header("HTTP/1.1 404 Not Found");
            exit;
        }

        //put请求写入GOLBAL中
        global $_PUT;

        //die($method); $type;

        if ($method == 'PUT'){
            parse_str(file_get_contents('php://input'), $_PUT);
            $token = !empty($_PUT['auth_token'])?$_PUT['auth_token']:null;
            //unset($_PUT['auth_token']);
            $this->_request->_put = $GLOBALS['_PUT'] = $_PUT ;
        }
        elseif ($method == 'DELETE') {
            parse_str(file_get_contents('php://input'), $GLOBALS['_DELETE']);
            $this->_request->_delete = $_DEL = $GLOBALS['_DELETE'];
            $token = $_DEL['auth_token'];
        }
        else {
            $token = $this->_request->get('auth_token');
        }

        if (!empty($token)) {
            $this->_u = \Api\User::checkToken($token);
        }

        /**
         * 安全校验及关闭自动渲染视图
         */
        if (($this->_environ != 'develop')) {
            $this->_doVerify();
        }

        if (method_exists($this,'_init'))
            $this->_init();

    }

    /**
     * 返回并结束程序，调用析构输出API结果
     * @method response
     * @param  [type]   $code    [description]
     * @param  [type]   $message [description]
     * @param  string   $result  [description]
     * @param  string   $page    [description]
     * @return [type]            [description]
     */
    protected function response($code, $message, $result = '', $page = '')
    {
        $this->_lang    = Di::get('lang');
        $response = Response::getInstance();
        $response->response = array(
            'message' => array(
                'code'=>$code,
                'content'=>$this->_lang->translate($message),
                //'content'=>$message,
            )
        );
        if ($page) {
            $response->response['page'] = $page;
        }
        if ($result) {
            $response->response['result'] = $result;
        }
        //die();
    }

    /**
     * 结束时自动输出信息
     * @method __destruct
     * @access private
     */
    public function __destruct()
    {
        $response = Response::getInstance();
        if ($response->response == false)
        {
            // $response->response['message']['code']          = 20000;
            // $response->response['message']['content']       = 'response no data!';
            // $response->response['message']['http_method']   = $this->method;
            // $response->response['message']['http_uri']      = $this->uri;
            // $response->response['message']['time']          = date('Y-m-d H:i:s');
            // Response::outputResponse();

        } else {
            $response->response['message']['http_method']   = $this->_request->getMethod();
            $response->response['message']['http_uri']      = $this->_request->getRequestUri();
            $response->response['message']['time']          = date('Y-m-d H:i:s');

            $this->_response = $this->getResponse();
            $this->_response->setHeader('Server','KanbanApi');
            $this->_response->setHeader('Access-control-allow-methods','GET, PUT, POST, DELETE');
            $this->_response->setHeader('X-kanban-version','1.0');
            $this->_response->setHeader('X-server-time',time());
            $this->_response->setHeader('X-kanban-environment',\TQ\ENVIRON);
            $this->_response->setHeader('X-Powered-By','TQ/1.0');
            $this->_response->response();

            Response::outputResponse();
        }


    }


    /**
     * 开启数据校验，增加数据安全性
     * @method _doVerify
     * @return void
     */
    private function _doVerify(){
        //return true;
        if ( !empty($this->_config->develop)) return true;
        $apiKey = Di::get('config')->api->key;

        $apiVersion = $this->_request->getParam('ver'); // 当前使用的API版本,默认为空


        $verifyStr  = $this->_request->get('verify')?
                      $this->_request->get('verify'):$this->_request->getPost('verify');
        $params     = $this->_request->getVerifyParams(); // 获得未经过滤的参数，用于后面的加密拼接

        //print_r($verifyStr);

        if (($this->_action == 'noVerify')

        ){
            return true;
        }

        if ( (!empty($verifyStr)) /* and ($apiVersion > 0)*/ ) {

            $listParam = array();
            $i = 0;
            foreach ($params as $key => $value ) {
                if ( (stripos($key, "/api/") > -1 )
                    //|| ($key == "controller")
                    || (($key == "controller") && ($this->_controller == $params['controller']))
                    //|| ($key == "module")
                    || (($key == "module") && ($this->_module == $params['module']))
                    //|| ($key == "action")
                    || (($key == "action") && ($this->_action == $params['action']))
                    //|| ($key == "save")
                    || ($key == "ver")
                    || ($key == "verify")
                ) {

                } else {
                    $listParam[$i]= ($key . '=' . $value);
                    $i++;
                }
            }
            sort($listParam);

            $verify = "";
            for ($i=0; $i<count($listParam);$i++) {
                $verify = $verify . trim($listParam[$i]) ;
            }
            //echo $verify .  PHP_EOL;
            $newverify = md5(($verify) . md5($apiKey)); //密钥
            // echo $newverify.PHP_EOL;
            // echo $verifyStr.PHP_EOL;

            if (trim($verifyStr) == $newverify){
                $this->_newVerify = $newverify;
                return true;
            } else {
                if ((\TQ\ENVIRON == 'product')) $newverify = '';
                $this->response(20010,'Data verify not open'.' ! '.$newverify);

            }
            exit();
        } else {
            $this->response(20010,'Data verify not open'.' . ');
            die();
        }
    }

    public function getUser($token = null)
    {
        $token = $this->_request->get('auth_token');
        if (!empty($token)) {
            $this->_u = \Api\User::checkToken($token);
        }
        if (Di::has('u')) {
            return Di::get('u');
        }
    }



}
