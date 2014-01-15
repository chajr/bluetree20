<?php
/**
 * @category    BlueFramework
 * @package     modules
 * @subpackage  example_translations
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 * @version     0.1.0
 */
class example_translations
    extends module_class
{
    static $version             = '0.1.0';
    static $name                = 'translations example';
    public $requireLibraries    = array();
    public $requireModules      = array();

    /**
     * initialize module
     */
    public function run()
    {
        $this->_prepareLayout();
    }

    /**
     * load layout template, run translations
     */
    protected function _prepareLayout()
    {
        $this->layout('index');
        $this->_translate();
    }

    public function runErrorMode(){
    }
}
