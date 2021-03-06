<?php
/**
 * @category    BlueFramework
 * @package     modules
 * @subpackage  example_block_3
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 * @version     1.0.0
 */
class example_blocks_3
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

        $this->generate('module_name', get_class($this));
    }

    /**
     * load layout template, run translations
     */
    protected function _prepareLayout()
    {
        $this->layout('index');
    }

    public function runErrorMode(){}
}
