<?php
/**
 * index file, start whole framework
 *
 * @category    BlueFramework
 * @package     Start
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 * @version     2.2.0
 */
starter_class::load('packages/tester/benchmark_class.php');
benchmark_class::start();

$bool = starter_class::load('packages/CORE/error_class.php');
if (!$bool) {
    die ('missing error_class :(');
}

ob_start('fatal');
if (!isset($_SESSION)) {
    session_start();
}

benchmark_class::znacznik('start error handling and session');
starter_class::run();
benchmark_class::znacznik('the end');

ob_end_flush();
benchmark_class::stop();

echo benchmark_class::display();

/**
 * framework core
 * runs libraries and modules, allows to exchange data between them, return ready content to display
 * all modules and libraries are lunched inside this class
 *
 * @category    BlueFramework
 * @package     Start
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 * @version     2.1.0
 * @final
 */
final class starter_class
{
    /**
     * start framework, display content or errors
     * load class for error handling, start buffering, sets error handling
     * start framework core, display content or errors
     * 
     * @throws coreException core_error_0, core_error_1
    */
    static final function run()
    {
        error_reporting (E_ERROR | E_WARNING | E_PARSE | E_NOTICE | E_STRICT | E_ALL);
        set_error_handler ("error");

        try {
            $bool = starter_class::package('CORE');
            benchmark_class::znacznik('load core package');

            if (!$bool) {
                throw new coreException('core_error_0');
            }

            $core = new core_class();
            if ($core->render !== NULL && !empty($core->render) && $core->render ){
                echo $core->render;
                unset($core->render);
            } else {
                throw new coreException ('core_error_1');
            }

        } catch (coreException $errorCore) {
            $errorCore->showCore();
        }
    }

    /**
     * allows to load framework files, return information about loaded files
     * can return content of file, or some of variables from file
     * 
     * @param string $path
     * @param boolean|string $read if TRUE file will be read, if string return variable given as string value
     * @param string $type how file will be loaded (default - require_once)
     * @return mixed
     * @example load('cfg/config')
     * @example load('elements/layouts/index.html', TRUE) - read content
     * @example load('cfg/lang/core_pl.php', 'content') - read given variable
     * @example load('cfg/lang/packages/CORE/core_class.php', 0, 'require')
     */
    static final function load($path, $read = FALSE, $type = '')
    {
        $path = self::path().$path;
        
        if (!file_exists($path)) {
               return FALSE;
        }

        if ($read) {
            if ($read === TRUE) {
                return file_get_contents($path);
               } else {
                include ($path);

                if (isset($$read)) {
                    return $$read;
                } else {
                    return FALSE;
                }
            }
        }

        switch ($type) {
            case'require':
                require($path);
                break;

            case'include':
                include ($path);
                break;

            case'include_once':
                include_once ($path);
                break;

            default:
                require_once ($path);
                break;
           }

        return TRUE;
    }

    /**
     * allows to load packages
     * all package files if only package name,
     * or single files from package separated by comma
     * load only files with suffix _class, _interface or _abstract
     * 
     * @param string $pack package name, or files to load
     * @return array|boolean list of loaded files or FALSE on error
     * @example pack('CORE') - all files from package
     * @example pack('CORE/core_class') - single library
     * @example pack('CORE/core_class,error_class,some_abstract') - couple of libraries
     */
    static final function package($pack)
    {
        $preg = preg_match('#^[\w-]+\/([\w-]+[,]?)+$#', $pack);

        if($preg){
            $pack = explode('/', $pack);
            $list = explode(',', $pack[1]);
            $pack = $pack[0];
        }

        $handler = opendir(self::path('packages') . $pack);

        if ($handler) {
            $array = array();
            
            while ($file = readdir($handler)) {
                $type = preg_match(
                    '#^[\\w-]+(_(class|interface|abstract)\.php){1}$#',
                    $file
                );

                if ($type) {
                    $short = str_replace('_class.php', '', $file);
                    $short = str_replace('_interface.php', '', $short);
                    $short = str_replace('_abstract.php', '', $short);

                    if ($preg) {
                        $bool = in_array($short, $list);
                           if (!$bool) {
                            continue;
                        }
                    }

                    $bool = self::load('packages/' . $pack . '/' . $file);
                    $array[] = $short;

                    if (!$bool) {
                        closedir($handler);
                        return FALSE;
                    }
                }
            }

            closedir($handler);
        }
        return $array;
    }

    /**
     * return main path
     * return value of dirname(__FILE__).'/BLUE_FRAMEWORK/ 
     * or path given in parameter or main path
     * if parameter is string create path from given dir,
     * if FALSE, main framework directory,
     * if is TRUE path to index.php
     * 
     * @param string|boolean $pack
     * @return string
     * @example path() - return path to framework directory
     * @example path('elements/layouts'); - path to given directory
     * @example path(TRUE) - return main path index.php
     */
    static final function path($pack = FALSE)
    {
        if ($pack === TRUE) {
            return dirname(__FILE__);
        } elseif ($pack) {
            $pack .= '/';
        } else {
            $pack = '';
        }

        return dirname(__FILE__) . '/BLUE_FRAMEWORK/' . $pack;
    }
}
