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
 * @version     1.4.0
 */
class main
    extends module_class
{
    static $version             = '1.4.0';
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
                header ("Content-Type:text/xml");
                $this->generate('empty', $this->siteMap(), 1);
                break;

            case'breadcrumbs':
                $breadcrumbsData = $this->breadcrumbsAdvanced();

                $this->generate(
                    'current_page_url',
                    $breadcrumbsData['current_page'],
                    TRUE
                );
                $this->generate(
                    'breadcrumbs',
                    $breadcrumbsData['breadcrumbs_html'],
                    TRUE
                );
                break;
        }
    }

    /**
     * create breadcrumbs html and return it
     * 
     * @return array
     */
    public function breadcrumbsAdvanced()
    {
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

        $breadcrumbs->loop('breadcrumbs', $breadcrumbsData);

        return [
            'current_page'          => $currentPageUrl,
            'breadcrumbs_html'      => $breadcrumbs->render()
        ];
    }

    public function runErrorMode(){}
}
