<?php
/**
 * Created by PhpStorm.
 * User: kikkomep
 * Date: 12/10/15
 * Time: 5:07 PM
 */

defined('MOODLE_INTERNAL') || die();


function init_js_imageviewer($IMAGE_SERVER){

    global $CFG, $PAGE;

    $scripts = array(
        "/static/ome_seadragon/js/openseadragon.min.js",
        //"/static/ome_seadragon/js/jquery-1.11.3.min.js",
        "/static/ome_seadragon/js/paper-full.min.js",
        "/static/ome_seadragon/js/ome_seadragon.min.js",
        "/static/ome_seadragon/js/openseadragon-scalebar.min.js"
    );

    foreach($scripts as $script){
        $PAGE->requires->js(new moodle_url("$IMAGE_SERVER$script"), true);
    }

    //FIXME: replace with the new location
    $PAGE->requires->js(new moodle_url("$CFG->wwwroot/repository/omero/viewer/viewer-model.js"));
    //$PAGE->requires->js(new moodle_url("$CFG->wwwroot/repository/omero/viewer/viewer-controller.js"));
}