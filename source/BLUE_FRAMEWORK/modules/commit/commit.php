<?php
/**
 *
 * @category    BlueFramework
 * @package     modules
 * @subpackage  commit
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 * @version     0.3.3
 */
class commit
    extends module_class
{
    static $version             = '0.3.3';
    static $name                = 'Commit generator';
    public $requireLibraries    = array();
    public $requireModules      = array();

    /**
     * contain all information from post
     * @var array
     */
    protected $_information = array();

    /**
     * contain ready to display messages
     * @var string
     */
    protected $_rendered = '';

    /**
     * running module
     */
    public function run()
    {
        $this->_createLayout();

        if ($this->params[0] === 'generate') {
            $this->generateCommit();
            $this->generate('parent', $this->get->getParentPage());
        }
    }

    /**
     * generate commit message
     */
    public function generateCommit()
    {
        foreach ($this->post as $key => $value) {

            if (trim($value) === '') {
                continue;
            }

            $this->_createBaseInfo($key, $value);
            $this->_createBlock($key, $value);
            $this->_createBlockDescriptions($key, $value);
        }

        $this->_processInformation();
        $this->_renderInformation();
    }

    /**
     * attache to commit messages
     */
    protected function _renderInformation()
    {
        if ($this->_rendered) {
            $this->generate('messages', $this->_rendered);
        }
    }

    /**
     * process information grouped in array and save it in $this->_rendered
     */
    protected function _processInformation()
    {
        foreach ($this->_information as $information) {
            if (!isset($information['type'])) {
                $this->error('warning', 'missing_file_url', '');
                return;
            }

            $type       = $information['type'];
            $renderer   = new display_class(array(
                'independent' => TRUE,
                'template'    => 'modules/commit/layouts/' . $type . '.html'
            ));

            $renderer->generate('file_path', trim($information['path']));

            if (isset($information['from'])) {
                $renderer->generate('from', trim($information['from']));
            }

            if (isset($information['to'])) {
                $renderer->generate('to', trim($information['to']));
            }

            if (isset($information['remove'])) {
                $renderer->generate('remove', trim($information['remove']));
            }

            if (isset($information['added'])) {
                $renderer->generate('added', trim($information['added']));
            }

            if (isset($information['mUpdate'])) {
                $renderer->generate('mUpdate', trim($information['mUpdate']));
            }

            if (isset($information['mAdd'])) {
                $renderer->generate('mAdd', trim($information['mAdd']));
            }

            if (isset($information['mRemove'])) {
                $renderer->generate('mRemove', trim($information['mRemove']));
            }

            $this->_rendered .= $renderer->render();
            $this->_rendered .= "\n";
        }
    }

    /**
     * create main block for messages in array
     * 
     * @param string $key
     * @param string $value
     */
    protected function _createBlock($key, $value)
    {
        $bool = preg_match('#(modifyVersion|modify|removed|add)_[\d]+#', $key);

        if ($bool) {
            $key  = explode('_', $key);

            $this->_information[$key[1]]['path'] = $value;
            $this->_information[$key[1]]['type'] = $key[0];
        }
    }

    /**
     * create description from messages in array
     * 
     * @param string $key
     * @param string $value
     */
    protected function _createBlockDescriptions($key, $value)
    {
        $newLine    = '';
        $bool       = preg_match(
            '#(mAdd|mUpdate|mRemove|modify|remove|added|from|to)_[\d]+_[\d]+#',
            $key
        );

        if ($bool) {
            $key  = explode('_', $key);

            if (!isset($this->_information[$key[1]][$key[0]])) {
                $this->_information[$key[1]][$key[0]] = '';
            }

            if ($key[0] === 'mAdd' || $key[0] === 'mUpdate' || $key[0] === 'mRemove') {
                $newLine = "\n                ";
            }

            $convert = stripslashes($value);
            $this->_information[$key[1]][$key[0]] .= $convert . $newLine;
        }
    }

    /**
     * attache to content main information
     * 
     * @param string $key
     * @param string $value
     */
    protected function _createBaseInfo($key, $value)
    {
        if ($key === 'title') {
            $this->generate('title', stripslashes($value));
        }

        if ($key === 'version') {
            $this->generate('version', stripslashes($value));
        }

        if ($key === 'issue') {
            $this->generate('issue', stripslashes($value));
        }

        if ($key === 'alpha') {
            $this->generate('alpha', '' . "\n");
        }

        if ($key === 'description') {
            $this->generate('description', stripslashes($value));
        }
    }

    /**
     * create layout for generator, and rendered commit
     */
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

    public function runErrorMode(){}
}
