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
 * Question type class for the omeromultichoice question type.
 *
 * @package    qtype
 * @subpackage omeromultichoice
 * @copyright  2015 CRS4
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later //FIXME: check the licence
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/engine/lib.php');
require_once($CFG->dirroot . '/question/type/multichoice/questiontype.php');
require_once($CFG->dirroot . '/question/type/omeromultichoice/question.php');


/**
 * The omeromultichoice question type.
 *
 * @copyright  2015 CRS4
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later // FIXME: check the licence
 */
class qtype_omeromultichoice extends qtype_multichoice
{

    const PLAIN_ANSWERS = 0;
    const ROI_BASED_ANSWERS = 1;

//    public function move_files($questionid, $oldcontextid, $newcontextid) {
//        parent::move_files($questionid, $oldcontextid, $newcontextid);
//        $this->move_files_in_hints($questionid, $oldcontextid, $newcontextid);
//    }
//
//    protected function delete_files($questionid, $contextid) {
//        parent::delete_files($questionid, $contextid);
//        $this->delete_files_in_hints($questionid, $contextid);
//    }
//
//    public function save_question_options($question) {
//        $this->save_hints($question);
//    }



    protected function make_question_instance($questiondata)
    {
        question_bank::load_question_definition_classes($this->name());
        if ($questiondata->options->single) {
            $class = 'qtype_omeromultichoice_single_question';
        } else {
            $class = 'qtype_omeromultichoice_multi_question';
        }
        return new $class();
    }


    public function get_question_options($question)
    {
        global $DB, $OUTPUT;
        $question->options = $DB->get_record('qtype_omemultichoice_options',
            array('questionid' => $question->id), '*', MUST_EXIST);
        question_type::get_question_options($question);
    }

    public function save_question_options($question)
    {
        global $DB;
        $context = $question->context;
        $result = new stdClass();

        $oldanswers = $DB->get_records('question_answers',
            array('question' => $question->id), 'id ASC');

        // Following hack to check at least two answers exist.
        $answercount = 0;
        foreach ($question->answer as $key => $answer) {
            if ($answer != '') {
                $answercount++;
            }
        }
        if ($answercount < 2) { // Check there are at lest 2 answers for multiple choice.
            $result->notice = get_string('notenoughanswers', 'qtype_multichoice', '2');
            return $result;
        }

        // Insert all the new answers.
        $totalfraction = 0;
        $maxfraction = -1;
        foreach ($question->answer as $key => $answerdata) {
            if (trim($answerdata['text']) == '') {
                continue;
            }

            // Update an existing answer if possible.
            $answer = array_shift($oldanswers);
            if (!$answer) {
                $answer = new stdClass();
                $answer->question = $question->id;
                $answer->answer = '';
                $answer->feedback = '';
                $answer->id = $DB->insert_record('question_answers', $answer);
            }

            // Doing an import.
            $answer->answer = $this->import_or_save_files($answerdata,
                $context, 'question', 'answer', $answer->id);
            $answer->answerformat = $answerdata['format'];
            $answer->fraction = $question->fraction[$key];
            $answer->feedback = $this->import_or_save_files($question->feedback[$key],
                $context, 'question', 'answerfeedback', $answer->id);
            $answer->feedbackformat = $question->feedback[$key]['format'];

            $DB->update_record('question_answers', $answer);

            if ($question->fraction[$key] > 0) {
                $totalfraction += $question->fraction[$key];
            }
            if ($question->fraction[$key] > $maxfraction) {
                $maxfraction = $question->fraction[$key];
            }
        }

        // Delete any left over old answer records.
        $fs = get_file_storage();
        foreach ($oldanswers as $oldanswer) {
            $fs->delete_area_files($context->id, 'question', 'answerfeedback', $oldanswer->id);
            $DB->delete_records('question_answers', array('id' => $oldanswer->id));
        }

        $options = $DB->get_record('qtype_omemultichoice_options', array('questionid' => $question->id));
        if (!$options) {
            $options = new stdClass();
            $options->questionid = $question->id;
            $options->answertype = qtype_omeromultichoice::PLAIN_ANSWERS;
            $options->omeroimageurl = '';
            $options->correctfeedback = '';
            $options->partiallycorrectfeedback = '';
            $options->incorrectfeedback = '';
            $options->id = $DB->insert_record('qtype_omemultichoice_options', $options);
        }

        $options->single = $question->single;
        if (isset($question->layout)) {
            $options->layout = $question->layout;
        }
        $options->answertype = $question->answertype;
        $options->omeroimageurl = $question->omero_image_url;
        $options->answernumbering = $question->answernumbering;
        $options->shuffleanswers = $question->shuffleanswers;
        $options = $this->save_combined_feedback_helper($options, $question, $context, true);
        $DB->update_record('qtype_omemultichoice_options', $options);

        $this->save_hints($question, true);

        // Perform sanity checks on fractional grades.
        if ($options->single) {
            if ($maxfraction != 1) {
                $result->noticeyesno = get_string('fractionsnomax', 'qtype_multichoice',
                    $maxfraction * 100);
                return $result;
            }
        } else {
            $totalfraction = round($totalfraction, 2);
            if ($totalfraction != 1) {
                $result->noticeyesno = get_string('fractionsaddwrong', 'qtype_multichoice',
                    $totalfraction * 100);
                return $result;
            }
        }
    }



//
//    protected function initialise_question_instance(question_definition $question, $questiondata) {
//        // TODO.
//        parent::initialise_question_instance($question, $questiondata);
//    }
//
//    public function get_random_guess_score($questiondata) {
//        // TODO.
//        return 0;
//    }
//
//    public function get_possible_responses($questiondata) {
//        // TODO.
//        return array();
//    }


    protected function initialise_question_instance(question_definition $question, $questiondata)
    {
        parent::initialise_question_instance($question, $questiondata);
        // set the omero image url
        $question->omeroimageurl = $questiondata->options->omeroimageurl;
        // set the question answer type
        if(!empty($questiondata->options->answertype)) {
            $question->answertype = $questiondata->options->answertype;
        } else {
            $question->answertype = qtype_omeromultichoice::PLAIN_ANSWERS;
        }
    }


    public function delete_question($questionid, $contextid)
    {
        global $DB;
        $DB->delete_records('qtype_omemultichoice_options', array('questionid' => $questionid));
        parent::delete_question($questionid, $contextid);
    }


    ///////////////////////////////////////////////////////////////////////////////

    /**
     * Returns the list of supported omero-question types
     *
     * @return array
     */
    public static function get_question_types(){
        return array(qtype_omeromultichoice::PLAIN_ANSWERS, qtype_omeromultichoice::ROI_BASED_ANSWERS);
    }
}
