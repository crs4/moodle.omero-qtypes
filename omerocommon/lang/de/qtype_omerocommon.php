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
$string['pluginname'] = 'Omero Fragen gemeinsam genutzte Bibliothek';
$string['pluginname_help'] = 'Gemeinsam genutzte Bibliothek mit Einteilung nach Fragetyp';
$string['pluginname_link'] = 'Frage/Typ/omerogemeinsam';
$string['pluginnamesummary'] = 'Gemeinsam genutzte Bibliothek mit Einteilung nach Fragetypen';

# language settings
$string['language'] = "Sprache";

# classifiers (i.e., question tags)
$string['questionclassifiers'] = 'Kategorien';
$string['selectquestionclassifiers'] = 'Wähle eine oder mehrere Fragekategorien aus: ';
$string['editquestionclassifiers'] = 'Bearbeite Kategorien';

# answers
$string['answer_options_properties'] = 'Antwortmöglichkeiten';
$string['add_answers'] = 'Antworten hinzufügen';
$string['choiceno'] = '{$a}';
$string['answer_choiceno'] = "Antwortmöglichkeit n. ";
$string['answer_grade'] = "Bewertung";
$string['answerhowmany'] = 'Eine oder mehrere richtige Antworten?';
$string['answersingleno'] = 'Mehrere richtige Antworten möglich';
$string['answersingleyes'] = 'Nur eine richtige Antwort möglich';

# feedback images
$string['feedbackimages'] = 'Rückmeldung Bilder';
$string['feedbackimagename'] = 'Bildname';
$string['feedbackimagedescription'] = 'Bildbeschreibung';

# feedback
$string['general_and_combined_feedback'] = 'Allgemeine und zusammengesetzte Rückmeldung';
$string['notice_your_answer'] = 'Überdenke deine Antwort: ';
$string['notice_your_answers'] = 'Überdenke deine Antworten: ';
$string['see'] = 'Beachte';

# image viewer
$string['omero_image_viewer'] = "Omero Bild Viewer";
$string['image_viewer_student_navigation'] = "Studentennavigation sperren:";
$string['image_viewer_lock_student_navigation'] = "";
$string['image_viewer_locked_student_navigation'] = "<i class='glyphicon glyphicon-lock'></i>";

# ROIs
$string['roi_shape_inspector'] = "ROI Form Inspektor";
$string['roi_id'] = 'Bezeichnungen';
$string['roi_comment'] = 'Kommentar';
$string['roi_type'] = 'Typ';
$string['roi_width'] = 'Breite';
$string['roi_height'] = 'Höhe';
$string['roi_shape_details'] = "ROI Formdetails";
$string['roi_description'] = "Beschreibung";
$string['roi_visibility'] = "Sichtbarkeit";
$string['roi_focus'] = "Fokus";
$string['roi_visible'] = "Sichtbare ROIs";
$string['roi_focusable'] = "Fokusbereiche";

# save/update controls
$string['savechangesandcontinueediting'] = "Speichern";
$string['savechangesandexit'] = "Speichern und Beenden";
$string['close'] = "Schließen";

# validation messages
$string['validate_question'] = "Frage";
$string['validate_editor_not_valid'] = "ungültig.";
$string['validate_editor_not_existing_rois'] = "Die folgenden ROIs sind auf OMERO nicht länger verfügbar: ";
$string['validate_editor_check_question'] = "Bitte kontrolliere deine Antwort!!!";
$string['validate_player_not_existing_rois'] = "ungültig. <br>Bitte kontaktiere deine(-n) Instruktor/-in oder Prüfer/-in!!!";
$string['validate_warning'] = "Warnung";
$string['validate_field_required'] = "Diese Feld ist erforderlich!!!";
$string['validate_no_answers'] = "Antworten betragen weniger als 1 !!!";
$string['validate_no_image'] = "Kein Bild ausgewählt!!!";
$string['validate_at_least_one_100'] = "Eine der Antwortmöglichkeiten sollte 100% betragen !!!";
$string['validate_at_most_one_100'] = "Eine Antwort sollte 100% betragen!!!";
$string['validate_sum_of_grades'] = "Alle Antworten zusammen sollten 100% betragen !!!";
