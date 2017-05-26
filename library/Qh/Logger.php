<?php
namespace Qh;

use Qh\Util;

class Logger extends \SplFileObject
{
    const RED    = '1;31m';
    const GREEN  = '1;32m';
    const PURPLE = '1;35m';
    const CYAN   = '1;36m';
    const WHITE  = '1;37m';

    const RESET_SEQ = "\033[0m";
    const COLOR_SEQ = "\033[";
    const BOLD_SEQ  = "\033[1m";

    private static $start_time;

    private static $memory;

    private static $logger_instance;

    public static function startLogging()
    {
        self::$start_time = microtime(true);
        self::$memory = memory_get_usage(true);
        $buffer = "\n".self::COLOR_SEQ . self::GREEN
                . "Started at : [" . date('H:i:s d-m-Y', time()) . "]"
                . self::RESET_SEQ;
        static::getLogger()->testLog();
        static::getLogger()->log($buffer);
    }

    public static function stopLogging()
    {
        $buffer = self::COLOR_SEQ . self::GREEN . "Completed in "
            . number_format((microtime(true) - self::$start_time) * 1000, 0)
            . "ms | "
            . "Mem Usage: ("
            . number_format( (memory_get_usage(true) - self::$memory) / (1024), 0, ",", "." )
            ." kb)"
            . self::RESET_SEQ."\n";
        static::getLogger()->log($buffer);
    }

    public static function getLogger($env=null, $open_mode="a")
    {
        if (static::$logger_instance) return static::$logger_instance;
        $env = !empty($env) ? $env: \TQ\ENVIRON;

        $date = date('Ymd',time());
        $filename = APP_PATH . '/data/logs' . DS . $env . '_'. $date.'.log';
        static::$logger_instance = new static($filename,$open_mode);
        return static::$logger_instance;
    }

    public function __construct($filename=null, $open_mode = "a")
    {
        $date = date('Ymd',time());
        $filename = $filename ?: APP_PATH . "/data/logs" . DS . \TQ\ENVIRON .'_'. $date. ".log";
        parent::__construct($filename, $open_mode);
    }

    public function log($string)
    {
        if (is_array($string)) {
            $string = print_r($string,true);
        }
        $this->fwrite($string . "\n");
    }

    public function errorLog($string)
    {
        $this->log(COLOR_SEQ . "1;37m" . "!! WARNING: " . $string . RESET_SEQ);
    }

    public function logQuery($query, $class_name=null, $parse_time = 0, $action='Load')
    {
        $class_name = $class_name ?: 'Sql';
        $buffer = self::COLOR_SEQ . self::PURPLE . "$class_name $action ("
            . number_format($parse_time * 1000, '4')
            . "ms)  " . self::RESET_SEQ . self::COLOR_SEQ . self::WHITE
            .   $query . self::RESET_SEQ;

        $this->log($buffer);
    }

    public function logRequest($request)
    {
        $this->log("Processing "
            . $request->getModuleName() .'\\'
            . $request->getControllerName()
            . "Controller#"
            . $request->getActionName()
            . " (for {$request->getServer('REMOTE_ADDR')}"
            . " at " . date('Y-m-d H:i:s') .")"
            . " [{$request->getMethod()}]"
        );
        $params = array();
        // $params = array_merge($params,
        //     $request->getParams(),
        //     $request->getPost(),
        //     $request->getFiles(),
        //     $request->getQuery(),
        //     $request->getPut(),
        //     $request->getDelete()
        // );
        $params = array(
            'query'=>$request->getQuery(),
            'param'=>$request->getParams(),
            'post'=>$request->getPost(),
            'file'=>$request->getFiles(),
            'put'=>$request->getPut(),
            'delete'=>$request->getDelete()
        );

        $this->log("Parameters: " . print_r($params, true));
        $servers = array(
            $request->getServer()
        );
        //$this->log("Server: " .print_r($servers,true));
        $this->logHeader($request->getServer());
    }

    public function logHeader($server) {
        foreach ($server as $key=>$value){
            if (strpos($key,'HTTP') === false ) {
                unset($server[$key]);
            }
        }
        $this->log("Header: " .print_r($server,true));

    }

    public function logException($exception)
    {
        $this->log(
            get_class($exception) . ": "
            . $exception->getMessage()
            . " in file "
            . $exception->getFile()
            . " at line "
            . $exception->getLine()
        );
        $this->log($exception->getTraceAsString());
    }

    public static function logError($errno, $errstr, $errfile, $errline)
    {
        $date = date('Ymd',time());
        $filename = APP_PATH . '/data/logs' . DS . 'error' . '_'. $date.'.log';

        $loger = new static($filename);

        $request_uri = ($_SERVER['REQUEST_URI']);
        $cur_time = date('H:i:s',time());
        $string = "[$cur_time]: \n"
                . "URI \t: $request_uri\n"
                . "[$errno]  \t: $errstr\n"
                . "file\t: $errfile\n"
                . "line\t: $errline\n"
                . "";

        $loger->fwrite($string . "\n");

    }

    public function testLog()
    {
        $logPath = APP_PATH.'/data/logs';
        if (!file_exists($logPath)) {
            Util::createDir($logPath);
        }
    }

}
