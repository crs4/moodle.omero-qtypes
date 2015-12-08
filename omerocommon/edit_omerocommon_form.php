<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Defines the editing form for the omeromultichoice question type.
 *
 * @package    qtype
 * @subpackage omeromultichoice
 * @copyright  2015 CRS4
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later //FIXME: check the licence
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/omerocommon/question.php');
require_once($CFG->dirroot . '/question/type/omerocommon/questiontype_base.php');
require_once($CFG->dirroot . '/question/type/multichoice/edit_multichoice_form.php');
require_once($CFG->dirroot . '/question/type/omerocommon/js/modules.php');

/**
 * omeromultichoice question editing form definition.
 *
 * @copyright  2015 CRS4
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later // FIXME: check the licence
 */
abstract class qtype_omerocommon_edit_form extends qtype_multichoice_edit_form
{
    private $localized_strings = array(
        "questiontext", "generalfeedback",
        "correctfeedback", "partiallycorrectfeedback", "incorrectfeedback"
    );

    public function qtype()
    {
        return 'omerocommon';
    }


    /**
     * Build the form definition.
     *
     * This adds all the form fields that the default question type supports.
     * If your question type does not support all these fields, then you can
     * override this method and remove the ones you don't want with $mform->removeElement().
     */
    protected function definition()
    {
        // CSS and JS requirements
        $this->set_form_requirements();

        // general section
        $this->define_general_section();

        // answer properties
        $this->define_answer_options_properties_section();

        // image viewer and ROI inspector
        $this->define_image_and_roi_viewer();

        // answer section
        $this->define_answers_section();

        // feedback section
        $this->define_feedback_section();

        // interactive settings
        $this->add_interactive_settings(true, true);

        // tags section
        $this->define_tags_section();

        // hidden fields
        $this->define_hidden_fields();

        // controls
        $this->define_update_and_preview_controls();
    }


    protected function set_form_requirements()
    {
        global $CFG, $PAGE;
        // CSS
        $PAGE->requires->css(new moodle_url("$CFG->wwwroot/question/type/omerocommon/css/common-question-editor.css"));
        //$PAGE->requires->css(new moodle_url("$CFG->wwwroot/question/type/omerocommon/css/bootstrap-panel.css"));
        // Javascript
        init_js_modules("omerocommon");
    }


    /**
     * Defines the general section of the question editor
     *
     * @throws coding_exception
     */
    protected function define_general_section()
    {
        global $COURSE, $CFG, $DB, $PAGE;
        $mform = $this->_form;
        $qtype = $this->qtype();
        $langfile = "qtype_{$qtype}";

        // header
        $mform->addElement('header', 'generalheader', get_string("general", 'form'));

        // question category
        $this->define_category_selector();

        // language selector
        $languages = array();
        $languages += get_string_manager()->get_list_of_translations();
        $mform->addElement('select', 'question_language',
            get_string('language', 'qtype_omerocommon'), $languages,
            array("class" => "question-language-selector"));
        $mform->setDefault('lang', current_language());

        // question name
        $mform->addElement('text', 'name', get_string('questionname', 'question'),
            array('size' => 50, 'maxlength' => 255, "style" => "width: 98%;"));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        // question text
        $mform->addElement('editor', 'questiontext', get_string('questiontext', 'question'),
            array('rows' => 15), $this->editoroptions);
        $mform->setType('questiontext', PARAM_RAW);
        $mform->addRule('questiontext', null, 'required', null, 'client');
    }


