<?php
/**
 * @category    BlueFramework
 * @package     modules
 * @subpackage  example_generate
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 * @version     1.0.0
 */
class example_generate
    extends module_class
{
    static $version             = '1.0.0';
    static $name                = 'content generate module example';
    public $requireLibraries    = array();
    public $requireModules      = array();

    /**
     * initialize module
     */
    public function run()
    {
        $this->_prepareLayout();

        $this->_simpleCoreMarker();
        $this->_simpleMarker();
        $this->_arrayMarker();
        $this->_optionalMarker();
        $this->_loopMarker();
        $this->_loopMarkerEmpty();
        $this->_loopOptionalMarker();
        $this->_nestedLoopMarker();
        $this->_loopWithMissingElements();
        $this->_sessionData();
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
     * replace simple marker in core template
     */
    protected function _simpleCoreMarker()
    {
        $this->generate('content_from_module', '{;lang;content_to_replace;}', TRUE);
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

    /**
     * replace marker for optional markers
     */
    protected function _optionalMarker()
    {
        $this->generate('marker_a', '');
    }

    /**
     * show data from array in loop
     */
    protected function _loopMarker()
    {
        $list = [
            [
                'item1' => '{;lang;val;}: a1',
                'item2' => '{;lang;val;}: a2'
            ],
            [
                'item1' => '{;lang;val;}: b1',
                'item2' => '{;lang;val;}: b2'
            ]
        ];
        $this->loop('loop1', $list);
    }

    /**
     * give empty array to loo to show message about no content
     */
    protected function _loopMarkerEmpty()
    {
        $list = [];
        $this->loop('loop2', $list);
    }

    /**
     * loop example with optional content
     */
    protected function _loopOptionalMarker()
    {
        $arr = [
            [
                'aaa' => '{;lang;val;} aaa 1',
                'bbb' => '{;lang;val;} bbb 2',
                'op1' => 'op1'
            ],
            [
                'aaa' => '{;lang;val;} ccc 1',
                'bbb' => '{;lang;val;} ddd 2',
                'op2' => 'op2'
            ]
        ];
        $this->loop('loop3', $arr);
    }

    /**
     * nested loops example
     */
    protected function _nestedLoopMarker()
    {
        $nestedLoopBase = [
            [
                'id'    => 1,
                'name'  => '{;lang;val;} 1',
            ],
            [
                'id'    => 2,
                'name'  => '{;lang;val;} 2',
            ],
            [
                'id'    => 3,
                'name'  => '{;lang;val;} 3',
            ],
        ];

        $nestedLoopSecond = [
            [
                'id'    => 1,
                'name'  => '{;lang;sec_val;} 1',
            ],
            [
                'id'    => 2,
                'name'  => '{;lang;sec_val;} 2',
            ],
            [
                'id'        => 3,
                'name'      => '{;lang;sec_val;} 3',
                'optional'  => 'ok',
            ],
        ];

        $this->loop('nested_loop_group', $nestedLoopBase);

        foreach ($nestedLoopSecond as $catCategory) {
            $loopName = 'nested_loop_' . $catCategory['id'];
            $this->loop($loopName, $nestedLoopSecond);
        }
    }

    /**
     * show loop with some missing elements in template
     */
    protected function _loopWithMissingElements()
    {
        $list = [
            [
                'item1' => '{;lang;val;}: a1',
                'item2' => '{;lang;val;}: a2'
            ],
            [
                'item1' => '{;lang;val;}: b1',
                'item2' => '{;lang;val;}: b2'
            ]
        ];

        $this->loop('loop4', $list);
    }

    /**
     * how data directly from session
     */
    protected function _sessionData()
    {
        if ($this->session->value) {
            $this->session->value += 1;
        } else {
            $this->session->value = 1;
        }

        $this->setSession(
            'session_display_test',
            $this->session->value,
            'display'
        );
    }

    public function runErrorMode(){}
}
