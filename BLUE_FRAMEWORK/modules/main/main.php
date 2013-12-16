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
 * @version     1.3.0
 */
class main
    extends module_class
{
    static $version             = '1.3.0';
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

            case'breadcrumbs':
                benchmark_class::turnOffBenchmark();
                tracer_class::turnOffTracer();

                $data = [
                    'independent' => TRUE,
                    'clean'       => FALSE,
                    'language'    => $this->get->getLanguage(),
                    'template'    => 'elements/layouts/examples/breadcrumbs.html',
                ];
                $breadcrumbs                = new display_class($data);
                $breadcrumbsData            = $this->breadcrumbs();
                $breadcrumbsData[0]['name'] = '<i class="icon-home-alt"></i>';

                $currentPageUrl = '';
                foreach ($this->get->fullGetList() as $page) {
                    $currentPageUrl .= $page . '/';
                }

                $this->generate('current_page_url', $currentPageUrl, TRUE);
                $breadcrumbs->loop('breadcrumbs', $breadcrumbsData);
                $this->generate('breadcrumbs', $breadcrumbs->render(), TRUE);
                break;
        }
    }

    public function runErrorMode(){}
}
