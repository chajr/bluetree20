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
 * @version     1.0.1
 */
class main
    extends module_class
{
    static $version             = '1.0.1';
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
                benchmark_class::turnOffBenchmark();
                tracer_class::turnOffTracer();

                header ("Content-Type:text/xml");
                $this->generate('empty', $this->siteMap(), 1);
                break;
        }
    }

    public function runErrorMode(){}
}