    /**
     * Defines the category selector
     *
     * @throws coding_exception
     */
    protected function define_category_selector()
    {
        global $COURSE, $CFG, $DB, $PAGE;
        $mform = $this->_form;
        $qtype = $this->qtype();

        if (!isset($this->question->id)) {
            if (!empty($this->question->formoptions->mustbeusable)) {
                $contexts = $this->contexts->having_add_and_use();
            } else {
                $contexts = $this->contexts->having_cap('moodle/question:add');
            }
            // Adding question.
            $mform->addElement('questioncategory', 'category', get_string('category', 'question'),
                array('contexts' => $contexts));
        } else if (!($this->question->formoptions->canmove ||
            $this->question->formoptions->cansaveasnew)
        ) {
            // Editing question with no permission to move from category.
            $mform->addElement('questioncategory', 'category', get_string('category', 'question'),
                array('contexts' => array($this->categorycontext)));
            $mform->addElement('hidden', 'usecurrentcat', 1);
            $mform->setType('usecurrentcat', PARAM_BOOL);
            $mform->setConstant('usecurrentcat', 1);
        } else {
            // Editing question with permission to move from category or save as new q.
            $currentgrp = array();
            $currentgrp[0] = $mform->createElement('questioncategory', 'category',
                get_string('categorycurrent', 'question'),
                array('contexts' => array($this->categorycontext)));
            if ($this->question->formoptions->canedit ||
                $this->question->formoptions->cansaveasnew
            ) {
                // Not move only form.
                $currentgrp[1] = $mform->createElement('checkbox', 'usecurrentcat', '',
                    get_string('categorycurrentuse', 'question'));
                $mform->setDefault('usecurrentcat', 1);
            }
            $currentgrp[0]->freeze();
            $currentgrp[0]->setPersistantFreeze(false);
            $mform->addGroup($currentgrp, 'currentgrp',
                get_string('categorycurrent', 'question'), null, false);

            $mform->addElement('questioncategory', 'categorymoveto',
                get_string('categorymoveto', 'question'),
                array('contexts' => array($this->categorycontext)));
            if ($this->question->formoptions->canedit ||
                $this->question->formoptions->cansaveasnew
            ) {
                // Not move only form.
                $mform->disabledIf('categorymoveto', 'usecurrentcat', 'checked');
            }
        }
    }


    /**
     * Defines the section to set the properties of the answer options
     *
     * @throws coding_exception
     */
    protected function define_answer_options_properties_section()
    {
        global $PAGE, $OUTPUT;
        $mform = $this->_form;

        // header
        $mform->addElement('header', 'answeroptionspropertiesheader',
            get_string("answer_options_properties", 'qtype_omerocommon'));

        // selector to allow single or multi answers
        $menu = array(
            get_string('answersingleno', 'qtype_omerocommon'),
            get_string('answersingleyes', 'qtype_omerocommon'),
        );
        $mform->addElement('select', 'single',
            get_string('answerhowmany', 'qtype_omerocommon'), $menu);
        $mform->setDefault('single', 1);

        // how to number answer options
        $mform->addElement('select', 'answernumbering',
            get_string('answernumbering', 'qtype_multichoice'),
            qtype_multichoice::get_numbering_styles());
        $mform->setDefault('answernumbering', 'abc');

        // default mark
        $mform->addElement('text', 'defaultmark', get_string('defaultmark', 'question'),
            array('size' => 7));
        $mform->setType('defaultmark', PARAM_FLOAT);
        $mform->setDefault('defaultmark', 1);
        $mform->addRule('defaultmark', null, 'required', null, 'client');

        // flag to set the shuffling of answer options
        $mform->addElement('advcheckbox', 'shuffleanswers',
            get_string('shuffleanswers', 'qtype_multichoice'), null, null, array(0, 1));
        $mform->addHelpButton('shuffleanswers', 'shuffleanswers', 'qtype_multichoice');
        $mform->setDefault('shuffleanswers', 1);

        // set as expanded by default
        $mform->setExpanded('answeroptionspropertiesheader');
    }


