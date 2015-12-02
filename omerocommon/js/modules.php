<?php
/**
 * Created by PhpStorm.
 * User: kikkomep
 * Date: 12/2/15
 * Time: 9:30 AM
 */

defined('MOODLE_INTERNAL') || die();

function init_js_modules()
{
    global $CFG, $PAGE, $plugins;

    // include the $plugins definition
    include $CFG->dirroot . "/question/type/omerocommon/jquery/plugins.php";

    // basic jquery requirements
    $PAGE->requires->jquery();
    $PAGE->requires->jquery_plugin('ui');
    $PAGE->requires->jquery_plugin('ui-css');

    // includes all plugin requirements
    foreach($plugins as $name => $module){

        if ($CFG->debug) {
            $PAGE->requires->jquery_plugin($name, "qtype_omerocommon");
        } else {
            echo "TODO: set the JS production files!!!";
        }
    }
}