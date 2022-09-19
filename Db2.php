<?php

/**
 * DESCRIPTION.
 *
 * DB
 *
 *  @author    Paragon Kingsley
 *  @copyright 2016-2019 Paragon Kingsley
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License ("AFL") v. 3.0
 */



class Db2
{

    /** @var $dsn string database server connection information */
    private $dsn;

    /** @var dbcon database resources */
    private $dbcon;

    protected $tablename;
    private $db_user;
    private $db_password;
    /** instantiate database connection */
    public function __construct()
    {
         $result = $this->loadConfiguration();
        if ($result===true) {
            try {
                $this->dbcon = new PDO($this->dsn, $this->db_user, $this->db_password);
                $this->dbcon->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                // for developmental purpose,
                echo 'Connection failed: '.$e->getMessage();
            }
        } else {
            echo $result;
        }
    }

    public function loadConfiguration()
    {
        try {
            if (file_exists(dirname(__FILE__).'/config.json')) {
                $config = json_decode(file_get_contents(dirname(__FILE__).'/config.json'));
                if (is_object($config)) {
                    $this->dsn = 'mysql:dbname='.$config->db_name.';host='.$config->db_host;
                    $this->db_user = $config->db_user;
                    $this->db_password = $config->db_password;
                    return true;
                }
           
                return false;
            }
            return 'Configuration File Load failed';
        } catch (Exception $e) {
             return  'Error occured trying to load configuration';
        }
    }

    /**
     *Inserts a record into a database table.

     * @param $this->tablename string database table name
     * @param $arraypair  Array key : value pair of all the database fields to insert.
     * @param $datatype Array array of the field type, 's' for string 'i' for int
     *
     * @return ID of inserted row OR Bool(T|F)
     */
    public function insertID($arraypair, $datatype = null)
    {
        if ($this->tablename == '' || !is_array($arraypair)) {
            return false;
        }
        $columns = array();
        $data = array();
        $holder = array();
        foreach ($arraypair as $k => $v) {
            $columns[] = $k;
            $holder[] = ':'.$k;
            $data[':'.$k] = $v;
        }
        try {
            $sql = 'INSERT INTO '.$this->tablename.' ('.implode(', ', $columns).') VALUES('.implode(', ', $holder).')';

            $stmt = $this->dbcon->prepare($sql);

            $i = 0;
            foreach ($data as $key => $value) {
                if (isset($datatype[$i])) {
                    if ($datatype[$i] == 'i') {
                        $dtype = PDO::PARAM_INT;
                    } else {
                        $dtype = PDO::PARAM_STR;
                    }
                } else {
                     $dtype = PDO::PARAM_STR;
                }
               
                $stmt->bindValue($key, $value, $dtype);
            //echo ''.$key.'|'.$value.'|'.$dtype.'<br>';
                ++$i;
            }

            if ($stmt->execute()) {
                return $this->dbcon->lastInsertId();
            }

            return false;
        } catch (PDOException $e) {
            echo $e->getMessage().' Could not insert';
        }
    }

    /**
     *Inserts a record into a database table.

     * @param $tablename string database table name
     * @param $arraypair  Array key : value pair of all the database fields to insert.
     * @param $datatype Array array of the field type, 's' for string 'i' for int
     *
     * @return Bool(T|F)
     */
    public function insert($arraypair, $datatype = null)
    {
        if ($this->tablename == '' || !is_array($arraypair)) {
            return false;
        }
        $columns = array();
        $data = array();
        $holder = array();
        foreach ($arraypair as $k => $v) {
            $columns[] = $k;
            $holder[] = ':'.$k;
            $data[':'.$k] = $v;
        }
        try {
            $sql = 'INSERT INTO '.$this->tablename.' ('.implode(', ', $columns).') VALUES('.implode(', ', $holder).')';

            $stmt = $this->dbcon->prepare($sql);

            $i = 0;
            foreach ($data as $key => $value) {
                if (isset($datatype[$i])) {
                    if ($datatype[$i] == 'i') {
                        $dtype = PDO::PARAM_INT;
                    } else {
                        $dtype = PDO::PARAM_STR;
                    }
                } else {
                    $dtype = PDO::PARAM_STR;
                }
               
                $stmt->bindValue($key, $value, $dtype);
            //echo ''.$key.'|'.$value.'|'.$dtype.'<br>';
                ++$i;
            }

            if ($stmt->execute()) {
                return true;
            }

            return false;
        } catch (PDOException $e) {
            echo $e->getMessage().' Could not insert';
        }
    }

