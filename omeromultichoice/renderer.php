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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/multichoice/renderer.php');
require_once($CFG->dirroot . '/question/type/omerocommon/js/modules.php');
require_once($CFG->dirroot . '/question/type/omerocommon/rendererhelper.php');
require_once($CFG->dirroot . '/question/type/omerocommon/viewer/viewer_config.php');

/**
 * Render class for OmeroMultichoice question types with a single correct answer.
 *
 * @package    qtype
 * @subpackage omeromultichoice
 * @copyright  2015-2016 CRS4
 * @license    https://opensource.org/licenses/mit-license.php MIT license
 */
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
        $result = "";
        $question = $qa->get_question();
        if ($question->shownumcorrect) {
            foreach ($question->get_order($qa) as $ans_number => $ans_id) {
                $answer = $question->answers[$ans_id];
                if (question_state::graded_state_for_fraction($answer->fraction) ==
                    question_state::$gradedright
                ) {
                    $result = get_string('correctansweris', 'qtype_multichoice',
                        qtype_omeromultichoice_base_renderer::number_answer($ans_number, $question->answernumbering));
                }
            }
        }
        return $result;
    }
}

/**
 * Render class for OmeroMultichoice question types with multiple correct answers.
 *
 * @package    qtype
 * @subpackage omeromultichoice
 * @copyright  2015-2016 CRS4
 * @license    https://opensource.org/licenses/mit-license.php MIT license
 */
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
        $result = "";
        $question = $qa->get_question();
        if ($question->shownumcorrect) {
            $right = array();
            foreach ($question->get_order($qa) as $ans_number => $answer_id) {
                $answer = $question->answers[$answer_id];
                if ($answer->fraction > 0) {
                    $right[] = qtype_omeromultichoice_base_renderer::number_answer($ans_number, $question->answernumbering);
                }
                $counter++;
            }

            if (!empty($right)) {
                $result = get_string('correctansweris', 'qtype_multichoice', implode(' + ', $right));
            }
        }
        return $result;
    }

}

