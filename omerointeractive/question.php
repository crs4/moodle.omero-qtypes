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
 * omerointeractive question definition class.
 *
 * @package    qtype
 * @subpackage omerointeractive
 * @copyright  2015 CRS4
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later // FIXME: check the licence
 */


defined('MOODLE_INTERNAL') || die();


require_once($CFG->dirroot . '/question/type/omerocommon/question.php');


/**
 * Represents a omerointeractive question with a single correct answer option.
 *
 * @copyright  2015 CRS4
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later // FIXME: check the licence
 */
class qtype_omerointeractive_single_question extends qtype_multichoice_single_question
{
    /**
     * Return a propert renderer
     *
     * @param moodle_page $page
     * @return renderer_base
     */
    public function get_renderer(moodle_page $page)
    {
        return $page->get_renderer('qtype_omerointeractive', 'single');
    }

    /**
     * Return an associative array which describes
     * the set of expected data as <NAME,TYPE> pairs.
     *
     * @return array
     */
    public function get_expected_data()
    {
        return array('answer' => PARAM_RAW);
    }

    /**
     * Check whether a choice has been selected
     *
     * @param $response
     * @param $value
     * @return bool
     */
    public function is_choice_selected($response, $value)
    {
        if ($response && isset($response->shapes) && count($response->shapes) > 0) {
            foreach ($response->shapes as $shape) {
                if ($shape !== "none") {
                    if ((string)$shape->shape_id == (string)$value)
                        return true;
                }
            }
        }
        return false;
    }


    /**
     * Evaluate a response
     *
     * @param array $response
     * @return array
     */
    public function grade_response(array $response)
    {
        $fraction = 0;
        if (array_key_exists('answer', $response)) {
            $re = json_decode($response["answer"]);
            if (isset($re->shapes)
                && count($re->shapes) > 0
                && $re->shapes[0] != "none"
                && array_key_exists($re->shapes[0]->shape_group, $this->order)
            ) $fraction = $this->answers[$this->order[$re->shapes[0]->shape_group]]->fraction;
        }
        return array($fraction, question_state::graded_state_for_fraction($fraction));
    }


    /**
     * Return the response as decoded object
     *
     * @param question_attempt $qa
     * @return mixed
     */
    public function get_response(question_attempt $qa)
    {
        $response = parent::get_response($qa);
        $re = json_decode($response);
        return $re;
    }

    /**
     * Return the raw response as array
     * @param question_attempt $qa
     * @return mixed
     */
    public function get_raw_response(question_attempt $qa)
    {
        return parent::get_response($qa);
    }
}


/**
 * Class qtype_omerointeractive_multi_question:
 * for questions with multi correct answer options
 *
 */
class qtype_omerointeractive_multi_question extends qtype_omerointeractive_single_question
{
    /**
     * Return the proper renderer for this class
     *
     * @param moodle_page $page
     * @return renderer_base
     */
    public function get_renderer(moodle_page $page)
    {
        return $page->get_renderer('qtype_omerointeractive', 'multi');
    }


    /**
     * Return an associative array which describes
     * the set of expected data as <NAME,TYPE> pairs.
     *
     * @return array
     */
    public function get_expected_data()
    {
        return array('answer' => PARAM_RAW);
    }


    /**
     * Evaluate a response
     *
     * @param array $response
     * @return array
     */
    public function grade_response(array $response)
    {
        $fraction = 0;
        if (array_key_exists('answer', $response)) {
            $re = json_decode($response["answer"]);
            if (isset($re->shapes) && count($re->shapes) > 0) {
                foreach ($re->shapes as $ks => $shape) {
                    if ($shape !== "none") {
                        $answer = $this->answers[$this->order[$shape->shape_group]];
                        if ($answer && $answer->answer) {
                            $roi_answer_cardinality = count(explode(",", $answer->answer));
                            if ($roi_answer_cardinality > 0)
                                $fraction += $answer->fraction / $roi_answer_cardinality;
                        }
                    }
                }
            }

        }
        return array($fraction, question_state::graded_state_for_fraction($fraction));
    }


    /**
     * Return the raw response as array
     * @param question_attempt $qa
     * @return mixed
     */
    public function get_raw_response(question_attempt $qa)
    {
        return parent::get_response($qa);
    }


    /**
     * @param array $response responses, as returned by
     *      {@link question_attempt_step::get_qt_data()}.
     * @return int the number of choices that were selected. in this response.
     */
    public function get_num_selected_choices(array $response)
    {
        $responses = json_decode($response["answer"]);
        if (isset($responses->shapes))
            return count($responses->shapes);
        return 0;
    }


    /**
     * @return int the number of choices that are correct.
     */
    public function get_num_correct_choices()
    {
        $numcorrect = 0;
        foreach ($this->answers as $ans) {
            if (!question_state::graded_state_for_fraction($ans->fraction)->is_incorrect()) {
                if ($ans->answer != "none" && $ans->fraction > 0) {
                    $numcorrect += count(explode(",", $ans->answer));
                }
            }
        }
        return $numcorrect;
    }

    protected function disable_hint_settings_when_too_many_selected(
        question_hint_with_parts $hint)
    {
        $hint->clearwrong = false;
    }

    public function get_hint($hintnumber, question_attempt $qa)
    {
        $hint = parent::get_hint($hintnumber, $qa);
        if (is_null($hint)) {
            return $hint;
        }

        if ($this->get_num_selected_choices($qa->get_last_qt_data()) >
            $this->get_num_correct_choices($qa)
        ) {
            $hint = clone($hint);
            $this->disable_hint_settings_when_too_many_selected($hint);
        }
        return $hint;
    }
}