    /**
     * @throws coding_exception
     * @throws dml_exception
     */
    protected function define_image_and_roi_viewer()
    {
        $mform = $this->_form;
        // header
        $mform->addElement('header', 'omeroimageheader',
            get_string('omero_image_viewer', 'qtype_omerocommon'));
        // file picker
        $mform->addElement('omerofilepicker', 'omeroimageurl', ' ', null,
            array('maxbytes' => 2048, 'accepted_types' => array('*'),
                'return_types' => array(FILE_EXTERNAL),
                'omero_image_server' => get_config('omero', 'omero_restendpoint'))
        );


        $mform->addElement('header', 'roitableinspectorheader',
            get_string('roi_shape_inspector', 'qtype_omeromultichoice'), '');
        $mform->setExpanded('roitableinspectorheader', 1);

        $mform->addElement('html', '
            <div class="fitem" id="roi-shape-inspector-table-container" class="hidden">
                <div class="fitemtitle"><label for="roi-shape-inspector-table"></label></div>
                <div class="felement">

                <!-- TOOLBAR -->
                <div id="roi-shape-inspector-table-toolbar" class="hidden">

                    <button id="add-new-roi" class="btn btn-success" disabled>
                        <i class="lyphicon glyphicon-plus"></i> Add
                    </button>

                    <button id="edit-roi" class="btn btn-warning" disabled>
                        <i class="glyphicon glyphicon-edit"></i> Edit
                    </button>

                    <button id="remove" class="btn btn-danger" disabled>
                        <i class="glyphicon glyphicon-remove"></i> Delete
                    </button>

                    <!-- Single button -->
                    <div class="btn-group">
                      <button type="button" class="btn btn-info  dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Group <span class="caret"></span>
                      </button>
                      <ul class="dropdown-menu">
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
                       data-detail-view="true"
                       data-minimum-count-columns="2"
                       data-show-pagination-switch="true"
                       data-pagination="true"
                       data-id-field="id"
                       data-page-list="[10, 25, 50, 100, ALL]"
                       data-show-footer="false"
                       data-side-pagination="server">
                </table>
              </div>
            </div>
');
        // set as expanded by default
        $mform->setExpanded('omeroimageheader');
    }


    /**
     * Defines the answers section:
     * the default answer section has no header an
     * a set of default hidden fields (see standard answer fields)
     * to represent a single answer.
     * It calls the <pre>add_per_answer_fields</pre> method
     * to define the list of fields to represent an answer:
     * such a method actually calls <pre>add_per_answer_fields</pre>.
     *
     *
     * @return mixed
     */
    protected function define_answers_section()
    {
        // defines the list of params to represents the answer
        $this->add_per_answer_fields($this->_form, "", question_bank::fraction_options_full(), 0);
    }

    /**
     * Return an array containing the following info related to the answer section:
     *  - ID
     *  - label
     *
     * @return array
     * @throws coding_exception
     */
    protected function define_answer_section_header()
    {
        return array(
            "answerhdr",
            get_string('answers', 'question')
        );
    }

    /**
     * Add a set of form fields, obtained from get_per_answer_fields, to the form,
     * one for each existing answer, with some blanks for some new ones.
     * @param object $mform the form being built.
     * @param $label the label to use for each option.
     * @param $gradeoptions the possible grades for each answer.
     * @param $minoptions the minimum number of answer blanks to display.
     *      Default QUESTION_NUMANS_START.
     * @param $addoptions the number of answer blanks to add. Default QUESTION_NUMANS_ADD.
     */
    protected function add_per_answer_fields(&$mform, $label, $gradeoptions,
                                             $minoptions = QUESTION_NUMANS_START, $addoptions = QUESTION_NUMANS_ADD)
    {
        $header_info = $this->define_answer_section_header();
        $mform->addElement('header', $header_info[0], $header_info[1], '');
        $mform->setExpanded($header_info[0], 1);

        $answersoption = '';
        $repeatedoptions = array();
        $repeated = $this->get_per_answer_fields($mform, $label, $gradeoptions,
            $repeatedoptions, $answersoption);

        if (isset($this->question->options)) {
            $repeatsatstart = count($this->question->options->$answersoption);
        } else {
            $repeatsatstart = $minoptions;
        }

        $this->repeat_elements($repeated, $repeatsatstart, $repeatedoptions,
            'noanswers', 'addanswers', $addoptions,
            $this->get_more_choices_string(), false);
    }


    /**
     * Redefines the set of params to represent an answer
     *
     * @param object $mform
     * @param the $label
     * @param the $gradeoptions
     * @param reference $repeatedoptions
     * @param reference $answersoption
     * @return array
     */
    protected function get_per_answer_fields($mform, $label, $gradeoptions,
                                             &$repeatedoptions, &$answersoption)
    {
        $repeated = array();
        $repeated[] = $mform->createElement('hidden', 'answer');
        $repeated[] = $mform->createElement('hidden', 'fraction');
        $repeated[] = $mform->createElement('hidden', 'feedback');
        $repeated[] = $mform->createElement('hidden', 'answerformat');
        $repeated[] = $mform->createElement('hidden', 'feedbackformat');

        // locale maps answer and feedback
        $repeated[] = $mform->createElement('hidden', 'answer_locale_map');
        $repeated[] = $mform->createElement('hidden', 'feedback_locale_map');

        $mform->setType("answer", PARAM_TEXT);
        $mform->setType("fraction", PARAM_FLOAT);
        $mform->setType("feedback", PARAM_TEXT);
        $mform->setType("answerformat", PARAM_RAW);
        $mform->setType("feedbackformat", PARAM_RAW);

        $mform->setType("answer_locale_map", PARAM_RAW);
        $mform->setType("feedback_locale_map", PARAM_RAW);

        $mform->setDefault('answerformat', 1);
        $mform->setDefault('feedbackformat', 1);

        $repeatedoptions['answer']['type'] = PARAM_RAW;
        $repeatedoptions['fraction']['default'] = 0;
        $answersoption = 'answers';
        return $repeated;
    }


    /**
     * Defines a shared section to edit general and combined feedback
     */
    protected function define_feedback_section()
    {
        $mform = $this->_form;

        // header
        $mform->addElement('header', 'feedbackheader', get_string('general_and_combined_feedback', 'qtype_omerocommon'));

        // general feedback
        $mform->addElement('editor', 'generalfeedback', get_string('generalfeedback', 'question'),
            array('rows' => 10), $this->editoroptions);
        $mform->setType('generalfeedback', PARAM_RAW);
        $mform->addHelpButton('generalfeedback', 'generalfeedback', 'question');

        // combined feedback
        $this->add_combined_feedback_fields(true);
        $mform->disabledIf('shownumcorrect', 'single', 'eq', 1);
    }

    /**
     * Overrides the corresponding method of the base class
     * to hide delete the default section header
     *
     * @param bool|false $withshownumpartscorrect
     * @throws coding_exception
     */
    protected function add_combined_feedback_fields($withshownumpartscorrect = false)
    {
        $mform = $this->_form;
        $fields = array('correctfeedback', 'partiallycorrectfeedback', 'incorrectfeedback');
        foreach ($fields as $feedbackname) {
            $element = $mform->addElement('editor', $feedbackname,
                get_string($feedbackname, 'question'),
                array('rows' => 5), $this->editoroptions);
            $mform->setType($feedbackname, PARAM_RAW);
            // Using setValue() as setDefault() does not work for the editor class.
            $element->setValue(array('text' => get_string($feedbackname . 'default', 'question')));

            if ($withshownumpartscorrect && $feedbackname == 'partiallycorrectfeedback') {
                $mform->addElement('advcheckbox', 'shownumcorrect',
                    get_string('options', 'question'),
                    get_string('shownumpartscorrectwhenfinished', 'question'));
                $mform->setDefault('shownumcorrect', true);
            }
        }
    }

    /**
     * Defines the tags section
     *
     * @throws coding_exception
     */
    protected function define_tags_section()
    {
        global $CFG;
        $mform = $this->_form;
        if (!empty($CFG->usetags)) {
            $mform->addElement('header', 'tagsheader', get_string('tags'));
            $mform->addElement('tags', 'tags', get_string('tags'));
            $mform->setExpanded('tagsheader');
        }
    }


    /**
     * Defines a set of controls to save the question
     *
     * @throws coding_exception
     */
    protected function define_update_and_preview_controls()
    {
        global $DB, $PAGE;
        $mform = $this->_form;
        if (!empty($this->question->id)) {
            $mform->addElement('header', 'createdmodifiedheader',
                get_string('createdmodifiedheader', 'question'));
            $a = new stdClass();
            if (!empty($this->question->createdby)) {
                $a->time = userdate($this->question->timecreated);
                $a->user = fullname($DB->get_record(
                    'user', array('id' => $this->question->createdby)));
            } else {
                $a->time = get_string('unknown', 'question');
                $a->user = get_string('unknown', 'question');
            }
            $mform->addElement('static', 'created', get_string('created', 'question'),
                get_string('byandon', 'question', $a));
            if (!empty($this->question->modifiedby)) {
                $a = new stdClass();
                $a->time = userdate($this->question->timemodified);
                $a->user = fullname($DB->get_record(
                    'user', array('id' => $this->question->modifiedby)));
                $mform->addElement('static', 'modified', get_string('modified', 'question'),
                    get_string('byandon', 'question', $a));
            }

            $mform->closeHeaderBefore('createdmodifiedheader');
        }

        //if a preview is available generates the corresponding link
        if ($this->can_preview()) {
            $previewlink = $PAGE->get_renderer('core_question')->question_preview_link(
                $this->question->id, $this->context, true);
            $buttonarray[] = $mform->createElement('static', 'previewlink', '', $previewlink);
            $mform->addGroup($buttonarray, 'updatebuttonar', ' ', array(' '), false);
            $mform->closeHeaderBefore('updatebuttonar');
        }

        // defines the set of control buttons
        $buttonarray = array();
        $buttonarray[] = $mform->createElement('submit', 'updatebutton',
            get_string('savechangesandcontinueediting', 'qtype_omerocommon'));
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('savechangesandexit', "qtype_omerocommon"));
        $buttonarray[] = $mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', ' ', array(' '), false);
        $mform->closeHeaderBefore('buttonar');

        if ((!empty($this->question->id)) && (!($this->question->formoptions->canedit ||
                $this->question->formoptions->cansaveasnew))
        ) {
            $mform->hardFreezeAllVisibleExcept(array('categorymoveto', 'buttonar', 'currentgrp'));
        }
    }


