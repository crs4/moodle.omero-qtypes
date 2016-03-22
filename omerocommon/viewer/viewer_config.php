<?php

// Copyright (c) 2015-2016, CRS4
//
// Permission is hereby granted, free of charge, to any person obtaining a copy of
// this software and associated documentation files (the "Software"), to deal in
// the Software without restriction, including without limitation the rights to
// use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
// the Software, and to permit persons to whom the Software is furnished to do so,
// subject to the following conditions:
//
// The above copyright notice and this permission notice shall be included in all
// copies or substantial portions of the Software.
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
// FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
// COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
// IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
// CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

defined('MOODLE_INTERNAL') || die();

function init_js_imageviewer($IMAGE_SERVER){

    global $CFG, $PAGE;

    $scripts = array(
        "/static/ome_seadragon/js/openseadragon.min.js",
        "/static/ome_seadragon/js/paper-full.min.js",
        "/static/ome_seadragon/js/ome_seadragon.min.js",
        "/static/ome_seadragon/js/openseadragon-scalebar.min.js"
    );

    foreach($scripts as $script){
        $PAGE->requires->js(new moodle_url("$IMAGE_SERVER$script"), true);
    }

    // set the image server url
    $CFG->omero_image_server = $CFG->wwwroot . "/question/type/omerocommon/viewer/viewer-model.php";
}