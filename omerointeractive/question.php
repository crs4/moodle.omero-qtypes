<?php

// Copyright (c) 2015-2016, CRS4
//
// Permission is hereby granted, free of charge, to any person obtaining a copy of
// this software and associated documentation files (the "Software"), to deal in
// the Software without restriction, including without limitation the rights to
// use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
// the Software, and to permit persons to whom the Software is furnished to do so,
// subject to the following conditions:
//
// The above copyright notice and this permission notice shall be included in all
// copies or substantial portions of the Software.
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
// FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
// COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
// IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
// CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

/**
 * omerointeractive question definition class.
 *
 * @package    qtype
 * @subpackage omerointeractive
 * @copyright  2015-2016 CRS4
 * @license    https://opensource.org/licenses/mit-license.php MIT license
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/omerocommon/question.php');

/**
 * Represents a omerointeractive question with a single correct answer option.
 *
 * @copyright  2015-2016 CRS4
 * @license    https://opensource.org/licenses/mit-license.php MIT license
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