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
require_once($CFG->dirroot . '/question/type/omerocommon/questiontype_base.php');


/**
 * The omeromultichoice question type.
 *
 * @copyright  2015 CRS4
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later // FIXME: check the licence
 */
class qtype_omeromultichoice extends qtype_omerocommon
{

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

        if ($answercount < 1) { // Check there are at lest 2 answers for multiple choice.
            $result->notice = get_string('notenoughanswers', 'qtype_multichoice', '1');
            return $result;
        }

        // Insert all the new answers.
        $totalfraction = 0;
        $maxfraction = -1;
        foreach ($question->answer_locale_map as $key => $answertext) {
            if (trim($answertext) == '') {
                continue;
            }

            // Update an existing answer if possible.
            $answer = array_shift($oldanswers);
            if (!$answer) {
                $answer = new stdClass();
                $answer->question = $question->id;
                $answer->answer = '';
                $answer->answerformat = '';
                $answer->feedback = '';
                $answer->fraction = 0;
                $answer->id = $DB->insert_record('question_answers', $answer);
            }


            $answer->answer = qtype_omerocommon::serialize_to_multilang_form($question->answer_locale_map[$key]);
            $answer->feedback = qtype_omerocommon::serialize_to_multilang_form($question->feedback_locale_map[$key]);

            if (isset($question->fraction[$key]))
                $answer->fraction = $question->fraction[$key];


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
            $options->focusablerois = '';
            $options->omeroimagelocked = 0;
            $options->omeroimageproperties = "";
            $options->id = $DB->insert_record($this->get_table_name(), $options);
        }

        $options->single = $question->single;
        if (isset($question->layout)) {
            $options->layout = $question->layout;
        }

        $options->omeroimageurl = $question->omeroimageurl;
        $options->omeroimagelocked = $question->omeroimagelocked;
        $options->omeroimageproperties = $question->omeroimageproperties;
        $options->answernumbering = $question->answernumbering;
        $options->shuffleanswers = $question->shuffleanswers;
        $options->visiblerois = $question->visiblerois;
        $options->focusablerois = $question->focusablerois;
        $options = $this->save_combined_feedback_helper($options, $question, $context, true);

        $options->correctfeedback = qtype_omerocommon::serialize_to_multilang_form($question->correctfeedback_locale_map);
        $options->partiallycorrectfeedback = qtype_omerocommon::serialize_to_multilang_form($question->partiallycorrectfeedback_locale_map);
        $options->incorrectfeedback = qtype_omerocommon::serialize_to_multilang_form($question->incorrectfeedback_locale_map);

        $DB->update_record($this->get_table_name(), $options);

        $this->save_hints($question, true);

        // Perform sanity checks on fractional grades.
        if ($options->single) {
            if ($maxfraction != 1) {
                $result->notice = get_string('fractionsnomax', 'qtype_multichoice',
                    $maxfraction * 100);
                return $result;
            }
        } else {
            $totalfraction = round($totalfraction, 2);
            if ($totalfraction != 1) {
                $result->notice = get_string('fractionsaddwrong', 'qtype_multichoice',
                    $totalfraction * 100);
                return $result;
            }
        }
    }
}