    protected function define_hidden_fields()
    {
        $mform = $this->_form;

        // default hidden fields
        $this->add_hidden_fields();

        $mform->addElement('hidden', 'cmid');
        $mform->setType('cmid', PARAM_INT);

        $mform->addElement('hidden', 'qtype');
        $mform->setType('qtype', PARAM_ALPHA);

        $mform->addElement('hidden', 'visiblerois', 'none');
        $mform->setType("visiblerois", PARAM_RAW);

        $mform->addElement('hidden', 'makecopy');
        $mform->setType('makecopy', PARAM_INT);

        $mform->addElement("hidden", 'questiontext_locale_map');
        $mform->setType('questiontext_locale_map', PARAM_RAW);

        $mform->addElement("hidden", 'generalfeedback_locale_map');
        $mform->setType('generalfeedback_locale_map', PARAM_RAW);

        $mform->addElement("hidden", 'correctfeedback_locale_map');
        $mform->setType('correctfeedback_locale_map', PARAM_RAW);

        $mform->addElement("hidden", 'partiallycorrectfeedback_locale_map');
        $mform->setType('partiallycorrectfeedback_locale_map', PARAM_RAW);

        $mform->addElement("hidden", 'incorrectfeedback_locale_map');
        $mform->setType('incorrectfeedback_locale_map', PARAM_RAW);
    }


