<?php
/**
 * @category    BlueFramework
 * @package     modules
 * @subpackage  example_incoming_data
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 * @version     1.1.0
 */
class example_incoming_data
    extends module_class
{
    static $version             = '1.1.0';
    static $name                = 'incoming data module example';
    public $requireLibraries    = array();
    public $requireModules      = array();

    public function run()
    {
        $this->_prepareLayout();

        $variable = $this->get->variable;
        $this->generate('get_variable', $variable);

        $variable = $this->post->variable;
        $this->generate('post_variable', $variable);

        $this->_showSessionExamples();
        $this->_showCookieExamples();
        $this->_fileUploadExample();
        $this->_getObjectData();
    }

    /**
     * load layout template, run translations
     */
    protected function _prepareLayout()
    {
        $this->layout('index');
        $this->set('style', 'css');
        $this->_translate();
    }

    /**
     * show and set data in session
     */
    protected function _showSessionExamples()
    {
        $this->generate(array(
            'public'    => $this->session->val,
            'user'      => $this->getSessionVariable('val_user', 'user')
        ));

        if ($this->session->val) {
            $this->session->val += 1;
        } else {
            $this->session->val = 1;
        }

        if ($this->getSessionVariable('val_user', 'user')) {
            $userValue = $this->getSessionVariable('val_user', 'user') +1;
        } else {
            $userValue = 1;
        }

        $this->setSession('val_user', $userValue, 'user');
        $this->generate(array(
            'public2'   => $this->session->val,
            'user2'     => $this->getSessionVariable('val_user', 'user')
        ));

        $this->setSession(
            'session_display_test',
            $this->session->val . ' - display',
            'display'
        );
    }

    /**
     * set and show data from cookies
     */
    protected function _showCookieExamples()
    {
        $this->generate('cookie', $this->cookie->cookieVariable);
        if ($this->cookie->cookieVariable) {
            $this->cookie->cookieVariable += 1;
        } else {
            $this->cookie->cookieVariable = 1;
        }
        $this->generate('cookie2', $this->cookie->cookieVariable);
    }

    /**
     * check that file was uploaded and show base information about file
     */
    protected function _fileUploadExample()
    {
        if ($this->files->file_upload) {
            $fileData = var_export($this->files->file_upload, TRUE);
            $this->generate('file_data', $fileData);
        }
    }

    /**
     * show some data directly from get object
     */
    protected function _getObjectData()
    {
        $getArray = array(
            'path'          => get::realPath(),
            'rget'          => var_export(get::convertGet('', 'val/val2/val3'), TRUE),
            'lang'          => $this->get->getLanguage(),
            'current_page'  => $this->get->getCurrentPage(),
            'parent'        => $this->get->getParentPage(),
            'master'        => $this->get->getMasterPage(),
            'full'          => var_export($this->get->fullGetList(TRUE), TRUE),
            'full2'         => var_export($this->get->fullGetList(), TRUE),
            'type'          => $this->get->pageType(),
            'path_domain'   => $this->get->path(),
            'path2'         => $this->get->path(TRUE),
        );

        $this->generate($getArray);
    }

    public function runErrorMode(){}
}
