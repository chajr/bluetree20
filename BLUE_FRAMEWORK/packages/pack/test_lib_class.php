<?php
class test_lib_class
{
    protected $_param = NULL;

    public function __construct ($param)
    {
        $this->param = $param;
    }

    public function getParam($transform = FALSE)
    {
        if ($transform) {
            return strtoupper($this->param);
        }
        return $this->param;
    }
}