<?php
/**
 * main module, depends of given parameters runs some useful functions
 * module always run on all pages and subpages
 * 
 * @category    BlueFramework
 * @package     modules
 * @subpackage  main
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 * @version     1.0.0
 */
class main
    extends module_class
{
    static $version             = '1.0.0';
    static $name                = 'Main Module';
    public $requireLibraries    = array();
    public $requireModules      = array();

    /**
     * running module method
     */
    public function run()
    {
        switch ($this->params[0]) {
            case'sitemap':
                $this->generate('empty', $this->sitemap(), 1);
                break;
        }
    }

    /**
     * run when module throws some error
     */
    public function runErrorMode()
    {

    }

    public function install()
    {

    }

    public function uninstall()
    {

    }
}
