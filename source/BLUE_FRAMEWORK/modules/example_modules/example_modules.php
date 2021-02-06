<?php
/**
 * @category    BlueFramework
 * @package     modules
 * @subpackage  example_modules
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 * @version     1.0.0
 */
class example_modules
    extends module_class
{
    static $version             = '1.0.0';
    static $name                = '';
    public $requireLibraries    = array();
    public $requireModules      = array();

    /**
     * @var string
     */
    public $testVariable;

    /**
     * initialize module
     */
    public function run()
    {
        $this->_prepareLayout();

        $this->_moduleConfigXml();
        $this->_moduleConfigTree();
        $this->_checkModules();
        $this->_setVariable();
        $this->_setSessionVariable();
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
     * get data from module configuration file
     */
    protected function _moduleConfigXml()
    {
        $list = var_export($this->loadModuleOptions(), TRUE);
        $this->generate('module_config', $list);

        $this->generate(
            'module_config_single',
            $this->loadModuleOptions('config_option_3')
        );
    }

    /**
     * get module configuration from tree
     */
    protected function _moduleConfigTree()
    {
        $param = var_export($this->params, TRUE);
        $this->generate('params', $param);

        $this->generate('params_single', $this->params[1]);
    }

    /**
     * check that module exist and list all lunched modules
     */
    protected function _checkModules()
    {
        if (isset($this->modules['example_modules_reader'])) {
            $this->generate('reader_exist', '{;lang;exists;}');
        } else {
            $this->generate('reader_exist', '{;lang;not_exists;}');
        }
        $this->generate('modules', var_export(array_keys($this->modules), TRUE));
    }

    /**
     * set data to variable that can be used by other modules
     */
    protected function _setVariable()
    {
        $this->testVariable = '{;lang;test_variable;}';
        $this->generate('module_data', $this->testVariable);
    }

    /**
     * set data to session that can be used by other modules
     */
    protected function _setSessionVariable()
    {
        $this->session->testVal = '{;lang;test_session_variable;}';
        $this->generate('session_data', $this->session->testVal);
    }

    /**
     * that method can be lunched by other modules
     */
    public function methodToLunch()
    {
        $this->generate('other_methods', '{;lang;started;}');
    }

    public function runErrorMode(){}
}
