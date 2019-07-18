<?php

class ViewApi
{
    public function __construct()
    {
        header("Access-Control-Allow-Orgin: *");
        header("Access-Control-Allow-Methods: *");
	//header("Content-Type: application/json");
	header("Access-Control-Max-Age: 31536000");
	header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type,token, Access-Control-Allow-Headers, Origin,Accept, X-Requested-With, Content-Type, Access-Control-Request-Method, Access-Control-Request-Headers");
	header("Access-Control-Expose-Headers: Location");
    }

    public function requestStatus($code)
    {
        $status = array(
            200 => 'OK',
            401 => 'Unauthorized',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            500 => 'Internal Server Error',
        );
        return ($status[$code]) ? $status[$code] : $status[500];
    }

    public function response($data, $status = 500)
    {
        error_log ("_response_".print_r(json_encode($data), true), 3, "/var/www/html/errors.log");
        header("HTTP/1.1 " . $status . " " . $this->requestStatus($status));
        return json_encode($data);
    }
}
