<?php

class TSUser
{
    public $user_id;
    public $username;
    public $firstname;
    public $lastname;
    public $password;
    public $date_add;
    private $dbservice;
    private $tablename;

    public function __construct()
    {
        $this->dbservice = (new Db());
        $this->tablename = 'ts_user';
    }
    public function register($data)
    {
        $this->dbservice->insert($this->tablename, $data, array('s', 's', 's', 's', 's'));
    }

    public function login($username, $password)
    {
        $user_id = null;
        $user_id = $this->dbservice->getValue('SELECT user_id FROM '.$this->tablename, array('username' => $username, 'password' => $password));

        if ($user_id) {
            return $user_id;
        }

        return false;
    }

  public function is_loggedin()
 {
if(isset($_SESSION['user_session']))
{
   return true;
}
 }


    public function redirect($url)
    {
        header("Location: $url");
    }

    public function logout()
    {
        
        unset($_SESSION['user_id']);
        session_destroy();

        return true;
    }

    public function getUserByID($user_id)
    {
        
    }

    public function getAllUser()
    {
    }

    public function countUsers()
    {
        return $this->dbservice->getValue('SELECT COUNT(user_id) FROM '.$this->tablename);
    }
}
