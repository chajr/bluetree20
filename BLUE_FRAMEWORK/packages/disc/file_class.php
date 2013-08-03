<?php
/**
 * allow to manage file as object
 *
 * @category    BlueFramework
 * @package     dics
 * @subpackage  file
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 * @version     0.5.0
 */
class file_class 
    extends folder_class
{
    /**
     * contains error information
     * @var string
     */
    public $err;

    /**
     * create file, or read existing
     */
    public function __construct()
    {
        //if not exists, first run parent constructor to create directory
    }

    /**
     * return list of files in given directory, correct with given extensions
     * 
     * chect what function make, and check possibility to move to parent class
     * 
     * @param $path
     * @param bool $directories
     * @param bool $extensions
     * @param bool $type
     * @return array|bool|null
     */
    static function fileList($path, $directories = FALSE, $extensions = FALSE, $type = TRUE)
    {
        $bool   = is_dir($path);
        $list   = array();

        if (!$bool) {
            return FALSE;
        }

        $handle = opendir($path);

        if ($handle) {

            if ($extensions) {
                if (!is_array($extensions)) {
                    $extensions = explode(',', trim($extensions));
                }
            }

            while ($content = readdir($handle)) {
                if ($content === '.' || $content === '..') {
                    continue;
                }

                if (!$directories && is_dir($path . '/' . $content)) {
                    continue;
                }

                if ($extensions && is_file($path . '/' . $content)) {
                    $ext    = pathinfo($content);
                    $bool   = in_array($ext['extension'], $extensions);

                    if ($type && $bool) {
                        $list[] = $content;
                    } elseif (!$type && !$bool) {
                        $list[] = $content;
                    }

                } else {
                    $list[] = $content;
                }
            }

        } else {
            return NULL;
        }

        closedir($handle);
        return $list;
    }

    /**
     * create a new file
     * 
     * @param $path
     * @param mixed $data
     * @return bool
     */
    public function newFile($path, $data = FALSE)
    {
        $bool = @fopen($path, 'w');

        if (!$bool) {
            return FALSE;
        }

        if ($data) {
            $bool = @fwrite($bool, $data);

            if (!$bool) {
                return FALSE;
            }
        }

        @fclose($bool);
        chmod($path, 0777);
        return TRUE;
    }

    /**
     * remove file
     * 
     * @param $path
     * @return bool|void
     */
    public function deleteFile($path)
    {
        $bool = @unlink($path);

        if (!$bool) {
            return FALSE;
        }

        return TRUE;
    }

    /**
     * move file to other location
     * 
     * @param $loc_1
     * @param $loc_2
     * @param bool $rename
     */
    public static function move($loc_1, $loc_2, $rename = FALSE){

    }

    /**
     * 
     */
    public function save()
    {
        
    }

    /**
     * @return bool|void
     */
    public function copyFile()
    {
        
    }
    
}
