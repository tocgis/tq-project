<?php

namespace Service;
use Qh\Http\Upload\Upload;

class File extends Upload
{
    public $_upload;
    public $_instance;

    /**
     * 上传配置
     * @var array
     */
    private $config = array(
        'pathtype'=>'',
        'mimes' => array(), //允许上传的文件MiMe类型
        'maxSize' => 0, //上传的文件大小限制 (0-不做限制)
        'exts' => array(
            'jpg', 'gif', 'png', 'jpeg', "bmp", "mp4", "mp3", "wav", "mov", "avi", "wmv" ,"wma", "mobi","doc","docx","xlsx","xls","csv",
            "ppt","pptx","rp","pdf","wps","rtf","txt","pps","vsd"
        ), //允许上传的文件后缀
        'autoSub' => true, //自动子目录保存文件
        'subName' => array('date', 'Ymd'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
        'rootPath' => APP_PATH .'/../../imghost/public/upfile/', //保存根路径
        'savePath' => '', //保存路径
        'saveName' => array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
        'saveExt' => '', //文件保存后缀，空则使用原后缀
        'replace' => false, //存在同名是否覆盖
        'hash' => true, //是否生成hash编码
        'callback' => false, //检测文件是否存在回调，如果存在返回文件信息数组

    );

    /**
     * 构造
     *
     * @method __construct
     */
    public function __construct($config = []) {
        $this->_upload = new Upload();

        $config = array_merge($this->config, $config);
        $this->_upload->__construct($config);
        //parent::__construct($config);
    }

    public static function instance($config = [])
    {
        if(!(self::$_instance instanceof self)){
            self::$_instance = new self($config);
        }
        return self::$_instance;
    }

}
