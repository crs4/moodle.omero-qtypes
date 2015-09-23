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
class qtype_omeromultichoice_edit_form extends qtype_multichoice_edit_form
{

    public function qtype()
    {
        return 'omeromultichoice';
    }

    /**
     * Define the form for editing the question
     *
     * @param $mform
     */
    protected function definition_inner($mform)
    {
        global $PAGE, $OUTPUT;

        $module = array(
            'name' => 'omero_multichoice_helper',
            'fullpath' => '/question/type/omeromultichoice/omero_multichoice_helper.js',
            'requires' => array('omemultichoice_qtype', 'node', 'node-event-simulate', 'core_dndupload'));
        $PAGE->requires->js_init_call('M.omero_multichoice_helper.init', array(), true, $module);


        $mform->addElement('omerofilepicker', 'omeroimagefilereference', get_string('file'), null,
            array('maxbytes' => 2048, 'accepted_types' => array('*'),
                'return_types' => array(FILE_INTERNAL | FILE_EXTERNAL))
        );

//        $enable_add_plaintext_answer_button =
//            !isset($_REQUEST['answertype']) || $_REQUEST['answertype'] == qtype_omeromultichoice::PLAIN_ANSWERS;
//        if (!$enable_add_plaintext_answer_button)

        if ((isset($_REQUEST['answertype'])
                && $_REQUEST['answertype'] == qtype_omeromultichoice::ROI_BASED_ANSWERS) ||
            (isset($this->question->options)
                && $this->question->options->answertype == qtype_omeromultichoice::ROI_BASED_ANSWERS)
        ) {
            $mform->addElement("button", "add-roi-answer",
                get_string("add_roi_answer", "qtype_omeromultichoice"));
            //,array("disabled" => true));
        }

        $menu = array(
            get_string('answersingleno', 'qtype_multichoice'),
            get_string('answersingleyes', 'qtype_multichoice'),
        );
        $mform->addElement('select', 'single',
            get_string('answerhowmany', 'qtype_multichoice'), $menu);
        $mform->setDefault('single', 1);

        // Set answer types and the related selector
        $answer_type_menu = array();
        foreach (qtype_omeromultichoice::get_question_types() as $type) {
            array_push($answer_type_menu, get_string("qtype_$type", 'qtype_omeromultichoice'));
        }
        $mform->addElement('select', 'answertype',
            get_string('answer_type', 'qtype_omeromultichoice'), $answer_type_menu,
            array("onchange" => "document.forms[0].elements['noanswers'].value=0; document.forms[0].submit()"));
        $mform->setDefault('answertype', qtype_omeromultichoice::PLAIN_ANSWERS);


        $mform->addElement('advcheckbox', 'shuffleanswers',
            get_string('shuffleanswers', 'qtype_multichoice'), null, null, array(0, 1));
        $mform->addHelpButton('shuffleanswers', 'shuffleanswers', 'qtype_multichoice');
        $mform->setDefault('shuffleanswers', 1);

        $mform->addElement('select', 'answernumbering',
            get_string('answernumbering', 'qtype_multichoice'),
            qtype_multichoice::get_numbering_styles());
        $mform->setDefault('answernumbering', 'abc');

        // Set the initial number of answers to 0; add answers one by one
        $this->add_per_answer_fields($mform, get_string('choiceno', 'qtype_multichoice', '{no}'),
            question_bank::fraction_options_full(), 0, 1);

        $this->add_combined_feedback_fields(true);
        $mform->disabledIf('shownumcorrect', 'single', 'eq', 1);

        // default interactive settings
        $this->add_interactive_settings(true, true);

        // Set the editing mode
        $mform->setType("editing_mode", PARAM_BOOL);
        $mform->addElement('hidden', 'editing_mode', 'true');

        //
        $mform->setType("current_selected_roi", PARAM_RAW);
        $mform->addElement('hidden', 'current_selected_roi', 'none');

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
     * Language string to use for 'Add {no} more {whatever we call answers}'.
     */
    protected function get_more_choices_string()
    {
        return get_string('add_roi_answer', 'qtype_omeromultichoice');
    }


    /**
     * Build the repeated elements of the form
     * (i.e., form elements for setting answers)
     *
     * @param $mform
     * @param $label
     * @param $gradeoptions
     * @param $repeatedoptions
     * @param $answersoption
     * @return array
     */
    protected function get_per_answer_fields($mform, $label, $gradeoptions,
                                             &$repeatedoptions, &$answersoption)
    {
        //if($mform->getElement("answertype")->getSelected()[0]==qtype_omeromultichoice::ROI_BASED_ANSWERS)
        if ((isset($_REQUEST['answertype']) && $_REQUEST['answertype'] == qtype_omeromultichoice::ROI_BASED_ANSWERS) ||
            (isset($this->question->options) && $this->question->options->answertype == qtype_omeromultichoice::ROI_BASED_ANSWERS)
        )
            return $this->get_per_roi_based_answer_fields($mform, $label, $gradeoptions,
                $repeatedoptions, $answersoption);
        else return $this->get_per_plaintext_answer_fields($mform, $label, $gradeoptions,
            $repeatedoptions, $answersoption);
    }

    protected function get_per_plaintext_answer_fields($mform, $label, $gradeoptions,
                                                       &$repeatedoptions, &$answersoption)
    {
        $repeated = array();
        $repeated[] = $mform->createElement('editor', 'answer',
            $label, array('rows' => 1), $this->editoroptions);
        $repeated[] = $mform->createElement('select', 'fraction',
            get_string('grade'), $gradeoptions);
        $repeated[] = $mform->createElement('editor', 'feedback',
            get_string('feedback', 'question'), array('rows' => 1), $this->editoroptions);
        $repeated[] = $mform->createElement('html', '<div style="margin-bottom: 60px;"></div>');
        $repeatedoptions['answer']['type'] = PARAM_RAW;
        $repeatedoptions['fraction']['default'] = 0;
        $answersoption = 'answers';
        return $repeated;
    }


    /**
     * Build the repeated elements of the form
     * (i.e., form elements for setting answers)
     *
     * @param $mform
     * @param $label
     * @param $gradeoptions
     * @param $repeatedoptions
     * @param $answersoption
     * @return array
     */
    protected function get_per_roi_based_answer_fields($mform, $label, $gradeoptions,
                                                       &$repeatedoptions, &$answersoption)
    {
        $repeated = array();

        $repeated[] = $mform->createElement('html', '<div class="fitem roi-based-answer">');

        // ROI choice label
        $repeated[] = $mform->createElement('static', "description", $label);

        $repeated[] = $mform->createElement('html', '<div class="felement felement-roi-based-answer">');

        // Main DIV container
        $repeated[] = $mform->createElement('html', '<div class="omeromultichoice-qanswer-roi-based-answer-container">');


        // hidden field for storing answer/roi ID
        $repeated[] = $mform->createElement('hidden', 'answer', "none");

        // ROI details
        $repeated[] = $mform->createElement('html', '<div class="omeromultichoice-qanswer-container">');
        $repeated[] = $mform->createElement('html', '<div class="omeromultichoice-qanswer-roi-container">');
        // Image container
        $repeated[] = $mform->createElement('html', '<div class="omeromultichoice-qanswer-roi-image-container">');
        $repeated[] = $mform->createElement('html', '<img src="" class="roi_thumb shape_thumb" style="vertical-align: top;" color="f00" width="150px" height="150px">');
        $repeated[] = $mform->createElement('html', '</div>'); // -> Close 'qanswer-roi-image-container
        // ROI description
        $repeated[] = $mform->createElement('html', '<div class="omeromultichoice-qanswer-roi-details-container">');
        $repeated[] = $mform->createElement('html', '<div class="omeromultichoice-qanswer-roi-details-text-container">');
        // Adds ROI description fields
        $roi_description_fields = array("id", "comment", "type", "width", "height");
        foreach ($roi_description_fields as $field) {
            $repeated[] = $mform->createElement('html', '<div class="omeromultichoice-qanswer-roi-details-text">');
            $repeated[] = $mform->createElement('html', '<span class="roi-field-label">' . get_string("roi_" . $field, "qtype_omeromultichoice") . ':</span>');
            $repeated[] = $mform->createElement('html', '<span class="roi-field-value">...</span>');
            $repeated[] = $mform->createElement('html', '</div>');
        }

        $repeated[] = $mform->createElement('html', '</div>'); // -> Close 'details-text-container'
        $repeated[] = $mform->createElement('html', '</div>'); // -> Close 'qanswer-roi-details-container
        $repeated[] = $mform->createElement('html', '</div>'); // -> Close 'qanswer-roi-container'

        // ROI-based answer grade selector
        $repeated[] = $mform->createElement('select', 'fraction', get_string('grade'), $gradeoptions);

        // Feedback editor
        $repeated[] = $mform->createElement('editor', 'feedback',
            get_string('feedback', 'question'), array('rows' => 1), $this->editoroptions);

        $repeated[] = $mform->createElement('html', '</div>'); // -> Close 'qanswer-roi-details-container'
        $repeated[] = $mform->createElement('html', '</div>'); // -> Close 'qanswer-roi-based-answer-container'

        $repeated[] = $mform->createElement('html', '</div>'); // -> Close 'felement'

        $repeated[] = $mform->createElement('html', '</div>'); // -> Close 'fitem'

        // Default values
        $repeatedoptions['answer']['type'] = PARAM_RAW;
        $repeatedoptions['fraction']['default'] = 0;
        $answersoption = 'answers';

        return $repeated;
    }


    protected function data_preprocessing($question)
    {
        $question = parent::data_preprocessing($question);
        if (isset($this->question->options) && $question->options->answertype == qtype_omeromultichoice::ROI_BASED_ANSWERS) {
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
                && $question->options->answertype == qtype_omeromultichoice::ROI_BASED_ANSWERS) {
                $roi_based_answers = [];
                foreach ($question->options->answers as $answer) {
                    array_push($roi_based_answers, $answer->answer);
                }
            }

            $question->visible_rois = $question->options->visiblerois;
            $question->roi_based_answers = implode(",", $roi_based_answers);
            $question->answertype = $question->options->answertype;
            $question->omero_image_url = $question->options->omeroimageurl;
        }
        return $question;
    }


