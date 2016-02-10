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
 * Upgrade library code for the omeromultichoice question type.
 *
 * @package    qtype
 * @subpackage omeromultichoice
 * @copyright  2015-2016 CRS4
 * @licence    https://opensource.org/licenses/mit-license.php MIT licence
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Class for converting attempt data for omeromultichoice questions when upgrading
 * attempts to the new question engine.
 *
 * This class is used by the code in question/engine/upgrade/upgradelib.php.
 *
 * @copyright  2015-2016 CRS4
 * @licence    https://opensource.org/licenses/mit-license.php MIT licence
 */
class qtype_omeromultichoice_qe2_attempt_updater extends question_qtype_attempt_updater {
    protected $order;

    public function is_blank_answer($state) {
        // Blank omeromultichoice answers are not empty strings, they rather end in a colon.
        return empty($state->answer) || substr($state->answer, -1) == ':';
    }

    public function right_answer() {
        if ($this->question->options->single) {
            foreach ($this->question->options->answers as $ans) {
                if ($ans->fraction > 0.999) {
                    return $this->to_text($ans->answer);
                }
            }

        } else {
            $rightbits = array();
            foreach ($this->question->options->answers as $ans) {
                if ($ans->fraction >= 0.000001) {
                    $rightbits[] = $this->to_text($ans->answer);
                }
            }
            return implode('; ', $rightbits);
        }
    }

    protected function explode_answer($answer) {
        if (strpos($answer, ':') !== false) {
            list($order, $responses) = explode(':', $answer);
            return $responses;
        } else {
            // Sometimes, a bug means that a state is missing the <order>: bit,
            // We need to deal with that.
            $this->logger->log_assumption("Dealing with missing order information
                    in attempt at multiple choice question {$this->question->id}");
            return $answer;
        }
    }

    public function response_summary($state) {
        $responses = $this->explode_answer($state->answer);
        if ($this->question->options->single) {
            if (is_numeric($responses)) {
                if (array_key_exists($responses, $this->question->options->answers)) {
                    return $this->to_text($this->question->options->answers[$responses]->answer);
                } else {
                    $this->logger->log_assumption("Dealing with a place where the
                            student selected a choice that was later deleted for
                            multiple choice question {$this->question->id}");
                    return '[CHOICE THAT WAS LATER DELETED]';
                }
            } else {
                return null;
            }

        } else {
            if (!empty($responses)) {
                $responses = explode(',', $responses);
                $bits = array();
                foreach ($responses as $response) {
                    if (array_key_exists($response, $this->question->options->answers)) {
                        $bits[] = $this->to_text(
                                $this->question->options->answers[$response]->answer);
                    } else {
                        $this->logger->log_assumption("Dealing with a place where the
                                student selected a choice that was later deleted for
                                multiple choice question {$this->question->id}");
                        $bits[] = '[CHOICE THAT WAS LATER DELETED]';
                    }
                }
                return implode('; ', $bits);
            } else {
                return null;
            }
        }
    }

    public function was_answered($state) {
        $responses = $this->explode_answer($state->answer);
        if ($this->question->options->single) {
            return is_numeric($responses);
        } else {
            return !empty($responses);
        }
    }

    public function set_first_step_data_elements($state, &$data) {
        if (!$state->answer) {
            return;
        }
        list($order, $responses) = explode(':', $state->answer);
        $data['_order'] = $order;
        $this->order = explode(',', $order);
    }

    public function supply_missing_first_step_data(&$data) {
        $data['_order'] = implode(',', array_keys($this->question->options->answers));
    }

    public function set_data_elements_for_step($state, &$data) {
        $responses = $this->explode_answer($state->answer);
        if ($this->question->options->single) {
            if (is_numeric($responses)) {
                $flippedorder = array_combine(array_values($this->order), array_keys($this->order));
                if (array_key_exists($responses, $flippedorder)) {
                    $data['answer'] = $flippedorder[$responses];
                } else {
                    $data['answer'] = '-1';
                }
            }

        } else {
            $responses = explode(',', $responses);
            foreach ($this->order as $key => $ansid) {
                if (in_array($ansid, $responses)) {
                    $data['choice' . $key] = 1;
                } else {
                    $data['choice' . $key] = 0;
                }
            }
        }
    }
}
