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
 * omeromultichoice question renderer class.
 *
 * @package    qtype
 * @subpackage omerointeractive
 * @copyright  2015-2016 CRS4
 * @license    https://opensource.org/licenses/mit-license.php MIT license
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/multichoice/renderer.php');
require_once($CFG->dirroot . '/question/type/omerocommon/js/modules.php');
require_once($CFG->dirroot . '/question/type/omerocommon/viewer/viewer_config.php');

/**
 * Generates the output for omeromultichoice questions (single correct answer).
 *
 * @copyright  2015-2016 CRS4
 * @license    https://opensource.org/licenses/mit-license.php MIT license
 */
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

    public function is_right_marker($shape_grade_map, $response, $marker_index)
    {
        $shape = $response->shapes[$marker_index];
        return $response->shapes[$marker_index] !== "none" ?
            $shape_grade_map->{$shape->shape_id} <= 0 ? 0 : $shape_grade_map->{$shape->shape_id} : 0;
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
        $right = array();
        $result = "";
        $question = $qa->get_question();
        if ($question->shownumcorrect) {
            foreach ($question->get_order($qa) as $ans_number => $ans_id) {
                $answer = $question->answers[$ans_id];
                if (question_state::graded_state_for_fraction($answer->fraction) == question_state::$gradedright) {
                    foreach (explode(",", $answer->answer) as $k => $v) {
                        array_push($right,
                            '<i roi-shape-id="' . $v .
                            '" class="glyphicon glyphicon-map-marker roi-shape-info"></i> ' .
                            '[' . $v . "]");
                    }
                }
            }
            $result = get_string(count($right) == 1
                    ? 'single_correctansweris' : 'single_correctanswerare', 'qtype_omerointeractive') . implode(", ", $right);
        }
        return $result;
    }
}


/**
 * Generates the output for omeromultichoice questions (multiple correct answers).
 *
 * @copyright  2015-2016 CRS4
 * @license    https://opensource.org/licenses/mit-license.php MIT license
 */
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
        if ($ans->fraction > 0) {
            return 1;
        } else {
            return 0;
        }
    }

    public function is_right_marker($shape_grade_map, $response, $marker_index)
    {
        $shape = $response->shapes[$marker_index];
        return $response->shapes[$marker_index] !== "none" ?
            $shape_grade_map->{$shape->shape_id} <= 0 ? 0 : 1 : 0;
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
        $result = "";
        $question = $qa->get_question();
        if ($question->shownumcorrect) {
            $right = array();
            foreach ($question->get_order($qa) as $ans_number => $answer_id) {
                $answer = $question->answers[$answer_id];
                if ($answer->fraction > 0) {
                    $right_shape_set = array();
                    foreach (explode(",", $answer->answer) as $si => $shape_id)
                        $right_shape_set[] .= '<i roi-shape-id="' . $shape_id . '" class="glyphicon glyphicon-map-marker roi-shape-info"></i> ' .
                            '[' . $shape_id . "]";
                    if (!empty($right_shape_set))
                        $right[] .= (implode(', ', $right_shape_set));
                }
                $counter++;
            }

            if (!empty($right)) {
                $result = get_string($counter == 1
                        ? 'multi_correctansweris' : 'multi_correctanswerare', 'qtype_omerointeractive') .
                    implode(', ', $right);
            }
        }
        return $result;
    }

    public function manual_comment(question_attempt $qa, question_display_options $options)
    {
        return parent::manual_comment($qa, $options); // TODO: Change the autogenerated stub
    }
}

/**
 * Generates the output for omeromultichoice questions: base class with utility methods.
 *
 * @copyright  2015-2016 CRS4
 * @license    https://opensource.org/licenses/mit-license.php MIT license
 */
abstract class qtype_omerointeractive_base_renderer extends qtype_multichoice_renderer_base
{

    const IMAGE_VIEWER_CONTAINER = "image-viewer-container";
    const IMAGE_ADD_MARKER_CTRL = "enable_add_makers_id";
    const IMAGE_EDIT_MARKER_CTRL = "enable_edit_markers_ctrl_id";
    const IMAGE_DEL_MARKER_CTRL = "remove_marker_ctrl_id";
    const IMAGE_CLEAR_MARKER_CTRL = "clear_marker_ctrl_id";
    const MARKER_REMOVERS_CONTAINER = "marker_removers_container";
    const FOCUS_AREAS_CONTAINER = "focus_areas_container";