/**
 * Render class for OmeroMultichoice question types: base class with utility methods.
 *
 * @package    qtype
 * @subpackage omeromultichoice
 * @copyright  2015-2016 CRS4
 * @license    https://opensource.org/licenses/mit-license.php MIT license
 */
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

        // set the ID of the ModalImagePanel
        $modal_image_panel_id = $omero_frame_id . "-" . qtype_omerocommon_renderer_helper::MODAL_VIEWER_ELEMENT_ID;

        // set the name of feedback image class
        $feedback_image_class = $omero_frame_id . "-feedbackimage";

        // set the ID of the answer container
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

        $multi_correct_answer = ($question instanceof qtype_omeromultichoice_multi_renderer);
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
        $num_of_response = 0;
        foreach ($question->get_order($qa) as $value => $ansid) {
            $ans = $question->answers[$ansid];
            $inputattributes['name'] = $renderer->get_input_name($qa, $value);
            $inputattributes['value'] = $renderer->get_input_value($value);
            $inputattributes['id'] = $renderer->get_input_id($qa, $value . "XXX");
            $isselected = $question->is_choice_selected($response, $value);
            if ($isselected) {
                $inputattributes['checked'] = 'checked';
                $num_of_response++;
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

            $feedbackimages = array();
            foreach ($ans->feedbackimages as $image_id => $image) {
                array_push($feedbackimages, $image_id);
            }

            $feedbackimages_html = '<div style="display: block; float: right;">[ '
                . get_string("see", "qtype_omerocommon") . " ";

            $current_language = current_language();
            foreach ($ans->feedbackimages as $image) {
                $feedbackimages_html .= '<span class="' . $feedback_image_class . '" imageid="' . $image->id . '"'
                    . ' currentlanguage="' . $current_language . '"'
                    . ' imagename="' . $image->name . '"'
                    . ' imagedescription="' . htmlspecialchars($image->description_locale_map->$current_language) . '"'
                    . ' imagelock="' . ($image->lock ? "true" : "false") . '"'
                    . ' imageproperties="' . htmlspecialchars(json_encode($image->properties)) . '"'
                    . ' visiblerois="' . implode(",", $image->visiblerois) . '"'
                    . ' focusablerois="' . implode(",", $image->focusablerois) . '"' . '>' .
                    '<i class="glyphicon glyphicon-book" style="margin-left: 2px; margin-right: 5px;"></i>'
                    . '"' . $image->name . '"</span>';
            }
            $feedbackimages_html .= ' ]</div>';

            // Param $options->suppresschoicefeedback is a hack specific to the
            // oumultiresponse question type. It would be good to refactor to
            // avoid refering to it here.
            $feedback_content = null;
            if ($options->feedback && empty($options->suppresschoicefeedback) && $isselected
            ) {
                $feedback_content = ($isselected ?
                    ('<span class="pull-right">' . $renderer->feedback_image($renderer->is_right($ans)) . '</span>')
                    : "");
                $feedback_text = trim($ans->feedback);
                if (!empty(strip_tags($feedback_text))) {
                    $feedback_content .= html_writer::tag("div",
                        html_writer::tag("i", " ",
                            array(
                                "class" => "pull-left glyphicon glyphicon-record",
                                "style" => "margin-left: 10px; margin-right: 5px"
                            )
                        ) .
                        format_text($feedback_text) . $feedbackimages_html,
                        array(
                            "class" => "outcome",
                            "style" => "padding: 20px 15px;"
                        )
                    );
                }
                $feedback[] = $feedback_content;

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

            $radiobutton_label = html_writer::tag('label',
                $question->format_text(
                    $renderer->number_in_style($value, $question->answernumbering) .
                    preg_replace('/<p[^>]*>(.*)<\/p[^>]*>/i', '$1', $ans->answer), $ans->answerformat,
                    $qa, 'question', 'answer', $ansid)
                ,
                array('for' => $inputattributes['id']));

            $radiobuttons[] = $hidden . html_writer::empty_tag('input', $inputattributes) .
                qtype_omeromultichoice_base_renderer::number_answer($value, $question->answernumbering) . $radiobutton_label;
        }


        /**
         * Render the question
         */
        $result = '';

        // add the ModalImagePanel
        $result .= qtype_omerocommon_renderer_helper::modal_viewer(true, true, true, $modal_image_panel_id);

        // main question_answer_container
        $result .= html_writer::start_tag('div', array('id' => $question_answer_container, 'class' => 'ablock'));

        // question text
        $result .= html_writer::tag('div', $question->format_questiontext($qa), array('class' => 'qtext'));

        // viewer of the question image
        $result .= '<div class="image-viewer-with-controls-container">';


        $result .= '<div id="' . self::to_unique_identifier($qa, "graphics_container") . '" class="image-viewer-container" style="position: relative;" >
            <div id="' . self::to_unique_identifier($qa, self::IMAGE_VIEWER_CONTAINER) . '" style="position: absolute; width: 100%; height: 500px; margin: auto;"></div>
            <canvas id="' . self::to_unique_identifier($qa, 'annotations_canvas') . '" style="position: absolute; width: 100%; height: 500px; margin: auto;"></canvas>
            <div id="' . self::to_unique_identifier($qa, self::IMAGE_VIEWER_CONTAINER) . '-loading-dialog" class="image-viewer-loading-dialog"></div>
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

        if (!empty($question->focusablerois)) {
            $result .= '<div id="' . self::to_unique_identifier($qa, self::FOCUS_AREAS_CONTAINER) . '" ' .
                ' class="focus_areas_container">' .
                '<span class="focus-areas-text">* ' . get_string("focusareas", "qtype_omerointeractive") . '</span> ' . '</div>';
        }

        $result .= html_writer::start_tag('div', array('class' => 'multichoice-options-container'));

        $result .= html_writer::tag('div',
            !$options->correctness ? $renderer->prompt() :
                ($num_of_response === 1 ?
                    get_string("notice_your_answer", "qtype_omerocommon") :
                    get_string("notice_your_answers", "qtype_omerocommon")
                ),
            array('class' => 'prompt'));

        $result .= html_writer::start_tag('div', array('class' => 'answer'));
        foreach ($radiobuttons as $key => $radio) {
            $result .= html_writer::tag('div', $radio . ' ' . $feedback[$key],
                array('class' => $classes[$key]));
        }
        $result .= html_writer::end_tag('div'); // Answer.

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

        if ($qa->get_state() == question_state::$invalid) {
            $result .= html_writer::nonempty_tag('div',
                $question->get_validation_error($qa->get_last_qt_data()),
                array('class' => 'validationerror'));
        }

        // set the player configuration
        $player_config = array(
            "image_id" => $omero_image,
            "image_properties" => json_decode($question->omeroimageproperties),
            "image_frame_id" => $omero_frame_id,
            "image_annotations_canvas_id" => self::to_unique_identifier($qa, "annotations_canvas"),
            "modal_image_panel_id" => $modal_image_panel_id,
            "feedback_image_class" => $feedback_image_class,
            "image_server" => $OMERO_SERVER,
            "viewer_model_server" => $CFG->omero_image_server,
            "image_viewer_container" => self::to_unique_identifier($qa, self::IMAGE_VIEWER_CONTAINER),
            "image_navigation_locked" => (bool)$question->omeroimagelocked,
            "qname" => $question->name,
            "question_answer_container" => $question_answer_container,
            "focus_areas_container" => self::to_unique_identifier($qa, self::FOCUS_AREAS_CONTAINER),
            "visible_rois" => empty($question->visiblerois) ? [] : explode(",", $question->visiblerois),
            "focusable_rois" => empty($question->focusablerois) ? [] : explode(",", $question->focusablerois),
            "answer_input_name" => $answer_input_name
        );

        // embed the player configuration within an hidden input element
        $player_config_element_id = self::to_unique_identifier($qa, "player-config");
        $result .= html_writer::empty_tag(
            "input", array("id" => $player_config_element_id,
                "type" => "hidden",
                "value" => json_encode($player_config))
        );

        // start the pplayer
        $PAGE->requires->js_call_amd(
            "qtype_omeromultichoice/question-player-multichoice",
            "start", array($player_config_element_id)
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
        $PAGE->requires->css("/question/type/omerocommon/css/modal-image-dialog.css");
        $PAGE->requires->css("/question/type/omerocommon/css/message-dialog.css");
        $PAGE->requires->string_for_js('validate_question', 'qtype_omerocommon');
        $PAGE->requires->string_for_js('validate_editor_not_valid', 'qtype_omerocommon');
        $PAGE->requires->string_for_js('validate_editor_check_question', 'qtype_omerocommon');
        $PAGE->requires->string_for_js('validate_editor_not_existing_rois', 'qtype_omerocommon');
        $PAGE->requires->string_for_js('validate_player_not_existing_rois', 'qtype_omerocommon');
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
        return "<span style='font-weight: bold;'>($number)</span> ";
    }
}


