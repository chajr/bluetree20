<?php
/**
 * @category    BlueFramework
 * @package     modules
 * @subpackage  example_layout
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 * @version     0.2.0
 */
class example_layout
    extends module_class
{
    static $version             = '0.2.0';
    static $name                = 'layout usage module example';
    public $requireLibraries    = array();
    public $requireModules      = array();

    public function run()
    {
        $this->layout('index');
    }

    public function runErrorMode(){
    }
}