    /**
     * Language string to use for 'Add {no} more {whatever we call answers}'.
     */
    protected function get_more_choices_string()
    {
        return get_string('add_roi_answer', 'qtype_omerocommon');
    }


    /**
     * Specific data preprocessing
     *
     * @param object $question
     * @return object
     */
    protected function data_preprocessing($question)
    {
        $question = parent::data_preprocessing($question);

        // specific preprocessing
        if (!empty($question->options)) {
            $question->questiontext_locale_map = $question->questiontext["text"];
            $question->generalfeedback_locale_map = $question->generalfeedback["text"];
            $question->correctfeedback_locale_map = $question->options->correctfeedback;
            $question->incorrectfeedback_locale_map = $question->options->incorrectfeedback;
            $question->partiallycorrectfeedback_locale_map = $question->options->partiallycorrectfeedback;

            $question->questiontext["text"] = "";
            $question->generalfeedback["text"] = "";
        }

        return $question;
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

            $question->answer_locale_map[$key] = $answer->answer;
            $question->feedback_locale_map[$key] = $answer->feedback;

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


    /**
     * Validate question data
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files)
    {
        // TODO: define a validation procedure like the parent::validation($data, $files);
        return array();
    }

    function get_data()
    {
        $data = parent::get_data(); // TODO: Change the autogenerated stub

        if (!empty($data)) {
            if (is_array($data)) {

                $data["questiontext"] = $data["questiontext_locale_map"];
                $data["generalfeedback"] = $data["generalfeedback_locale_map"];

                $answer = &$data["answer"];

            } else {

                $data->questiontext = $data->questiontext_locale_map;
                $data->generalfeedback = $data->generalfeedback_locale_map;
                $answer = &$data->{"answer"};
            }

            return $data;
        }


    }
}
