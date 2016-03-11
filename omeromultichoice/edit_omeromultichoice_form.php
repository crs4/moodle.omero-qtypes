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
 * Defines the editing form for the omeromultichoice question type.
 *
 * @package    qtype
 * @subpackage omeromultichoice
 * @copyright  2015-2016 CRS4
 * @license    https://opensource.org/licenses/mit-license.php MIT license
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/omerocommon/edit_omerocommon_form.php');

/**
 * omeromultichoice question editing form definition.
 *
 * @copyright  2015-2016 CRS4
 * @license    https://opensource.org/licenses/mit-license.php MIT license
 */
class qtype_omeromultichoice_edit_form extends qtype_omerocommon_edit_form
{
    private $localized_strings = array(
        "questiontext", "generalfeedback",
        "correctfeedback", "partiallycorrectfeedback", "incorrectfeedback"
    );

    public function qtype()
    {
        return 'omeromultichoice';
    }

    /**
     * Updates the CSS/JS requirements for this form
     */
    protected function set_form_requirements()
    {
        parent::set_form_requirements();
        init_js_modules("omeromultichoice");
    }


    protected function definition()
    {
        global $CFG, $PAGE;
        parent::definition();

        $mform = $this->_form;
        $mform->addElement("hidden", self::EDITOR_INFO_ELEMENT_NAME);
        $mform->setType(self::EDITOR_INFO_ELEMENT_NAME, PARAM_RAW);
        $mform->setDefault(self::EDITOR_INFO_ELEMENT_NAME, json_encode(
                array(
                    "image_server" => get_config('omero', 'omero_restendpoint'),
                    "viewer_model_server" => $CFG->wwwroot . "/repository/omero/viewer/viewer-model.php",
                    "image_info_container_id" => $this->image_info_container_id,
                    "image_selector_id" => $this->image_selector_id,
                    "answer_header" => "id_answerhdr",
                    "fraction_options" => question_bank::fraction_options_full()
                )
            )
        );

        //--------------------------------------------------------------------------------------------
        //FIXME: just for debugging
        $PAGE->requires->js(new moodle_url("$CFG->wwwroot/repository/omero/viewer/viewer-model.js"));
        //--------------------------------------------------------------------------------------------

        $PAGE->requires->js_call_amd("qtype_omeromultichoice/question-editor-multichoice", "main",
            array(self::EDITOR_INFO_ELEMENT_NAME)
        );
    }

    protected function define_roi_table_inspector()
    {
        $mform = $this->_form;
        $mform->addElement('html', '

                        <div id="omero-image-viewer-toolbar" class="hidden">
                        <div class="checkboxx">
                          <div style="display: inline-block;">
                          <a id="omero-image-properties-update" href="javascript:void(0)" title="Update image center">
                            <i class="glyphicon glyphicon-screenshot"></i>
                          </a>
                          <span id="omero-image-viewer-properties">x: 123123, y: 12312312, zm: 123123123</span>
                          </div>
                          <div id="omero-image-view-lock-container">
                              <label for="omero-image-view-lock">' .
            get_string('image_viewer_student_navigation', 'qtype_omerocommon') .
            '</label>
                              <input id="omero-image-view-lock" name="omero-image-view-lock" data-toggle="toggle"
                                     type="checkbox" data-onstyle="success" data-offstyle="default"
                                     data-on="' . get_string('image_viewer_locked_student_navigation', 'qtype_omerocommon') . '"
                                     data-off="' . get_string('image_viewer_lock_student_navigation', 'qtype_omerocommon') . '">
                          </div>
                        </div>
        ');
        $mform->addElement('html', '</div>');


        $mform->addElement('header', 'roitableinspectorheader',
            get_string('roi_shape_inspector', 'qtype_omerocommon'), '');
        $mform->setExpanded('roitableinspectorheader', 0);

        $mform->addElement('html', '
            <div class="fitem" id="roi-shape-inspector-table-container" class="hidden">
                <div class="fitemtitle"><label for="roi-shape-inspector-table"></label></div>
                <div class="felement">

                <!-- TOOLBAR -->
                <div id="roi-shape-inspector-table-toolbar" class="hidden">

                </div>
                <!-- ROI TABLE -->
                <table id="roi-shape-inspector-table"
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
            </div>
');
    }

    protected function define_answer_section_commons_top()
    {
        $mform = $this->_form;

        $options = array();
        for ($i = 1; $i <= 10; $i++)
            array_push($options, "<li><a value=\"$i\" href=\"#\">$i</a></li>");

        $mform->addElement('html', '
            <div id="answers_toolbar" class="panel" style="text-align: right;">
            <div id="add_answer_button" class="btn-group" style="margin-bottom: 20px;">
              <button type="button" class="btn btn-info dropdown-toggle"
                      data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' .
            get_string('add_answers', 'qtype_omeromultichoice') .
            ' <span class="caret"></span>
              </button>
              <ul class="dropdown-menu">' . implode($options) . '</ul>' .
            '</div></div>'
        );
    }


    protected function define_answers_section()
    {
        $mform = $this->_form;

        // header
        parent::define_answers_section();
    }

    function display()
    {
        global $PAGE;
        parent::display(); // TODO: Change the autogenerated stub
        $PAGE->requires->js_init_code(
            'window.question = ' . json_encode($this->question)
        );
    }

    /**
     * Defines the set of locale strings used for JS modules
     *
     * @throws coding_exception
     */
    protected function export_locale_js_strings()
    {
        global $PAGE;
        parent::export_locale_js_strings();
        $PAGE->requires->string_for_js('correctansweris', 'qtype_omeromultichoice');
        $PAGE->requires->string_for_js('answer_text', 'qtype_omeromultichoice');
    }
}
