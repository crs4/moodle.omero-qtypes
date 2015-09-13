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

        echo "<br/>FORM definition....";
        $module = array('name' => 'omero_multichoice_helper', 'fullpath' => '/question/type/omeromultichoice/omero_multichoice_helper.js',
            'requires' => array('omemultichoice_qtype', 'node', 'node-event-simulate', 'core_dndupload'));
        $PAGE->requires->js_init_call('M.omero_multichoice_helper.init', array(), true, $module);


        $mform->addElement('omerofilepicker', 'usefilereference', get_string('file'), null,
            array('maxbytes' => 2048, 'accepted_types' => array('*'),
                'return_types' => array(FILE_INTERNAL | FILE_EXTERNAL)));

        $mform->addElement("button", "add-roi-answer",
            get_string("add_roi_answer", "qtype_omeromultichoice"), array("disabled"=> true));


        $menu = array(
            get_string('answersingleno', 'qtype_multichoice'),
            get_string('answersingleyes', 'qtype_multichoice'),
        );
        $mform->addElement('select', 'single',
            get_string('answerhowmany', 'qtype_multichoice'), $menu);
        $mform->setDefault('single', 1);

        $mform->addElement('advcheckbox', 'shuffleanswers',
            get_string('shuffleanswers', 'qtype_multichoice'), null, null, array(0, 1));
        $mform->addHelpButton('shuffleanswers', 'shuffleanswers', 'qtype_multichoice');
        $mform->setDefault('shuffleanswers', 1);

        $mform->addElement('select', 'answernumbering',
            get_string('answernumbering', 'qtype_multichoice'),
            qtype_multichoice::get_numbering_styles());
        $mform->setDefault('answernumbering', 'abc');

        // Set the initial number of answers to 0; add answers one by one
        $this->add_per_answer_fields($mform, get_string('choiceno', 'qtype_omeromultichoice', '{no}'),
            question_bank::fraction_options_full(), 0, 1);

        $this->add_combined_feedback_fields(true);
        $mform->disabledIf('shownumcorrect', 'single', 'eq', 1);

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


        echo "<br/>FORM definition: done<br/><br/>";
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
        $repeated = array();

        // ROI Selector Container
        $repeated[] = $mform->createElement('html', '<div class="omeromultichoice-qanswer-roi-selector-container">');
        $repeated[] = $mform->createElement('static', "description", $label . ": ");
        $repeated[] = $mform->createElement('select', 'roi', "", $gradeoptions);
        $repeated[] = $mform->createElement('html', '</div>'); // -> Close 'qanswer-roi-selector-container'

        $repeated[] = $mform->createElement('hidden', 'answer', "xxx");

        $repeated[] = $mform->createElement('html', '<div class="omeromultichoice-qanswer-container">');
        $repeated[] = $mform->createElement('html', '<div class="omeromultichoice-qanswer-roi-container">');
        // Image container
        $repeated[] = $mform->createElement('html', '<div class="omeromultichoice-qanswer-roi-image-container">');
        $repeated[] = $mform->createElement('html', '<img src=" http://192.168.1.160:8080/webgateway/render_shape_thumbnail/011/?color=f00" id="11_shape_thumb" class="roi_thumb shape_thumb" style="vertical-align: top;" color="f00" width="150px" height="150px">');
        $repeated[] = $mform->createElement('html', '</div>'); // -> Close 'qanswer-roi-image-container

        $repeated[] = $mform->createElement('html', '<div class="omeromultichoice-qanswer-roi-details-container">');
        $repeated[] = $mform->createElement('html', '<div class="omeromultichoice-qanswer-roi-details-text-container">');
        $repeated[] = $mform->createElement('html',
            '<div class="omeromultichoice-qanswer-roi-details-text"><b>Name:</b> ain asdasdlkj asd</div>');
        $repeated[] = $mform->createElement('html',
            '<div class="omeromultichoice-qanswer-roi-details-text"><b>Label:</b> ain asdasdlkj asd</div>');
        $repeated[] = $mform->createElement('html',
            '<div class="omeromultichoice-qanswer-roi-details-text"><b>Label:</b> ain asdasdlkj asd </div>');
        $repeated[] = $mform->createElement('html', '</div>'); // -> Close 'details-text-container'
        $repeated[] = $mform->createElement('html', '</div>'); // -> Close 'qanswer-roi-details-container

        $repeated[] = $mform->createElement('html', '</div>'); // -> Close 'qanswer-roi-container'

        $repeated[] = $mform->createElement('select', 'fraction', get_string('grade'), $gradeoptions);

        $repeated[] = $mform->createElement('editor', 'feedback',
            get_string('feedback', 'question'), array('rows' => 1), $this->editoroptions);

        $repeated[] = $mform->createElement('html', '</div>'); // -> Close 'qanswer-roi-details-container'

        $repeatedoptions['answer']['type'] = PARAM_RAW;
        $repeatedoptions['fraction']['default'] = 0;
        $answersoption = 'answers';

        echo "<br/> REPEATED options   ....  <br/>";
        print_r($repeatedoptions);
        echo "<br/>   .... ";
        echo "<br/>   .... ";

        $mform->setType("roi_id", PARAM_RAW);
        $repeated[] = $mform->createElement('hidden', 'roi_id', 'xxx');

