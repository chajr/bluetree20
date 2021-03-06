<?php
/**
 * @category    BlueFramework
 * @package     modules
 * @subpackage  example_generate
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 * @version     1.0.0
 */
class example_core_templates
    extends module_class
{
    static $version             = '1.0.0';
    static $name                = 'content generate module example';
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

    public function runErrorMode(){}
}
