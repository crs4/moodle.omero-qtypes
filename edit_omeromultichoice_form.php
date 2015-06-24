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

    protected function definition_inner($mform)
    {
        global $PAGE, $OUTPUT;

        $module = array('name'=>'omero_multichoice_helper', 'fullpath'=>'/question/type/omeromultichoice/omero_multichoice_helper.js',
            'requires'=>array('omemultichoice_qtype', 'node', 'node-event-simulate', 'core_dndupload'));
        $PAGE->requires->js_init_call('M.omero_multichoice_helper.init', array(), true, $module);

        $menu = array(
            get_string('answersingleno', 'qtype_multichoice'),
            get_string('answersingleyes', 'qtype_multichoice'),
        );
        $mform->addElement('omerofilepicker', 'usefilereference', get_string('file'), null,
            array('maxbytes' => 2048, 'accepted_types' => array('*'),
                  'return_types'=> array( FILE_INTERNAL | FILE_EXTERNAL)));

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

        $this->add_per_answer_fields($mform, get_string('choiceno', 'qtype_multichoice', '{no}'),
            question_bank::fraction_options_full(), max(1, QUESTION_NUMANS_START));

        $this->add_combined_feedback_fields(true);
        $mform->disabledIf('shownumcorrect', 'single', 'eq', 1);

        $this->add_interactive_settings(true, true);
}


//    protected function data_preprocessing($question) {
//        $question = parent::data_preprocessing($question);
//        $question = $this->data_preprocessing_hints($question);
//
//        return $question;
//    }
//
//    public function qtype() {
//        return 'omeromultichoice';
//    }
}
