<?php
include_once "./libs/MySQL.php";
include_once "config.php";

class users
{
    private $DB;
    function __construct()
    {
        $this->DB = new MySQL(TYPE_MYSQL_DB, HOST_MYSQL_DB, NAME_MYSQL_DB, USER_MYSQL_DB, PASS_MYSQL_DB);
    }
    
    function __destruct()
    {
        unset($this->DB);
    }
 
    public function getAllUsers()
    {
        $query = $this->DB->connect()->setTableName(PREFIX_TABLE_MYSQL_DB."users")->SetFild("id")->SetFild("name")->SetFild("password")->SetFild("email")->SetFild("blocked", $blocked)->SetFild("role");
        $res = $query->Select()->execution();
        return $res;
    } 

    public function getListUsers()
    {
        $id = $var['id'];
        $query = $this->DB->connect()->setTableName(PREFIX_TABLE_MYSQL_DB."users")->SetFild("id")->SetFild("name")->SetFild("password")->SetFild("email")->SetFild("blocked", $blocked);
        if (isset($id) && $id!="")
        {
            $query->setConditions("id", $id);
        }
 
        $res = $query->Select()->execution();
        return $res;
    }
    
    public function getUser($var)
    {
        $user = $var['user'];
        $password = $var['password'];
        if (!isset($user) || $user=="")
        {
           return null;
        };

        /*$test = ['user10'=>'777'];
        if($test[$user] == $pass)
        {
            return true;
        }*/
        
        $query = $this->DB->connect()->setTableName(PREFIX_TABLE_MYSQL_DB."users")->SetFild("id")->SetFild("name")->SetFild("password")->SetFild("role")->setConditions("name", $user);
        if (isset($password) && $password!="")
        {
            $query->setConditions("password", $password);
        }
        $res = $query->Select()->execution();
        error_log ("_res_".print_r($res, true), 3, "/var/www/html/errors.log");
        if (!$res || count($user)==0)
        {
            return null;
        }

        return $res;
    }

    public function postUser($var)
    {
        $user = $var['user'];
        $password = $var['password'];
        $blocked = $var['blocked'];
        $email = $var['email'];
        $role = $var['role'];
        
        if (isset($user) && isset($password) && isset($email) && !$this->getUser($var))
        {
            $blocked = ($blocked ? 1 : 0);
            $role = (isset($role) ? $role : 'user');
            $res = $this->DB->connect()->setTableName(PREFIX_TABLE_MYSQL_DB."users")->SetFild("name", $user)->SetFild("password", $password)->SetFild("blocked", $blocked)->SetFild("email", $email)->SetFild("role", $role)->insert()->execution();
            //error_log ("_4_ ".print_r($res, true), 3, "/home/user10/public_html/errors.log");
            return true;
        }
        return null;
    }
    
    public function putUser($var)
    {
        $id = $var['id'];
        
        $user = $var['user'];
        $password = $var['password'];
        $blocked = $var['blocked'];
        $email = $var['email'];
        
        if (isset($id))
        {
            $blocked = ($blocked ? 1 : 0);
            $res = $this->DB->connect()->setTableName(PREFIX_TABLE_MYSQL_DB."users")->SetFild("name", $user)->SetFild("password", $password)->SetFild("blocked", $blocked)->SetFild("email", $email)->setConditions("id", $id)->update()->execution();
            return true;
        }
        return null;
    }
}