<?php
/**
 * @category    BlueFramework
 * @package     modules
 * @subpackage  example_navigation
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 * @version     1.0.0
 */
class example_navigation
    extends module_class
{
    static $version             = '1.0.0';
    static $name                = '';
    public $requireLibraries    = [];
    public $requireModules      = ['main'];

    /**
     * initialize module
     */
    public function run()
    {
        $this->_prepareLayout();

        $this->_breadcrumbs();
        $this->_advancedBreadcrumbs();
        $this->_map();
        $this->_siteMap();
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
     * show breadcrumbs
     */
    protected function _breadcrumbs()
    {
        $bread = var_export($this->breadcrumbs(), TRUE);
        $this->generate('breadcrumbs', $bread);
    }

    /**
     * show breadcrumbs list rendered by main module
     */
    protected function _advancedBreadcrumbs()
    {
        if (isset($this->modules['main'])) {
            $breadcrumbsData = $this->modules['main']->breadcrumbsAdvanced();

            $this->generate('current_page_url', $breadcrumbsData['current_page']);
            $this->generate('breadcrumbs_advanced', $breadcrumbsData['breadcrumbs_html']);
        }
    }

    /**
     * show map as array
     */
    protected function _map()
    {
        $map = var_export($this->map(), TRUE);
        $this->generate('map', $map);
    }

    /**
     * show google page map
     */
    protected function _siteMap()
    {
        $siteMap = $this->siteMap();
        $siteMap = str_replace('<', '&lt;', $siteMap);
        $siteMap = str_replace('>', '&gt;', $siteMap);

        $this->generate('site_map', $siteMap);
    }

    public function runErrorMode(){}
}
