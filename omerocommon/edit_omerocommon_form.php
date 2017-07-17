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
 * Defines the editing form for the 'qtype_omerocommon' question type.
 *
 * @package    qtype
 * @subpackage omerocommon
 * @copyright  2015-2016 CRS4
 * @license    https://opensource.org/licenses/mit-license.php MIT license
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/omerocommon/rendererhelper.php');
require_once($CFG->dirroot . '/question/type/omerocommon/question.php');
require_once($CFG->dirroot . '/question/type/omerocommon/questiontype_base.php');
require_once($CFG->dirroot . '/question/type/multichoice/edit_multichoice_form.php');
require_once($CFG->dirroot . '/question/type/omerocommon/js/modules.php');
require_once($CFG->dirroot . '/question/type/omerocommon/viewer/viewer_config.php');
require_once($CFG->dirroot . '/question/type/omerocommon/db/access.php');

/**
 * Base question editor form for Omero questions (see qtype_omeromultichoice and qtype_omerointeractive).
 */
abstract class qtype_omerocommon_edit_form extends qtype_multichoice_edit_form
{
    const EDITOR_INFO_ELEMENT_NAME = "id_editor_info";
    protected $image_info_container_id;
    protected $image_selector_id;
    protected $view_mode = "view";

    private $localized_strings = array(
        "questiontext", "generalfeedback",
        "correctfeedback", "partiallycorrectfeedback", "incorrectfeedback"
    );

    public function qtype()
    {
        return 'omerocommon';
    }

    public function get_view_mode()
    {
        return $this->view_mode;
    }

    public function is_author_mode()
    {
        return $this->view_mode === "author";
    }

    public function is_translate_mode()
    {
        return $this->view_mode === "translate";
    }

    public function is_view_mode()
    {
        return $this->get_view_mode() === "view";
    }

    private function get_allowed_translation_languages()
    {
        return get_allowed_translation_languages($this->context);
    }

    protected function get_visible_languages()
    {
        $languages = array();
        $available_languages = get_string_manager()->get_list_of_translations();
        if ($this->is_view_mode()) {
            $languages += $available_languages;
        } else {
            $languages["en"] = $available_languages["en"];
            if ($this->is_translate_mode())
                $languages += $this->get_allowed_translation_languages();
        }
        return $languages;
    }

    protected function set_view_mode()
    {
        $view_mode = optional_param('mode', null, PARAM_RAW);
        if (!is_null($view_mode)) {
            $this->view_mode = optional_param('mode', "view", PARAM_RAW);
            $_SESSION["view_mode"] = $this->view_mode;
        } else if (isset($_SESSION["view_mode"])) {
            $this->view_mode = $_SESSION["view_mode"];
        } else {
            $this->view_mode = "author";
            $_SESSION["view_mode"] = $this->view_mode;
        }
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
        // set view mode
        $this->set_view_mode();

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

        // locale strings for JS modules
        $this->export_locale_js_strings();
    }


