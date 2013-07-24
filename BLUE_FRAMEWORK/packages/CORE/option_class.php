<?php
/**
 * read and return framework or module configuration
 *
 * @category    BlueFramework
 * @package     BlueFramework Core
 * @subpackage  configuration
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 * @version     2.1.0
 */
final class option_class 
{
    /**
     * load configuration file, and return list of all options
     * if was some error on file loading will return FALSE
     * 
     * @param string|boolean $module
     * @return array|boolean 
    */
    public static function load($module = NULL)
    {
        $options = array();
        $xml     = new xml_class();

        if ($module) {
            $path = starter_class::path('modules/' . $module . '/elements');
            $bool = $xml->loadXmlFile($path . 'config.xml', TRUE);

            if (!$bool) {
                return FALSE;
            }
        } else {
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
        }

        $list = $xml->documentElement;
        foreach ($list->childNodes as $nod) {
            if($nod->nodeType != 1){
                continue;
            }

            if($nod->nodeName == 'langs'){
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

    /**
     * get single framework option
     * 
     * @example show ('debug') - get option named debug
     * @static
     * @param string $option
     * @return mixed
     * 
     * @todo add possibility to return single option for module
    */
    public static function show($option)
    {
        $xml = new xml_class();
        $xml->loadXmlFile(starter_class::path('cfg') . 'config.xml', TRUE);
        $val = $xml->getId($option)->getAttribute('value');

        return $val;
    }
}
