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
 * Question type class for the omerocommon question type.
 *
 * @package    qtype
 * @subpackage omerocommon
 * @copyright  2015 CRS4
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later //FIXME: check the licence
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/engine/lib.php');
require_once($CFG->dirroot . '/question/type/multichoice/questiontype.php');


/**
 * The omeromultichoice question type.
 *
 * @copyright  2015 CRS4
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later // FIXME: check the licence
 */
abstract class qtype_omerocommon extends qtype_multichoice
{

    /**
     * Returns the name of the concrete class
     * which this class subsumes.
     *
     * @return string
     */
    protected function get_qtype()
    {
        return get_class($this);
    }


    /**
     * Returns the name of the table to store questions
     * represented by the subclasses of this base class.
     * Note that we reduce the table name due to the
     * limitation which imposes table names of 28 characters.
     *
     * @return mixed
     */
    protected function get_table_name()
    {
        return str_replace("omero", "ome", get_class($this)) . "_options";
    }

    /**
     * If your question type has a table that extends the question table, and
     * you want the base class to automatically save, backup and restore the extra fields,
     * override this method to return an array wherer the first element is the table name,
     * and the subsequent entries are the column names (apart from id and questionid).
     *
     * @return mixed array as above, or null to tell the base class to do nothing.
     */
    public function extra_question_fields()
    {
        return array($this->get_table_name(),
            "omeroimageurl", "visiblerois"
        );
    }


    protected function make_question_instance($questiondata)
    {
        question_bank::load_question_definition_classes($this->name());
        $class = get_class($this);
        if ($questiondata->options->single) {
            $class = $class . '_single_question';
        } else {
            $class = $class . '_multi_question';
        }
        return new $class();
    }


    public function get_question_options($question)
    {
        global $DB;
        $question->options = $DB->get_record($this->get_table_name(),
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
        foreach ($question->answer_locale_map as $key => $answer) {
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
        foreach ($question->answer as $key => $answertext) {
            if (trim($answertext) == '') {
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

            $answer->answer = $answertext;
            $answer->answerformat = $question->answerformat[$key];
            $answer->fraction = $question->fraction[$key];
            $answer->feedback = $question->feedback[$key];
            $answer->feedbackformat = $question->feedbackformat[$key];

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

        $options = $DB->get_record($this->get_table_name(), array('questionid' => $question->id));
        if (!$options) {
            $options = new stdClass();
            $options->questionid = $question->id;
            $options->omeroimageurl = '';
            $options->correctfeedback = '';
            $options->partiallycorrectfeedback = '';
            $options->incorrectfeedback = '';
            $options->visiblerois = '';
            $options->id = $DB->insert_record($this->get_table_name(), $options);
        }

        $options->single = $question->single;
        if (isset($question->layout)) {
            $options->layout = $question->layout;
        }
        $options->omeroimageurl = $question->omeroimageurl;
        $options->answernumbering = $question->answernumbering;
        $options->shuffleanswers = $question->shuffleanswers;
        $options->visiblerois = $question->visiblerois;
        $options = $this->save_combined_feedback_helper($options, $question, $context, true);
        $DB->update_record($this->get_table_name(), $options);

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
        $question->visible_rois = $questiondata->options->visiblerois;
        // set the question answer type
        if (!empty($questiondata->options->answertype)) {
            $question->answertype = $questiondata->options->answertype;
        } else {
            $question->answertype = qtype_omerocommon::PLAIN_ANSWERS;
        }
    }


    public function delete_question($questionid, $contextid)
    {
        global $DB;
        $DB->delete_records($this->get_table_name(), array('questionid' => $questionid));
        parent::delete_question($questionid, $contextid);
    }
}
