<?php
/**
 * testing pdo connection
 *
 * @category    BlueFramework
 * @package     modules
 * @subpackage  connection
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 * @version     0.1.0
 */
class connection 
    extends module_class
{
    static $version             = '0.1.0';
    static $name                = 'pdo connection test';
    public $requireLibraries    = array('pdo_connection');
    public $requireModules      = array();
    public $moduleVariable;

    public function run()
    {
        $this->generate('empty', 'empty', TRUE);
        
        try{
            $connection = new pdo_connection_class($this->loadModuleOptions());
        } catch (Exception $e) {
            echo '<pre>';
            var_dump($e->getMessage());
            echo '</pre>';
            exit;
        }

        echo '<pre>';
        var_dump($connection->err);
        echo '</pre>';
        
        $b = $connection->query('SELECT * from test');
        echo '<pre>';
        var_dump($b);
        echo '</pre>';

        echo '<pre>';
        var_dump($b->fetchAll());
        echo '</pre>';
    }
    
    public function runErrorMode(){
        
    }
}
