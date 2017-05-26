<?php
namespace Qh\Http\Api;

use TQ\Registry as Di;

/**
 * API输出控制
 */
class Response
{
    private static $_instance;
    public  $response       = false;
    public  $response_type  = 'json';
    public  $_lang;

    private function __construct()
    {
        $this->_lang  = Di::get('lang');

    }

    public static function getInstance()
    {
        if(!(self::$_instance instanceof self)){
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    /**
     * 定义标准的API输出格式
     *
     * @method apiResponse
     * @param  integer     $code
     * @param  string      $message
     * @param  array       $result
     * @param  array       $page
     * @return mixed
     */
    public static function setResponse($code = 10000, $message ='', $result = array(), $page = array())
    {
        $response = self::getInstance();
        $response->response = array(
            'message' => array(
                'code'=>$code,
                'content'=>self::t($message),
            )
        );
        if ($page) {
            $response->response['page'] = $page;
        }
        if ($result) {
            $response->response['result'] = $result;
        } else {
            if ($code == 10000) {
                $response->response['result'] = [];
            }
        }

        return $response->response;
    }

    /**
     * 立即输出结果
     *
     * @param  integer $code
     * @param  string  $message
     * @param  [type]  $result
     * @param  [type]  $page
     * @return [type]
     */
    public static function exitResponse($code = 10000, $message ='', array $result = [], array $page = [])
    {
        self::setResponse($code, $message, $result, $page);
        exit();
    }

    public static function getResponse()
    {
        $response = self::getInstance();
        return $response->response;
    }

    /**
     * 输出结果
     * @method outResponse
     * @return [type]
     */
    public static function outputResponse()
    {

        $response = self::getInstance();
        switch ($response->response_type)
        {
            case 'xml':
                header('Content-type: application/xml');
                echo \Parse\Xml::encode($response->response);
                break;

            case 'json':
            default:
                header('Content-type: application/json');
                die (json_encode($response->response ));     //unicode不转码
        }
    }

    public static function t($message)
    {
        $lang = Di::get('lang');
        if ($lang) {
            $message = @$lang->translate($message);
        } 
        return $message;
    }

}
