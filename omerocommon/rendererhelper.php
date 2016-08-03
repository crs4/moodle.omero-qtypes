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

/**
 * qtype_omerocommon_renderer_helper class.
 *
 * @package    qtype
 * @subpackage omerocommon
 * @copyright  2015-2016 CRS4
 * @license    https://opensource.org/licenses/mit-license.php MIT license
 */


defined('MOODLE_INTERNAL') || die();


require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/engine/lib.php');
require_once($CFG->dirroot . '/question/type/multichoice/questiontype.php');
require_once($CFG->dirroot . '/question/type/multichoice/question.php');


/**
 * Represents a basic omero question type.
 */
class qtype_omerocommon_renderer_helper
{

    const MODAL_VIEWER_ELEMENT_ID = "modalImageDialogPanel";

    public static function modal_viewer($hide_save_button = false,
                                        $hide_toolbar = false,
                                        $element_id = self::MODAL_VIEWER_ELEMENT_ID)
    {
        $modal_image_dialog_panel_id = $element_id;
        $modal_image_header_id = $modal_image_dialog_panel_id . "-header";
        $modal_image_header_title_id = $modal_image_header_id . "-title";
        $modal_image_body_id = $modal_image_dialog_panel_id . "-body";
        $modal_image_footer_id = $modal_image_dialog_panel_id . "-footer";
        $modal_image_graphics_container_id = $modal_image_dialog_panel_id . "-graphics_container";
        $modal_image_viewer_container = $modal_image_dialog_panel_id . "-image-viewer-container";
        $modal_image_annotation_canvas = $modal_image_dialog_panel_id . "-annotations_canvas";
        $modal_image_loading_dialog = $modal_image_dialog_panel_id . "-image-viewer-container-loading-dialog";
        $modal_image_roi_inspector_container_id = $modal_image_dialog_panel_id . "-roi-shape-inspector-table-container";
        $modal_image_roi_inspector_toolbar_id = $modal_image_dialog_panel_id . "-roi-shape-inspector-table-toolbar";
        $modal_image_roi_inspector_table_id = $modal_image_dialog_panel_id . "-roi-shape-inspector-table";

        $modal_image_toolbar = $modal_image_dialog_panel_id . "-toolbar";
        $modal_image_update_properties = $modal_image_dialog_panel_id . "-update-image-properties";
        $modal_image_properties = $modal_image_dialog_panel_id . "-image-properties";
        $modal_image_view_lock_container = $modal_image_dialog_panel_id . "-image-lock-container";
        $modal_image_view_lock = $modal_image_dialog_panel_id . "-view-lock";

        $modal_image_viewer_html = '<div id="' . $modal_image_graphics_container_id . '" class="image-viewer-container" style="position: relative;" >
            <div id="' . $modal_image_viewer_container . '" style="position: absolute; width: 100%; height: 500px; margin: auto;"></div>
            <canvas id="' . $modal_image_annotation_canvas . '" style="position: absolute; width: 100%; height: 500px; margin: auto;"></canvas>
            <div id="' . $modal_image_loading_dialog . '" class="image-viewer-loading-dialog"></div>
        </div>';

        if (!$hide_toolbar) {
            $modal_image_viewer_html .= '
            <div id="' . $modal_image_toolbar . '" class="hidden">
                <div class="checkboxx">
                    <div style="display: inline-block;">
                        <a id="' . $modal_image_update_properties . '" href="javascript:void(0)" title="Update image center">
                            <i class="glyphicon glyphicon-screenshot"></i>
                        </a>
                        <span id="' . $modal_image_properties . '">x: 123123, y: 12312312, zm: 123123123</span>
                     </div>
                     <div id="' . $modal_image_view_lock_container . '">
                        <label for="omero-image-view-lock">'
            . get_string('image_viewer_student_navigation', 'qtype_omerocommon') . '
                        </label>
                        <input id="' . $modal_image_view_lock . '" name="omero-image-view-lock" data-toggle="toggle"
                               type="checkbox" data-onstyle="success" data-offstyle="default"
                               data-on="' . get_string('image_viewer_locked_student_navigation', 'qtype_omerocommon') . '"
                               data-off="' . get_string('image_viewer_lock_student_navigation', 'qtype_omerocommon') . '">
                      </div>
                 </div>
             </div>';


        $modal_image_viewer_html .= '<div id="' . $modal_image_roi_inspector_container_id . '">
                <div ><label for="' . $modal_image_roi_inspector_table_id . '"></label></div>
                <div >

                <!-- TOOLBAR -->
                <div id="' . $modal_image_roi_inspector_toolbar_id . '" class="hidden">

                </div>
                <!-- ROI TABLE -->
                <table id="' . $modal_image_roi_inspector_table_id . '"
                       data-toolbar="#toolbar"
                       data-search="true"
                       data-height="400"
                       data-show-refresh="true"
                       data-show-toggle="true"
                       data-show-columns="true"
                       data-show-export="true"
                       data-detail-view="false"
                       data-minimum-count-columns="2"
                       data-show-pagination-switch="false"
                       data-pagination="false"
                       data-id-field="id"
                       data-page-list="[10, 25, 50, 100, ALL]"
                       data-show-footer="false"
                       data-side-pagination="client">
                </table>
              </div>
            </div>';

        $container = '
            <div class="modal fade" id="modalImageDialogPanel" tabindex="-1" role="dialog" aria-labelledby="modalImageDialogLabel">
              <div class="modal-dialog" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title text-warning" id="modalImageDialogLabel">
                        <!--<i class="glyphicon glyphicon-warning-sign"></i>--> ' .
            get_string('omero_image_viewer', 'qtype_omerocommon') .
            '</h4>
                  </div>
                  <div class="modal-body text-left">
                    <div id="modal-frame-text">' . $modal_image_viewer_html . '</div>
                  </div>
                  <div class="modal-footer text-center">
                    <button type="button" class="save btn btn-default" data-dismiss="modal">' . get_string('savechangesandcontinueediting', 'qtype_omerocommon') . '</button>
                  </div>
                </div>
              </div>
            </div>';

        return $container;
    }


    public static function modal_dialog()
    {
        return '<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title text-warning" id="myModalLabel">
            <i class="glyphicon glyphicon-warning-sign"></i> ' . get_string('validate_warning', 'qtype_omerocommon') .
        '</h4>
      </div>
      <div class="modal-body text-left">
        <span id="modal-frame-text"></span>
      </div>
      <div class="modal-footer text-center">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>';
    }
}