//        // FIXME: to debug
//        $mform->setType("answer[text]", PARAM_RAW);
//        $repeated[] = $mform->createElement('hidden', 'answer[text]', 'true');
//
//        $mform->setType("answer", PARAM_RAW);
//        $repeated[] = $mform->createElement('hidden', 'answer', 'truexxx');

        return $repeated;
    }


    protected function data_preprocessing($question) {
        echo "<br>Preprocessing....<br/>";
        print_r($question);
        $question = parent::data_preprocessing($question);
        $question = $this->data_preprocessing_answers($question, true);
        $question = $this->data_preprocessing_combined_feedback($question, true);
        $question = $this->data_preprocessing_hints($question, true, true);

        if (!empty($question->options)) {
            $question->single = $question->options->single;
            $question->shuffleanswers = $question->options->shuffleanswers;
            $question->answernumbering = $question->options->answernumbering;
        }

        echo "<br/> Options: <br/>";
        //print_r($question->options);
        echo "<br/> Options done!!! <br/>";

        echo "<br>Preprocessing: done....<br/>";

        return $question;
    }


    public function get_data(){
        echo "<br>GETTING DATA.....";

        $data = parent::get_data();
        echo "<br><br>Data RETRIVIED....";
        print_r($data);

        echo "<br><br>Updated DATA....";
        $this->update_raw_data($data);
        print_r($data);

        echo "<br/>Getting DATA: DONE....";
        return $data;
    }


    private function update_raw_data(&$data){
        if(!empty($data)) {
            if(is_array($data))
                $answers = $data["answer"];
            else
                $answers = $data->{"answer"};
            echo "<br>Number of answers: " . count($answers);

            if (isset($_POST["roi_based_answers"])) {
                $roi_based_answers_el = $_POST["roi_based_answers"];
                $roi_based_answers = explode(",", $roi_based_answers_el);
                foreach($roi_based_answers as $k => $a){
                    $answers[$k] = array("text" => "$a", "format" => 1, "itemid" => "");
                }
            }
        }
    }


    public function set_data($question) {
        echo "Calling set data.....";
        print_r($question);
        parent::set_data($question);
        echo "<br/>Calling set data: DONE....";
    }

    protected function data_preprocessing_answers($question, $withanswerfiles = false) {
        echo "<br/>Proprocessing answers....<br/>";
        print_r($question);

        if (empty($question->options->answers)) {
            return $question;
        }


        print_r($question->options);

        $key = 0;
        foreach ($question->options->answers as $answer) {
            if ($withanswerfiles) {
                // Prepare the feedback editor to display files in draft area.
                $draftitemid = file_get_submitted_draft_itemid('answer['.$key.']');
                $question->answer[$key]['text'] = file_prepare_draft_area(
                    $draftitemid,          // Draftid
                    $this->context->id,    // context
                    'question',            // component
                    'answer',              // filarea
                    !empty($answer->id) ? (int) $answer->id : null, // itemid
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
            $draftitemid = file_get_submitted_draft_itemid('feedback['.$key.']');
            $question->feedback[$key]['text'] = file_prepare_draft_area(
                $draftitemid,          // Draftid
                $this->context->id,    // context
                'question',            // component
                'answerfeedback',      // filarea
                !empty($answer->id) ? (int) $answer->id : null, // itemid
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

        echo "<br/>Proprocessing answers: done ....<br/><br/>";

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
    public function validation($data, $files) {
        echo "Number of ROIS: " . count($data['roi_id']);
        print_r($data['roi_id']);

        echo "<br/><br/>Printing answers: ";
        print_r($data["answer"]);

        echo "<br/><br/>Printing fraction: ";
        print_r($data["fraction"]);

        echo "<br/><br/>Printing feedback: ";
        print_r($data["feedback"]);


        echo "<br/><br/>";

        $this->update_raw_data($data);

        $errors = array();
        if(count($data["answer"])<3)
            $errors["generic"] = "At least 2 answers";



//        foreach ($data as $k => $v) {
//            echo "<br/>" . $k . " ---> " . $v;
//            if(is_array($v)){
//                echo "<br/>Array: " . $k . "---------------------------------";
//                foreach($v as $ak => $av){
//                    echo "<br/>" . $ak . " ---> " . $av;
//                }
//                echo "<br/>Array: " . $k . "---------------------------------";
//            }
//        }


        $errors = parent::validation($data, $files);

        //if(count($data['roi_id']<2))
        //    $errors["answer[0]"] = "At least....";
        return $errors;
    }


    public function render(){
        echo "Rendering";
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
                                             $minoptions = QUESTION_NUMANS_START, $addoptions = QUESTION_NUMANS_ADD) {
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
                             $addfieldsname, $addfieldsno=5, $addstring=null, $addbuttoninside=false){
        if ($addstring===null){
            $addstring = get_string('addfields', 'form', $addfieldsno);
        } else {
            $addstring = str_ireplace('{no}', $addfieldsno, $addstring);
        }
        $repeats = optional_param($repeathiddenname, $repeats, PARAM_INT);
        $addfields = optional_param($addfieldsname, '', PARAM_TEXT);
        if (!empty($addfields)){
            $repeats += $addfieldsno;
        }
        $mform =& $this->_form;
        $mform->registerNoSubmitButton($addfieldsname);
        $mform->addElement('hidden', $repeathiddenname, $repeats);
        $mform->setType($repeathiddenname, PARAM_INT);
        //value not to be overridden by submitted value
        $mform->setConstants(array($repeathiddenname=>$repeats));
        $namecloned = array();
        for ($i = 0; $i < $repeats; $i++) {
            foreach ($elementobjs as $elementobj){
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
        for ($i=0; $i<$repeats; $i++) {
            foreach ($options as $elementname => $elementoptions){
                $pos=strpos($elementname, '[');
                if ($pos!==FALSE){
                    $realelementname = substr($elementname, 0, $pos)."[$i]";
                    $realelementname .= substr($elementname, $pos);
                }else {
                    $realelementname = $elementname."[$i]";
                }
                foreach ($elementoptions as  $option => $params){

                    switch ($option){
                        case 'default' :
                            $mform->setDefault($realelementname, str_replace('{no}', $i + 1, $params));
                            break;
                        case 'helpbutton' :
                            $params = array_merge(array($realelementname), $params);
                            call_user_func_array(array(&$mform, 'addHelpButton'), $params);
                            break;
                        case 'disabledif' :
                            foreach ($namecloned as $num => $name){
                                if ($params[0] == $name){
                                    $params[0] = $params[0]."[$i]";
                                    break;
                                }
                            }
                            $params = array_merge(array($realelementname), $params);
                            call_user_func_array(array(&$mform, 'disabledIf'), $params);
                            break;
                        case 'rule' :
                            if (is_string($params)){
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

