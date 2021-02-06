<?php
/**
 * base operations on files and directories
 *
 * @category    BlueFramework
 * @package     dics
 * @subpackage  disc
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 * @version     1.0.0
 */
class disc_class
{
    /**
     * restricted characters for file and directory names
     * @var string
     */
    const RESTRICTED_SYMBOLS = '#[:?*<>"|\\\]#';

    /**
     * remove file or directory with all content
     * 
     * @param string $path
     * @return boolean information that operation was successfully, or NULL if path incorrect
     */
    static function delete($path)
    {
        $bool = TRUE;

        if(!file_exists($path)){
            return NULL;
        }
        
        @chmod($path, 0777);
        
        if (is_dir($path)) {
            
            $list   = self::readDirectory($path);
            $paths  = self::returnPaths($list, $path, TRUE);

            if (isset($paths['file'])) {
                foreach ($paths['file'] as $val) {
                    unlink($val);
                }
            }

            if (isset($paths['dir'])) {
                foreach ($paths['dir'] as $val) {
                    rmdir($val);
                }
            }

            rmdir($path);
        } else {
               $bool = @unlink($path);
        }

        return $bool;
    }

    /**
     * copy file or directory to given source
     * if source directory not exists, create it
     * 
     * @param string $path
     * @param string $target
     * @return boolean information that operation was successfully, or NULL if path incorrect
     */
    static function copy($path, $target)
    {
        if (!files::exist($path)) {
            return NULL;
        }

        if (is_dir($path)) {

            if (!files::exist($target)) {
                mkdir($target);
            }

            $elements   = self::readDirectory($path);
            $paths      = self::returnPaths($elements, '');

            foreach ($paths['dir'] as $dir) {
                mkdir($dir);
            }

            foreach ($paths['file'] as $file) {
                copy($path . "/$file", $target . "/$file");
            }

        } else {

            if (!$target) {
                $filename   = explode('/', $path);
                $target     = end($filename);
            } else {

                $bool = self::mkdir($target);
                if ($bool) {
                    return NULL;
                }
            }

            $bool = copy($path, $target);
        }

        return $bool;
    }

    /**
     * create new directory in given location
     * 
     * @param string $path
     * @return boolean
     * @static
     */
    static function mkdir($path)
    {
        $bool = preg_match(self::RESTRICTED_SYMBOLS, $path);

        if (!$bool) {
            $bool = mkdir ($path);
            return $bool;
        }

        return FALSE;
    }

    /**
     * create empty file, and optionally put in them some data
     * 
     * @param string $path
     * @param string $fileName
     * @param mixed $data
     * @return boolean information that operation was successfully, or NULL if path incorrect
     * @example mkfile('directory/inn', 'file.txt')
     * @example mkfile('directory/inn', 'file.txt', 'Lorem ipsum')
     */
    static function mkfile($path, $fileName, $data = NULL)
    {
        if (!files::exist($path)) {
            return NULL;
        }

        $bool = preg_match(self::RESTRICTED_SYMBOLS, $fileName);

        if (!$bool) {
            $f = @fopen("$path/$fileName", 'w');
            fclose($f);
            
            if ($data) {
                $bool = file_put_contents("$path/$fileName", $data);
                return $bool;
            }
        }

        return FALSE;
    }

    /**
     * change name of file/directory
     * also can be used to copy operation
     * 
     * @param string $path original path or name
     * @param string $target new path or name
     * @return boolean information that operation was successfully, or NULL if path incorrect
     */
    static function rename($path, $target)
    {
        if (!file_exists($path)) {
            return NULL;
        }

        if (files::exist($target)) {
            return FALSE;
        }

        $bool = preg_match(self::RESTRICTED_SYMBOLS, $target);

        if (!$bool) {
            $bool = rename($path, $target);
            return $bool;
        }

        return FALSE;
    }

    /**
     * move file or directory to given target
     * 
     * @param $path
     * @param $target
     */
    static function move($path, $target)
    {

    }

    /**
     * read directory content, and return all inside directories (with their content)
     * sorted by array_multisort function
     * 
     * @param string $path
     * @return array array with given path structure, or NULL if path incorrect
     * @example readDirectory('dir/some_dir')
     * @example readDirectory(); - read __FILE__ destination
     */
    static function readDirectory($path = NULL)
    {
        $list = array();

        if (!$path) {
            $path = dirname(__FILE__);
        }

        if (!files::exist($path)) {
            return NULL;
        }

        $handle = opendir($path);
        if ($handle) {

            while ($element = readdir($handle) ){
                if ($element === '..' || $element === '.') {
                    continue;
                }

                if (is_dir("$path/$element")) {
                    $list[$element] = self::readDirectory("$path/$element");
                } else {
                    $list[] = $element;
                }
            }

            closedir($handle);
        }

        array_multisort($list);
        return $list;
    }

    /**
     * transform array wit directory/files tree to list of paths grouped on files and directories
     * 
     * @param array $array array to transform
     * @param string $path base path for elements, if emty use paths from transformed structure
     * @param boolean $reverse if TRUE revert array (required for deleting)
     * @return array array with path list for files and directories
     * @example returnPaths($array, '')
     * @example returnPaths($array, '', TRUE)
     * @example returnPaths($array, 'some_dir/dir', TRUE)
     */
    static function returnPaths($array, $path = '', $reverse = FALSE)
    {
        if($reverse){
            $array = array_reverse($array);
        }

        $pathList = array();

        foreach ($array as $key => $val) {
            if (is_dir($path . "/$key")) {

                $list = self::returnPaths($val, $path . "/$key");
                foreach ($list as $element => $value) {

                    if ($element === 'file') {
                        foreach ($value as $file) {
                            $pathList['file'][] = "$file";
                        }
                    }

                    if ($element === 'dir') {
                        foreach ($value as $dir) {
                            $pathList['dir'][] = "$dir";
                        }
                    }

                }
                $pathList['dir'][] = $path . "/$key";

            } else {
                $pathList['file'][] = $path . "/$val";
            }
        }

        return $pathList;
    }
}
