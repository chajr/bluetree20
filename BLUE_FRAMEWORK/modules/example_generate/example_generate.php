<?php
/**
 * @category    BlueFramework
 * @package     modules
 * @subpackage  example_generate
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 * @version     0.3.0
 */
class example_generate
    extends module_class
{
    static $version             = '0.3.0';
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
        $this->set('css', 'css');

        $this->_simpleMarker();
        $this->_arrayMarker();
        $this->_optionalMarker();
        $this->_loopMarker();
        $this->_loopMarkerEmpty();
        $this->_nestedLoopMarker();
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

    public function runErrorMode(){}
}
