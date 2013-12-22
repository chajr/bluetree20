<?php
/**
 * @category    BlueFramework
 * @package     modules
 * @subpackage  example_layout
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 * @version     1.0.0
 */
class example_layout
    extends module_class
{
    static $version             = '1.0.0';
    static $name                = 'layout usage module example';
    public $requireLibraries    = array();
    public $requireModules      = array();

    public function run()
    {
        $this->_prepareLayout();
        $this->_baseDisplayClass();
        $this->_operatingDisplayClass();
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
     * create instance of display class
     * 
     * @return display_class
     */
    protected function _additionalDisplayClass()
    {
        $configuration = [
            'independent' => TRUE,
            'clean'       => FALSE,
            'language'    => $this->get->getLanguage(),
            'session'     => $this->session,
            'get'         => $this->get,
            'template'    => 'modules/example_layout/layouts/additional_template.html',
        ];
        return new display_class($configuration);
    }

    /**
     * show template from display_class instance before changed
     */
    protected function _baseDisplayClass()
    {
        $displayObject = $this->_additionalDisplayClass();
        $content       = $displayObject->render();

        $this->generate('display_class_content', $content);
    }

    /**
     * make some operations on display_class instance and display it
     */
    protected function _operatingDisplayClass()
    {
        $this->setSession(
            'session_marker',
            'SESSION',
            'display'
        );

        $loopData = [
            ['data' => 1],
            ['data' => 2],
            ['data' => 3],
            ['data' => 4],
        ];

        $displayObject = $this->_additionalDisplayClass();
        $displayObject->generate('simple_content', 'TRUE');
        $displayObject->loop('additional_loop', $loopData);

        $content = $displayObject->render();
        $this->generate('display_class_operations', $content);
    }

    public function runErrorMode(){
    }
}
