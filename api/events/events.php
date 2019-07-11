<?php
include_once "./libs/MySQL.php";
include_once "config.php";

class events
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

    public function execution($action, $paramsUrl, $paramsPost)
    {
        return $this->{$action}();
    }

    public function getEvents($var)
    {
        $date_start = $var['date_start'];
        $date_start = $var['date_end'];
        $id_user = $var['id_user'];
        $id_room = $var['id_room'];
        $query = $this->DB->connect()->setTableName(PREFIX_TABLE_MYSQL_DB."event")->SetFild("date_create")->SetFild("note");
        if (isset($id_user) && $id_user!=0)
        {
            $query->setConditions("id_user", $id_user);
        };
        if (isset($id_room) && $id_room!=0)
        {
            $query->setConditions("id_room", $id_room);
        };
        $query->select()->setTableName(PREFIX_TABLE_MYSQL_DB."event_child")->SetFild("id")->SetFild("date_start")->SetFild("date_end")->
        setJoinConditions(PREFIX_TABLE_MYSQL_DB."event.id = ".PREFIX_TABLE_MYSQL_DB."event_child.id_event")->leftJoin()->
        setTableName(PREFIX_TABLE_MYSQL_DB."rooms")->SetFild("name")->
        setJoinConditions(PREFIX_TABLE_MYSQL_DB."event.id_room = ".PREFIX_TABLE_MYSQL_DB."rooms.id")->leftJoin();

        $res =$query->execution();
        return $res;

    }

    public function postEvent($var)
    {
        $id_user = $var['id_user'];
        $id_room = $var['id_room'];
        $date_create = $var['date_create'];
        $note = $var['note'];

        if (isset($id_user) && isset($id_room) && isset($date_create) && isset($note))
        {
            $this->DB->connect()->setTableName(PREFIX_TABLE_MYSQL_DB."event")->SetFild("id_user", $id_user)->SetFild("id_room", $id_room)->SetFild("date_create", $date_create)->SetFild("note", $note)->insert();

            $res =$this->DB->execution();
            return $res;
        }
        return null;
    }

    public function postEventChild($var)
    {
        $id = $var['id'];
        $id_event = $var['id_event'];
        $date_start = $var['date_start'];
        $date_end = $var['date_end'];

        if (isset($id) && isset($id_event) && isset($date_start) && isset($date_end))
        {
            $this->DB->connect()->setTableName(PREFIX_TABLE_MYSQL_DB."event_child")->SetFild("id_event", $id_event)->SetFild("id", $id)->SetFild("date_start", $date_start)->SetFild("date_end", $date_end)->insert();

            $res =$this->DB->execution();
            return $res;
        }
        return null;
    }

    public function deleleEvent($var)
    {
        $id = $var['id'];

        if (isset($id))
        {
            $this->DB->connect()->setTableName(PREFIX_TABLE_MYSQL_DB."event")->setConditions("id", $id)->delete();

            $res =$this->DB->execution();
            if($res)
            {
                $res_arr['id_event'] = $id;
                return $this->delEventChild($res_arr);
            }
            
            return $res;
        }
        return null;
    }

    public function deleteEventChild($var)
    {
        $id = $var['id'];
        $id_event = $var['id_event'];

        if (isset($id_event))
        {
            $this->DB->connect()->setTableName(PREFIX_TABLE_MYSQL_DB."EventChild")->setConditions("id_event", $id_event)->delete();

            $res =$this->DB->execution();
            return $res;
        } 
        elseif (isset($id))
        {
            $this->DB->connect()->setTableName(PREFIX_TABLE_MYSQL_DB."EventChild")->setConditions("id", $id)->delete();

            $res =$this->DB->execution();
            return $res;
        }
        return null;
    }


    private function getColumrVal($table, $key)
    {
        $res_arr = array();

        $query = $this->DB->connect();
        $arr = $query->setTableName(PREFIX_TABLE_MYSQL_DB.$table)->SetFild($key)->select()->execution();
        foreach($arr as $val)
        {
            $res_arr[] = $val[$key];
        }
        return $res_arr;
    }

    public function getDataDescription()
    {
        $desc = array();
        $desc['rooms'] = $this->getColumrVal("rooms", "name");
       
        return $desc;

    }
}