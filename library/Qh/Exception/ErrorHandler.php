<?php

namespace Qh\Exception;

use Qh\Http\Api\Response;

class ErrorHandler
{
    public static function ApiHandler($errno, $errstr, $errfile, $errline)
    {

        switch ($errno) {
            case \TQ\ERR\AUTOLOAD_FAILED:
            case \TQ\ERR\NOTFOUND\MODULE:
            case \TQ\ERR\NOTFOUND\CONTROLLER:
            case \TQ\ERR\NOTFOUND\ACTION:
                header("Not Found");
                break;

            default:
                break;
        }

        $response = array(
            'error' => array(
                'code'=>$errno,
                'content'=>$errstr,
                'line'=>$errline,
                'file'=>$errfile
            )
        );

        //return Response::setResponse(500,'error hapen',$response);
        header('Content-type: application/json');
        exit(json_encode( $response ));
        die();

    }
}