    public function get_data()
    {
        $data = parent::get_data();
        if (isset($_REQUEST['answertype']) &&
            $_REQUEST['answertype'] == qtype_omeromultichoice::ROI_BASED_ANSWERS
        )
            $this->update_raw_data($data);
        return $data;
    }


    private function update_raw_data(&$data)
    {
        if (!empty($data)) {
            if (is_array($data))
                $answers = &$data["answer"];
            else
                $answers = &$data->{"answer"};

            if (isset($_POST["roi_based_answers"])) {
                $roi_based_answers_el = $_POST["roi_based_answers"];
                $roi_based_answers = explode(",", $roi_based_answers_el);
                foreach ($roi_based_answers as $k => $a) {
                    $answers[$k] = array("text" => "$a", "format" => 1, "itemid" => "x");
                }
            }
        }
    }


    public function set_data($question)
    {
        parent::set_data($question);
    }

    protected function data_preprocessing_answers($question, $withanswerfiles = false)
    {
        if (empty($question->options->answers)) {
            return $question;
        }

        $key = 0;
        foreach ($question->options->answers as $answer) {
            if ($withanswerfiles) {
                // Prepare the feedback editor to display files in draft area.
                $draftitemid = file_get_submitted_draft_itemid('answer[' . $key . ']');
                $question->answer[$key]['text'] = file_prepare_draft_area(
                    $draftitemid,          // Draftid
                    $this->context->id,    // context
                    'question',            // component
                    'answer',              // filarea
                    !empty($answer->id) ? (int)$answer->id : null, // itemid
                    $this->fileoptions,    // options
                    $answer->answer        // text.
                );
                $question->answer[$key]['itemid'] = $draftitemid;
                $question->answer[$key]['format'] = $answer->answerformat;
            } else {
                $question->answer[$key] = $answer->answer;
            }

            $question->fraction[$key] = 0 + $answer->fraction;
            $question->feedback[$key] = array();

            // Evil hack alert. Formslib can store defaults in two ways for
            // repeat elements:
            //   ->_defaultValues['fraction[0]'] and
            //   ->_defaultValues['fraction'][0].
            // The $repeatedoptions['fraction']['default'] = 0 bit above means
            // that ->_defaultValues['fraction[0]'] has already been set, but we
            // are using object notation here, so we will be setting
            // ->_defaultValues['fraction'][0]. That does not work, so we have
            // to unset ->_defaultValues['fraction[0]'].
            unset($this->_form->_defaultValues["fraction[{$key}]"]);

            // Prepare the feedback editor to display files in draft area.
            $draftitemid = file_get_submitted_draft_itemid('feedback[' . $key . ']');
            $question->feedback[$key]['text'] = file_prepare_draft_area(
                $draftitemid,          // Draftid
                $this->context->id,    // context
                'question',            // component
                'answerfeedback',      // filarea
                !empty($answer->id) ? (int)$answer->id : null, // itemid
                $this->fileoptions,    // options
                $answer->feedback      // text.
            );
            $question->feedback[$key]['itemid'] = $draftitemid;
            $question->feedback[$key]['format'] = $answer->feedbackformat;
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

    protected function get_hint_fields($withclearwrong = false, $withshownumpartscorrect = false)
    {
        list($repeated, $repeatedoptions) = parent::get_hint_fields($withclearwrong, $withshownumpartscorrect);
        $repeatedoptions['hintclearwrong']['disabledif'] = array('single', 'eq', 1);
        $repeatedoptions['hintshownumcorrect']['disabledif'] = array('single', 'eq', 1);
        return array($repeated, $repeatedoptions);
    }

    /**
     * Perform the form validation
     *
     * @param $data
     * @param $files
     * @return mixed
     */
    public function validation($data, $files)
    {
        if (isset($_REQUEST['answertype'])
            && $_REQUEST['answertype'] == qtype_omeromultichoice::ROI_BASED_ANSWERS) {
            //
            $this->update_raw_data($data);
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


    ///////////////////////////////////////////////////////////////////////////////////////////////////////////


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
        $mform->addElement('header', 'answerhdr',
            get_string('answers', 'question'), '');
        $mform->setExpanded('answerhdr', 1);
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
            $this->get_more_choices_string(), true);
    }


    ///////////////////////////////////////////////////////////////////////////////////////////////////////////


    /**
     * FIXME: is it really needed?
     * Override method to add a repeating group of elements to a form,
     * only for disabling 'addanswers' button
     *
     * @param array $elementobjs Array of elements or groups of elements that are to be repeated
     * @param int $repeats no of times to repeat elements initially
     * @param array $options a nested array. The first array key is the element name.
     *    the second array key is the type of option to set, and depend on that option,
     *    the value takes different forms.
     *         'default'    - default value to set. Can include '{no}' which is replaced by the repeat number.
     *         'type'       - PARAM_* type.
     *         'helpbutton' - array containing the helpbutton params.
     *         'disabledif' - array containing the disabledIf() arguments after the element name.
     *         'rule'       - array containing the addRule arguments after the element name.
     *         'expanded'   - whether this section of the form should be expanded by default. (Name be a header element.)
     *         'advanced'   - whether this element is hidden by 'Show more ...'.
     * @param string $repeathiddenname name for hidden element storing no of repeats in this form
     * @param string $addfieldsname name for button to add more fields
     * @param int $addfieldsno how many fields to add at a time
     * @param string $addstring name of button, {no} is replaced by no of blanks that will be added.
     * @param bool $addbuttoninside if true, don't call closeHeaderBefore($addfieldsname). Default false.
     * @return int no of repeats of element in this page
     */
    function repeat_elements($elementobjs, $repeats, $options, $repeathiddenname,
                             $addfieldsname, $addfieldsno = 5, $addstring = null, $addbuttoninside = false)
    {
        if ($addstring === null) {
            $addstring = get_string('addfields', 'form', $addfieldsno);
        } else {
            $addstring = str_ireplace('{no}', $addfieldsno, $addstring);
        }
        $repeats = optional_param($repeathiddenname, $repeats, PARAM_INT);
        $addfields = optional_param($addfieldsname, '', PARAM_TEXT);
        if (!empty($addfields)) {
            $repeats += $addfieldsno;
        }
        $mform =& $this->_form;
        $mform->registerNoSubmitButton($addfieldsname);
        $mform->addElement('hidden', $repeathiddenname, $repeats);
        $mform->setType($repeathiddenname, PARAM_INT);
        //value not to be overridden by submitted value
        $mform->setConstants(array($repeathiddenname => $repeats));
        $namecloned = array();
        for ($i = 0; $i < $repeats; $i++) {
            foreach ($elementobjs as $elementobj) {
                $elementclone = fullclone($elementobj);
                $this->repeat_elements_fix_clone($i, $elementclone, $namecloned);

                if ($elementclone instanceof HTML_QuickForm_group && !$elementclone->_appendName) {
                    foreach ($elementclone->getElements() as $el) {
                        $this->repeat_elements_fix_clone($i, $el, $namecloned);
                    }
                    $elementclone->setLabel(str_replace('{no}', $i + 1, $elementclone->getLabel()));
                }

                $mform->addElement($elementclone);
            }
        }
        for ($i = 0; $i < $repeats; $i++) {
            foreach ($options as $elementname => $elementoptions) {
                $pos = strpos($elementname, '[');
                if ($pos !== FALSE) {
                    $realelementname = substr($elementname, 0, $pos) . "[$i]";
                    $realelementname .= substr($elementname, $pos);
                } else {
                    $realelementname = $elementname . "[$i]";
                }
                foreach ($elementoptions as $option => $params) {
                    switch ($option) {
                        case 'default' :
                            $mform->setDefault($realelementname, str_replace('{no}', $i + 1, $params));
                            break;
                        case 'helpbutton' :
                            $params = array_merge(array($realelementname), $params);
                            call_user_func_array(array(&$mform, 'addHelpButton'), $params);
                            break;
                        case 'disabledif' :
                            foreach ($namecloned as $num => $name) {
                                if ($params[0] == $name) {
                                    $params[0] = $params[0] . "[$i]";
                                    break;
                                }
                            }
                            $params = array_merge(array($realelementname), $params);
                            call_user_func_array(array(&$mform, 'disabledIf'), $params);
                            break;
                        case 'rule' :
                            if (is_string($params)) {
                                $params = array(null, $params, null, 'client');
                            }
                            $params = array_merge(array($realelementname), $params);
                            call_user_func_array(array(&$mform, 'addRule'), $params);
                            break;

                        case 'type':
                            $mform->setType($realelementname, $params);
                            break;

                        case 'expanded':
                            $mform->setExpanded($realelementname, $params);
                            break;

                        case 'advanced' :
                            $mform->setAdvanced($realelementname, $params);
                            break;
                    }
                }
            }
        }

        // FIXME: disable the button for adding new repeated elements
        $mform->addElement('submit', $addfieldsname, $addstring);

        if (!$addbuttoninside) {
            $mform->closeHeaderBefore($addfieldsname);
        }

        return $repeats;
    }
}

