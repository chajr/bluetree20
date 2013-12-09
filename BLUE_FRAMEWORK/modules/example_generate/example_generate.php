<?php
/**
 * @category    BlueFramework
 * @package     modules
 * @subpackage  example_generate
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 * @version     0.2.0
 */
class example_generate
    extends module_class
{
    static $version             = '0.2.0';
    static $name                = 'content generate module example';
    public $requireLibraries    = array();
    public $requireModules      = array();

    /**
     * initialize module
     */
    public function run()
    {
        $this->layout('index');
        $this->_translate();

        $this->_simpleMarker();
        $this->_arrayMarker();
    }

    /**
     * replace simple marker
     */
    protected function _simpleMarker()
    {
        $this->generate('marker', '{;lang;content_to_replace;}');
    }

    /**
     * replace markers from array
     */
    protected function _arrayMarker()
    {
        $data = [
            'marker_1' => '{;lang;content_to_replace_1;}',
            'marker_2' => '{;lang;content_to_replace_2;}',
            'marker_3' => '{;lang;content_to_replace_3;}',
        ];
        $this->generate($data);
    }

    public function runErrorMode(){}
}
