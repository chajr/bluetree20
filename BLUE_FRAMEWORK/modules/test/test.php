<?php
class test extends module_class
{
    public function run()
    {
        $this->_translate();
        $this->layout('test_template');
        $this->generate('hello_marker', $this->prepareContent());
    }

    public function prepareContent()
    {
        $string     = 'bla bla bla';
        $content    = '';
        $lib        = new test_lib_class($string);

        $content .= $lib->getParam();
        $content .= ' - ';
        $content .= $lib->getParam(TRUE);

        return $content;
    }

    public function runErrorMode()
    {
        $this->error(
            'ok',
            'success',
            'module started in error mode'
        );
    }
}