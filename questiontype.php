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
class qtype_omeromultichoice extends qtype_multichoice {

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
}
