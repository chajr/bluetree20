<?php
/**
 * example to display all maps methods returned data
 *
 * @category    BlueFramework
 * @package     modules
 * @subpackage  map
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 * @version     1.0.0
 */
class map
    extends module_class
{
    static $version             = '1.0.0';
    static $name                = 'Maps test module';
    public $requireLibraries    = array();
    public $requireModules      = array();

    public function run()
    {
        $this->generate('breadcrumbs', var_export($this->breadcrumbs(), TRUE), TRUE);
        $this->generate('sitemap', htmlspecialchars($this->siteMap()), TRUE);
        $this->generate('map', var_export($this->map(), TRUE), TRUE);
    }

    public function runErrorMode(){}
}
