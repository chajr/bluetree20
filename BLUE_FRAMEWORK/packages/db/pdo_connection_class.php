<?php
/**
 * establish connection with database and put reference to it to special array
 *
 * @author chajr <chajr@bluetree.pl>
 * @category    BlueFramework
 * @package     db
 * @subpackage  mysql
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 * @version     1.2.1
 *
 * Display <a href="http://sam.zoy.org/wtfpl/COPYING">Do What The Fuck You Want To Public License</a>
 * @license http://sam.zoy.org/wtfpl/COPYING Do What The Fuck You Want To Public License
 */
class pdo_connection_class
    extends PDO
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
     * 
     */
    public function __construct($config, $charset = 'UTF8')
    {
        self::$defaultCharset = $charset;

        if (isset($config) && !empty($config)) {

            $dsn = $config['type'] . ':host=' . $config['host'] . ';dbname=' . $config['dbname'] . ';port=3307';
            
            parent::__construct($dsn, $config['user'], $config['pass']);

            
            if ($this->errorInfo()) {
                $this->err = $this->errorInfo();
                return;
            }

            $this->query("SET NAMES '$charset'");
        }

        if (!isset($config['name']) || !$config['name']) {
            $config['name'] = 'default';
        }

        self::$connections[$config['name']] = $this;
    }
}