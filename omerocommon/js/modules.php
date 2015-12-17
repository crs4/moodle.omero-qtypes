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
function init_js_modules($qtype_package, $header=false)
{
    global $CFG, $PAGE, $plugins, $not_amd_modules;

    // Prefix of JS modules
    $module_prefix = "qtype_$qtype_package";

    // TODO: replace with no-debug mode
    $isdebug = true; // $CFG->debug

    // Array containing name of modules
    $modules = array();

    // checkes and load jquery dependencies
    $jquery_plugings_declaration = $CFG->dirroot . "/question/type/$qtype_package/jquery/plugins.php";
    if (file_exists($jquery_plugings_declaration)) {
        // include the $plugins definition
        include "$jquery_plugings_declaration";

        // basic jquery requirements
        $PAGE->requires->jquery();
        $PAGE->requires->jquery_plugin('ui');
        $PAGE->requires->jquery_plugin('ui-css');

        // includes all plugin requirements
        foreach ($plugins as $name => $module) {

            if ($isdebug) {
                $PAGE->requires->jquery_plugin($name, "qtype_$qtype_package");
            } else {
                echo "TODO: set the JS production files!!!";
            }
        }
    }

    // Detect whether use 'src' or 'dist' folder
    $isdebug = true; // $CFG->debug
    if ($isdebug) {
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
                    $PAGE->requires->js(new moodle_url("$CFG->wwwroot/question/type/$qtype_package/js/src/$foldername/$filename"), $header);
                    array_push($modules, $fileinfo['filename']);
                }
            }
        }
    }

    // loads the module configuration file is exists
    $modules_config_filename = $CFG->dirroot . "/question/type/$qtype_package/js/modules_config.php";
    if(file_exists($modules_config_filename)) {
        include "$modules_config_filename";
    }

    // Call initialization function for all AMD modules !!!
    // Note that all modules (but those in the $not_amd_modules global variable
    // are assumed to be AMD
    foreach ($modules as $module) {
        if(!in_array("$module_prefix/$module", $not_amd_modules))
            $PAGE->requires->js_call_amd("$module_prefix/$module", "initialize");
    }
}