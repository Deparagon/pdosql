<?php

class User extends Db
{
    public $user_id;
    public $sponsor;
    public $username;
    public $password;
    public $firstname;
    public $lastname;
    public $email;
    public $phone;
    public $address;
    public $city;
    public $state;
    public $country;
    public $status;
    public $date_add;
    public $date_upd;
    private $power;

    public function __construct()
    {
        parent::__construct();
        $this->tablename = 'user';
    }

    public function register($data)
    {
        $type = array('i', 's', 's', 's', 's' , 's', 's', 's', 's', 's', 's','i', 's');
        if ($user_id = $this->insertID($data)) {
            return $user_id;
        }

        return false;
    }

    public function isActive($user_id)
    {
        $id = $this->getValueWithParams('user_id', array('status' => 1, 'user_id' => (int) $user_id));

        if ($id == $user_id) {
            return true;
        }

        return false;
    }

    public static function isActivated($user_id)
    {
        return (new self())->isActive($user_id);
    }

    private function _makeA($user_id)
    {
        if ($this->update(array('status' => 1), array('user_id' => (int) $user_id))) {
            return true;
        }

        return false;
    }

    public function getUsernameAndID()
    {
        $sql = 'SELECT username, user_id FROM '.$this->tablename.' ';

        return $this->doSelection($sql,  array('power' => 0), '', '');
    }
    public function getAllMembers($lmt)
    {
        $sql = 'SELECT * FROM '.$this->tablename.' ';

        return $this->doSelection($sql,  '', '', $lmt);
    }

    public function getAllActivemembers()
    {
        $sql = 'SELECT * FROM '.$this->tablename.' ';

        return $this->doSelection($sql,  array('status' => 1));
    }

    public function getUsernameFromID($user_id)
    {
        return $this->getValueWithParams('username', array('user_id' => $user_id));
    }

    public function countAllMembers()
    {
        return $this->getValueWithParams('COUNT(user_id)', '');
    }

    public function countAllActiveMembers()
    {
        return $this->getValueWithParams('COUNT(user_id)', array('status' => 1));
    }

    public function updateMemberVar($field, $value, $user_id)
    {
        $this->update(array($field => $value), array('user_id' => $user_id));

        return true;
    }

    public static function makeActiveStatus($user_id)
    {
        return (new self())->_makeA($user_id);
    }

    public function updateMemeber($user_id, $firstname, $lastname, $address, $city, $state, $country)
    {
        return $this->update(array('firstname' => $firstname, 'lastname' => $lastname, 'address' => $address, 'city' => $city, 'state' => $state, 'country' => $country), array('user_id' => $user_id));
    }

    public function getMember($user_id)
    {
        $sql = 'SELECT * FROM ';

        return $this->getRow($sql, array('user_id' => $user_id));
    }

    public static function getMemberByID($user_id)
    {
        return (new self())->getMember($user_id);
    }

    public static function getMemberByUsername($username)
    {
        $sql = 'SELECT * FROM ';
        return (new self())->getRow($sql, array('username' => $username));
    }
    public function checkPower($user_id)
    {
        $id = null;
        $id = $this->getValueWithParams('user_id', array('power' => 1, 'user_id' => (int) $user_id));

        if ((int) $id === (int) $user_id) {
            return true;
        }

        return false;
    }

    public function changePassword($user_id, $newpassword)
    {
     

        return $this->update(array('password' => $newpassword), array('user_id' => $user_id));
    }


    public function getIDByName($username)
    {
        $id = $this->getValueWithParams('user_id', array('username' => $username));

        if ((int) $id > 0) {
            return $id;
        }

        return false;
    }

    public function checkUserExist($username)
    {
        $id = $this->getValueWithParams('user_id', array('username' => $username));

        if ((int) $id > 0) {
            return true;
        }

        return false;
    }
    public function checkPhoneExist($phone)
    {
        $id = $this->getValueWithParams('user_id', array('phone' => $phone));

        if ((int) $id > 0) {
            return true;
        }

        return false;
    }
    public function doLogin($username, $password)
    {
      
        $id = $this->getValueWithParams('user_id', array('username' => $username, 'password' => $password));

        if ((int) $id > 0) {
            return $id;
        }

        return false;
    }

    public function findByDetail($info)
    {
        $sql = 'SELECT * FROM '.$this->tablename.' WHERE username LIKE ? OR phone LIKE ? OR email LIKE ? LIMIT 5';

        return $this->finder($sql, array($info, $info, $info));
    }

    public static function findUsername($info)
    {
        return (new self())->findByDetail($info);
    }

    public function findFromInactive($info)
    {
        $sql = 'SELECT * FROM '.$this->tablename.' WHERE username LIKE ? OR phone LIKE ? OR email LIKE ? AND status =0 LIMIT 5';

        return $this->finder($sql, array($info, $info, $info));
    }

    public static function findInactiveMembers($info)
    {
        return (new self())->findFromInactive($info);
    }

    public function deleteByID($user_id)
    {
        return $this->delete(array('user_id' => $user_id));
    }
    public static function deleteMember($user_id)
    {
        return (new self())->deleteByID($user_id);
    }
}
