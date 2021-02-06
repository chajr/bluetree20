<?php
/**
 * handling meta tags and page title
 * load and and start libraries and modules
 * 
 * @category    BlueFramework
 * @package     BlueFramework Core
 * @subpackage  meta
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 * @version     2.2.2
 */
class meta_class
{
    /**
     * contains inforamtion that meta class is on
     * @var boolean
     */
     private $_on = TRUE;

    /**
     * list of meta tags to use in page
     * @var array
     */
     private $_meta = array('title' => '');

    /**
     * complete meta tag string
     * @var string
     */
     private $_completeMeta = '';

    /**
     * read xml with meat definitions, check that meta is on, 
     * set array with specific values
     * 
     * @param array $get list of run pages
     * @throws coreException core_error_18
     */
     public function __construct($get)
     {
         if (class_exists('tracer_class')) {
             tracer_class::marker(array(
                 'start meta class',
                 debug_backtrace()
             ));
         }

         if (!(bool)core_class::options('meta')) {
             $this->_on = FALSE;
         } else {
            $xml    = new xml_class();
            $bool   = $xml->loadXmlFile(
                starter_class::path('cfg') . 'meta.xml',
                TRUE
            );

            if (!$bool) {
                throw new coreException(
                    'core_error_9',
                    starter_class::path('cfg') . 'meta.xml'
                );
            }

            $index = $xml->getId('index');
            if (!$index) {
                throw new coreException('core_error_18');
            }

            $this->_read($index);
            if (!empty($get)) {

                foreach ($get as $page) {
                    $element = $xml->getId($page);
                    if ($element) {
                        $this->_read($element);
                    }
                }
            }
        }
    }

     /**
      * add to meta complete meta tag
      * 
      * @param string $meta
      * @example add('<meta content="bluetree.pl powered by blueFramework 2.0" name="author"/>')
     */
    public function add($meta)
    {
        if ($this->_on) {
            $this->_completeMeta .= "\t\t$meta\n";
        }
    }

     /**
      * add content to specific tags
      * checks that meta tag exists in list and add content to him
      * 
      * @param string $type searched attribute
      * @param string $content dane do dopisania
      * @example insert('keywords', ', word 1, word 2') - add keywords
     */
    public function insert($type, $content)
    {
        if ($this->_on && isset($this->_meta[$type])) {
            $this->_meta[$type] .= ' ' . $content;
        }
    }

     /**
      * join meta tags to one string and put it on main template
      * 
      * @param display_class $display
     */
    public function render(display_class $display)
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'render metatags',
                debug_backtrace()
            ));
        }

        if ($this->_on) {
            $buffer = '';

            foreach ($this->_meta as $key => $val) {
                if ($key === 'title') {
                    continue;
                } else {
                    $buffer .= "\t\t" . '<meta content="' 
                        . $val . '" name="' . $key . '"/>' . "\n";
                }
            }

            $buffer .= "\t\t" . '<title>' . $this->_meta['title'] . '</title>' . "\n";
            $display->generate('core;meta', $buffer . $this->_completeMeta);
        }
    }

    /**
     * process elements taken from meta tag list
     * first check title and second rest of meta tags
     * 
     * @param DOMElement $element
     */
    private function _read(DOMElement $element)
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'read existing meta elements',
                debug_backtrace()
            ));
        }

        $update = $element->firstChild->getAttribute('update');
        if ((bool)$update && isset($this->_meta['title'])) {
            $this->_meta['title'] .= $element->firstChild->getAttribute('title');
        } else {
            $this->_meta['title'] = $element->firstChild->getAttribute('title');
        }
        
        $meta = $element->getElementsByTagName('meta');
        foreach ($meta as $element) {
            if ($element->getAttribute('name')) {
                $key = $element->getAttribute('name');
            } elseif ($element->getAttribute('http-equiv') ){
                $key = $element->getAttribute('http-equiv');
            }
            
            $update = $element->getAttribute('update');
            if ((bool)$update && isset($this->_meta[$key])) {
                $this->_meta[$key] .= $element->getAttribute('content');
            } else {
                $this->_meta[$key] = $element->getAttribute('content');
            }
        }
    }
}
