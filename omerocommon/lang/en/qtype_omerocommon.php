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

# generics
$string['pluginname'] = 'Omero Question Common Library';
$string['pluginname_help'] = 'Common library shared between Omero Question types';
$string['pluginname_link'] = 'question/type/omerocommon';
$string['pluginnamesummary'] = 'Common library shared between Omero Question types.';

# language settings
$string['language'] = "Language";

# classifiers (i.e., question tags)
$string['questionclassifiers'] = 'Classifiers';
$string['selectquestionclassifiers'] = 'Select one or more question classifiers';
$string['editquestionclassifiers'] = 'Edit';

# answers
$string['answer_options_properties'] = 'Properties of answer options';
$string['add_answers'] = 'Add answers';
$string['choiceno'] = '{$a}';
$string['answer_choiceno'] = "Answer choice n. ";
$string['answer_grade'] = "Grade";
$string['answerhowmany'] = 'One or multiple correct answers?';
$string['answersingleno'] = 'Multiple correct answers allowed';
$string['answersingleyes'] = 'One correct answer only';

# feedback
$string['general_and_combined_feedback'] = 'General and combined feedback';

# image viewer
$string['omero_image_viewer'] = "Omero Image Viewer";
$string['image_viewer_lock_student_navigation'] = "lock student navigation";

# ROIs
$string['roi_shape_inspector'] = "ROI Shape Inspector";
$string['roi_id'] = 'Identifier';
$string['roi_comment'] = 'Comment';
$string['roi_type'] = 'Type';
$string['roi_width'] = 'Width';
$string['roi_height'] = 'Height';
$string['roi_shape_details'] = "ROI Shape Details";
$string['roi_description'] = "Description";
$string['roi_visibility'] = "Visibility";
$string['roi_focus'] = "Focus";

# save/update controls
$string['savechangesandcontinueediting'] = "Save";
$string['savechangesandexit'] = "Save and Exit";

# validation messages
$string['validate_warning'] = "Warning";
$string['validate_no_answers'] = "Answers are less than 1 !!!";
$string['validate_no_image'] = "No image selected !!!";
$string['validate_at_least_one_100'] = "One of the choices should have grade 100% !!!";
$string['validate_at_most_one_100'] = "At most one answer should have grade 100% !!!";
$string['validate_sum_of_grades'] = "The sum of grades should be equal to 100% !!!";
