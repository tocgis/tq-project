<?php

namespace Qh\Http;

class Request extends \TQ\Request\Http
{

    private $_posts;
    private $_params;
    private $_query;
    private $_put;

    /**
     * [get description]
     * @param  [type]  $param
     * @param  boolean $flag
     * @return [type]
     */
    public function get($param, $flag=false) {
        if ($this->isPut()) {
            $put = $this->getPut();
            $pData = empty($put)?:$put;
            if (isset($pData[$param])) {
                $res =  @htmlspecialchars($pData[$param],ENT_QUOTES,'UTF-8');
                $result = !empty($res)?$res:null;
                return $result;
            } else {
                return null;
            }
        } elseif ($this->isDelete()) {
            $delete = $this->getDelete();
            $pData = empty($delete)?:$delete;
            if (isset($pData[$param])) {
                $res =  @htmlspecialchars($pData[$param],ENT_QUOTES,'UTF-8');
                $result = !empty($res)?$res:null;
                return $result;
            } else {
                return null;
            }
        }
        else {
            if (!empty($param) && $flag == false ) {
                $param_data = parent::get($param);
                if (is_array($param_data)) {
                    return $param_data;
                } else {
                    $res = htmlspecialchars($param_data,ENT_QUOTES,'UTF-8');
                    $result = !empty($res)?$res:null;
                    return $result;
                }

            } else {
                return parent::get($param);
            }
        }

    }

    public function getPost()
    {
        if ($this->_posts) {
            return $this->_posts;
        }

        $this->_posts = $this->filter_params(parent::getPost());
        return $this->_posts;
    }

    public function getPut()
    {
        if ($this->_put) {
            return $this->_put;
        }
        if ($this->isPut()) {
            parse_str(file_get_contents('php://input'), $put);
            $this->_put = !empty($put)?$this->filter_params($put):[];
            return $this->_put;
        }
    }

    public function getDelete()
    {
        if (isset($this->_delete)) {
            return $this->_delete;
        }
        if ("DELETE" ==strtoupper($this->getMethod()) ) {
            parse_str(file_get_contents('php://input'), $delete);
            $this->_delete = !empty($delete)?$this->filter_params($delete):[];
            return $this->_delete;
        }

    }

    public function getParams()
    {
        if ($this->_params) {
            return $this->_params;
        }

        $this->_params = $this->filter_params(parent::getParams());
        return $this->_params;

    }

    public function getQuery()
    {
        if ($this->_query) {
            return $this->_query;
        }

        $this->_query = $this->filter_params(parent::getQuery());
        return $this->_query;

    }

    private static function filter_params($params)
    {
        if (!empty($params)) {
            array_walk_recursive($params, function(&$value, $key){
                $value=htmlspecialchars($value,ENT_QUOTES,'UTF-8');
            });
        }

        return $params;
    }

    public function isDelete()
    {
        if ($this->method == 'DELETE') return true;
    }

    public static function getHeader($header)
    {

        // Try to get it from the $_SERVER array first
        $temp = 'HTTP_' . strtoupper(str_replace('-', '_', $header));
        if (isset($_SERVER[$temp])) {
            return $_SERVER[$temp];
        }

        // This seems to be the only way to get the Authorization header on
        // Apache
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if (isset($headers[$header])) {
                return $headers[$header];
            }
            $header = strtolower($header);
            foreach ($headers as $key => $value) {
                if (strtolower($key) == $header) {
                    return $value;
                }
            }
        }

        return false;
    }

    /**
     * 获得未经过滤的参数
     * 该方法主要用于进行APP端参数拼接校验
     * @method getVerifyParams
     * @return array
     */
    public function getVerifyParams(){
        $params = array_merge(
                    array(),
                    //parent::getParams(), // RESTful 模式去除该参数
                    parent::getQuery(),
                    parent::getPost(),
                    $this->getPut(),
                    $this->getDelete()
        );
        return $params;
    }

    public function __set($param,$value)
    {
        return $this->$param = $value;
    }


}
