<?php
/**
 * example module number 3
 *
 * @category    BlueFramework
 * @package     modules
 * @subpackage  mod3
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 * @version     0.1.1
 */
class mod3 
    extends module_class
{
    static $version             = '0.1.1';
    static $name                = 'module number 3';
    public $requireLibraries    = array();
    public $requireModules      = array('modul1');

    public function run()
    {
        //load layout
        $this->layout('layout1');

        $this->_translate();

        //set content to layout
        $this->generate('marker', 'some content to replace');
    }

    public function runErrorMode()
    {
        $this->generate('marker', 'that is error mode from module 3');
    }

    public function install(){}
    public function uninstall(){}
}
