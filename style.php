<?php
/**
 * allow access to protected core or module styles
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 * @version     1.0.0
 */
if (isset($_GET['module']) && isset($_GET['style'])) {
    $module = $_GET['module'];
    $style  = $_GET['style'];
    $path   = 'BLUE_FRAMEWORK/';

    if ($module === 'core') {
        $path .= 'elements/css/' . $style . '.css';
    } else {
        $path .= 'modules/' . $module . '/elements/css/' . $style . '.css';
    }

    if (file_exists($path)) {
        header('Content-type: text/css');
        echo file_get_contents($path);
    }
}