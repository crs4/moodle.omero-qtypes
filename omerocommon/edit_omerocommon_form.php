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

require_once($CFG->dirroot . '/question/type/multichoice/edit_multichoice_form.php');

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
        return 'omerointeractive';
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
        global $COURSE, $CFG, $DB, $PAGE;

        $qtype = $this->qtype();
        $langfile = "qtype_{$qtype}";

        // the form
        $mform = $this->_form;

        // Sets the requirements (CSS and JS)
        $this->define_requirements();

        // Setup the general section
        $this->definition_general_question_data();

        // Setup the type of answers
        $this->definition_answer_types($mform);

        // Setup the image related to the question
        $this->definition_image_selector();

        // Setup question details
        $this->add_question_specific_fields($mform);

        // Set the initial number of answers to 0; add answers one by one
        // Set the initial number of answers to 0; add answers one by one
        $this->add_per_answer_fields($mform, get_string('choiceno', 'qtype_multichoice', '{no}'),
            question_bank::fraction_options_full(), 4, 1);

        // Combined feedback
        $this->add_combined_feedback_fields(true);
        $mform->disabledIf('shownumcorrect', 'single', 'eq', 1);

        // default interactive settings
        $this->add_interactive_settings(true, true);

        // Initialize hidden textarea for localized strings
        $this->add_localized_fields_for($this->localized_strings);//


        if (!empty($CFG->usetags)) {
            $mform->addElement('header', 'tagsheader', get_string('tags'));
            $mform->addElement('tags', 'tags', get_string('tags'));
        }

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
        }

        // Sets hidden fields
        $this->add_hidden_fields();

        // Update/Preview controls
        $buttonarray = array();
        $buttonarray[] = $mform->createElement('submit', 'updatebutton',
            get_string('savechangesandcontinueediting', 'question'));
        if ($this->can_preview()) {
            $previewlink = $PAGE->get_renderer('core_question')->question_preview_link(
                $this->question->id, $this->context, true);
            $buttonarray[] = $mform->createElement('static', 'previewlink', '', $previewlink);
        }

        $mform->addGroup($buttonarray, 'updatebuttonar', '', array(' '), false);
        $mform->closeHeaderBefore('updatebuttonar');

        $this->add_action_buttons(true, get_string('savechanges'));

        if ((!empty($this->question->id)) && (!($this->question->formoptions->canedit ||
                $this->question->formoptions->cansaveasnew))
        ) {
            $mform->hardFreezeAllVisibleExcept(array('categorymoveto', 'buttonar', 'currentgrp'));
        }
    }

    /**
     *
     */
    protected function define_requirements()
    {
        global $PAGE;
        $PAGE->requires->jquery();
        $PAGE->requires->jquery_plugin('ui');
        $PAGE->requires->jquery_plugin('ui-css');

        $PAGE->requires->jquery_plugin("bootstrap", "qtype_omerocommon");
        $PAGE->requires->jquery_plugin("bootstrap-table", "qtype_omerocommon");
        $PAGE->requires->jquery_plugin("dragtable", "qtype_omerocommon");

        $module = array(
            'name' => 'omero_multichoice_question_helper',
            'fullpath' => '/question/type/omeromultichoice/js/question-helper.js',
            'requires' => array('omemultichoice_qtype', 'node', 'node-event-simulate', 'core_dndupload'));
        $PAGE->requires->js_init_call('M.omero_multichoice_helper.init', array(), true, $module);

        $module = array(
            'name' => 'htmlt_utils',
            'fullpath' => '/question/type/omeromultichoice/js/html-utils.js',
            'requires' => array());
        $PAGE->requires->js_init_call('M.omero_multichoice_html_utils.init', array(), true, $module);
    }


    /**
     * @throws coding_exception
     */
    protected function definition_general_question_data()
    {

        $mform = $this->_form;

        // Standard fields at the start of the form.
        $mform->addElement('header', 'generalheader', get_string("general", 'form'));

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

        // Choose language section
        $languages = array();
        $languages += get_string_manager()->get_list_of_translations();
        $mform->addElement('select', 'question_language',
            get_string('language', 'qtype_omeromultichoice'), $languages,
            array("class" => "question-language-selector"));
        $mform->setDefault('lang', current_language());

        // Question Text
        $mform->addElement('text', 'name', get_string('questionname', 'question'),
            array('size' => 50, 'maxlength' => 255));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');


        $mform->addElement('editor', 'questiontext', get_string('questiontext', 'question'),
            array('rows' => 15), $this->editoroptions);
        $mform->setType('questiontext', PARAM_RAW);
        $mform->addRule('questiontext', null, 'required', null, 'client');

        $mform->addElement('text', 'defaultmark', get_string('defaultmark', 'question'),
            array('size' => 7));
        $mform->setType('defaultmark', PARAM_FLOAT);
        $mform->setDefault('defaultmark', 1);
        $mform->addRule('defaultmark', null, 'required', null, 'client');

        $mform->addElement('editor', 'generalfeedback', get_string('generalfeedback', 'question'),
            array('rows' => 10), $this->editoroptions);
        $mform->setType('generalfeedback', PARAM_RAW);
        $mform->addHelpButton('generalfeedback', 'generalfeedback', 'question');
    }


    /**
     * @throws dml_exception
     */
    protected function definition_image_selector()
    {
        $mform = $this->_form;

        $mform->addElement('html', '<div style="margin-top: 50px"></div>');
        $mform->addElement('header', 'omeroimageheader',
            get_string('omero_image_and_rois', 'qtype_omeromultichoice'), '');
        $mform->setExpanded('omeroimageheader', 1);


        $mform->addElement('omerofilepicker', 'omeroimagefilereference', " ", null,
            array('maxbytes' => 2048, 'accepted_types' => array('*'),
                'return_types' => array(FILE_EXTERNAL),
                'omero_image_server' => get_config('omero', 'omero_restendpoint'))
        );
    }


    /**
     * Define the form for editing the question
     *
     * @param $mform
     */
    protected abstract function add_question_specific_fields($mform);


    /**
     * @param $mform
     * @throws coding_exception
     */
    protected function definition_answer_types($mform)
    {
        global $PAGE, $OUTPUT;

        $menu = array(
            get_string('answersingleno', 'qtype_omeromultichoice'),
            get_string('answersingleyes', 'qtype_omeromultichoice'),
        );
        $mform->addElement('select', 'single',
            get_string('answerhowmany', 'qtype_omeromultichoice'), $menu);
        $mform->setDefault('single', 1);

        // Set answer types and the related selector
        $answer_type_menu = array();
        foreach (qtype_omerointeractive::get_question_types() as $type) {
            array_push($answer_type_menu, get_string("qtype_$type", 'qtype_omeromultichoice'));
        }
        $mform->addElement('select', 'answertype',
            get_string('answer_type', 'qtype_omeromultichoice'), $answer_type_menu,
            array("onchange" => "M.omero_multichoice_helper._on_question_type_changed()")
        );
        $mform->setDefault('answertype', qtype_omerointeractive::PLAIN_ANSWERS);


        $mform->addElement('advcheckbox', 'shuffleanswers',
            get_string('shuffleanswers', 'qtype_multichoice'), null, null, array(0, 1));
        $mform->addHelpButton('shuffleanswers', 'shuffleanswers', 'qtype_multichoice');
        $mform->setDefault('shuffleanswers', 1);

        $mform->addElement('select', 'answernumbering',
            get_string('answernumbering', 'qtype_multichoice'),
            qtype_multichoice::get_numbering_styles());
        $mform->setDefault('answernumbering', 'abc');


        if ((isset($_REQUEST['answertype'])
                && $_REQUEST['answertype'] == qtype_omerointeractive::ROI_BASED_ANSWERS) ||
            (isset($this->question->options)
                && $this->question->options->answertype == qtype_omerointeractive::ROI_BASED_ANSWERS)
        ) {
            $mform->addElement("button", "add-roi-answer",
                get_string("add_roi_answer", "qtype_omeromultichoice"));
        }
    }


    protected function add_hidden_fields()
    {
        parent::add_hidden_fields(); // TODO: Change the autogenerated stub

        $mform = $this->_form;

        $mform->addElement('hidden', 'qtype');
        $mform->setType('qtype', PARAM_ALPHA);

        $mform->addElement('hidden', 'makecopy');
        $mform->setType('makecopy', PARAM_INT);


        // Set the editing mode
        $mform->setType("editing_mode", PARAM_BOOL);
        $mform->addElement('hidden', 'editing_mode', 'true');

        //
        $mform->setType("current_selected_roi", PARAM_RAW);
        $mform->addElement('hidden', 'current_selected_roi', 'none');

        //
        $mform->setType("visible_rois", PARAM_RAW);
        $mform->addElement('hidden', 'visible_rois', 'none');

        //
        $mform->setType("roi_based_answers", PARAM_RAW);
        $mform->addElement('hidden', 'roi_based_answers', 'none');

        //
        $mform->setType("available_rois", PARAM_RAW);
        $mform->addElement('hidden', 'available_rois', 'none');

        //
        $mform->setType("omero_image_url", PARAM_RAW);
        $mform->addElement('hidden', 'omero_image_url', 'none');
    }


    /**
     * @param $string_names
     */
    protected function add_localized_fields_for($string_names)
    {
        $mform = $this->_form;
        // Initialize hidden textarea for localized strings
        $languages = get_string_manager()->get_list_of_translations();
        foreach ($string_names as $localized_string) {
            foreach ($languages as $lang_id => $lang_string) {
                $mform->addElement('textarea', $localized_string . "_" . $lang_id,
                    "", array("style" => "display: none;", "lang" => $lang_id, "class" => "$localized_string"));
            }
        }
    }

    /**
     * Language string to use for 'Add {no} more {whatever we call answers}'.
     */
    protected function get_more_choices_string()
    {
        return get_string('add_roi_answer', 'qtype_omeromultichoice');
    }


    /**
     * @param object $question
     * @return object
     */
    protected function data_preprocessing($question)
    {
        $question = parent::data_preprocessing($question);
        if (isset($this->question->options) && $question->options->answertype == qtype_omerointeractive::ROI_BASED_ANSWERS) {
            $question = $this->data_preprocessing_answers($question, false);
        } else {
            $question = $this->data_preprocessing_answers($question, true);
        }
        $question = $this->data_preprocessing_combined_feedback($question, true);
        $question = $this->data_preprocessing_hints($question, true, true);

        if (!empty($question->options)) {
            $question->single = $question->options->single;
            $question->shuffleanswers = $question->options->shuffleanswers;
            $question->answernumbering = $question->options->answernumbering;

            // Prepare the roi_based_answers field
            if (isset($this->question->options)
                && $question->options->answertype == qtype_omerointeractive::ROI_BASED_ANSWERS
            ) {
                $roi_based_answers = [];
                foreach ($question->options->answers as $answer) {
                    array_push($roi_based_answers, $answer->answer);
                }
            }

            $question->visible_rois = $question->options->visiblerois;
            if (isset($roi_based_answers)) {
                $question->roi_based_answers = implode(",", $roi_based_answers);
            }
            $question->answertype = $question->options->answertype;
            $question->omero_image_url = $question->options->omeroimageurl;
        }
        return $question;
    }


    public function get_data()
    {
        $data = parent::get_data();
        $this->update_localized_strings($data);
        return $data;
    }


    protected function update_localized_strings(&$data)
    {
        $languages = array();
        $languages += get_string_manager()->get_list_of_translations();

        if (!empty($data)) {
            foreach ($this->localized_strings as $localized_string) {

                if (is_array($data)) {
                    $obj = &$data["$localized_string"];
                } else {
                    $obj = &$data->{"$localized_string"};
                }

                $text = "";
                foreach ($languages as $lang_id => $lang_description) {
                    $txt = "";
                    if (is_array($data))
                        $txt = &$data[$localized_string . "_" . $lang_id];
                    else
                        $txt = &$data->{$localized_string . "_" . $lang_id};
                    // removes YUI ids
                    $txt = preg_replace('/id="([^"]+)"/i', "", $txt);
                    $text .= '<span class="multilang" lang="' . $lang_id . '">' . $txt . '</span>';
                }
                if (isset($obj["text"]))
                    $obj["text"] = $text;
                else $obj = $text;
            }

            if (!isset($_REQUEST['answertype']) || $_REQUEST['answertype'] != qtype_omerointeractive::ROI_BASED_ANSWERS) {
                if (is_array($data)) {
                    $answer = &$data["answer"];
                } else {
                    $answer = &$data->{"answer"};
                }

                for ($i = 0; $i < count($answer); $i++) {
                    $answer[$i]["text"] = "";
                    foreach ($languages as $lang_id => $lang_description) {
                        if (is_array($data)) {
                            $answer_lang = &$data["answer_" . $lang_id];
                        } else {
                            $answer_lang = &$data->{"answer_" . $lang_id};
                        }
                        if (isset($answer_lang[$i]) && !empty($answer_lang[$i])) {
                            // removes YUI ids
                            $txt = preg_replace('/id="([^"]+)"/i', "", $answer_lang[$i]);
                            $answer[$i]["text"] .= '<span class="multilang" lang="' . $lang_id . '">' . $txt . '</span>';
                        }
                    }
                }
            }
        }
    }

    public function set_data($question)
    {
        foreach ($this->localized_strings as $localized_string)
            $this->set_localized_string($question, $localized_string);

        $count = 0;
        if (isset($question->options) && isset($question->options->answers)) {
            foreach ($question->options->answers as $i => $answer) {
                $matches = $this->getLocaleStrings($answer->answer);
                if (count($matches[0]) > 0) {
                    for ($i = 0; $i < count($matches); $i++) {
                        $language = $matches[$i][0];
                        $localized_string = $matches[$i][1];
                        if (!isset($question->{"answer_" . $language}))
                            $question->{"answer_" . $language} = array();
                        array_push($question->{"answer_" . $language}, $localized_string);
                    }
                } else {
                    $languages = get_string_manager()->get_list_of_translations();
                    foreach ($languages as $language => $lang_name) {
                        if (!isset($question->{"answer_" . $language}))
                            $question->{"answer_" . $language} = array();
                        if (strcmp($language, current_language()) === 0) {
                            array_push($question->{"answer_" . $language}, $answer->answer);
                        } else {
                            array_push($question->{"answer_" . $language}, "");
                        }
                    }
                }
                $count++;
            }
        }

        parent::set_data($question);
    }


    /**
     * Perform the form validation
     *
     * @param $data
     * @param $files
     * @return mixed
     */
    public
    function validation($data, $files)
    {
        if (isset($_REQUEST['answertype']) && $_REQUEST['answertype'] == qtype_omerointeractive::ROI_BASED_ANSWERS) {
            $this->update_roi_based_answers($data);
        }

        if ($_REQUEST['noanswers'] < 3)
            $errors["generic"] = "At least 2 answers";

        // checks specific errors
        $errors = array();
        if (!isset($data["answer"]) || count($data["answer"]) < 3)
            $errors["generic"] = "At least 2 answers";

        // question multichoice validation
        if ($_REQUEST['noanswers'] > 0)
            $errors = parent::validation($data, $files);

        // return found errors
        return $errors;
    }


    /**
     * Returns the list of span[@multilang]
     * contained within the given <pre>$html</pre>
     *
     * @param $html
     * @return array array of pairs (language, string)
     */
    protected function getLocaleStrings($html)
    {
        $result = array();
        $dom = new DOMDocument();
        $dom->strictErrorChecking = FALSE;
        $dom->loadHTML('<?xml version="1.0" encoding="UTF-8"?><html><body>' . $html . '</body></html>');
        $finder = new DomXPath($dom);
        $classname = "multilang";
        $nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
        foreach ($nodes as $node) {
            $data = [$node->getAttribute("lang"), $this->DOMinnerHTML($node)];
            array_push($result, $data);
        }
        return $result;
    }

    /**
     * Returns the innerHTML of a given DOMNode
     *
     * @param DOMNode $element
     * @return string
     */
    private function DOMinnerHTML(DOMNode $element)
    {
        $innerHTML = "";
        $children = $element->childNodes;

        foreach ($children as $child) {
            $innerHTML .= $element->ownerDocument->saveHTML($child);
        }

        return $innerHTML;
    }


    protected function set_localized_string($obj, $property_name)
    {
        if ($obj == null) return;

        $languages = get_string_manager()->get_list_of_translations();
        $query_obj = null;

        if (isset($obj->{$property_name}))
            $query_obj = $obj;

        else if (isset($obj->options) && isset($obj->options->{$property_name}))
            $query_obj = $obj->options;

        if ($query_obj != null) {
            $matches = $this->getLocaleStrings($query_obj->{$property_name});
            if (count($matches[0]) == 0) {
                foreach ($languages as $language => $lang_name) {
                    if (strcmp($language, current_language()) === 0) {
                        $obj->{$property_name . "_" . $language} = $query_obj->{$property_name};
                    } else {
                        $obj->{$property_name . "_" . $language} = "";
                    }
                }
            } else {
                for ($i = 0; $i < count($matches); $i++) {
                    $language = $matches[$i][0];
                    $localized_string = $matches[$i][1];
                    $obj->{$property_name . "_" . $language} = $localized_string;
                }
            }
        } else {
            foreach ($languages as $language) {
                $obj->{$property_name . "_" . $language} = "";
            }
        }

        if (isset($obj->{$property_name . "_" . current_language()})) {
            $obj->{$property_name} = $obj->{$property_name . "_" . current_language()};
        }
    }
}

