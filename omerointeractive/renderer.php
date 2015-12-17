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


class qtype_omerointeractive_single_renderer extends qtype_multichoice_single_renderer
{
    public function head_code(question_attempt $qa)
    {
        parent::head_code($qa);
        qtype_omerointeractive_base_renderer::configure_requirements($qa);
    }


    public function formulation_and_controls(question_attempt $qa,
                                             question_display_options $options)
    {
        return qtype_omerointeractive_base_renderer::impl_formulation_and_controls($this, $qa, $options);
    }

    public function get_input_type()
    {
        return 'radio';
    }

    protected function is_right(question_answer $ans)
    {
        return parent::is_right($ans);
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
                return get_string('correctansweris', 'qtype_omerointeractive') .
                '<i roi-shape-id="' . $answer->answer . '" class="glyphicon glyphicon-map-marker roi-shape-info"></i> ' .
                '[' . $answer->answer . "]";
            }
        }
        return '';
    }
}


class qtype_omerointeractive_multi_renderer extends qtype_multichoice_multi_renderer
{
    public function head_code(question_attempt $qa)
    {

        parent::head_code($qa);
        qtype_omerointeractive_base_renderer::configure_requirements($qa);
    }


    public function formulation_and_controls(question_attempt $qa,
                                             question_display_options $options)
    {
        return qtype_omerointeractive_base_renderer::impl_formulation_and_controls($this, $qa, $options);
    }

    protected function is_right(question_answer $ans)
    {
        return $ans->fraction > 0;
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
                $right_shape_set = array();
                foreach (explode(",", $answer->answer) as $si => $shape_id)
                    $right_shape_set[] .= '<i roi-shape-id="' . $shape_id . '" class="glyphicon glyphicon-map-marker roi-shape-info"></i> ' .
                        '[' . $shape_id . "]";
                if (!empty($right_shape_set))
                    $right[] .= ("{" . implode(' - ', $right_shape_set) . "}");
            }
            $counter++;
        }

        if (!empty($right)) {
            return get_string('correctansweris', 'qtype_omerointeractive') .
            implode(' + ', $right);
        }
        return '';
    }

    public function manual_comment(question_attempt $qa, question_display_options $options)
    {
        return parent::manual_comment($qa, $options); // TODO: Change the autogenerated stub
    }
}


abstract class qtype_omerointeractive_base_renderer extends qtype_multichoice_renderer_base
{

    const IMAGE_VIEWER_CONTAINER = "image-viewer-container";
    const IMAGE_ADD_MARKER_CTRL = "enable_add_makers_id";
    const IMAGE_EDIT_MARKER_CTRL = "enable_edit_markers_ctrl_id";
    const IMAGE_DEL_MARKER_CTRL = "remove_marker_ctrl_id";
    const IMAGE_CLEAR_MARKER_CTRL = "clear_marker_ctrl_id";
    const MARKER_REMOVERS_CONTAINER = "marker_removers_container";


    public static function configure_requirements(question_attempt $qa)
    {
        global $CFG, $PAGE;
        init_js_modules("omerocommon");
        init_js_modules("omerointeractive");
        init_js_imageviewer(get_config('omero', 'omero_restendpoint'));
        $PAGE->requires->css("/question/type/omerocommon/css/question-player-base.css");
    }


