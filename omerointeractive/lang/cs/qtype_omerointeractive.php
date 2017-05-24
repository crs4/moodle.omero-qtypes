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
 * Strings for the module 'qtype_omerointeractive', language 'cs'
 *
 * @package    qtype
 * @subpackage omerointeractive
 * @copyright  2015-2016 CRS4
 * @license    https://opensource.org/licenses/mit-license.php MIT license
 */

# generics
$string['pluginname'] = 'Omero interaktivní';
$string['pluginname_help'] = 'Typ otázky, který od studenta vyžaduje, aby identifikoval pomocí značek specifické oblasti obrázku jako odpověď na testovou otázku; systém je schopen vyhodnotit odpověď studenta porovnáním polohy značek s instruktorem určenými ROI.';
$string['pluginname_link'] = 'question/type/omeroineractive';
$string['pluginnameadding'] = 'Přidání "Omero interaktivní" otázky';
$string['pluginnameediting'] = 'Úprava  "Omero interaktivní" otázky';
$string['pluginnamesummary'] = 'Typ otázky, který od studenta vyžaduje, aby identifikoval pomocí značek specifické oblasti obrázku jako odpověď na testovou otázku; systém je schopen vyhodnotit odpověď studenta porovnáním polohy značek s instruktorem určenými ROI.';

# answers
$string['add_answers'] = "Přidejte skupiny odpovědi";
$string['answer_group'] = "Skupina";
$string['answer_groups'] = "Skupiny odpovědi";
$string['answer_group_of_rois'] = "Seznam ROI";
$string['answer_group_removed_invalid_rois'] = "Následující ROI nejsou již dostupné v OMERO:";
$string['validation_noroi_per_group'] = "Každá odpověď musí mít alespoň jednu ROI !!!";

# marker controls
$string['add_marker'] = "Přidat";
$string['edit_marker'] = "Editovat";
$string['clear_markers'] = "Smazat";
$string['marker'] = 'Označit';
$string['yourmarkers'] = 'Vaše značky: ';

# focus area info
$string['focusareas'] = 'Zaostřete plochy: ';

# feedback
$string['your_marker_inside'] = 'je uvnitř ROI';
$string['your_marker_outside'] = 'is outside of any ROI';
$string['answerassociatedroi'] = 'Notice the relevant ROI (<em>Region Of Interest</em>) of this question: ';
$string['answerassociatedrois'] = 'Oznamte odpovídající ROI (<em>Regions Of Interest</em>) této otázky: ';
$string['single_correctansweris'] = 'Pro správnou odpověď jste měli označit následující ROI: ';
$string['single_correctanswerare'] = 'Pro správnou odpověď jste měli označit jednu z následujících ROI: ';
$string['multi_correctansweris'] = 'Pro správnou odpověď jste měli označit následující ROI: ';
$string['multi_correctanswerare'] = 'Pro správnou odpověď jste měli označit jednu z následujících ROI: ';