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
 * Strings for the plugin 'qtype_omerocommon', language 'en'
 *
 * @package    qtype
 * @subpackage omerocommon
 * @copyright  2015-2016 CRS4
 * @license    https://opensource.org/licenses/mit-license.php MIT license
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
$string['selectquestionclassifiers'] = 'Select one or more question classifiers: ';
$string['editquestionclassifiers'] = 'Manage classifiers';

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
$string['notice_your_answer'] = 'Notice your answer: ';
$string['notice_your_answers'] = 'Notice your answers: ';
$string['see'] = 'see';

# image viewer
$string['omero_image_viewer'] = "Omero Image Viewer";
$string['image_viewer_student_navigation'] = "lock student navigation:";
$string['image_viewer_lock_student_navigation'] = "";
$string['image_viewer_locked_student_navigation'] = "<i class='glyphicon glyphicon-lock'></i>";

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
$string['roi_visible'] = "Visible ROIs";
$string['roi_focusable'] = "Focus Areas";

# save/update controls
$string['savechangesandcontinueediting'] = "Save";
$string['savechangesandexit'] = "Save and Exit";
$string['close'] = "Close";

# validation messages
$string['validate_question'] = "Question";
$string['validate_editor_not_valid'] = "not valid.";
$string['validate_editor_not_existing_rois'] = "The following ROIs are no longer available in OMERO: ";
$string['validate_editor_check_question'] = "Please check your question !!!";
$string['validate_player_not_existing_rois'] = "not valid. <br>Please contact your instructor/examiner !!!";
$string['validate_warning'] = "Warning";
$string['validate_no_answers'] = "Answers are less than 1 !!!";
$string['validate_no_image'] = "No image selected !!!";
$string['validate_at_least_one_100'] = "One of the choices should have grade 100% !!!";
$string['validate_at_most_one_100'] = "At most one answer should have grade 100% !!!";
$string['validate_sum_of_grades'] = "The sum of answer grades should be equal to 100% !!!";