    public static function impl_formulation_and_controls(qtype_multichoice_renderer_base $renderer,
                                                         question_attempt $qa,
                                                         question_display_options $options)
    {
        global $CFG, $PAGE, $OUTPUT;

        // get the current question
        $question = $qa->get_question();

        // get the response
        $response = $question->get_response($qa);

        // answer prefix
        $answer_input_name = $qa->get_qt_field_name('answer');

        // set the ID of the OmeroImageViewer
        $omero_frame_id = "omero-image-viewer-" . str_replace(".", "-", uniqid('', true));

        $question_answer_container = "omero-interactive-question-container-" . str_replace(".", "-", uniqid('', true));

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

        // check
        $multi_correct_answer = ($question instanceof qtype_omerointeractive_multi_question);

        $no_max_markers = 0;
        $shape_fraaction = new stdClass();
        $available_shapes = array();
        $shape_groups = array();
        foreach ($question->get_order($qa) as $ans_idx => $ansid) {
            $ans = $question->answers[$ansid];
            $value = $ans->answer;
            if ($ans->fraction > 0 && !empty($value)) {
                $shape_group = array_map("intval", explode(",", $ans->answer));
                array_push($shape_groups, $shape_group);
                $shape_group_cardinality = count($shape_group);
                $no_max_markers += $shape_group_cardinality;
                $shape_group_fraction = 0;
                if ($shape_group_cardinality > 0) {
                    $shape_group_fraction = $multi_correct_answer
                        ? ($ans->fraction / $shape_group_cardinality)
                        : $ans->fraction;
                }
                foreach ($shape_group as $shape_id) {
                    $shape_fraaction->{$shape_id} = $shape_group_fraction;
                    array_push($available_shapes, $shape_id);
                }
            }
        }

        // Fix the max number of markers
        if (!$multi_correct_answer) $no_max_markers = 1;

        $answer_order = "";
        $feedback_class = "";
        $answer_options = array();
        $feedbackimg = array();
        $feedback = array();
        $classes = array();
        foreach ($question->get_order($qa) as $value => $ansid) {
            $ans = $question->answers[$ansid];
            $answer_order .= $ans->answer;
            $answer_options_attributes['name'] = $renderer->get_input_name($qa, $value);
            $answer_options_attributes['value'] = $renderer->get_input_value($value);
            $answer_options_attributes['id'] = $renderer->get_input_id($qa, $value);


            $hidden = '';
            if (!$options->readonly && $renderer->get_input_type() == 'checkbox') {
                $hidden .= html_writer::empty_tag('input', array(
                    'type' => 'hidden',
                    'name' => $answer_options_attributes['name'],
                    'value' => 0,
                ));
            }

            foreach (explode(",", $ans->answer) as $shape_id) {
                $answer_options[] = $hidden .
                    html_writer::empty_tag('li', $answer_options_attributes) .
                    html_writer::tag('label',
                        html_writer::tag("i", " ",
                            array(
                                "class" => "glyphicon glyphicon-map-marker roi-shape-info",
                                "roi-shape-id" => $shape_id
                            )
                        )
                        . " [" . $shape_id . "] " . $question->make_html_inline($question->format_text(
                            $ans->feedback, $ans->answerformat,
                            $qa, 'question', 'answer', $ansid)),
                        array('for' => $answer_options_attributes['id'])
                    );


                $class = 'r' . ($value % 2);

                // Switch to determine whether to show correct answer or not
                //foreach (explode(",", $ans->answer) as $shape_id) {

                $isselected = $question->is_choice_selected($response, $shape_id);
                if ($isselected) {
                    $answer_options_attributes['checked'] = 'checked';
                } else {
                    unset($answer_options_attributes['checked']);
                }
                if ($options->correctness && $isselected) {
                    $feedback_class = $renderer->feedback_image($renderer->is_right($ans));
                    $feedbackimg[] = $renderer->feedback_image($renderer->is_right($ans));
                    $class .= ' ' . $renderer->feedback_class($renderer->is_right($ans));
                } else {
                    $feedbackimg[] = '';
                }
                $classes[] = $class;
            }
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
        $result .= '<div class="panel image-viewer-with-controls-container">';

        $result .= '<!-- TOOLBAR -->
                <div class="btn-group interactive-player-toolbar pull-right" data-toggle="buttons">
                    <a href="#" id="' . self::IMAGE_ADD_MARKER_CTRL . '" class="btn btn-success disabled"  aria-label="Left Align">
                        <i class="glyphicon glyphicon-plus"></i> Add
                    </a>
                    <a href="#" id="' . self::IMAGE_EDIT_MARKER_CTRL . '" class="btn btn-warning disabled" aria-label="Left Align">
                        <i class="glyphicon glyphicon-edit"></i> Edit
                    </a>
                    <a href="#" id="' . self::IMAGE_CLEAR_MARKER_CTRL . '" class="btn btn-danger disabled" aria-label="Left Align">
                        <i class="glyphicon glyphicon-remove"></i> Clear
                    </a>
                </div>';

        $result .= '<div id="graphics_container" class="image-viewer-container" style="position: relative;" >
            <div id="' . self::IMAGE_VIEWER_CONTAINER . '" style="position: absolute; width: 100%; height: 500px; margin: auto;"></div>
            <canvas id="annotations_canvas" style="position: absolute; width: 100%; height: 500px; margin: auto;"></canvas>
        </div>';


        $image_properties = null;
        if ($question->omeroimageproperties) {
            $image_properties = json_decode($question->omeroimageproperties);
            $result .= '<div class="panel image_position_button">' .
                '<span class="pull-right sm">' .
                '(x,y): ' . $image_properties->center->x . ", " . $image_properties->center->y .
                '<i class="restore-image-center-btn pull-right glyphicon glyphicon-screenshot" style="margin-left: 10px;">' .
                '</i></span></div>';
        }

        $result .= '<div id="' . self::MARKER_REMOVERS_CONTAINER . '" ' .
            ' class="panel remove_marker_button_group">' .
            '<span class="yourmarkers-text">' . get_string("yourmarkers", "qtype_omerointeractive") . '</span> ' . '</div>';

        $result .= '</div>';


        $answer_input_name = $qa->get_qt_field_name('answer');
        $answer_options_attributes = array(
            'type' => $renderer->get_input_type(),
            'name' => $answer_input_name,
        );

        if ($options->readonly) {
            $answer_options_attributes['disabled'] = 'disabled';
        }

        if ($options->correctness) {
            $result .= html_writer::start_tag('div', array('class' => 'question-summary hidden'));
            $result .= html_writer::tag('div', get_string(
                (count($answer_options) == 1 ? "answerassociatedroi" : "answerassociatedrois"),
                "qtype_omerointeractive"), array("class" => "answer-summary-fixed-text"));

            $result .= html_writer::start_tag('ul', array('class' => 'answer'));
            foreach ($answer_options as $key => $answer_option) {
                $result .= html_writer::tag('div', $answer_option . ' ' . $feedbackimg[$key],
                        array('class' => $classes[$key])) . "\n";
            }
            $result .= html_writer::end_tag('ul'); // Answer.
            $result .= html_writer::end_tag('div'); // Answer.
        }

        $result .= html_writer::end_tag('div'); // Ablock.

        if ($qa->get_state() == question_state::$invalid) {
            $result .= html_writer::nonempty_tag('div',
                $question->get_validation_error($qa->get_last_qt_data()),
                array('class' => 'validationerror'));
        }

        $result .= html_writer::empty_tag('input', array(
            'type' => 'hidden',
            'name' => "answer_input_name",
            'value' => $answer_input_name,
        ));


        $PAGE->requires->js_call_amd(
            "qtype_omerointeractive/question-player-interactive",
            "start", array(
                array(
                    "image_id" => $omero_image,
                    "image_properties" => json_decode($question->omeroimageproperties),
                    "image_frame_id" => $omero_frame_id,
                    "image_annotations_canvas_id" => "annotations_canvas",
                    "image_server" => $OMERO_SERVER,
                    "image_viewer_container" => self::IMAGE_VIEWER_CONTAINER,
                    "image_navigation_locked" => (bool)$question->omeroimagelocked,
                    "question_answer_container" => $question_answer_container,
                    "enable_add_makers_ctrl_id" => self::IMAGE_ADD_MARKER_CTRL,
                    "enable_edit_markers_ctrl_id" => self::IMAGE_EDIT_MARKER_CTRL,
                    "remove_marker_ctrl_id" => self::IMAGE_DEL_MARKER_CTRL,
                    "clear_marker_ctrl_id" => self::IMAGE_CLEAR_MARKER_CTRL,
                    "marker_removers_container" => self::MARKER_REMOVERS_CONTAINER,
                    "answer_input_name" => $answer_input_name,
                    "available_shapes" => ($available_shapes),
                    "shape_groups" => $shape_groups,
                    "visible_rois" => explode(",", $question->visiblerois),
                    "correction_mode" => (bool)$options->correctness,
                    "response" => $response,
                    "answers" => $response,
                    "answer_fraction" => $shape_fraaction,
                    "max_markers" => $no_max_markers
                )
            )
        );

        return $result;
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

