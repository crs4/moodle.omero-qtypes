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
 * Strings for the plugin 'qtype_omerocommon', language 'it'
 *
 * @package    qtype
 * @subpackage omerocommon
 * @copyright  2015-2016 CRS4
 * @license    https://opensource.org/licenses/mit-license.php MIT license
 */

# generics
$string['pluginname'] = 'Omero Question Common Library';
$string['pluginname_help'] = 'Libreria condivisa fra i tipi di Omero Question';
$string['pluginname_link'] = 'question/type/omerocommon';
$string['pluginnamesummary'] = 'Libreria condivisa fra i tipi di Omero Question.';

# language settings
$string['language'] = "Lingua";

# classifiers (i.e., question tags)
$string['questionclassifiers'] = 'Classificatori';
$string['selectquestionclassifiers'] = 'Seleziona uno o più classificatori di domanda: ';
$string['editquestionclassifiers'] = 'Gestisci Classificatori';

# answers
$string['answer_options_properties'] = 'Proprietà delle opzioni di risposta';
$string['add_answers'] = 'Aggiungi risposte';
$string['choiceno'] = '{$a}';
$string['answer_choiceno'] = "Opzione risposta n. ";
$string['answer_grade'] = "Punteggio";
$string['answerhowmany'] = 'Una sola o più risposte corrette?';
$string['answersingleno'] = 'Consenti risposte corrette multiple';
$string['answersingleyes'] = 'Una sola risposta corretta';

# feedback
$string['general_and_combined_feedback'] = 'Feedback generale e combinato';
$string['notice_your_answer'] = 'Presta attenzione alla tua risposta: ';
$string['notice_your_answers'] = 'Presta attenzione alla tue risposte: ';

# image viewer
$string['omero_image_viewer'] = "Visualizzatore dell'immagine Omero";
$string['image_viewer_student_navigation'] = "blocco navigazione studente:";
$string['image_viewer_locked_student_navigation'] = "<i class='glyphicon glyphicon-lock'></i>";
$string['image_viewer_lock_student_navigation'] = "";

# ROIs
$string['roi_shape_inspector'] = "ROI Shape Inspector";
$string['roi_id'] = 'Identificatore';
$string['roi_comment'] = 'Commento';
$string['roi_type'] = 'Tipo';
$string['roi_width'] = 'Larghezza';
$string['roi_height'] = 'Altezza';
$string['roi_shape_details'] = "Dettagli ROI Shape";
$string['roi_description'] = "Descrizione";
$string['roi_visibility'] = "Visibilità";
$string['roi_focus'] = "Fuoco";

# save/update controls
$string['savechangesandcontinueediting'] = "Salva";
$string['savechangesandexit'] = "Salva ed esci";

# validation messages
$string['validate_warning'] = "Avviso";
$string['validate_no_answers'] = "Le opzioni di risposta sono meno di 1 !!!";
$string['validate_no_image'] = "Nessuna immagine selezionata !!!";
$string['validate_at_least_one_100'] = "Una delle opzioni di risposta dovrebbe avere punteggio 100% !!!";
$string['validate_at_most_one_100'] = "Al più una opzione di risposta dovrebbe avere punteggio 100% !!!";
$string['validate_sum_of_grades'] = "La somma dei punteggi delle opzioni di risposta dovrebbe essere uguale a 100% !!!";
