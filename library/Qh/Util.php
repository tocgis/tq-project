<?php
namespace Qh;

class Util
{
    //保存类实例的静态成员变量
    private static $_instance;

    private function __construct()
    {
    }

    /**
     * Get an instance of the {@link Inflector} class.
     *
     * @return object
     */
    public static function getInstance()
    {
        if(!(self::$_instance instanceof self)){
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    /**
     * Convert a camelized string to a lowercase, underscored string.
     * 把驼峰法命名转换为小写并用下划线连接
     *
     * @param string $s string to convert
     * @return string
     */
    public function uncamelize($s)
    {
        $normalized = '';

        for ($i=0,$n=strlen($s); $i<$n; ++$i)
        {
            if (ctype_alpha($s[$i]) && self::is_upper($s[$i]))
                $normalized .= '_' . strtolower($s[$i]);
            else
                $normalized .= $s[$i];
        }
        return trim($normalized,' _');
    }

    /**
     * Turn a string into its camelized version.
     * 把一个字符串转换为驼峰法命名
     *
     * @param string $s string to convert
     * @return string
     */
    public function camelize($s)
    {
        $s = preg_replace('/[_-]+/','_',trim($s));
        $s = str_replace(' ', '_', $s);

        $camelized = '';

        for ($i=0,$n=strlen($s); $i<$n; ++$i)
        {
            if ($s[$i] == '_' && $i+1 < $n)
                $camelized .= strtoupper($s[++$i]);
            else
                $camelized .= $s[$i];
        }

        $camelized = trim($camelized,' _');

        if (strlen($camelized) > 0)
            $camelized[0] = strtolower($camelized[0]);

        return $camelized;
    }


    /**
     * Determines if a string contains all uppercase characters.
     * 确定一个字符串包含大写字母
     *
     * @param string $s string to check
     * @return bool
     */
    public static function is_upper($s)
    {
        return (strtoupper($s) === $s);
    }

    /**
     * Determines if a string contains all lowercase characters.
     * 确定一个字符串是否包含所有的小写字符。
     *
     * @param string $s string to check
     * @return bool
     */
    public static function is_lower($s)
    {
        return (strtolower($s) === $s);
    }

    /**
     * Convert a string with space into a underscored equivalent.
     * 将一个空格转换成一个带有空格的字符串。
     *
     * @param string $s string to convert
     * @return string
     */
    public function underscorify($s)
    {
        return preg_replace(array('/[_\- ]+/','/([a-z])([A-Z])/'),array('_','\\1_\\2'),trim($s));
    }

    public function keyify($class_name)
    {
        return strtolower($this->underscorify(denamespace($class_name))) . '_id';
    }

    /**
      * Recursively create a directory
      */
    public static function createDir($dir, $mode=0777)
    {
        if(!is_dir($dir)) {
            self::createDir(dirname($dir), $mode);
            mkdir($dir, $mode);
        }
        return true;
    }

    /**
     * 随机生成Token（30位随机数）
     */
    public static function getToKen($leng = 30){
        $str = '';
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol)-1;
        for($i=0;$i<$leng;$i++){
            //$str .= substr($strPol, rand(0,$max),1);
            $str .= $strPol[rand(0,$max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
        }
        return $str;
    }

    /**
     * 随机
     * @param unknown $length
     * @return string
     */
    public static function randomkeysNumber($length = 6)
    {
        $arr = Array('1','2','3','4','5','0','6','7','8','9');//定义数组
        shuffle($arr);//打乱元素顺序
        $rand = array_slice($arr,0,6);//取前四个元素
        $result=implode('',$rand);//转成字符串
        return $result;
    }

    public static function uuid($prefix = '') {
        $chars = md5(uniqid(mt_rand(), true));
        $uuid = substr($chars, 0, 8) . '-';
        $uuid .= substr($chars, 8, 4) . '-';
        $uuid .= substr($chars, 12, 4) . '-';
        $uuid .= substr($chars, 16, 4) . '-';
        $uuid .= substr($chars, 20, 12);
        return $prefix . $uuid;

    }

    /**
     * 生成不重复的订单号
     *
     * @method orderNumber
     * @param  [type]      $seed [description]
     * @return [type]      [description]
     */
    public static function orderNumber($seed)
    {
        return $seed.date('YmdHis').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 6);
    }

    public static function cut_str($string, $sublen, $start = 0, $code = 'UTF-8')
    {
        if($code == 'UTF-8')
        {
            $pa = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/";
            preg_match_all($pa, $string, $t_string);
            if(count($t_string[0]) - $start > $sublen) return join('', array_slice($t_string[0], $start, $sublen));
            return join('', array_slice($t_string[0], $start, $sublen));
        }
        else
        {
            $start = $start*2;
            $sublen = $sublen*2;
            $strlen = strlen($string);
            $tmpstr = '';
            for($i=0; $i< $strlen; $i++)
            {
                if($i>=$start && $i< ($start+$sublen))
                {
                    if(ord(substr($string, $i, 1))>129)
                    {
                        $tmpstr.= substr($string, $i, 2);
                    }
                    else
                    {
                        $tmpstr.= substr($string, $i, 1);
                    }
                }
                if(ord(substr($string, $i, 1))>129) $i++;
            }
            //if(strlen($tmpstr)< $strlen ) $tmpstr.= "...";
            return $tmpstr;
        }
    }


}
