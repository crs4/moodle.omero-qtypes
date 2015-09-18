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
        global $CFG, $PAGE, $OUTPUT;

        // get the current question
        $question = $qa->get_question();

        // the OMERO image URL
        $omero_image_url = $question->omeroimageurl;

        // extract the omero server
        $OMERO_SERVER = substr($omero_image_url, 0, strpos($omero_image_url, "/webgateway"));

        // get the image id
        $omero_image_str = strrchr($omero_image_url, "/");
        $omero_image = substr($omero_image_str, 1, strlen($omero_image_str));

        // set the frame of the OmeroImageViewer
        $omero_frame_id = "omero-image-viewer";

//        $module = array('name' => 'omero_multichoice_helper', 'fullpath' => '/question/type/omeromultichoice/omero_multichoice_helper.js',
//            'requires' => array('omemultichoice_qtype', 'node', 'node-event-simulate', 'core_dndupload'));
//        $PAGE->requires->js_init_call('M.omero_multichoice_helper.init', array(), true, $module);


        $omero_image_wrapper = '<script type="text/javascript" ' .
            'src="/moodle/question/type/omeromultichoice/omero_multichoice_helper.js" ' .
            '></script>';


        // build the iframe element for wrapping the OmeroImageViewer
        $omero_image_wrapper .= html_writer::tag('iframe', "",
            array(
                "src" => "/moodle/repository/omero/viewer.php" .
                    "?id=$omero_image" .
                    "&width=" . urlencode("100%") .
                    "&height=400px" .
                    "&frame=$omero_frame_id" .
                    "&showRoiTable=false",
                "width" => "100%",
                "height" => "450px",
                "id" => $omero_frame_id //,
                //"onload" => 'M.omero_multichoice_helper.init();'
            )
        );

        // set question controls
        $response = $question->get_response($qa);

        $inputname = $qa->get_qt_field_name('answer');
        $inputattributes = array(
            'type' => $this->get_input_type(),
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
            array_push($roi_id_list, $ans->answer);
            $inputattributes['name'] = $this->get_input_name($qa, $value);
            $inputattributes['value'] = $this->get_input_value($value);
            $inputattributes['id'] = $this->get_input_id($qa, $value);
            $isselected = $question->is_choice_selected($response, $value);
            if ($isselected) {
                $inputattributes['checked'] = 'checked';
            } else {
                unset($inputattributes['checked']);
            }
            $hidden = '';
            if (!$options->readonly && $this->get_input_type() == 'checkbox') {
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
                $answer_content = html_writer::tag('span', $ans->answer);
            }

            $radiobuttons[] = $hidden . html_writer::empty_tag('input', $inputattributes) .
                //html_writer::tag('span',
                html_writer::tag('label',
                    "<b>" . $this->number_in_style($value, $question->answernumbering) . "</b>" . $answer_content
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
                $feedbackimg[] = $this->feedback_image($this->is_right($ans));
                $class .= ' ' . $this->feedback_class($this->is_right($ans));
            } else {
                $feedbackimg[] = '';
            }
            $classes[] = $class;
        }

        /**
         * Render the question
         */

        $result = '';
        // question text
        $result .= html_writer::tag('div', $question->format_questiontext($qa),
            array('class' => 'qtext'));
        // viewer of the question image
        $result .= $omero_image_wrapper;
        $script_args = ($question->answertype == qtype_omeromultichoice::ROI_BASED_ANSWERS) ?
            "[" . implode(",", $roi_id_list) . "]" : "'all'";
        $result .= html_writer::script(
            "M.omero_multichoice_helper.init('omero_multichoice_helper', " .
            "'$omero_frame_id', $script_args)");

        $result .= html_writer::start_tag('div', array('class' => 'ablock'));
        $result .= html_writer::tag('div', $this->prompt(), array('class' => 'prompt'));

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


        // add initialization


        return $result;
    }

//
//    public function specific_feedback(question_attempt $qa) {
//        // TODO.
//        return '';
//    }
//
//    public function correct_response(question_attempt $qa) {
//        // TODO.
//        return '';
//    }
}
