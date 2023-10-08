<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public static function response($code, $message = "", $data = array()){
        
        switch($code){
            case 200: //successful API call
                $defaultMessage = 'Success.';
                break;
            case 400: //when pass in invalid parameters
                $defaultMessage = 'Bad Request.';
                break;
            case 401: //authentication failed
                $defaultMessage = 'Unauthorized.';
                break;
            case 403: //no authentication to call this API
                $defaultMessage = 'Forbidden.';
                break;
            case 404: //requested data not found
                $defaultMessage = 'Not Found.';
                break;
            default:
                $code = 400;
                $message = 'Bad Request.';
                break;
        }
        
        if(strlen($message) == 0){
            $message = $defaultMessage;
        }
        
		$return['message'] = $message;
        $return['code'] = $code;
        $return['data'] = $data;
        
        response()->json($return, $code)->send();
        exit;
    }
}
