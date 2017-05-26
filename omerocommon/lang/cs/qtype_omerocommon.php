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
 * Strings for the plugin 'qtype_omerocommon', language 'cs'
 *
 * @package    qtype
 * @subpackage omerocommon
 * @copyright  2015-2016 CRS4
 * @license    https://opensource.org/licenses/mit-license.php MIT license
 */

# generics
$string['pluginname'] = 'Spoleèná knihovna Omero otázek';
$string['pluginname_help'] = 'Spoleèná knihovna sdílená Omero typy otázek';
$string['pluginname_link'] = 'question/type/omerocommon';
$string['pluginnamesummary'] = 'Spoleèná knihovna sdílená Omero typy otázek.';

# language settings
$string['language'] = "Jazyk";

# classifiers (i.e., question tags)
$string['questionclassifiers'] = 'Klasifikátory';
$string['selectquestionclassifiers'] = 'Vyberte jeden nebo více klasifikátorù otázky: ';
$string['editquestionclassifiers'] = 'Pracujte s klasifikátory';

# answers
$string['answer_options_properties'] = 'Vlastnosti možností odpovìdí';
$string['add_answers'] = 'Pøidejte odpovìdi';
$string['choiceno'] = '{$a}';
$string['answer_choiceno'] = "Volba odpovìdi è. ";
$string['answer_grade'] = "Stupeò";
$string['answerhowmany'] = 'Jedna, nebo více správných odpovìdí?';
$string['answersingleno'] = 'Povoleno více správných odpovìdí';
$string['answersingleyes'] = 'Pouze jedna správná odpovìï';

# feedback images
$string['feedbackimages'] = 'Obrázky zpìtné vazby';
$string['feedbackimagename'] = 'Název obrázku';
$string['feedbackimagedescription'] = 'Popis obrázku';

# feedback
$string['general_and_combined_feedback'] = 'Obecná a kombinovaná zpìtná vazba';
$string['notice_your_answer'] = 'Zaznamenejte Vaši odpovìï: ';
$string['notice_your_answers'] = 'Zaznamenejte Vaše odpovìdi: ';
$string['see'] = 'viz';

# image viewer
$string['omero_image_viewer'] = "Prohlížeè obrázkù Omero";
$string['image_viewer_student_navigation'] = "zamknìte navigaci studenta:";
$string['image_viewer_lock_student_navigation'] = "";
$string['image_viewer_locked_student_navigation'] = "<i class='glyphicon glyphicon-lock'></i>";

# ROIs
$string['roi_shape_inspector'] = "Inspektor tvaru ROI";
$string['roi_id'] = 'Identifikátor';
$string['roi_comment'] = 'Komentáø';
$string['roi_type'] = 'Typ';
$string['roi_width'] = 'Šíøe';
$string['roi_height'] = 'Výška';
$string['roi_shape_details'] = "Detaily tvaru ROI";
$string['roi_description'] = "Popis";
$string['roi_visibility'] = "Viditelnost";
$string['roi_focus'] = "Zaostøení";
$string['roi_visible'] = "Viditelné ROI";
$string['roi_focusable'] = "Zaostøit plochy";

# save/update controls
$string['savechangesandcontinueediting'] = "Uložit";
$string['savechangesandexit'] = "Uložit a odejít";
$string['close'] = "Zavøít";

# validation messages
$string['validate_question'] = "Otázka";
$string['validate_editor_not_valid'] = "neplatné.";
$string['validate_editor_not_existing_rois'] = "Tyto ROI již nejsou v OMERO dostupné: ";
$string['validate_editor_check_question'] = "Prosím zkontrolujte Vaši otázku !!!";
$string['validate_player_not_existing_rois'] = "neplatná. <br>Prosím kontaktujte Vašeho instruktora/zkoušejícího !!!";
$string['validate_warning'] = "Varování";
$string['validate_field_required'] = "Toto pole je požadováno !!!";
$string['validate_no_answers'] = "Odpovìdi jsou ménì než 1 !!!";
$string['validate_no_image'] = "Není vybrán obrázek !!!";
$string['validate_at_least_one_100'] = "Jeden z výbìrù by mìl mít stupeò 100% !!!";
$string['validate_at_most_one_100'] = "Nanejvýš jedna odpovìï by mìla mít stupeò 100% !!!";
$string['validate_sum_of_grades'] = "Souèet stupòù odpovìdí by mìl být 100% !!!";
