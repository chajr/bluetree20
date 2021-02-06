<?php
/**
 * allow to manage directory as object
 *
 * @category    BlueFramework
 * @package     dics
 * @subpackage  file
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 * @version     0.5.0
 */
class folder_class 
    extends disc_class
{
    /**
     * list of founded inside directory elements
     * @var
     */
    public $elements;

    /**
     * directory size ik bytes
     * @var
     */
    public $size;

    /**
     * create or read given directory
     * 
     * @param $path
     */
    public function __construct($path)
    {
        if (!file_exists($path)) {
            //create directory
        } else {
            //read directory
        }
    }

    /**
     * 
     */
    public function deleteDirectory()
    {

    }

    /**
     * 
     */
    public function moveDirectory()
    {

    }

    /**
     * 
     */
    public function renameDirectory()
    {
        
    }
}