    protected function set_form_requirements()
    {
        global $CFG, $PAGE;
        // CSS
        $PAGE->requires->css(new moodle_url("$CFG->wwwroot/question/type/omerocommon/css/message-dialog.css"));
        $PAGE->requires->css(new moodle_url("$CFG->wwwroot/question/type/omerocommon/css/modal-image-dialog.css"));
        $PAGE->requires->css(new moodle_url("$CFG->wwwroot/question/type/omerocommon/css/common-question-editor.css"));
        // Javascript
        init_js_modules("omerocommon");
        init_js_imageviewer(get_config('omero', 'omero_restendpoint'));
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
        $languages = $this->get_visible_languages();

        $mform->addElement('select', 'question_language',
            get_string('language', 'qtype_omerocommon'), $languages,
            array("class" => "question-language-selector"));
        $mform->setDefault('question_language', current_language());

        // question name
        $mform->addElement('text', 'name', get_string('questionname', 'question'),
            array('size' => 50, 'maxlength' => 255, "style" => "width: 98%;", !$this->is_author_mode() ? "readonly" : "editable" => 1));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        // question text
        $mform->addElement('editor', 'questiontext', get_string('questiontext', 'question'),
            array('rows' => 5), $this->editoroptions);
        $mform->setType('questiontext', PARAM_RAW);
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

        if (!isset($this->question->id)) {
            if (!empty($this->question->formoptions->mustbeusable)) {
                $contexts = $this->contexts->having_add_and_use();
            } else {
                $contexts = $this->contexts->having_cap('moodle/question:add');
            }
            // Adding question.
            $mform->addElement('questioncategory', 'category',
                get_string('category', 'question'), array('contexts' => $contexts));
        } else if (!($this->question->formoptions->canmove ||
            $this->question->formoptions->cansaveasnew)
        ) {

            // Editing question with no permission to move from category.
            $mform->addElement('questioncategory', 'category',
                get_string('category', 'question'),
                array('contexts' => array($this->categorycontext)), array("disabled" => !$this->is_author_mode()));
            $mform->addElement('hidden', 'usecurrentcat', 1);
            $mform->setType('usecurrentcat', PARAM_BOOL);
            $mform->setConstant('usecurrentcat', 1);
        } else {

            // Editing question with permission to move from category or save as new q.
            $currentgrp = array();
            $currentgrp[0] = $mform->createElement('questioncategory', 'category',
                get_string('categorycurrent', 'question'),
                array('contexts' => array($this->categorycontext)), array("disabled" => !$this->is_author_mode()));
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
                array('contexts' => array($this->categorycontext)), array("disabled" => !$this->is_author_mode()));
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
            get_string('answerhowmany', 'qtype_omerocommon'), $menu,
            array($this->is_author_mode() ? "enabled" : "disabled" => !$this->is_author_mode()));
        $mform->setDefault('single', 1);

        // how to number answer options
        $mform->addElement('select', 'answernumbering',
            get_string('answernumbering', 'qtype_multichoice'),
            qtype_multichoice::get_numbering_styles(),
            array($this->is_author_mode() ? "enabled" : "disabled" => !$this->is_author_mode()));
        $mform->setDefault('answernumbering', 'abc');

        // default mark
        $mform->addElement('text', 'defaultmark', get_string('defaultmark', 'question'),
            array('size' => 7, !$this->is_author_mode() ? "readonly" : "editable" => 1));
        $mform->setType('defaultmark', PARAM_FLOAT);
        $mform->setDefault('defaultmark', 1);
        $mform->addRule('defaultmark', null, 'required', null, 'client');

        // flag to set the shuffling of answer options
        $mform->addElement('advcheckbox', 'shuffleanswers',
            get_string('shuffleanswers', 'qtype_multichoice'), null,
            array($this->is_author_mode() ? "enabled" : "disabled" => !$this->is_author_mode()), array(0, 1));
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
        $picker = $mform->addElement('omerofilepicker', 'omeroimageurl', ' ', null,
            array('accepted_types' => array('*'),
                'disable_image_selection' => !$this->is_author_mode(),
                'return_types' => array(FILE_EXTERNAL),
                'omero_image_server' => get_config('omero', 'omero_restendpoint')
            )
        );

        $this->image_selector_id = $picker->getSelectedImageInputId();
        $this->image_info_container_id = $picker->getFileInfoContainerId();

        // build the ROI table inspector
        if ($this->is_author_mode())
            $this->define_roi_table_inspector();

