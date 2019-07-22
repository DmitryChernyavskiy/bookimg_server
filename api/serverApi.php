<?php
include_once "events/events.php";
include_once "events/config.php";
include_once "users/users.php";
include_once "ViewApi.php";

class serverApi
{
    protected $requestUri = [];
    protected $requestParams = [];
    protected $method = ''; //GET|POST|PUT|DELETE
    protected $ViewApi;

    public function __construct()
    {
        $this->ViewApi = new ViewApi;

        $url = trim($_SERVER['REQUEST_URI']);
        if ($str=strpos($url, "?")){
            $url=substr($url, 0, $str);
        };
        $this->requestUri = explode('/', $url);
        $this->requestUri = array_splice($this->requestUri, 3);
        $this->requestParams = $_REQUEST;
        $this->method = $_SERVER['REQUEST_METHOD'];
        //error_log ("_1_ ".print_r($this->requestUri, true), 3, "/var/www/html/errors.log");
        //error_log ("_1_ ".print_r($this->requestUri, true), 3, "/home/user10/public_html/errors.log");

        if ($this->method == 'POST' && array_key_exists('HTTP_X_HTTP_METHOD', $_SERVER))
        {
            if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'DELETE')
            {
                $this->method = 'DELETE';
            } else if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'PUT')
            {
                $this->method = 'PUT';
            } else
            {
                throw new Exception("Unexpected Header");
            }
        }
    }

    public function run()
    {   
        if(array_shift($this->requestUri) !== 'api')
        {
            throw new RuntimeException('API Not Found', 404);
        }
        $className = array_shift($this->requestUri);
        if(!class_exists($className))
        {
            throw new RuntimeException('class API Not Found', 405);
        }
        $action = ($this->requestUri ? array_shift($this->requestUri) : null);
        
        $user = $_SERVER['PHP_AUTH_USER'];
        $pass = $_SERVER['PHP_AUTH_PW'];
        
        $requestParams = $this->requestParams;
         error_log("\n_file_get_key_ ".print_r(json_decode(file_get_contents('php://input'), true), true), 3, "/var/www/html/errors.log");
        if(($this->method == 'PUT' || $this->method == 'POST'))
        {
            if(count($requestParams)==0)
            {
                $requestParams = json_decode(file_get_contents('php://input'), true);      
            }
            if($this->method == 'POST')
            {
                $requestParams = json_decode(array_keys($requestParams)[0],true);
            }
            //error_log("\n_file_get_key_ ".print_r($requestParams, true), 3, "/var/www/html/errors.log");
        } 
        error_log("\n_file_get_key_ ".print_r($requestParams, true), 3, "/var/www/html/errors.log");
        if(isset($requestParams['auth']))
        {
            $user = $requestParams['auth']['username'];
            $pass = $requestParams['auth']['password'];
            //error_log("\n_params_ ".$user."/".$pass, 3, "/var/www/html/errors.log");
        }
        
        if(isset($requestParams['params']))
        {      
            $requestParams = $requestParams['params'];
        };
        //error_log ("_01_ ".print_r($this->requestParams, true), 3, "/home/user10/public_html/errors.log");
        //error_log ("_02_ ".print_r($requestParams, true), 3, "/home/user10/public_html/errors.log");
        $function = mb_strtolower($this->method).$action;
        //error_log ("\n_2_function_ ".$function, 3, "/var/www/html/errors.log");
        //error_log ("\n_2_classNam_ ".$className, 3, "/var/www/html/errors.log");
      
        $class = new $className;
        if((!$function) || !method_exists($class, $function))
        {
            throw new RuntimeException('Invalid Method '.$action, 405);
        }

        //error_log("\n_rrr_", 3, "/var/www/html/errors.log");
        $users = new users;
        $validated = (isset($user) && $users->getUser(['user'=> $user,'pass'=> $pass]));
        if (!$validated && $className != "users") {
            //header('WWW-Authenticate: Basic realm="My Realm"');
            //return $this->ViewApi->response('', 401);
        }
        //error_log("\n_ww11_".print_r($_SERVER ,true), 3, "/var/www/html/errors.log");
        //error_log("\n_ww11_".$this->method, 3, "/var/www/html/errors.log");
        error_log("\n_ww2_".$function."(".print_r($requestParams, true), 3, "/var/www/html/errors.log");
        $res = $class->{$function}($requestParams);
        switch ($this->method) {
            case 'GET':
                if(!$res)
                {
                    //error_log ("sdsd ".$function."".print_r($res, true), 3, "/home/user10/public_html/errors.log");
                    return $this->ViewApi->response('Data not found', 404);
                }
                break;
            case 'PUT':                
            case 'POST':
            case 'DELETE':
                if(!$res)
                {
                    return $this->ViewApi->response("Error saving data", 500);
                }
                break;
            default:
                return $this->ViewApi->response("Invalid method", 405);
        }
        return $this->ViewApi->response($res, 200);     
       
    }

}
