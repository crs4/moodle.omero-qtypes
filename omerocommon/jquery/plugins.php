<?php
/**
 * Created by PhpStorm.
 * User: kikkomep
 * Date: 11/25/15
 * Time: 5:33 PM
 */

defined('MOODLE_INTERNAL') || die();

// file: theme/sometheme/jquery/plugins.php
$plugins = array(

    'angular' => array(
        'files' => array(
            "angular/angular.1.3.14.js"
        )
    ),

    'bootstrap' => array(
        'files' => array(
            "bootstrap/bower_components/bootstrap/dist/js/bootstrap.min.js",
            "bootstrap/bower_components/bootstrap/dist/css/bootstrap.min.css"
        )
    ),

//    'dragtable' => array(
//        'files' => array(
//            "dragtable/jquery.dragtable.js",
//            "dragtable/dragtable.css"
//        )
//    ),

    'bootstrap-table' => array(
        'files' => array(
            "bootstrap-table/bower_components/bootstrap-table/dist/bootstrap-table-min.js",
            "bootstrap-table/bower_components/bootstrap-table/dist/bootstrap-table-min.css",
            "bootstrap-table/bower_components/bootstrap-table/dist/bootstrap-table-locale-all.min.js",
            "bootstrap-table/bower_components/bootstrap-table/dist/locale/bootstrap-table-en-US.min.js",
            "bootstrap-table/bower_components/bootstrap-table/dist/extensions/editable/bootstrap-table-editable.min.js",
            "bootstrap-table/bower_components/bootstrap-table/dist/extensions/editable/bootstrap-editable.js",
            "bootstrap-table/bower_components/bootstrap-table/dist/extensions/flat-json/bootstrap-table-flat-json.js",
            "bootstrap-table/bower_components/bootstrap-table/dist/extensions/reorder-columns/bootstrap-table-reorder-columns.js"

        )
    ),
);