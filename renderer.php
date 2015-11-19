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
 * omeromultichoice question renderer class.
 *
 * @package    qtype
 * @subpackage omeromultichoice
 * @copyright  2015 CRS4
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later //FIXME: check the licence
 */


defined('MOODLE_INTERNAL') || die();


/**
 * Generates the output for omeromultichoice questions.
 *
 * @copyright  2015 CRS4
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later //FIXME: check the licence
 */

require_once($CFG->dirroot . '/question/type/multichoice/renderer.php');


class qtype_omeromultichoice_single_renderer extends qtype_multichoice_single_renderer
{

    public function formulation_and_controls(question_attempt $qa,
                                             question_display_options $options)
    {
        return qtype_omeromultichoice_base_renderer::impl_formulation_and_controls($this, $qa, $options);
    }


    public function get_input_type()
    {
        return 'radio';
    }

    public function get_input_name(question_attempt $qa, $value)
    {
        return $qa->get_qt_field_name('answer');
    }

    public function get_input_value($value)
    {
        return $value;
    }

    public function get_input_id(question_attempt $qa, $value)
    {
        return $qa->get_qt_field_name('answer' . $value);
    }

    public function correct_response(question_attempt $qa)
    {
        $question = $qa->get_question();
        foreach ($question->get_order($qa) as $ans_number => $ans_id) {
            $answer = $question->answers[$ans_id];
            if (question_state::graded_state_for_fraction($answer->fraction) ==
                question_state::$gradedright
            ) {
                return get_string('correctansweris', 'qtype_multichoice',
                    qtype_omeromultichoice_base_renderer::number_answer($ans_number, $question->answernumbering));
            }
        }

        return '';
    }
}


class qtype_omeromultichoice_multi_renderer extends qtype_multichoice_multi_renderer
{
    public function formulation_and_controls(question_attempt $qa,
                                             question_display_options $options)
    {
        return qtype_omeromultichoice_base_renderer::impl_formulation_and_controls($this, $qa, $options);
    }


    public function get_input_type()
    {
        return 'checkbox';
    }

    public function get_input_name(question_attempt $qa, $value)
    {
        return $qa->get_qt_field_name('choice' . $value);
    }

    public function get_input_value($value)
    {
        return 1;
    }

    public function get_input_id(question_attempt $qa, $value)
    {
        return $this->get_input_name($qa, $value);
    }


    public function correct_response(question_attempt $qa)
    {
        $counter = 0;
        $question = $qa->get_question();
        $right = array();
        foreach ($question->get_order($qa) as $ans_number => $answer_id) {
            $answer = $question->answers[$answer_id];
            if ($answer->fraction > 0) {
                $right[] = qtype_omeromultichoice_base_renderer::number_answer($ans_number, $question->answernumbering);
            }
            $counter++;
        }

        if (!empty($right)) {
            return get_string('correctansweris', 'qtype_multichoice',
                implode(' + ', $right));
        }
        return '';
    }

}


abstract class qtype_omeromultichoice_base_renderer extends qtype_multichoice_renderer_base
{

