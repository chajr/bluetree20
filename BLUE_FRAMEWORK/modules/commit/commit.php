<?php
/**
 *
 * @category    BlueFramework
 * @package     modules
 * @subpackage  commit
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 * @version     0.1.0
 */
class commit
    extends module_class
{
    static $version             = '0.1.0';
    static $name                = 'Commit generator';
    public $requireLibraries    = array();
    public $requireModules      = array();

    /**
     * running module method
     */
    public function run()
    {
        $this->_createLayout();
        echo '<pre>';
        var_dump($this->post);
        echo '</pre>';
    }

    public function generateCommit()
    {
        
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
