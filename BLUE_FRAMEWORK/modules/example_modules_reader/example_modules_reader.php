<?php
/**
 * @category    BlueFramework
 * @package     modules
 * @subpackage  example_modules_reader
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 * @version     1.0.0
 */
class example_modules_reader
    extends module_class
{
    static $version             = '1.0.0';
    static $name                = '';
    public $requireLibraries    = array();
    public $requireModules      = array();

    /**
     * initialize module
     */
    public function run()
    {
        $this->_prepareLayout();
        $this->_getVariable();
        $this->_getSessionVariable();
        $this->_checkModules();
        $this->_lunchOtherModuleMethod();
    }

    /**
     * load layout template, run translations
     */
    protected function _prepareLayout()
    {
        $this->layout('index');
        $this->_translate();
    }

    /**
     * get varaible value from other module
     */
    protected function _getVariable()
    {
        $this->generate('module_data', $this->modules['example_modules']->testVariable);
    }

    /**
     * get session variable value set up by other module
     */
    protected function _getSessionVariable()
    {
        $this->generate('session_data', $this->session->testVal);
    }

    /**
     * check that module exist and list all lunched modules
     */
    protected function _checkModules()
    {
        if (isset($this->modules['example_modules'])) {
            $this->generate('example_modules_exist', '{;lang;exist;}');
        } else {
            $this->generate('example_modules_exist', '{;lang;not_exists;}');
        }
        $this->generate('modules', var_export(array_keys($this->modules), TRUE));
    }

    /**
     * lunch method from other module
     */
    protected function _lunchOtherModuleMethod()
    {
        /** @var example_modules $exampleModules */
        $exampleModules = $this->modules['example_modules'];
        $exampleModules->methodToLunch();
    }

    public function runErrorMode(){}
}
