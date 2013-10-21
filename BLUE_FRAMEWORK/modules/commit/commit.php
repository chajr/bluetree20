<?php
/**
 *
 * @category    BlueFramework
 * @package     modules
 * @subpackage  commit
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 * @version     0.2.0
 */
class commit
    extends module_class
{
    static $version             = '0.2.0';
    static $name                = 'Commit generator';
    public $requireLibraries    = array();
    public $requireModules      = array();

    protected $_information = array();

    /**
     * running module method
     */
    public function run()
    {
        $this->_createLayout();

        if ($this->params[0] === 'generate') {
            $this->generateCommit();
        }
    }

    public function generateCommit()
    {
        $data = 'a:19:{s:5:"title";s:12:"commit title";s:7:"version";s:5:"1.0.1";s:5:"issue";s:2:"44";s:5:"alpha";s:2:"on";s:8:"modify_5";s:15:"modified/2/file";s:9:"added_5_1";s:18:"added some content";s:9:"added_5_2";s:0:"";s:10:"remove_5_3";s:20:"removed some content";s:9:"removed_4";s:16:"/changed/version";s:8:"from_4_1";s:5:"1.0.1";s:6:"to_4_1";s:5:"1.1.0";s:9:"removed_3";s:17:"removed/file/path";s:8:"remove_3";s:24:"removed file description";s:8:"modify_2";s:14:"/modified/file";s:9:"added_2_1";s:17:"added description";s:9:"added_2_2";s:19:"added 2 description";s:10:"modify_2_3";s:19:"modifie description";s:5:"add_1";s:14:"added/filepath";s:7:"added_1";s:22:"added file description";}';
//        foreach ($this->post as $key => $value) {
        foreach (unserialize($data) as $key => $value) {
            echo '<pre>';
            var_dump($key, $value);
            echo '</pre>';
            if (trim($value) === '') {
                continue;
            }

            $this->_createBaseInfo($key, $value);
            $this->_createBlock($key, $value);
            $this->_createBlockDescriptions($key, $value);
        }

        $this->_renderInformation();
        echo '<pre>';
        var_dump($this->_information);
        echo '</pre>';
    }

    protected function _renderInformation()
    {
        
    }

    protected function _createBlock($key, $value)
    {
        $bool = preg_match('#(modify|removed|add)_[\d]+#', $key);
        $key  = explode('_', $key);

        if ($bool) {
            $this->_information[$key[1]][] = array('path' => $value);
        }
    }

    protected function _createBlockDescriptions($key, $value)
    {
        $bool = preg_match('#(modify|remove|added|from|to)_[\d]+_[\d]+#', $key);
        $key  = explode('_', $key);

        if ($bool) {
            $this->_information[$key[1]][] = array($key[0] => $value);
        }
    }

    protected function _createBaseInfo($key, $value)
    {
        if ($key === 'title') {
            $this->generate('title', $value);
        }

        if ($key === 'version') {
            $this->generate('version', $value);
        }

        if ($key === 'issue') {
            $this->generate('issue', $value);
        }

        if ($key === 'alpha') {
            $this->generate('alpha', '' . "\n");
        }

        if ($key === 'description') {
            $this->generate('description', $value);
        }
    }

    protected function _createLayout()
    {
        $this->set(
            'https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js',
            'js',
            'external'
        );

        $this->_translate();
        $this->set('commit', 'css');
        $this->set('commit', 'js');

        if ($this->params[0] === 'generate') {
            $this->layout('generate');
        } else {
            $this->layout('commit1');
        }
    }

    public function runErrorMode()
    {

    }
}
