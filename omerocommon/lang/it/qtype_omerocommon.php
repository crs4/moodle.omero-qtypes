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
$string['pluginname_help'] = 'Libreria condivisa fra i tipi di Omero Question';
$string['pluginname_link'] = 'question/type/omerocommon';
$string['pluginnamesummary'] = 'Libreria condivisa fra i tipi di Omero Question.';

# language settings
$string['language'] = "Lingua";

# classifiers (i.e., question tags)
$string['questionclassifiers'] = 'Classificatori';
$string['selectquestionclassifiers'] = 'Seleziona uno o più classificatori di domanda';
$string['editquestionclassifiers'] = 'Modifica';

# answers
$string['answer_options_properties'] = 'Proprietà delle opzioni di risposta';
$string['add_answers'] = 'Aggiungi risposte';
$string['choiceno'] = '{$a}';
$string['answer_choiceno'] = "Opzione risposta n. ";
$string['answer_grade'] = "Voto";
$string['answerhowmany'] = 'Una sola o più risposte corrette?';
$string['answersingleno'] = 'Consenti risposte corrette multiple';
$string['answersingleyes'] = 'Una sola risposta corretta';

# feedback
$string['general_and_combined_feedback'] = 'Feedback generale e combinato';

# image viewer
$string['omero_image_viewer'] = "Visualizzatore dell'immagine Omero";
$string['image_viewer_lock_student_navigation'] = "blocca navigazione studente";

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
