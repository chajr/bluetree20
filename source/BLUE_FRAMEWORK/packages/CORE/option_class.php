<?php
/**
 * read and return framework or module configuration
 *
 * @category    BlueFramework
 * @package     BlueFramework Core
 * @subpackage  configuration
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 * @version     2.3.0
 */
final class option_class 
{
    /**
     * contains array of all loaded options to avoid double file read
     * @var array
     */
    static protected $_optionsList = array();

    /**
     * load configuration file, and return list of all options
     * if was some error on file loading will return FALSE
     * 
     * @param string $module
     * @param boolean $forceRead
     * @return array|boolean 
    */
    public static function load($module = 'core', $forceRead = FALSE)
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'load configuration',
                debug_backtrace()
            ));
        }

        $xml = new xml_class();

        if (isset(self::$_optionsList[$module]) && !$forceRead) {
            return self::$_optionsList[$module];
        }

        if ($module === 'core') {
            $bool = $xml->loadXmlFile(
                starter_class::path('cfg') . 'config.xml',
                TRUE
            );
            if (!$bool) {
                $path = starter_class::path('cfg');
                echo 'Main configuration load error<br/>' . $path . 'config.xml<br/>';
                echo $xml->err;
                exit;
            }
        } else {
            $path = starter_class::path('modules/' . $module . '/elements');
            $bool = $xml->loadXmlFile($path . 'config.xml', TRUE);

            if (!$bool) {
                return FALSE;
            }
        }

        self::$_optionsList[$module] = self::_getAllOptions($xml);
        return self::$_optionsList[$module];
    }

    /**
     * get single framework option
     * 
     * @example show ('debug') - get option named debug
     * @param string $option
     * @param string $module
     * @param boolean $forceRead
     * @return mixed
     */
    public static function show($option, $module = 'core', $forceRead = FALSE)
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'get single option',
                debug_backtrace()
            ));
        }

        $xml    = new xml_class();

        if (isset(self::$_optionsList[$module][$option]) || !$forceRead) {
            return self::$_optionsList[$module][$option];
        }

        if ($module === 'core') {
            $path = starter_class::path('cfg') . 'config.xml';
        } else {
            $path = starter_class::path(
                'modules/' . $module . '/elements/config.xml'
            );
        }

        $bool = $xml->loadXmlFile($path, TRUE);
        if (!$bool) {
            return FALSE;
        }

        self::$_optionsList[$module] = self::_getAllOptions($xml);
        return self::$_optionsList[$module][$option];
    }

    /**
     * read all options from configuration file
     * 
     * @param xml_class $xml
     * @return array
     */
    protected static function _getAllOptions(xml_class $xml)
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'read all options',
                debug_backtrace()
            ));
        }

        $options = array();

        $list = $xml->documentElement;
        foreach ($list->childNodes as $nod) {
            if($nod->nodeType != 1){
                continue;
            }

            if($nod->nodeName === 'langs'){
                $val = array();

                foreach ($nod->childNodes as $lang) {
                    if ((bool)$lang->getAttribute('on')) {
                        $val[] = $lang->getAttribute('id');
                    }
                }
            } else {
                $val = $nod->getAttribute('value');
            }

            $id = $nod->getAttribute('id');
            $options[$id] = $val;
        }

        return $options;
    }
}