        // set as expanded by default
        $mform->setExpanded('omeroimageheader');
    }

    /**
     * @return mixed
     */
    protected abstract function define_roi_table_inspector();

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
     * @return mixed
     */
    protected function define_answer_section_commons_top()
    {
    }

    /**
     * @return mixed
     */
    protected function define_answer_section_commons_bottom()
    {
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

        $this->define_answer_section_commons_top();

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

        $this->define_answer_section_commons_bottom();
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
        $repeated[] = $mform->createElement('hidden', 'feedbackimages');

        // locale maps answer and feedback
        $repeated[] = $mform->createElement('hidden', 'answer_locale_map');
        $repeated[] = $mform->createElement('hidden', 'feedback_locale_map');

        $mform->setType("answer", PARAM_TEXT);
        $mform->setType("fraction", PARAM_FLOAT);
        $mform->setType("feedback", PARAM_TEXT);
        $mform->setType("answerformat", PARAM_RAW);
        $mform->setType("feedbackformat", PARAM_RAW);
        $mform->setType("feedbackimages", PARAM_RAW);

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
        $mform->addElement('header', 'feedbackheader',
            get_string('general_and_combined_feedback', 'qtype_omerocommon'));

        // general feedback
        $mform->addElement('editor', 'generalfeedback',
            get_string('generalfeedback', 'question'),
            array('rows' => 10), $this->editoroptions);
        $mform->setType('generalfeedback', PARAM_RAW);
        $mform->addHelpButton('generalfeedback', 'generalfeedback', 'question');

        // combined feedback
        $this->add_combined_feedback_fields(true);
        //$mform->disabledIf('shownumcorrect', 'single', 'eq', 1);
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
        if ($this->is_author_mode()) {
            if (!empty($CFG->usetags)) {
                $mform->addElement('header', 'tagsheader', get_string('questionclassifiers', "qtype_omerocommon"));
                $mform->addElement('omeroquestiontags', 'tags', "", "officialtags",
                    get_string('selectquestionclassifiers', "qtype_omerocommon"),
                    get_string('editquestionclassifiers', "qtype_omerocommon"),
                    array("display" => "onlyofficial")
                );
                $mform->setExpanded('tagsheader');
            }
        }
    }

    protected function add_interactive_settings($withclearwrong = false,
                                                $withshownumpartscorrect = false)
    {
        $mform = $this->_form;

        $mform->addElement('header', 'multitriesheader',
            get_string('settingsformultipletries', 'question'));

        $penalties = array(
            1.0000000,
            0.5000000,
            0.3333333,
            0.2500000,
            0.2000000,
            0.1000000,
            0.0000000
        );
        if (!empty($this->question->penalty) && !in_array($this->question->penalty, $penalties)) {
            $penalties[] = $this->question->penalty;
            sort($penalties);
        }
        $penaltyoptions = array();
        foreach ($penalties as $penalty) {
            $penaltyoptions["{$penalty}"] = (100 * $penalty) . '%';
        }
        $mform->addElement('select', 'penalty',
            get_string('penaltyforeachincorrecttry', 'question'), $penaltyoptions,
            array($this->is_author_mode() ? "enabled" : "disabled" => !$this->is_author_mode()));
        $mform->addHelpButton('penalty', 'penaltyforeachincorrecttry', 'question');
        $mform->setDefault('penalty', 0.3333333);

        if (isset($this->question->hints)) {
            $counthints = count($this->question->hints);
        } else {
            $counthints = 0;
        }

        if ($this->question->formoptions->repeatelements) {
            $repeatsatstart = max(self::DEFAULT_NUM_HINTS, $counthints);
        } else {
            $repeatsatstart = $counthints;
        }

        // We do not use these fields: thus, we disable them by default
//        if ($this->is_author_mode()) {
//            // TODO: show these fields also in view and translate mode
//            list($repeated, $repeatedoptions) = $this->get_hint_fields(
//                $withclearwrong, $withshownumpartscorrect);
//            $this->repeat_elements($repeated, $repeatsatstart, $repeatedoptions,
//                'numhints', 'addhint', 1, get_string('addanotherhint', 'question'), true);
//        }
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
        // Modal frame to show dialog messages
        $this->add_modal_frames();
    }


    // definition of the modal frame
    protected function add_modal_frames()
    {
        $modal_dialog = qtype_omerocommon_renderer_helper::modal_dialog();
        $modal_viewer = qtype_omerocommon_renderer_helper::modal_viewer();
        return $this->_form->addElement("html", $modal_dialog . "\n" . $modal_viewer);
    }


    protected function define_hidden_fields()
    {
        $mform = $this->_form;

        // default hidden fields
        $this->add_hidden_fields();

        $mform->addElement('hidden', 'qtype');
        $mform->setType('qtype', PARAM_ALPHA);
        $mform->setDefault('qtype', $this->qtype());

        $mform->addElement('hidden', 'visiblerois', 'none');
        $mform->setType("visiblerois", PARAM_RAW);

        $mform->addElement('hidden', 'focusablerois', 'none');
        $mform->setType("focusablerois", PARAM_RAW);

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

        $mform->addElement("hidden", 'omeroimagelocked');
        $mform->setType('omeroimagelocked', PARAM_INT);
        $mform->setDefault('omeroimagelocked', 0);

        $mform->addElement("hidden", 'omeroimageproperties');
        $mform->setType('omeroimageproperties', PARAM_RAW);
    }


    /**
     * Defines the set of locale strings used for JS modules
     *
     * @throws coding_exception
     */
    protected function export_locale_js_strings()
    {
        global $PAGE;
        $PAGE->requires->string_for_js('edit', 'core');
        $PAGE->requires->string_for_js('delete', 'core');
        $PAGE->requires->string_for_js('roi_shape_details', 'qtype_omerocommon');
        $PAGE->requires->string_for_js('roi_description', 'qtype_omerocommon');
        $PAGE->requires->string_for_js('roi_visibility', 'qtype_omerocommon');
        $PAGE->requires->string_for_js('roi_focus', 'qtype_omerocommon');
        $PAGE->requires->string_for_js('roi_visible', 'qtype_omerocommon');
        $PAGE->requires->string_for_js('roi_focusable', 'qtype_omerocommon');
        $PAGE->requires->string_for_js('feedback', 'question');
        $PAGE->requires->string_for_js('validate_field_required', 'qtype_omerocommon');
        $PAGE->requires->string_for_js('answer', 'core');
        $PAGE->requires->string_for_js('answer_grade', 'qtype_omerocommon');
        $PAGE->requires->string_for_js('answer_choiceno', 'qtype_omerocommon');
        $PAGE->requires->string_for_js('feedbackimages', 'qtype_omerocommon');
        $PAGE->requires->string_for_js('feedbackimagename', 'qtype_omerocommon');
        $PAGE->requires->string_for_js('feedbackimagedescription', 'qtype_omerocommon');
        $PAGE->requires->string_for_js('validate_warning', 'qtype_omerocommon');
        $PAGE->requires->string_for_js('validate_no_image', 'qtype_omerocommon');
        $PAGE->requires->string_for_js('validate_no_answers', 'qtype_omerocommon');
        $PAGE->requires->string_for_js('validate_at_least_one_100', 'qtype_omerocommon');
        $PAGE->requires->string_for_js('validate_at_most_one_100', 'qtype_omerocommon');
        $PAGE->requires->string_for_js('validate_sum_of_grades', 'qtype_omerocommon');
        $PAGE->requires->string_for_js('validate_editor_not_existing_rois', 'qtype_omerocommon');
    }


    /**
     * Language string to use for 'Add {no} more {whatever we call answers}'.
     */
    protected function get_more_choices_string()
    {
        return get_string('add_answers', 'qtype_omerocommon');
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
            $question->questiontext_locale_map = json_encode(qtype_omerocommon::serialize_to_json_from($question->questiontext["text"]));
            $question->generalfeedback_locale_map = json_encode(qtype_omerocommon::serialize_to_json_from($question->generalfeedback["text"]));
            $question->correctfeedback_locale_map = json_encode(qtype_omerocommon::serialize_to_json_from($question->options->correctfeedback));
            $question->incorrectfeedback_locale_map = json_encode(qtype_omerocommon::serialize_to_json_from($question->options->incorrectfeedback));
            $question->partiallycorrectfeedback_locale_map = json_encode(qtype_omerocommon::serialize_to_json_from($question->options->partiallycorrectfeedback));

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
            $question->answer[$key] = json_encode(qtype_omerocommon::serialize_to_json_from($answer->answer));
            $question->answerformat[$key] = $answer->answerformat;
            // answer fraction
            $question->fraction[$key] = 0 + $answer->fraction;
            unset($this->_form->_defaultValues["fraction[{$key}]"]);
            // answer feedback
            $question->feedback[$key] = json_encode(qtype_omerocommon::serialize_to_json_from($answer->feedback));
            $question->feedbackformat[$key] = $answer->feedbackformat;

            $question->feedbackimages[$key] = empty($answer->images) ? json_encode(array()) : $answer->images;

            $question->answer_locale_map[$key] = json_encode(qtype_omerocommon::serialize_to_json_from($answer->answer));
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
