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

        $module = array('name' => 'omero_multichoice_helper', 'fullpath' => '/question/type/omeromultichoice/omero_multichoice_helper.js',
            'requires' => array('omemultichoice_qtype', 'node', 'node-event-simulate', 'core_dndupload'));
        $PAGE->requires->js_init_call('M.omero_multichoice_helper.init', array(), true, $module);

        $menu = array(
            get_string('answersingleno', 'qtype_multichoice'),
            get_string('answersingleyes', 'qtype_multichoice'),
        );
        $mform->addElement('omerofilepicker', 'usefilereference', get_string('file'), null,
            array('maxbytes' => 2048, 'accepted_types' => array('*'),
                'return_types' => array(FILE_INTERNAL | FILE_EXTERNAL)));
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
    }


    /**
     * Language string to use for 'Add {no} more {whatever we call answers}'.
     */
    protected function get_more_choices_string()
    {
        return get_string('add_roi_answer', 'qtype_omeromultichoice');
    }


    protected function get_per_answer_fields($mform, $label, $gradeoptions,
                                             &$repeatedoptions, &$answersoption)
    {
        $repeated = array();

        // ROI Selector Container
        $repeated[] = $mform->createElement('html', '<div class="omeromultichoice-qanswer-roi-selector-container">');
        $repeated[] = $mform->createElement('static', "description", $label . ": ");
        $repeated[] = $mform->createElement('select', 'roi', "", $gradeoptions);
        $repeated[] = $mform->createElement('html', '</div>'); // -> Close 'qanswer-roi-selector-container'


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
            '<div class="omeromultichoice-qanswer-roi-details-text"><b>Label:</b> ain asdasdlkj asd <input</div>');
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

        return $repeated;
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
        echo "Number of ROIS: " . count($data['roi']);
        $errors = parent::validation($data, $files);
        return $errors;
    }
}
