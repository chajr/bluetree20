<?php
/**
 * @category    BlueFramework
 * @package     modules
 * @subpackage  allow to tur on benchamrk by url parameter
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 * @version     1.0.0
 */
class benchmark
    extends module_class
{
    static $version             = '1.0.0';
    static $name                = 'allow to tur on benchmark by url parameter';
    public $requireLibraries    = array();
    public $requireModules      = array();

    /**
     * initialize module
     */
    public function run()
    {
        benchmark_class::turnOffBenchmark();
        tracer_class::turnOffTracer();

        if ($this->get->benchmark) {
            benchmark_class::turnOnBenchmark();
        }

        if ($this->get->tracer) {
            tracer_class::turnOnTracer();
        }

        if ($this->get->testing) {
            benchmark_class::turnOnBenchmark();
            tracer_class::turnOnTracer();
        }
    }

    public function runErrorMode(){
    }
}