    public static function configure_requirements(question_attempt $qa)
    {
        global $CFG, $PAGE;
        init_js_modules("omerocommon");
        init_js_modules("omerointeractive");
        init_js_imageviewer(get_config('omero', 'omero_restendpoint'));
        $PAGE->requires->css("/question/type/omerocommon/css/message-dialog.css");
        $PAGE->requires->css("/question/type/omerocommon/css/question-player-base.css");
        $PAGE->requires->string_for_js('validate_question', 'qtype_omerocommon');
        $PAGE->requires->string_for_js('validate_editor_not_valid', 'qtype_omerocommon');
        $PAGE->requires->string_for_js('validate_editor_check_question', 'qtype_omerocommon');
        $PAGE->requires->string_for_js('validate_editor_not_existing_rois', 'qtype_omerocommon');
        $PAGE->requires->string_for_js('validate_player_not_existing_rois', 'qtype_omerocommon');
    }


    private static function to_unique_identifier(question_attempt $qa, $identifier)
    {
        return $identifier . "-" . $qa->get_database_id();
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
        $omero_frame_id = self::to_unique_identifier($qa, "omero-image-viewer");
        // ID of the question answer container
        $question_answer_container = self::to_unique_identifier($qa, "omero-interactive-question-container");

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
        $answer_shape_map = array();
        $shape_grade_map = new stdClass();
        $available_shapes = array();
        $shape_groups = array();
        foreach ($question->get_order($qa) as $ans_idx => $ansid) {
            $ans = $question->answers[$ansid];
            $value = $ans->answer;
            $shape_group = array_map("intval", explode(",", $ans->answer));
            $shape_group_cardinality = count($shape_group);
            // update the max number of markers allowed
            if ($ans->fraction > 0 && !empty($value)) {
                $no_max_markers += $shape_group_cardinality;
            }
            // compute the shape grade
            $shape_group_fraction = 0;
            if ($shape_group_cardinality > 0) {
                $shape_group_fraction = $multi_correct_answer
                    ? ($ans->fraction / $shape_group_cardinality)
                    : $ans->fraction;
            }
            foreach ($shape_group as $shape_id) {
                $shape_grade_map->{$shape_id} = $shape_group_fraction;
                array_push($available_shapes, $shape_id);
                $answer_shape_map[$shape_id] = $ans;
            }
            array_push($shape_groups, array(
                "shapes" => $shape_group,
                "shape_grade" => $shape_group_fraction
            ));
        }

        // Fix the max number of markers
        if (!$multi_correct_answer) $no_max_markers = 1;

        $answer_order = "";
        $answer_options = array();

        $feedbackimg = array();
        $classes = array();

        // Show correct/wrong markers
        if ($options->correctness) {
            foreach ($response->markers as $index => $marker) {

                $shape = $response->shapes[$index];
                $isselected = true;
                $hidden = '';
                $answer_options_attributes = array();
                $marker_correction = $hidden;
                $marker_correction .= html_writer::empty_tag('li', $answer_options_attributes);
                $marker_correction_text =
                    get_string("marker", "qtype_omerointeractive") . " " .
                    html_writer::tag("i", " ",
                        array(
                            "class" => "glyphicon glyphicon-map-marker roi-shape-info",
                            "roi-shape-id" => $marker->shape_id
                        )
                    ) . " ";

                if ($shape !== "none") {
                    $marker_correction_text .=
                        get_string("your_marker_inside", "qtype_omerointeractive") . " " .
                        html_writer::tag("i", " ",
                            array(
                                "class" => "glyphicon glyphicon-map-marker roi-shape-info",
                                "roi-shape-id" => $shape->shape_id
                            )
                        ) . " [" . $shape->shape_id . "] ";
                } else $marker_correction_text .= get_string("your_marker_outside", "qtype_omerointeractive");

                $marker_correction_text .=
                    '<span class="pull-right">' .
                    $renderer->feedback_image($renderer->is_right_marker($shape_grade_map, $response, $index)) .
                    html_writer::tag("i", " ",
                        array(
                            "class" => "glyphicon glyphicon-eye-open roi-shape-visibility",
                            "roi-shape-id" => $marker->shape_id,
                            "style" => "margin-right: 5px"
                        )
                    ) .
                    '</span>';

                if ($shape !== "none" && !empty(strip_tags($answer_shape_map[$shape->shape_id]->feedback))) {
                    $marker_correction_text .=
                        html_writer::tag("div",
                            html_writer::tag("i", " ",
                                array(
                                    "class" => "pull-left glyphicon glyphicon-record",
                                    "style" => "margin-right: 5px"
                                )
                            ) .
                            format_text($answer_shape_map[$shape->shape_id]->feedback),
                            array("class" => "outcome")
                        );
                }

                $marker_correction .= html_writer::tag('label', $marker_correction_text);
                $answer_options[] = $marker_correction;

                $class = 'r' . ($value % 2);
                $answer_options_attributes['checked'] = 'checked';
                $feedback_class = $renderer->feedback_image($renderer->is_right_marker($shape_grade_map, $response, $index));
                $feedbackimg[] = $renderer->feedback_image($renderer->is_right_marker($shape_grade_map, $response, $index));
                $class .= ' ' . $renderer->feedback_class($renderer->is_right_marker($shape_grade_map, $response, $index));
                $classes[] = $class;
            }
        }

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
        $result .= '<!-- TOOLBAR -->
                <div class="btn-group interactive-player-toolbar pull-right" style="margin-left: 5px;" data-toggle="buttons" aria-pressed="false" autocomplete="true">
                    <a href="#" id="' . self::to_unique_identifier($qa, self::IMAGE_CLEAR_MARKER_CTRL) . '" class="btn btn-default disabled" aria-label="Left Align">
                        <i class="glyphicon glyphicon-remove"></i> ' . get_string('clear_markers', 'qtype_omerointeractive') .
            '</a>
                </div>
                <div class="btn-group interactive-player-toolbar pull-right" data-toggle="buttons" aria-pressed="false" autocomplete="off">
                    <a href="#" id="' . self::to_unique_identifier($qa, self::IMAGE_ADD_MARKER_CTRL) . '" class="btn btn-default disabled"  aria-label="Left Align">
                        <i class="glyphicon glyphicon-plus"></i> ' . get_string('add_marker', 'qtype_omerointeractive') .
            '</a>
                    <a href="#" id="' . self::to_unique_identifier($qa, self::IMAGE_EDIT_MARKER_CTRL) . '" class="btn btn-default disabled" aria-label="Left Align">
                        <i class="glyphicon glyphicon-edit"></i> ' . get_string('edit_marker', 'qtype_omerointeractive') .
            '</a>
                </div>';

        $result .= '<div id="' . self::to_unique_identifier($qa, "graphics_container") . '" class="image-viewer-container" style="position: relative;" >
            <div id="' . self::to_unique_identifier($qa, self::IMAGE_VIEWER_CONTAINER) . '" style="position: absolute; width: 100%; height: 500px; margin: auto; z-index: 0;"></div>
            <canvas id="' . self::to_unique_identifier($qa, 'annotations_canvas') . '" style="position: absolute; width: 100%; height: 500px; margin: auto; z-index: 1;"></canvas>
            <div id="' . self::to_unique_identifier($qa, self::IMAGE_VIEWER_CONTAINER) . '-loading-dialog" class="image-viewer-loading-dialog"></div>
        </div>';


        $image_properties = null;
        $image_properties = json_decode($question->omeroimageproperties);
        $result .= '<div class="image_position_button">' .
            '<span class="sm">' .
            (($question->omeroimageproperties) ?
                '<b>(x,y):</b> ' . $image_properties->center->x . ", " . $image_properties->center->y .
                '<i class="restore-image-center-btn glyphicon glyphicon-screenshot" style="margin-left: 10px;"></i>' :
                ""
            ) .
            '</span></div>';

        if (!empty($question->focusablerois)) {
            $result .= '<div id="' . self::to_unique_identifier($qa, self::FOCUS_AREAS_CONTAINER) . '" ' .
                ' class="focus_areas_container">' .
                '<span class="focus-areas-text">* ' . get_string("focusareas", "qtype_omerointeractive") . '</span> ' . '</div>';
        }

        $result .= '<div id="' . self::to_unique_identifier($qa, self::MARKER_REMOVERS_CONTAINER) . '" ' .
            ' class="remove_marker_button_group">' .
            '<span class="yourmarkers-text">* ' . get_string("yourmarkers", "qtype_omerointeractive") . '</span> ' . '</div>';
        $result .= '</div>';

        $answer_input_name = $qa->get_qt_field_name('answer');
        $answer_options_attributes = array(
            'type' => $renderer->get_input_type(),
            'name' => $answer_input_name,
        );

        if ($options->readonly) {
            $answer_options_attributes['disabled'] = 'disabled';
        }

        if ($options->correctness && count($response->markers) > 0) {
            $result .= html_writer::start_tag('div', array('class' => 'question-summary hidden'));
            $result .= html_writer::tag('div',
                (count($response->markers) === 1) ?
                    get_string("notice_your_answer", "qtype_omerocommon") :
                    get_string("notice_your_answers", "qtype_omerocommon"),
                array("class" => "answer-summary-fixed-text"));

            $result .= html_writer::start_tag('ul', array('class' => 'answer'));
            foreach ($answer_options as $key => $answer_option) {
                $result .= html_writer::tag('div', $answer_option, array('class' => $classes[$key])) . "\n";
            }
            $result .= html_writer::end_tag('ul'); // Answer.
            $result .= html_writer::end_tag('div'); // Answer.
        }

        $result .= html_writer::tag('div', '', array(
            'id' => $question_answer_container . '-invalidator-panel',
            'class' => "invalidator-panel"));

        $result .= html_writer::end_tag('div'); // Ablock.

        // support for dialog message
        $result .= html_writer::tag('div', '
         <div class="modal fade" id="modal-frame-' . $omero_frame_id . '" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title text-warning" id="modal-frame-label-' . $omero_frame_id . '">
            <i class="glyphicon glyphicon-warning-sign"></i> ' . get_string('validate_warning', 'qtype_omerocommon') .
            '</h4>
      </div>
      <div class="modal-body text-left">
        <span id="modal-frame-text-' . $omero_frame_id . '"></span>
      </div>
      <div class="modal-footer text-center">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>');


        $player_config = array(
            "image_id" => $omero_image,
            "image_properties" => json_decode($question->omeroimageproperties),
            "image_frame_id" => $omero_frame_id,
            "image_annotations_canvas_id" => self::to_unique_identifier($qa, "annotations_canvas"),
            "image_server" => $OMERO_SERVER,
            "image_viewer_container" => self::to_unique_identifier($qa, self::IMAGE_VIEWER_CONTAINER),
            "image_navigation_locked" => (bool)$question->omeroimagelocked,
            "viewer_model_server" => $CFG->omero_image_server,
            "qname" => $question->name,
            "question_answer_container" => $question_answer_container,
            "enable_add_makers_ctrl_id" => self::to_unique_identifier($qa, self::IMAGE_ADD_MARKER_CTRL),
            "enable_edit_markers_ctrl_id" => self::to_unique_identifier($qa, self::IMAGE_EDIT_MARKER_CTRL),
            "remove_marker_ctrl_id" => self::to_unique_identifier($qa, self::IMAGE_DEL_MARKER_CTRL),
            "clear_marker_ctrl_id" => self::to_unique_identifier($qa, self::IMAGE_CLEAR_MARKER_CTRL),
            "marker_removers_container" => self::to_unique_identifier($qa, self::MARKER_REMOVERS_CONTAINER),
            "focus_areas_container" => self::to_unique_identifier($qa, self::FOCUS_AREAS_CONTAINER),
            "answer_input_name" => $answer_input_name,
            "available_shapes" => ($available_shapes),
            "shape_groups" => $shape_groups,
            "visible_rois" => empty($question->visiblerois) ? [] : explode(",", $question->visiblerois),
            "focusable_rois" => empty($question->focusablerois) ? [] : explode(",", $question->focusablerois),
            "correction_mode" => (bool)$options->correctness,
            "response" => $response,
            "answers" => $response,
            "answer_fraction" => $shape_grade_map,
            "max_markers" => $no_max_markers
        );

        $player_config_element_id = self::to_unique_identifier($qa, "viewer-config");
        $result .= html_writer::empty_tag(
            "input", array("id"=> $player_config_element_id,
            "type" => "hidden",
                "value" => json_encode($player_config))
        );

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

        global $PAGE;
        $PAGE->requires->string_for_js('marker', 'qtype_omerointeractive');

        $PAGE->requires->js_call_amd(
            "qtype_omerointeractive/question-player-interactive",
            "start", array($player_config_element_id)
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


