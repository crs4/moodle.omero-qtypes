<?php
/**
 * Created by PhpStorm.
 * User: kikkomep
 * Date: 12/2/15
 * Time: 9:30 AM
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Loads and initializes the JS modules
 *
 * @param $qtype_package the name of the qtype package
 */
function init_js_modules($qtype_package)
{
    global $CFG, $PAGE, $plugins;

    // Prefix of JS modules
    $module_prefix = "qtype_$qtype_package";

    // Array containing name of modules
    $modules = array();

    // include the $plugins definition
    include $CFG->dirroot . "/question/type/omerocommon/jquery/plugins.php";

    // basic jquery requirements
    $PAGE->requires->jquery();
    $PAGE->requires->jquery_plugin('ui');
    $PAGE->requires->jquery_plugin('ui-css');

    // includes all plugin requirements
    foreach ($plugins as $name => $module) {

        if ($CFG->debug) {
            $PAGE->requires->jquery_plugin($name, "qtype_omerocommon");
        } else {
            echo "TODO: set the JS production files!!!";
        }
    }

    // Detect whether use 'src' or 'dist' folder
    if ($CFG->debug) {
        $source_folder = $CFG->dirroot . "/question/type/$qtype_package/js/src";
    } else {
        echo "TODO: set the JS production files!!!";
        $source_folder = $CFG->dirroot . "/question/type/$qtype_package/js/dist";
    }


    // Scan JS folder
    foreach (scandir($source_folder) as $foldername) {

        chdir("$source_folder");
        if (strcmp($foldername, ".") !== 0
            && strcmp($foldername, "..") !== 0
            && is_dir("$foldername")
        ) {
            foreach (scandir($foldername) as $filename) {
                $fileinfo = pathinfo($filename);
                if (isset($fileinfo['extension']) && $fileinfo['extension'] === 'js') {
                    $PAGE->requires->js(new moodle_url("$CFG->wwwroot/question/type/$qtype_package/js/src/$foldername/$filename"));
                    array_push($modules, $fileinfo['filename']);
                }
            }
        }
    }

    // Call initialization function
    foreach($modules as $module){
        $PAGE->requires->js_call_amd("$module_prefix/$module", "initialize");
    }
}