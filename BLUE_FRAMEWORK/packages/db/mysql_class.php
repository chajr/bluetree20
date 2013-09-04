<?php
/**
 * handling of MySQL database
 *
 * @category    BlueFramework
 * @package     db
 * @subpackage  mysql
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 * @version     3.3.1
 * 
 * Display <a href="http://sam.zoy.org/wtfpl/COPYING">Do What The Fuck You Want To Public License</a>
 * @license http://sam.zoy.org/wtfpl/COPYING Do What The Fuck You Want To Public License
 */
class mysql_class
    extends abstractSql_class
{
    /**
     * set default connection and run given query
     * optionally we can give other connection and change charset
     * 
     * @param string $sql
     * @param string $connection optionally connection name (default - default)
     * @param string $charset
     * @example new mysql_class('SELECT * FROM table')
     * @example new mysql_class('SELECT * FROM table', 'connection')
     * @example new mysql_class('SELECT * FROM table', 0, 'LATIN1')
     */
    public function __construct($sql, $connection = 'default', $charset = NULL)
    {
        if (!$connection) {
            $this->_connection = 'default';
        } else {
            $this->_connection = $connection;
        }

        if ($charset) {
            $this->_setCharset($charset);
        }

        $this->_query($sql);

        if ($charset) {
            $this->_setCharset(mysql_connection_class::$defaultCharset);
        }
    }

    /**
     * return data converted to array
     * if $full is TRUE return all data, else as single row
     * 
     * @param boolean $full
     * @return array
     */
    public function result($full = FALSE)
    {
        if ($this->rows) {

            if ($full) {
                $arr = array();

                while ($array = $this->_result->fetch_assoc()) {

                    if (!$array) {
                        return NULL;
                    }

                    $arr[] = $array;
                }

            } else {
                $arr = $this->_result->fetch_assoc();
            }

            return $arr;
        }

        return NULL;
    }

    /**
     * return full result from query
     * used $this->result(TRUE); method
     * 
     * @return array
     */
    public function fullResult()
    {
        return $this->result(TRUE);
    }

    /**
     * return mysqli_result result object
     * 
     * @return mysqli_result
     */
    public function returns()
    {
        return $this->_result;
    }

    /**
     * run query to database
     * 
     * @param string $sql
     */
    private function _query($sql)
    {
        $bool = mysql_connection_class::$connections[$this->_connection]
            ->query($sql);
        
        if (!$bool) {
            $this->err = mysql_connection_class::$connections[$this->_connection]
                ->error;
            return;
        }
        
        if (mysql_connection_class::$connections[$this->_connection]->insert_id) {
            $this->id = mysql_connection_class::$connections[$this->_connection]
                ->insert_id;
        }
        
        if (!is_bool($bool) && !is_integer($bool)) {
            $this->rows = $bool->num_rows;
        }
        
        $this->_result = $bool;
    }

    /**
     * change charset for queries
     * 
     * @param string $charset
     */
    private function _setCharset($charset)
    {
        mysql_connection_class::$connections[$this->_connection]
            ->query("SET NAMES '$charset'");
    }
}

/**
 * establish connection with database and put reference to it to special array
 * 
 * @author chajr <chajr@bluetree.pl>
 * @category    BlueFramework
 * @package     db
 * @subpackage  mysql
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 * @version     1.2.0
 * 
 * @todo define some other charset
 * 
 * Display <a href="http://sam.zoy.org/wtfpl/COPYING">Do What The Fuck You Want To Public License</a>
 * @license http://sam.zoy.org/wtfpl/COPYING Do What The Fuck You Want To Public License
 */
class mysql_connection_class
    extends mysqli
{
    /**
     * information about connection error
     * @var string 
     */
    public $err;

    /**
     * contains connection array
     * @var array 
     */
    static $connections = array();

    /**
     * default charset
     * @var string 
     */
    static $defaultCharset = 'UTF8';

    /**
     * creates instance of mysqli object and connect to database
     * name default is used for default connection to database !!!!
     * 
     * @param array $config (host, username, pass, dbName, connectionName)
     * @param string $charset (default UTF8)
     * @example new mysql_connection_class(array('localhost', 'user', 'pass', 'db', 'connection'))
     * @example new mysql_connection_class(array('localhost', 'user', 'pass', 'db'))
     * @example new mysql_connection_class(array('localhost', 'user', 'pass', 'db'), 'LATIN1')
     */
    public function __construct($config, $charset = 'UTF8')
    {
        self::$defaultCharset = $charset;

        if (isset($config) && !empty($config)) {

            parent::__construct($config[0], $config[1], $config[2], $config[3]);

            if (mysqli_connect_error()) {
                $this->err = mysqli_connect_error();
                return;
            }

            $this->query("SET NAMES '$charset'");
        }

        if (!isset($config[4]) || !$config[4]) {
            $config[4] = 'default';
        }
        
        self::$connections[$config[4]] = $this;
    }

    /**
     * destroy all connections
     */
    public function __destruct()
    {
        self::$connections = array();
    }

    /**
     * destroy all, or given connection
     * 
     * @param array|string $connectionList array of connections or single connection
     * @example destruct()
     * @example destruct('default')
     * @example destruct(array('connection1', 'connection2'))
     */
    static function destroyConnection($connectionList = NULL)
    {
        if ($connectionList) {

            if (is_array($connectionList)) {

                foreach ($connectionList as $connection) {
                    unset(self::$connections[$connection]);
                }

            } else {
                unset(self::$connections[$connectionList]);
            }

        } else {
            self::$connections = array();
        }
    }
}
