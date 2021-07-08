<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use \DB;

use ReallySimpleJWT\Build;
use ReallySimpleJWT\Secret;
use ReallySimpleJWT\Helper\Validator;
use ReallySimpleJWT\Encoders\EncodeHS256;
use ReallySimpleJWT\Token;
use ReallySimpleJWT\Parse;
use ReallySimpleJWT\Jwt;
use ReallySimpleJWT\Decode;


class ReallySimpleJWT

{
    /**
     * Construct this object by extending the basic Controller class
     */
    private static $debug = false; 
    private static $throwerror = false;
    private static $passthrough = true;
    private $validhosts;
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var string$secret = 'Hello&MikeFooBar123';
     */
    public static $samplesecret = "Hello&MikeFooBar123";
    public static $sampletoken = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJPcnRoYW5jIFBBQ1MiLCJzdWIiOiJWaWV3ZXIgVG9rZW4iLCJpYXQiOjE2MTQ3OTY1NDAsInVpZCI6MSwiZXhwIjoxNjE0Nzk2ODQwLCJkYXRhIjoidGVzdCJ9.27Y6A-Ka6jdpt1zU14LTF284klVMz_FEfF_SUnvTuD0";
    protected static $secret = "Hello&MikeFooBar123";

    public function __construct()
    
    {
        $this->validhosts = [];
        $this->validhosts[] = array(
        
            "HTTP_AUTHORIZATION" => "Bearer CURLTOKEN",
            "HTTP_TOKEN" => "wxwzisme",
            "HTTP_ORIGIN" => "medical.ky",
            "HTTP_ORIG_IP" => "0.0.0.0",
        );
        $this->validhosts[] = array(
        
            "HTTP_AUTHORIZATION" => "Bearer CURLTOKEN",
            "HTTP_TOKEN" => "wxwzisme",
            "HTTP_ORIGIN" => "medical.ky",
            "HTTP_ORIG_IP" => "0.0.0.0",
        );

    }
    
    public static function logVariable($var) {
    
        if (self::$debug) {
        
            if (gettype($var) == "array" || gettype($var) == "object") {
                ob_start();
                echo json_encode($var, JSON_PRETTY_PRINT);
                $output = ob_get_clean();
            }
            else {
                $output = $var;
            }
            $output = json_encode(array("NGINX_AUTH" => $var), JSON_PRETTY_PRINT);
            error_log($output);
		}
	
	}


    public static function ValidateTokenString($token) {
    
        $jwt = new Jwt($token, self::$secret);
        $valid = Token::validate($token, self::$secret);
        error_log("Valid:  " . ($valid)?"Valid":"Not Valid");
        return $valid;
    
    }
    
    public static function get_ObjectFromString($JWTString) {
    
        return new Jwt($JWTString, self::$secret);
    
    }
    
    public static function ParseTokenObject($jwt) {
    
    	$parse = new Parse($jwt, new Decode());
		$parsed = $parse->parse();
		return $parsed;
    
    }
    
    public static function Get_JWT_String($name,$data) {
    
        $myip = $_SERVER['REMOTE_ADDR']; 
		$data['ip'] = $myip;

		$payload = [
		
		'iss' => 'JWT',
		'sub' => $name,
		'iat' => time(),
		'uid' => 1,
		'exp' => time() + 60 * 5,
		'data' => $data
		
		];
		
        $JWTString = Token::customPayload($payload, self::$secret);
		
		return $JWTString;
    }
    
    public static function Set_JWT_Cookie($name, $JWTString) {
    
        $COOKIE_PATH = '/';
        $COOKIE_DOMAIN = '.medical.ky';
        $COOKIE_SECURE = true;
        $COOKIE_HTTP = true;
        $SESSION_RUNTIME = 86400;
        $COOKIE_SAMESITE = 'Lax';
    
        
        return setcookie('JWT_'. $name, $JWTString, [
        
            'expires' => time() + $SESSION_RUNTIME,
            'path' => $COOKIE_PATH,
            'domain' => $COOKIE_DOMAIN,
            'secure' => $COOKIE_SECURE ,
            'httponly' =>  $COOKIE_HTTP,
            'samesite' => $COOKIE_SAMESITE,
		]);
		
    }
}

?>