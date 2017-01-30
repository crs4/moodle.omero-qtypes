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
 * Defines the editing form for the omerointeractive question type.
 *
 * @package    qtype
 * @subpackage omerointeractive
 * @copyright  2015-2016 CRS4
 * @license    https://opensource.org/licenses/mit-license.php MIT license
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/omerocommon/edit_omerocommon_form.php');

/**
 * omerointeractive question editing form definition.
 *
 * @copyright  2015-2016 CRS4
 * @license    https://opensource.org/licenses/mit-license.php MIT license
 */
//class qtype_omerointeractive_edit_form extends qtype_omerocommon_edit_form
class qtype_omerointeractive_edit_form extends qtype_omerocommon_edit_form
{

    const ADD_ROI_TO_GROUP = "add-to-group-btn";
    const ADD_ROI_GROUP_LIST_OPTIONS = "add-roi-group-list-options";

    public function qtype()
    {
        return 'omerointeractive';
    }

    /**
     * Updates the CSS/JS requirements for this form
     */
    protected function set_form_requirements()
    {
        global $PAGE, $CFG;
        parent::set_form_requirements();
        init_js_modules("omerointeractive");
        $PAGE->requires->css(new moodle_url("$CFG->wwwroot/question/type/omerointeractive/css/question-editor-interactive.css"));
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
                    "image_server_api_version" => get_config('omero', 'omero_apiversion'),
                    "viewer_model_server" => $CFG->omero_image_server,
                    "image_info_container_id" => $this->image_info_container_id,
                    "image_selector_id" => $this->image_selector_id,
                    "answer_header" => "id_answergroupsheader",
                    "fraction_options" => question_bank::fraction_options_full(),
                    "add_to_group_element_id" => self::ADD_ROI_TO_GROUP,
                    "add_to_group_list_element_id" => self::ADD_ROI_GROUP_LIST_OPTIONS
                )
            )
        );

        global $PAGE;
        $PAGE->requires->js_call_amd("qtype_omerointeractive/question-editor-interactive", "main",
            array(self::EDITOR_INFO_ELEMENT_NAME)
        );
    }


    protected function define_answer_section_header()
    {
        return array(
            "answergroupsheader",
            get_string('answer_groups', 'qtype_omerointeractive')
        );
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
            get_string('add_answers', 'qtype_omerointeractive') .
            ' <span class="caret"></span>
              </button>
              <ul class="dropdown-menu">' . implode($options) . '</ul>' .
            '</div></div>'
        );
    }

    protected function define_answers_section()
    {
        parent::define_answers_section();
        $this->_form->addElement('html', '
        <ul id="roishape-answer-option-ctx-menu" class="dropdown-menu" role="menu" style="display:none" >
            <li class="divider"></li>
            <!--<li><a tabindex="-1" href="javascript:void(0)">Separated link</a></li>-->
            <li>
                <a id="roishape-answer-option-delete" tabindex="-1" href="javascript:void(0)">
                <i class="glyphicon glyphicon-remove-sign"></i>
                Remove</a>
            </li>
        </ul>
        ');
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
                          <span id="omero-image-viewer-properties"></span>
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
        $mform->setExpanded('roitableinspectorheader', 1);

        $mform->addElement('html', '
            <div class="fitem" id="roi-shape-inspector-table-container" class="hidden">
                <div class="fitemtitle"><label for="roi-shape-inspector-table"></label></div>
                <div class="felement">

                <!-- TOOLBAR -->
                <div id="roi-shape-inspector-table-toolbar" class="hidden">

                    <!-- Single button -->
                    <div class="btn-group">
                      <button id="' . self::ADD_ROI_TO_GROUP . '"
                              type="button" class="btn btn-info dropdown-toggle disabled"
                              data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' .
            get_string('answer_group', 'qtype_omerointeractive') .
            ' <span class="caret"></span>
                      </button>
                      <ul id="' . self::ADD_ROI_GROUP_LIST_OPTIONS . '" class="dropdown-menu option input-small " role="menu">
                        <li><a href="#">0</a></li>
                        <li><a href="#">1</a></li>
                        <li role="separator" class="divider"></li>
                        <li><a href="#">Add Group</a></li>
                      </ul>
                    </div>
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


    /**
     * Defines the set of locale strings used for JS modules
     *
     * @throws coding_exception
     */
    protected function export_locale_js_strings()
    {
        global $PAGE;
        parent::export_locale_js_strings();
        $PAGE->requires->string_for_js('answer_group', 'qtype_omerointeractive');
        $PAGE->requires->string_for_js('answer_group_of_rois', 'qtype_omerointeractive');
        $PAGE->requires->string_for_js('validation_noroi_per_group', 'qtype_omerointeractive');
        $PAGE->requires->string_for_js('answer_group_removed_invalid_rois', 'qtype_omerointeractive');
    }


    /**
     * Perform the necessary preprocessing for the fields added by
     * {@link add_per_answer_fields()}.
     * @param object $question the data being passed to the form.
     * @return object $question the modified data.
     */
    protected function data_preprocessing_answers($question, $withanswerfiles = false)
    {
        if (empty($question->options->answers)) {
            return $question;
        }

        $key = 0;
        foreach ($question->options->answers as $answer) {
            // answer content & format
            $question->answer[$key] = ($answer->answer);
            $question->answerformat[$key] = $answer->answerformat;
            // answer fraction
            $question->fraction[$key] = 0 + $answer->fraction;
            unset($this->_form->_defaultValues["fraction[{$key}]"]);
            // answer feedback
            $question->feedback[$key] = ($answer->feedback);
            $question->feedbackformat[$key] = $answer->feedbackformat;

            $question->feedbackimages[$key] = empty($answer->images) ? json_encode(array()) : $answer->images;

            $question->feedback_locale_map[$key] = json_encode(qtype_omerocommon::serialize_to_json_from($answer->feedback));

            $key++;
        }

        // Now process extra answer fields.
        $extraanswerfields = question_bank::get_qtype($question->qtype)->extra_answer_fields();
        if (is_array($extraanswerfields)) {
            // Omit table name.
            array_shift($extraanswerfields);
            $question = $this->data_preprocessing_extra_answer_fields($question, $extraanswerfields);
        }
        return $question;
    }
}
