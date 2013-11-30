<?php
class second extends module_class
{
    public function run()
    {
        $this->layout('second_template');
        $this->generate('another_module', 'second module content');
    }

    public function runErrorMode(){}
}