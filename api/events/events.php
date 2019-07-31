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

    public function getEvents($var, $id = '0', $admin = false)
    {
        $date_start = $var['date_start'];
        $date_end = $var['date_end'];
        $id_user = $var['id_user'];
        $id_room = $var['id_room'];
	$id_event = $var['id_event'];
        $query = $this->DB->connect()->setTableName(PREFIX_TABLE_MYSQL_DB."event")->SetFild("date_create")->SetFild("id_user");
        if (isset($id_event) && $id_event!=0)
        {
            $query->SetFild("note")->setConditions("id", $id_event);
        };
        if (isset($id_user) && $id_user!=0)
        {
            $query->setConditions("id_user", $id_user);
        }
        elseif (!$admin && isset($id_event) && $id_event!=0 && isset($id) && $id!="")
        {
            $query->setConditions("id_user", $id);
        };
        if (isset($id_room) && $id_room!=0)
        {
            $query->setConditions("id_room", $id_room);
        };
        $query->select()->setTableName(PREFIX_TABLE_MYSQL_DB."event_child")->SetFild("id_event")->SetFild("id")->setDayOfMonth("date_start", "day_of_month")->SetFild("date_start")->SetFild("date_end");
        if (isset($date_start) && $date_start!="")
        {
            $query->setConditions("date_start", $date_start,">=");
        };
        if (isset($date_end) && $date_end!="")
        {
            $query->setConditions("date_end", $date_end, "<");
        };
        $query->setJoinConditions(PREFIX_TABLE_MYSQL_DB."event.id = ".PREFIX_TABLE_MYSQL_DB."event_child.id_event")->InnerJoin()->
        setTableName(PREFIX_TABLE_MYSQL_DB."rooms")->SetFild("name")->
        setJoinConditions(PREFIX_TABLE_MYSQL_DB."event.id_room = ".PREFIX_TABLE_MYSQL_DB."rooms.id")->InnerJoin()->group();

        $res =$query->execution();
        return $res;

    }
    
    public function getCheckPeriod($var)
    {
        $date_start = $var['date_start'];
        $date_end = $var['date_end'];
        $id_room = $var['id_room'];
        $id_event = $var['id_event'];

        if (isset($date_start) && isset($date_end) && isset($id_room))
        {
            $query = $this->DB->connect()->setTableName(PREFIX_TABLE_MYSQL_DB."event_child")->SetFild("id")->setConditions("date_start", $date_end,"<")->setConditions("date_end", $date_start, ">");
            if (isset($id_event) && $id_event!=0)
            {
                $query->setConditions("id_event", $id_event, "<>");
            }
            $query->Select()->setTableName(PREFIX_TABLE_MYSQL_DB."event")->setConditions("id_room", $id_room)->setJoinConditions(PREFIX_TABLE_MYSQL_DB."event.id = ".PREFIX_TABLE_MYSQL_DB."event_child.id_event")->InnerJoin();
            $res =$query->execution();
            return (!$res);
        }

        return null;

    }
    
    public function getRooms($var)
    {
        $query = $this->DB->connect()->setTableName(PREFIX_TABLE_MYSQL_DB."rooms")->SetFild("id")->SetFild("name")->select();
        $res =$query->execution();
        return $res;

    }

    public function postEvent($var)
    {
        $id_user = $var['id_user'];
        $id_room = $var['id_room'];
        $note = $var['note'];
        $date_create = date("Y-m-d H:i:s");
    
        if (isset($id_user) && isset($id_room) && isset($date_create))
        {
            $query = $this->DB->connect()->setTableName(PREFIX_TABLE_MYSQL_DB."event")->SetFild("id_user", $id_user)->SetFild("id_room", $id_room)->SetFild("date_create", $date_create);
            if (isset($note))
            {
                $query->SetFild("note", $note);
            }
            $query->insert();

            if (!$query->execution())
            {
                return null;
            }       
            $res = $query->lastInsertId();
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

        if (isset($id_event) && isset($date_start) && isset($date_end))
        {
            $query = $this->DB->connect()->setTableName(PREFIX_TABLE_MYSQL_DB."event_child")->SetFild("id_event", $id_event)->SetFild("date_start", $date_start)->SetFild("date_end", $date_end)->insert();

            $res =$query->execution();
            return $res;
        }
        return null;
    }

    public function postDelEvent($var)
    {
        $id = $var['id'];

        if (isset($id))
        {
            $query = $this->DB->connect()->setTableName(PREFIX_TABLE_MYSQL_DB."event")->setConditions("id", $id)->delete();

            $res =$query->execution();
            if($res)
            {
                $res_arr['id_event'] = $id;
                return $this->delEventChild($res_arr);
            }
            
            return $res;
        }
        return null;
    }

    public function postDeleteEventChild($var)
    {
        $id = $var['id'];
        $id_event = $var['id_event'];

        if (isset($id_event))
        {
            $query = $this->DB->connect()->setTableName(PREFIX_TABLE_MYSQL_DB."EventChild")->setConditions("id_event", $id_event)->delete();

            $res =$query->execution();
            return $res;
        } 
        elseif (isset($id))
        {
            $query = $this->DB->connect()->setTableName(PREFIX_TABLE_MYSQL_DB."EventChild")->setConditions("id", $id)->delete();

            $res =$query->execution();
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
