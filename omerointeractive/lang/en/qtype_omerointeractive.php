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
 * Strings for the module 'qtype_omerointeractive', language 'en'
 *
 * @package    qtype
 * @subpackage omerointeractive
 * @copyright  2015-2016 CRS4
 * @license    https://opensource.org/licenses/mit-license.php MIT license
 */

# generics
$string['pluginname'] = 'Omero Interactive';
$string['pluginname_help'] = 'A question type which asks the students to identify, using markers, specific areas on the image in response to the test questions and it’s able to evaluate the student answers by comparing the location of the markers with instructor’s specified ROIs.';
$string['pluginname_link'] = 'question/type/omeroineractive';
$string['pluginnameadding'] = 'Adding a "Omero Interactive" question';
$string['pluginnameediting'] = 'Editing a "Omero Interactive" question';
$string['pluginnamesummary'] = 'A question type which asks the students to identify, using markers, specific areas on the image in response to the test questions and it’s able to evaluate the student answers by comparing the location of the markers with instructor’s specified ROIs.';

# answers
$string['add_answers'] = "Add answer groups";
$string['answer_group'] = "Group";
$string['answer_groups'] = "Answer Groups";
$string['answer_group_of_rois'] = "List of ROIs";
$string['answer_group_removed_invalid_rois'] = "The following ROIs are no longer available in OMERO:";
$string['validation_noroi_per_group'] = "Every answer group must have at least one ROI !!!";

# marker controls
$string['add_marker'] = "Add";
$string['edit_marker'] = "Edit";
$string['clear_markers'] = "Clear";
$string['marker'] = 'Marker';
$string['yourmarkers'] = 'Your markers: ';

# focus area info
$string['focusareas'] = 'Focus Areas: ';

# feedback
$string['your_marker_inside'] = 'is inside the ROI';
$string['your_marker_outside'] = 'is outside of any ROI';
$string['answerassociatedroi'] = 'Notice the relevant ROI (<em>Region Of Interest</em>) of this question: ';
$string['answerassociatedrois'] = 'Notice the relevant ROIs (<em>Regions Of Interest</em>) of this question: ';
$string['single_correctansweris'] = 'To correctly answer you have to select the following ROI: ';
$string['single_correctanswerare'] = 'To correctly answer you have to select one of the following ROIs: ';
$string['multi_correctansweris'] = 'To correctly answer you have to select the following ROI: ';
$string['multi_correctanswerare'] = 'To correctly and completely answer you have to select the following ROIs: ';