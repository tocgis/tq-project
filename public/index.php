<?php
ini_set('display_errors',      'on'); //由环境自行设置 是否显示错误，除非环境不支持显示错误，而又需要显示错误时，才设置该项

define('APP_PATH', dirname(dirname(__FILE__)));
define('DS',DIRECTORY_SEPARATOR);
define('APPLICATION_PATH',    APP_PATH.DS.'application');
define('PRJ_PATH', realpath('../../../'));

ini_set('tq.use_spl_autoload', 'on');
ini_set('tq.use_namespace',    'on');
//ini_set('tq.library',       PRJ_PATH.DS.'library');

$application = new TQ\Application(APPLICATION_PATH . "/configs/application.ini");
$application->bootstrap()
            ->run();
