<?php
/**
 * @category    BlueFramework
 * @package     modules
 * @subpackage  example_messages
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 * @version     0.3.0
 */
class example_messages
    extends module_class
{
    static $version             = '0.3.0';
    static $name                = 'messages generate module example';
    public $requireLibraries    = array();
    public $requireModules      = array();

    /**
     * initialize module
     */
    public function run()
    {
        $this->_prepareLayout();

        $this->_systemMessages();
        $this->_markerErrors();
        $this->_errorsArray();
        $this->_simpleException();
        $this->_startErrorMode();
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
     * show system messages
     */
    protected function _systemMessages()
    {
        $this->error(
            'critic',
            'error_from_module',
            '{;lang;additional_message;}'
        );

        $this->error(
            'warning',
            'warning_code',
            '{;lang;additional_message;}'
        );

        $this->error(
            'info',
            'info_code',
            '{;lang;additional_message;}'
        );

        $this->error(
            'ok',
            'ok_code',
            '{;lang;additional_message;}'
        );
    }

    /**
     * set error into marker
     */
    protected function _markerErrors()
    {
        $this->error(
            'error_marker',
            'some_code',
            '{;lang;other_info;} - {;lang;additional_message;}'
        );

        $this->error(
            'error_marker1',
            'some_code',
            '{;lang;other_info;}'
        );

        $this->error(
            'error_marker2',
            'some_code',
            '12345'
        );
    }

    /**
     * show list of created error messages
     * if status == 1 than message was created
     */
    protected function _errorsArray()
    {
        $errors = var_export($this->error, TRUE);
        $this->generate('errors_list', $errors);
    }

    protected function _simpleException()
    {
        try{
            throw new Exception('some exception');
        } catch (Exception $e) {
            $this->error('exception', 'simple_exception', '{;lang;normal_exception;}');
        }
    }

    protected function _startErrorMode()
    {
        throw new modException(
            'error_mode',
            '{;lang;other_info;}'
        );
    }

    public function runErrorMode(){
        $this->generate('error_mode', '{;lang;error_mode;}');
    }
}
