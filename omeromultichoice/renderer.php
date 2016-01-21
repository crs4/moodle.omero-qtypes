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

require_once($CFG->dirroot . '/question/type/omerocommon/js/viewer_config.php');
require_once($CFG->dirroot . '/question/type/multichoice/renderer.php');
require_once($CFG->dirroot . '/question/type/omerocommon/js/modules.php');


class qtype_omeromultichoice_single_renderer extends qtype_multichoice_single_renderer
{

    public function head_code(question_attempt $qa)
    {
        parent::head_code($qa);
        qtype_omeromultichoice_base_renderer::configure_requirements($qa);
    }

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
    public function head_code(question_attempt $qa)
    {
        parent::head_code($qa);
        qtype_omeromultichoice_base_renderer::configure_requirements($qa);
    }


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

    const IMAGE_VIEWER_CONTAINER = "image-viewer-container";
    const IMAGE_ADD_MARKER_CTRL = "enable_add_makers_id";
    const IMAGE_EDIT_MARKER_CTRL = "enable_edit_markers_ctrl_id";
    const IMAGE_DEL_MARKER_CTRL = "remove_marker_ctrl_id";
    const IMAGE_CLEAR_MARKER_CTRL = "clear_marker_ctrl_id";
    const MARKER_REMOVERS_CONTAINER = "marker_removers_container";
    const FOCUS_AREAS_CONTAINER = "focus_areas_container";

    private static function to_unique_identifier(question_attempt $qa, $identifier)
    {
        return $identifier . "-" . $qa->get_database_id();
    }