 /**
  *updates a record in a database table.

  * @param $tablename string database table name
  * @param $args  Array key : value (new value) pair of all the database fields to update.
  * @param $where Array key : value pair of the where fields.
  *
  * @return Bool(T|F)
  */
    public function update($newvar, $where)
    {
        if (!is_array($newvar) || !is_array($where)) {
            return;
        }

        if (!empty($where)) {
            foreach ($where as $k => $v) {
                $holder[] = $k.'=:'.$k;
                $datavalue[':'.$k] = $v;
            }
            $wheredata = ' WHERE '.implode(' AND ', $holder);
        }

      
        foreach ($newvar as $k => $v) {
            $updatables[] = $k.'=:'.$k;
            $data[':'.$k] = $v;
        }
        try {
            $sql = 'UPDATE '.$this->tablename.' SET '.implode(',', $updatables).' '.$wheredata;
            
            $stmt = $this->dbcon->prepare($sql);
            foreach ($data as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            foreach ($datavalue as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            if ($stmt->execute()) {
                return true;
            }

            return false;
        } catch (PDOException $e) {
            $e->getMessage().'. Update Failed';
        }
    }

    /**
     *deletes record(s)in a database table.

     * @param $this->tablename string database table name
     * @param $where  Array key : value pair of all the database fields to delete.
     *
     * @return Bool(T|F)
     */
    public function delete($where)
    {
        foreach ($where as $k => $v) {
            //$columns[] = $k;
            $holder[] = $k.'=:'.$k;
            $datavalue[':'.$k] = $v;
        }
        $sql = 'DELETE FROM '.$this->tablename.' WHERE '.implode(' AND ', $holder);
        $stmt = $this->dbcon->prepare($sql);
        foreach ($datavalue as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    /**
     *select(s) record(s) from a database(s) table.

     * @param $nonwhere string contains a part of the sql query before the where clause
     * @param $where  Array key : value pair of the where fields.
     * @param $mode string, type of values to return, OBJ for objects and ASSOC for associative array of the fields and field value
     *@param $orderby string order by sql clause e.g ORDER BY id_user DESC
     *
     * @return array of object, array, false)
     *               Example: ('SELECT * FROM '.self::DTABLE, array('idUser' => $idUser, 'orderState' => $state))
     */
    public function doSelection($nonwhere = '', $where = array(), $mode = '', $orderby = '')
    {

        if ($nonwhere =='') {
            $nonwhere = 'SELECT * FROM '.$this->tablename;
        }
        if (!empty($where)) {
            foreach ($where as $k => $v) {
                if (stripos($k, '.')) {
                    $kd = explode('.', $k);
                    //print_r($kd);
                    $holder[] = $k.'=:'.$kd[1];
                    $datavalue[':'.$kd[1]] = $v;
                } else {
                    $holder[] = $k.'=:'.$k;
                    $datavalue[':'.$k] = $v;
                }
                //$holder[] = $k.'=:'.$k;
                //$datavalue[':'.$k] = $v;
            }

            $sql = $nonwhere.' WHERE '.implode(' AND ', $holder).' '.$orderby;
            $stmt = $this->dbcon->prepare($sql);
            foreach ($datavalue as $key => $value) {
                $stmt->bindValue($key, $value);
            }
        } else {
            $sql = $nonwhere.$orderby;
            $stmt = $this->dbcon->prepare($sql);
        }

        if ($stmt->execute()) {
            if ($mode == 'OBJ') {
                $result = $stmt->fetchAll(PDO::FETCH_OBJ);
            } elseif ($mode == 'ASSOC') {
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $result = $stmt->fetchAll(PDO::FETCH_OBJ);
            }

            return $result;
        }

        return false;
    }

    /**
     *executes sql and returns Object.

     * @param $sql string $sql  the complete sql query
     *
     * @return OBJect
     */
    public function topSql($sql)
    {

        $stmt = $this->dbcon->prepare($sql);
        if ($stmt->execute()) {
            $result = $stmt->fetchAll(PDO::FETCH_OBJ);

            return $result;
        }
    }

    public function topField($sql)
    {
        $stmt = $this->dbcon->prepare($sql);
        if ($stmt->execute()) {
            $result = $stmt->fetchColumn();

            return $result;
        }
    }

    /**
     * searchs through the database table ;.

     * @param $sql string SQL with placeholders see sample
     * @param $value  Array value of in placeholder
     *
     * @example 'SELECT * FROM tablename WHERE reference LIKE ? OR idOrder LIKE ? OR idUser LIKE ? OR orderState LIKE ? OR paymentType LIKE ? LIMIT 5', array($info, $info, $info, $info, $info)
     *
     * @return OBJECT
     */
    public function finder($sql, $values)
    {
        foreach ($values as $v) {
            $stext[] = '%'.$v.'%';
        }
        $stmt = $this->dbcon->prepare($sql);
        $stmt->execute($stext);
        $result = $stmt->fetchAll(PDO::FETCH_OBJ);

        return $result;
    }

    /**
     *Returns single column field.

     * @param $nonwhere string $sql query string before the WHERE clause.
     * @param $where Array the WHERE field(s) array key : value page
     *
     * @return string | int the value of the requested field
     *                Example: 'SELECT COUNT(idOrder) FROM '.self::DTABLE, array('orderState' => $state)
     */
    public function getValue($nonwhere, $where = array())
    {
        if (!empty($where)) {
            foreach ($where as $k => $v) {
                $holder[] = $k.'=:'.$k;
                $datavalue[':'.$k] = $v;
            }

            $sql = $nonwhere.' '.$this->tablename.' WHERE '.implode(' AND ', $holder);
            $stmt = $this->dbcon->prepare($sql);
            foreach ($datavalue as $key => $value) {
                $stmt->bindValue($key, $value);
            }
        } else {
            $sql = $nonwhere.' '.$this->tablename;
            $stmt = $this->dbcon->prepare($sql);
        }
        if ($stmt->execute()) {
            $result = $stmt->fetchColumn();

            return $result;
        }

        return false;
    }



    /**
     *Returns single row field Last.

     * @param $nonwhere string $sql query string before the WHERE clause.
     * @param $where Array the WHERE field(s) array key : value page
     * @param $mode string OBJ / ASSOC for object/associative array
     *
     * @return object or associate array depending on mode
     *                Example:
     */
    public function getLastRow($before_where, $where, $order_by)
    {
        if (count($where) >0) {
            foreach ($where as $k => $v) {
                if (stripos($k, '.')) {
                    $kd = explode('.', $k);
                    $holder[] = $k.'=:'.$kd[1];
                    $datavalue[':'.$kd[1]] = $v;
                } else {
                    $holder[] = $k.'=:'.$k;
                    $datavalue[':'.$k] = $v;
                }
            }
            $sql = $before_where.' '.$this->tablename.'  WHERE '.implode(' AND ', $holder);
    
            $sql .= ' ORDER BY '.$order_by.' LIMIT 1';
            $stmt = $this->dbcon->prepare($sql);
            foreach ($datavalue as $key => $value) {
                $stmt->bindValue($key, $value);
            }
        } else {
            $sql = $before_where.' '.$this->tablename;
            $sql .= ' ORDER BY '.$order_by.' DESC LIMIT 1';
            $stmt = $this->dbcon->prepare($sql);
        }


        if ($stmt->execute()) {
            $result = $stmt->fetch(PDO::FETCH_OBJ);
            return $result;
        }

        return false;
    }


    /**
     *Returns single row field.

     * @param $nonwhere string $sql query string before the WHERE clause.
     * @param $where Array the WHERE field(s) array key : value page
     * @param $mode string OBJ / ASSOC for object/associative array
     *
     * @return object or associate array depending on mode
     *                Example:
     */
    public function getRow($nowhere, $where, $sqljoin = '', $mode = '')
    {
        foreach ($where as $k => $v) {
            if (stripos($k, '.')) {
                $kd = explode('.', $k);
                    //print_r($kd);
                    $holder[] = $k.'=:'.$kd[1];
                $datavalue[':'.$kd[1]] = $v;
            } else {
                $holder[] = $k.'=:'.$k;
                $datavalue[':'.$k] = $v;
            }
            //$holder[] = $k.'=:'.$k;
            //$datavalue[':'.$k] = $v;
        }

        if ($sqljoin != '') {
            $sql = $sqljoin.'  WHERE '.implode(' AND ', $holder);
        } else {
            $sql = $nowhere.' '.$this->tablename.'  WHERE '.implode(' AND ', $holder);
        }

        $stmt = $this->dbcon->prepare($sql);
        foreach ($datavalue as $key => $value) {
            $stmt->bindValue($key, $value);
        }

    
        if ($stmt->execute()) {
            if ($mode == 'OBJ') {
                $result = $stmt->fetch(PDO::FETCH_OBJ);
            } elseif ($mode == 'ASSOC') {
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $result = $stmt->fetch(PDO::FETCH_OBJ);
            }

            return $result;
        }

        return false;
    }

    /**
     *Returns single row field.

     * @param $nonwhere string $sql query string before the WHERE clause.
     * @param $where Array the WHERE field(s) array key : value page
     * @param $mode string OBJ / ASSOC for object/associative array
     *
     * @return object or associate array depending on mode
     *                Example:
     */
    public function getRowJoin($nowhere, $join, $where, $mode = '')
    {
        foreach ($where as $k => $v) {
            $holder[] = $k.'=:'.$k;
            $datavalue[':'.$k] = $v;
        }

        $sql = $nowhere.' '.$this->tablename.' '.$join.' '.' WHERE '.implode(' AND ', $holder);
        $stmt = $this->dbcon->prepare($sql);
        foreach ($datavalue as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        if ($stmt->execute()) {
            if ($mode == 'OBJ') {
                $result = $stmt->fetch(PDO::FETCH_OBJ);
            } elseif ($mode == 'ASSOC') {
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $result = $stmt->fetch(PDO::FETCH_OBJ);
            }

            return $result;
        }

        return false;
    }

    /**
     *Returns single column field.

     * @param $fieldname string the name of the field to return.
     * @param $tablename string the name of the database table
     * @param $where Array the WHERE field(s) array key : value page
     *
     * @return string | int the value of the requested field
     */
    public function getValueWithParams($fieldname, $where = '')
    {
        $nonwhere = 'SELECT '.$fieldname.' FROM '.$this->tablename.' ';
        if (!empty($where)) {
            foreach ($where as $k => $v) {
                $holder[] = $k.'=:'.$k;
                $datavalue[':'.$k] = $v;
            }

            $sql = $nonwhere.' WHERE '.implode(' AND ', $holder);
            $stmt = $this->dbcon->prepare($sql);
            foreach ($datavalue as $key => $value) {
                $stmt->bindValue($key, $value);
            }
        } else {
            $sql = $nonwhere;
            $stmt = $this->dbcon->prepare($sql);
        }
        if ($stmt->execute()) {
            $result = $stmt->fetchColumn();

            return $result;
        }

        return false;
    }


    public function truncate()
    {
        $sql = 'TRUNCATE TABLE '.$this->tablename;
        $stmt = $this->dbcon->prepare($sql);

        return $stmt->execute();
    }

    public function doSql($sql)
    {
        $stmt = $this->dbcon->prepare($sql);

        return $stmt->execute();
    }
}