    public static function impl_formulation_and_controls(qtype_multichoice_renderer_base $renderer,
                                                         question_attempt $qa,
                                                         question_display_options $options)
    {
        global $CFG, $PAGE, $OUTPUT;

        // get the current question
        $question = $qa->get_question();

        // the OMERO image URL
        $omero_image_url = $question->omeroimageurl;

        // extract the omero server
        $OMERO_SERVER = substr($omero_image_url, 0, strpos($omero_image_url, "/webgateway"));

        // parse the URL to get the image ID and its related params
        $matches = array();
        $pattern = '/\/([0123456789]+)(\?.*)/';
        if (preg_match($pattern, $omero_image_url, $matches)) {
            $omero_image = $matches[1];
            $omero_image_params = $matches[2];
        }


        // set question controls
        $response = $question->get_response($qa);

        $inputname = $qa->get_qt_field_name('answer');
        $inputattributes = array(
            'type' => $renderer->get_input_type(),
            'name' => $inputname
        );

        if ($options->readonly) {
            $inputattributes['disabled'] = 'disabled';
        }

        $roi_id_list = array();
        $radiobuttons = array();
        $feedbackimg = array();
        $feedback = array();
        $classes = array();
        foreach ($question->get_order($qa) as $value => $ansid) {
            $ans = $question->answers[$ansid];
            $inputattributes['name'] = $renderer->get_input_name($qa, $value);
            $inputattributes['value'] = $renderer->get_input_value($value);
            $inputattributes['id'] = $renderer->get_input_id($qa, $value);
            $isselected = $question->is_choice_selected($response, $value);
            if ($isselected) {
                $inputattributes['checked'] = 'checked';
            } else {
                unset($inputattributes['checked']);
            }
            $hidden = '';
            if (!$options->readonly && $renderer->get_input_type() == 'checkbox') {
                $hidden = html_writer::empty_tag('input', array(
                    'type' => 'hidden',
                    'name' => $inputattributes['name'],
                    'value' => 0,
                ));
            }

            $answer_content = "";
            if ($question->answertype == qtype_omeromultichoice::ROI_BASED_ANSWERS) {
                $answer_content = html_writer::tag("img", "", array(
                    "src" => "$OMERO_SERVER/webgateway/render_shape_thumbnail/" . $ans->answer . "/?color=f00",
                    "onclick" => "M.omero_multichoice_helper.moveToRoiShape($ans->answer)"
                ));
            } else {
                $formatoptions = new stdClass();
                $formatoptions->noclean = false;
                $formatoptions->para = false;
                $ans_text = $question->answers[$ansid]->answer;
                $ans_text = format_text($ans_text, $question->questiontextformat, $formatoptions);
                $ans_text = html_writer::tag('span', $ans_text,
                    array('class' => 'qtext'));
                $answer_content = '<div style="display: inline-block">' . $ans_text . '</div>';
            }

            $radiobuttons[] = $hidden . html_writer::empty_tag('input', $inputattributes) .
                //html_writer::tag('span',
                html_writer::tag('label',
                    "<b>" . $renderer->number_in_style($value, $question->answernumbering) . "</b>" . $answer_content
                );

            // Param $options->suppresschoicefeedback is a hack specific to the
            // oumultiresponse question type. It would be good to refactor to
            // avoid refering to it here.
            if ($options->feedback && empty($options->suppresschoicefeedback) &&
                $isselected && trim($ans->feedback)
            ) {
                $feedback[] = html_writer::tag('div',
                    $question->make_html_inline($question->format_text(
                        $ans->feedback, $ans->feedbackformat,
                        $qa, 'question', 'answerfeedback', $ansid)),
                    array('class' => 'specificfeedback'));
            } else {
                $feedback[] = '';
            }
            $class = 'r' . ($value % 2);
            if ($options->correctness && $isselected) {
                $feedbackimg[] = $renderer->feedback_image($renderer->is_right($ans));
                $class .= ' ' . $renderer->feedback_class($renderer->is_right($ans));
            } else {
                $feedbackimg[] = '';
            }
            $classes[] = $class;

            // If the question type is ROI based, add the question to
            // the list of ROIs to display
            if ($question->answertype == qtype_omeromultichoice::ROI_BASED_ANSWERS) {
                array_push($roi_id_list, $ans->answer);
            }
        }

        // Completes the list of ROIs to show with ROIs explicitly
        // selected by the teacher as ROI to display
        foreach (explode(",", $question->visible_rois) as $rtd) {
            if (!in_array($rtd, $roi_id_list))
                array_push($roi_id_list, $rtd);
        }

        /**
         * Render the question
         */
        $result = '';
        // question text
        $result .= html_writer::tag('div', $question->format_questiontext($qa),
            array('class' => 'qtext'));

        // viewer of the question image

        // set the ID of the OmeroImageViewer
        $omero_frame_id = "omero-image-viewer-" . uniqid('', true);

        // load the script for handling the OmeroImageViewer
        $omero_image_wrapper = '<script type="text/javascript" ' .
            'src="/moodle/question/type/omeromultichoice/omero_multichoice_helper.js" ' .
            '></script>';

        // build the iframe element for wrapping the OmeroImageViewer
        $omero_image_wrapper .= html_writer::tag('iframe', "",
            array(
                "src" => "/moodle/repository/omero/viewer/viewer.php" .
                    "?id=$omero_image" .
                    "&width=" . urlencode("100%") .
                    "&height=500px" .
                    "&frame=$omero_frame_id" .
                    "&showRoiTable=false" .
                    "&$omero_image_params" .
                    "&visibleRois=" . implode(",", $roi_id_list),
                "width" => "100%",
                "height" => "500px",
                "class" => "omero-image-viewer",
                //"style" => "border: none",
                "id" => $omero_frame_id //,
                //"onload" => 'M.omero_multichoice_helper.init();'
            )
        );
        $result .= $omero_image_wrapper;

        // TODO: use the question->visible_rois
        $script_args = "[" . implode(",", $roi_id_list) . "]"; // list of ROIs to display
        $result .= html_writer::script(
            "M.omero_multichoice_helper.init('omero_multichoice_helper', " .
            "'$omero_frame_id', $script_args)");

        $result .= html_writer::start_tag('div', array('class' => 'ablock'));
        $result .= html_writer::tag('div', $renderer->prompt(), array('class' => 'prompt'));

        $result .= html_writer::start_tag('div', array('class' => 'answer'));
        foreach ($radiobuttons as $key => $radio) {
            $result .= html_writer::tag('div', $radio . ' ' . $feedbackimg[$key] . $feedback[$key],
                    array('class' => $classes[$key])) . "\n";
        }
        $result .= html_writer::end_tag('div'); // Answer.

        $result .= html_writer::end_tag('div'); // Ablock.

        if ($qa->get_state() == question_state::$invalid) {
            $result .= html_writer::nonempty_tag('div',
                $question->get_validation_error($qa->get_last_qt_data()),
                array('class' => 'validationerror'));
        }

        return $result;
    }


    /**
     * @param int $num The number, starting at 0.
     * @param string $style The style to render the number in. One of the
     * options returned by {@link qtype_multichoice:;get_numbering_styles()}.
     * @return string the number $num in the requested style.
     */
    public static function number_answer($num, $style) {
        switch($style) {
            case 'abc':
                $number = chr(ord('a') + $num);
                break;
            case 'ABCD':
                $number = chr(ord('A') + $num);
                break;
            case '123':
                $number = $num + 1;
                break;
            case 'iii':
                $number = question_utils::int_to_roman($num + 1);
                break;
            case 'IIII':
                $number = strtoupper(question_utils::int_to_roman($num + 1));
                break;
            case 'none':
                return '';
            default:
                return 'ERR';
        }
        return "($number)";
    }
}