    public static function impl_formulation_and_controls(qtype_multichoice_renderer_base $renderer,
                                                         question_attempt $qa,
                                                         question_display_options $options)
    {
        global $CFG, $PAGE;

        // get the current question
        $question = $qa->get_question();

        // get the response
        $response = $question->get_response($qa);

        // answer prefix
        $answer_input_name = $qa->get_qt_field_name('answer');

        // set the ID of the OmeroImageViewer
        $omero_frame_id = self::to_unique_identifier($qa, "omero-image-viewer");

        $question_answer_container = self::to_unique_identifier($qa, "omero-multichoice-question-container");

        // the OMERO image URL
        $omero_image_url = $question->omeroimageurl;

        // extract the omero server
        $OMERO_SERVER = get_config('omero', 'omero_restendpoint');

        // parse the URL to get the image ID and its related params
        $matches = array();
        $pattern = '/\/([0123456789]+)(\?.*)?/';
        if (preg_match($pattern, $omero_image_url, $matches)) {
            $omero_image = $matches[1];
            $omero_image_params = count($matches) === 3 ? $matches[2] : "";
        }

        $no_max_markers = 0;
        $available_answers = array();
        foreach ($question->get_order($qa) as $ans_idx => $ansid) {
            $ans = $question->answers[$ansid];
            $value = $ans->answer;
            array_push($available_answers, $value);
            if ($ans->fraction > 0 && !empty($value)) {
                $shape_group = explode(",", $ans->answer);
                $no_max_markers += count($shape_group);
            }
        }

        $multi_correct_answer = ($question instanceof qtype_omerointeractive_multi_question);
        if (!$multi_correct_answer) $no_max_markers = 1;

        $inputname = $qa->get_qt_field_name('answer');
        $inputattributes = array(
            'type' => $renderer->get_input_type(),
            'name' => $inputname,
        );

        if ($options->readonly) {
            $inputattributes['disabled'] = 'disabled';
        }

        $radiobuttons = array();
        $feedbackimg = array();
        $feedback = array();
        $classes = array();
        foreach ($question->get_order($qa) as $value => $ansid) {
            $ans = $question->answers[$ansid];
            $inputattributes['name'] = $renderer->get_input_name($qa, $value);
            $inputattributes['value'] = $renderer->get_input_value($value);
            $inputattributes['id'] = $renderer->get_input_id($qa, $value . "XXX");
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
            $radiobuttons[] = $hidden . html_writer::empty_tag('input', $inputattributes) .
                html_writer::tag('label',
                    $question->format_text(
                        $renderer->number_in_style($value, $question->answernumbering) .
                        preg_replace('/<p[^>]*>(.*)<\/p[^>]*>/i', '$1', $ans->answer), $ans->answerformat,
                        $qa, 'question', 'answer', $ansid),
                    array('for' => $inputattributes['id']));

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
        }


        /**
         * Render the question
         */
        $result = '';

        // main question_answer_container
        $result .= html_writer::start_tag('div', array('id' => $question_answer_container, 'class' => 'ablock'));

        // question text
        $result .= html_writer::tag('div', $question->format_questiontext($qa), array('class' => 'qtext'));

        // viewer of the question image
        $result .= '<div class="image-viewer-with-controls-container">';


        $result .= '<div id="' . self::to_unique_identifier($qa, "graphics_container") . '" class="image-viewer-container" style="position: relative;" >
            <div id="' . self::to_unique_identifier($qa, self::IMAGE_VIEWER_CONTAINER) . '" style="position: absolute; width: 100%; height: 500px; margin: auto;"></div>
            <canvas id="' . self::to_unique_identifier($qa, 'annotations_canvas') . '" style="position: absolute; width: 100%; height: 500px; margin: auto;"></canvas>
        </div>';


        $image_properties = null;
        if ($question->omeroimageproperties) {
            $image_properties = json_decode($question->omeroimageproperties);
            $result .= '<div class="image_position_button">' .
                '<span class="sm">' .
                '<b>(x,y):</b> ' . $image_properties->center->x . ", " . $image_properties->center->y .
                '<i class="restore-image-center-btn glyphicon glyphicon-screenshot" style="margin-left: 10px;">' .
                '</i></span></div>';
        }

        $result .= '</div>';

        $result .= '<div id="' . self::to_unique_identifier($qa, self::FOCUS_AREAS_CONTAINER) . '" ' .
            ' class="focus_areas_container">' .
            '<span class="focus-areas-text">* ' . get_string("focusareas", "qtype_omerointeractive") . '</span> ' . '</div>';



        $result .= html_writer::start_tag('div', array('class' => 'multichoice-options-container'));
        $result .= html_writer::tag('div', $renderer->prompt(), array('class' => 'prompt'));

        $result .= html_writer::start_tag('div', array('class' => 'answer'));
        foreach ($radiobuttons as $key => $radio) {
            $result .= html_writer::tag('div', $radio . ' ' . $feedbackimg[$key] . $feedback[$key],
                    array('class' => $classes[$key]));
        }
        $result .= html_writer::end_tag('div'); // Answer.

        $result .= html_writer::end_tag('div'); // Ablock.

        if ($qa->get_state() == question_state::$invalid) {
            $result .= html_writer::nonempty_tag('div',
                $question->get_validation_error($qa->get_last_qt_data()),
                array('class' => 'validationerror'));
        }

        $PAGE->requires->js_call_amd(
            "qtype_omeromultichoice/question-player-multichoice",
            "start", array(
                array(
                    "image_id" => $omero_image,
                    "image_properties" => json_decode($question->omeroimageproperties),
                    "image_frame_id" => $omero_frame_id,
                    "image_annotations_canvas_id" => self::to_unique_identifier($qa, "annotations_canvas"),
                    "image_server" => $OMERO_SERVER,
                    "image_viewer_container" =>  self::to_unique_identifier($qa, self::IMAGE_VIEWER_CONTAINER),
                    "image_navigation_locked" => (bool)$question->omeroimagelocked,
                    "question_answer_container" => $question_answer_container,
                    "focus_areas_container" => self::to_unique_identifier($qa, self::FOCUS_AREAS_CONTAINER),
                    "visible_rois" => explode(",", $question->visiblerois),
                    "focusable_rois" => explode(",", $question->focusablerois),
                    "answer_input_name" => $answer_input_name
                )
            )
        );

        return $result;
    }

    public static function configure_requirements(question_attempt $qa)
    {
        global $CFG, $PAGE;
        init_js_modules("omerocommon");
        init_js_modules("omeromultichoice");
        init_js_imageviewer(get_config('omero', 'omero_restendpoint'));
        $PAGE->requires->css("/question/type/omerocommon/css/question-player-base.css");
    }


    /**
     * @param int $num The number, starting at 0.
     * @param string $style The style to render the number in. One of the
     * options returned by {@link qtype_multichoice:;get_numbering_styles()}.
     * @return string the number $num in the requested style.
     */
    public static function number_answer($num, $style)
    {
        switch ($style) {
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


