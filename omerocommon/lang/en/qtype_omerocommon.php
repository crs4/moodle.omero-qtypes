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
 * Strings for component 'qtype_omeromultichoice', language 'en', branch 'MOODLE_29_STABLE'
 *
 * @package    qtype
 * @subpackage omeromultichoice
 * @copyright  2015 CRS4

 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['pluginname'] = 'Omero MultiChoice';
$string['pluginname_help'] = 'Create a cloze question type with embedded response fields in the question text to enter a numeric
or text value or select a value from a number of options.';
$string['pluginname_link'] = 'question/type/omeromultichoice';
$string['pluginnameadding'] = 'Adding a Omero MultiChoice question';
$string['pluginnameediting'] = 'Editing a Omero MultiChoice question';
$string['pluginnamesummary'] = 'A Omero MultiChoice question type which allows the embedding of the response fields for various available
sub questions in the question text.
So the student can enter a numeric or short text answer or choose an answer or answer(s) from
 using a select box, check boxes or radio boxes.';


$string['answer_options_properties'] = 'Properties of answer options';

$string['general_and_combined_feedback'] = 'General and combined feedback';


$string['add_roi_answer'] = 'Add new ROI answer';

$string['choiceno'] = '{$a}';

$string['roi_choiceno'] = 'ROI choice {$a}';

$string['add_roi_answer'] = 'Add a new ROI based answer';

$string['answer_type'] = "Answer type";

$string['roi_shape_inspector'] = "ROI Shape Inspector";
$string['answer_groups'] = "Answer Groups";

$string['omero_image_and_rois'] = "Omero Image and ROIs";

$string['qtype_0'] = "Plaintext";
$string['qtype_1'] = "ROI answer";

/** ROI description
/* subset of a JSON ROI
 *   "height" : 603,
     "id" : 11,
     "strokeAlpha" : 0.765625,
     "strokeColor" : "#c4c4c4",
     "strokeWidth" : 1,
     "textValue" : "<br/>Comment ROI1",
     "theT" : 0,
     "theZ" : 0,
     "transform" : "none",
     "type" : "Rectangle",
     "width" : 604,
     "x" : 24019,
     "y" : 14605
 */
$string['roi_id'] = 'Identifier';
$string['roi_comment'] = 'Comment';
$string['roi_type'] = 'Type';
$string['roi_width'] = 'Width';
$string['roi_height'] = 'Height';

$string['language'] = "Language";

$string['answerhowmany'] = 'One or multiple correct answers?';
$string['answersingleno'] = 'Multiple correct answers allowed';
$string['answersingleyes'] = 'One correct answer only';