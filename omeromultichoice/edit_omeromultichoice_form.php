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
 * Defines the editing form for the omeromultichoice question type.
 *
 * @package    qtype
 * @subpackage omeromultichoice
 * @copyright  2015 CRS4
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later //FIXME: check the licence
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/multichoice/edit_multichoice_form.php');

/**
 * omeromultichoice question editing form definition.
 *
 * @copyright  2015 CRS4
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later // FIXME: check the licence
 */
class qtype_omeromultichoice_edit_form extends qtype_multichoice_edit_form
{
    private $localized_strings = array(
        "questiontext", "generalfeedback",
        "correctfeedback", "partiallycorrectfeedback", "incorrectfeedback"
    );

    public function qtype()
    {
        return 'omeromultichoice';
    }


    /**
     * Build the form definition.
     *
     * This adds all the form fields that the default question type supports.
     * If your question type does not support all these fields, then you can
     * override this method and remove the ones you don't want with $mform->removeElement().
     */
    protected function definition()
    {
        global $COURSE, $CFG, $DB, $PAGE;

        $PAGE->requires->jquery();
        $PAGE->requires->jquery_plugin('ui');
        $PAGE->requires->jquery_plugin('ui-css');

        $PAGE->requires->jquery_plugin("bootstrap", "qtype_omeromultichoice");
        $PAGE->requires->jquery_plugin("bootstrap-table", "qtype_omeromultichoice");
        $PAGE->requires->jquery_plugin("dragtable", "qtype_omeromultichoice");
//        $PAGE->requires->jquery_plugin("dataTables", "qtype_omeromultichoice");


        $module = array(
            'name' => 'htmlt_utils',
            'fullpath' => '/question/type/omeromultichoice/js/html-utils.js',
            'requires' => array());
        $PAGE->requires->js_init_call('M.omero_multichoice_html_utils.init', array(), true, $module);


        $qtype = $this->qtype();
        $langfile = "qtype_{$qtype}";

        $mform = $this->_form;

        // Standard fields at the start of the form.
        $mform->addElement('header', 'generalheader', get_string("general", 'form'));

        if (!isset($this->question->id)) {
            if (!empty($this->question->formoptions->mustbeusable)) {
                $contexts = $this->contexts->having_add_and_use();
            } else {
                $contexts = $this->contexts->having_cap('moodle/question:add');
            }

            // Adding question.
            $mform->addElement('questioncategory', 'category', get_string('category', 'question'),
                array('contexts' => $contexts));
        } else if (!($this->question->formoptions->canmove ||
            $this->question->formoptions->cansaveasnew)
        ) {
            // Editing question with no permission to move from category.
            $mform->addElement('questioncategory', 'category', get_string('category', 'question'),
                array('contexts' => array($this->categorycontext)));
            $mform->addElement('hidden', 'usecurrentcat', 1);
            $mform->setType('usecurrentcat', PARAM_BOOL);
            $mform->setConstant('usecurrentcat', 1);
        } else {
            // Editing question with permission to move from category or save as new q.
            $currentgrp = array();
            $currentgrp[0] = $mform->createElement('questioncategory', 'category',
                get_string('categorycurrent', 'question'),
                array('contexts' => array($this->categorycontext)));
            if ($this->question->formoptions->canedit ||
                $this->question->formoptions->cansaveasnew
            ) {
                // Not move only form.
                $currentgrp[1] = $mform->createElement('checkbox', 'usecurrentcat', '',
                    get_string('categorycurrentuse', 'question'));
                $mform->setDefault('usecurrentcat', 1);
            }
            $currentgrp[0]->freeze();
            $currentgrp[0]->setPersistantFreeze(false);
            $mform->addGroup($currentgrp, 'currentgrp',
                get_string('categorycurrent', 'question'), null, false);

            $mform->addElement('questioncategory', 'categorymoveto',
                get_string('categorymoveto', 'question'),
                array('contexts' => array($this->categorycontext)));
            if ($this->question->formoptions->canedit ||
                $this->question->formoptions->cansaveasnew
            ) {
                // Not move only form.
                $mform->disabledIf('categorymoveto', 'usecurrentcat', 'checked');
            }
        }

        // Choose language section
        $languages = array();
        $languages += get_string_manager()->get_list_of_translations();
        $mform->addElement('select', 'question_language',
            get_string('language', 'qtype_omeromultichoice'), $languages,
            array("class" => "question-language-selector"));
        $mform->setDefault('lang', current_language());

        // Question Text
        $mform->addElement('text', 'name', get_string('questionname', 'question'),
            array('size' => 50, 'maxlength' => 255));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');


        $mform->addElement('editor', 'questiontext', get_string('questiontext', 'question'),
            array('rows' => 15), $this->editoroptions);
        $mform->setType('questiontext', PARAM_RAW);
        $mform->addRule('questiontext', null, 'required', null, 'client');

        $mform->addElement('text', 'defaultmark', get_string('defaultmark', 'question'),
            array('size' => 7));
        $mform->setType('defaultmark', PARAM_FLOAT);
        $mform->setDefault('defaultmark', 1);
        $mform->addRule('defaultmark', null, 'required', null, 'client');

        $mform->addElement('editor', 'generalfeedback', get_string('generalfeedback', 'question'),
            array('rows' => 10), $this->editoroptions);
        $mform->setType('generalfeedback', PARAM_RAW);
        $mform->addHelpButton('generalfeedback', 'generalfeedback', 'question');

        // Any questiontype specific fields.
        $this->definition_inner($mform);

        if (!empty($CFG->usetags)) {
            $mform->addElement('header', 'tagsheader', get_string('tags'));
            $mform->addElement('tags', 'tags', get_string('tags'));
        }

        if (!empty($this->question->id)) {
            $mform->addElement('header', 'createdmodifiedheader',
                get_string('createdmodifiedheader', 'question'));
            $a = new stdClass();
            if (!empty($this->question->createdby)) {
                $a->time = userdate($this->question->timecreated);
                $a->user = fullname($DB->get_record(
                    'user', array('id' => $this->question->createdby)));
            } else {
                $a->time = get_string('unknown', 'question');
                $a->user = get_string('unknown', 'question');
            }
            $mform->addElement('static', 'created', get_string('created', 'question'),
                get_string('byandon', 'question', $a));
            if (!empty($this->question->modifiedby)) {
                $a = new stdClass();
                $a->time = userdate($this->question->timemodified);
                $a->user = fullname($DB->get_record(
                    'user', array('id' => $this->question->modifiedby)));
                $mform->addElement('static', 'modified', get_string('modified', 'question'),
                    get_string('byandon', 'question', $a));
            }
        }

        $this->add_hidden_fields();

        $mform->addElement('hidden', 'qtype');
        $mform->setType('qtype', PARAM_ALPHA);

        $mform->addElement('hidden', 'makecopy');
        $mform->setType('makecopy', PARAM_INT);

        $buttonarray = array();
        $buttonarray[] = $mform->createElement('submit', 'updatebutton',
            get_string('savechangesandcontinueediting', 'question'));
        if ($this->can_preview()) {
            $previewlink = $PAGE->get_renderer('core_question')->question_preview_link(
                $this->question->id, $this->context, true);
            $buttonarray[] = $mform->createElement('static', 'previewlink', '', $previewlink);
        }

        $mform->addGroup($buttonarray, 'updatebuttonar', '', array(' '), false);
        $mform->closeHeaderBefore('updatebuttonar');

        $this->add_action_buttons(true, get_string('savechanges'));

        if ((!empty($this->question->id)) && (!($this->question->formoptions->canedit ||
                $this->question->formoptions->cansaveasnew))
        ) {
            $mform->hardFreezeAllVisibleExcept(array('categorymoveto', 'buttonar', 'currentgrp'));
        }
    }


    /**
     * Define the form for editing the question
     *
     * @param $mform
     */
    protected function definition_inner($mform)
    {
        global $PAGE, $OUTPUT;


        $menu = array(
            get_string('answersingleno', 'qtype_omeromultichoice'),
            get_string('answersingleyes', 'qtype_omeromultichoice'),
        );
        $mform->addElement('select', 'single',
            get_string('answerhowmany', 'qtype_omeromultichoice'), $menu);
        $mform->setDefault('single', 1);

        // Set answer types and the related selector
        $answer_type_menu = array();
        foreach (qtype_omeromultichoice::get_question_types() as $type) {
            array_push($answer_type_menu, get_string("qtype_$type", 'qtype_omeromultichoice'));
        }
        $mform->addElement('select', 'answertype',
            get_string('answer_type', 'qtype_omeromultichoice'), $answer_type_menu,
            array("onchange" => "M.omero_multichoice_helper._on_question_type_changed()")
        );
        $mform->setDefault('answertype', qtype_omeromultichoice::PLAIN_ANSWERS);


        $mform->addElement('advcheckbox', 'shuffleanswers',
            get_string('shuffleanswers', 'qtype_multichoice'), null, null, array(0, 1));
        $mform->addHelpButton('shuffleanswers', 'shuffleanswers', 'qtype_multichoice');
        $mform->setDefault('shuffleanswers', 1);

        $mform->addElement('select', 'answernumbering',
            get_string('answernumbering', 'qtype_multichoice'),
            qtype_multichoice::get_numbering_styles());
        $mform->setDefault('answernumbering', 'abc');



        $mform->addElement('html', '<div style="margin-top: 50px"></div>');
        $mform->addElement('header', 'omeroimageheader',
            get_string('omero_image_and_rois', 'qtype_omeromultichoice'), '');
        $mform->setExpanded('omeroimageheader', 1);



        $module = array(
            'name' => 'omero_multichoice_question_helper',
            'fullpath' => '/question/type/omeromultichoice/js/question-helper.js',
            'requires' => array('omemultichoice_qtype', 'node', 'node-event-simulate', 'core_dndupload'));
        $PAGE->requires->js_init_call('M.omero_multichoice_helper.init', array(), true, $module);

        $mform->addElement('omerofilepicker', 'omeroimagefilereference', " ", null,
            array('maxbytes' => 2048, 'accepted_types' => array('*'),
                'return_types' => array(FILE_EXTERNAL),
                'omero_image_server' => get_config('omero', 'omero_restendpoint'))
        );

        if ((isset($_REQUEST['answertype'])
                && $_REQUEST['answertype'] == qtype_omeromultichoice::ROI_BASED_ANSWERS) ||
            (isset($this->question->options)
                && $this->question->options->answertype == qtype_omeromultichoice::ROI_BASED_ANSWERS)
        ) {
            $mform->addElement("button", "add-roi-answer",
                get_string("add_roi_answer", "qtype_omeromultichoice"));
        }



//        $mform->addElement('html', '<div style="margin-top: 50px"></div>');
//        $mform->addElement('header', 'roitableinspectorheader',
//            get_string('roi_shape_inspector', 'qtype_omeromultichoice'), '');
//        $mform->setExpanded('roitableinspectorheader', 1);

        $mform->addElement('html', '


<div class="fitem">
    <div class="fitemtitle"><label for="roiShapeInspectorTable"></label></div>
<div class="felement" style="height: 200px;">


    <div id="toolbar">

        <button id="add-new-roi" class="btn btn-success" disabled>
            <i class="lyphicon glyphicon-plus"></i> Add
        </button>

        <button id="edit-roi" class="btn btn-warning" disabled>
            <i class="glyphicon glyphicon-edit"></i> Edit
        </button>

        <button id="remove" class="btn btn-danger" disabled>
            <i class="glyphicon glyphicon-remove"></i> Delete
        </button>

        <!-- Single button -->
        <div class="btn-group">
          <button type="button" class="btn btn-info  dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Group <span class="caret"></span>
          </button>
          <ul class="dropdown-menu">
            <li><a href="#">0</a></li>
            <li><a href="#">1</a></li>
            <li role="separator" class="divider"></li>
            <li><a href="#">Add Group</a></li>
          </ul>
        </div>
    </div>




    <table id="roiShapeInspectorTable"
           data-toolbar="#toolbar"
           data-search="true"
           data-height="400"
           data-show-refresh="true"
           data-show-toggle="true"
           data-show-columns="true"
           data-show-export="true"
           data-detail-view="true"
           data-minimum-count-columns="2"
           data-show-pagination-switch="true"
           data-pagination="true"
           data-id-field="id"
           data-page-list="[10, 25, 50, 100, ALL]"
           data-show-footer="false"
           data-side-pagination="server">
    </table>
  </div>
</div>

<script src="type/omeromultichoice/js/roi-shape-table.js" type="text/javascript"></script>
<script type="text/javascript">
var tc = new RoiShapeTableController(1);
</script>

<script type="text/javascript">
tc.initTable("roiShapeInspectorTable");
</script>
');

        $mform->addElement('html', '<div style="margin-top: 320px"></div>');
        $mform->addElement('header', 'answerhdr',
            get_string('answer_groups', 'qtype_omeromultichoice'), '');
        $mform->setExpanded('answerhdr', 1);


        $mform->addElement('html', '
        <div class="panel panel-success">
          <div class="panel-heading">
            <h3 class="panel-title">
                <a href="#" id="username" data-type="text" data-pk="1" data-title="Enter username">SUCCESS</a>
            </h3>
          </div>
          <div class="panel-body">
            Panel content

            <input type="text" class="form-control" aria-label="...">
            <div class="input-group">
  <div class="input-group-btn">
    <!-- Buttons -->
  </div>
  <input type="text" class="form-control" aria-label="...">
</div>

<div class="input-group">
  <input type="text" class="form-control" aria-label="...">
  <div class="input-group-btn">
    <!-- Buttons -->
  </div>
</div>
          </div>
          <div class="panel-footer">Panel footer</div>
        </div>
        ');


        $mform->addElement("html", '<div class="fitem" style="margin-top: 500px">');
        $mform->addElement("html", '<div class="fitemtitle"><label for="myTable">Custom iFrame:</label></div>');
        $mform->addElement("html", '<div class="felement">');

        $mform->addElement("html", '<a href="#" title="Header" data-toggle="popover" data-trigger="hover" data-content="Some content">Hover over me</a>');

        $mform->addElement('html', '
<!-- Button trigger modal -->
<button id="enableModal" type="button" class="btn btn-default btn-lg">
  Launch demo modal
</button>

<!-- Modal -->
<div id="myModal" class="fade modal" tabindex="-1" role="dialog" style="height: 175px;">
  <!--<div class="modal-dialog" style="width: auto; padding: 0" >-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Modal title</h4>
      </div>
      <div class="modal-body">
        <p>One fine body&hellip;</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-success" data-dismiss="modal">Save changes</button>
      </div>
    </div><!-- /.modal-content -->
  <!--</div><!-- /.modal-dialog -->
</div><!-- /.modal -->
');
        $mform->addElement("html", "</div></div>");


        //<!-- Structure to embed a new custom element to the form -->
        $mform->addElement("html", '<div class="fitem">');
        $mform->addElement("html", '<div class="fitemtitle"><label for="myTable">Custom iFrame:</label></div>');
        $mform->addElement("html", '<div class="felement"><iframe id="myTable" src=""></iframe></div>');
        $mform->addElement("html", "</div>");


        //<!-- Structure to embed a new custom text element to the form -->
        $mform->addElement("html", '<div class="fitem">');
        $mform->addElement("html", '<div class="fitemtitle"><label for="myCustomTextArea">CustomTextArea:</label></div>');
        $mform->addElement("html", '<div class="felement"><div id="myCustomTextAreaContainer" src=""><textarea id="myCustomTextArea"></textarea></div></div>', "Test");
        $mform->addElement("html", "</div>");


        //<!-- Structure to embed a new custom element to the form -->
        $mform->addElement("html", '<div class="fitem">');
        $mform->addElement("html", '<div class="fitemtitle"><label for="myTable">Prova:</label></div>');
        $mform->addElement("html", '<div class="felement"><iframe id="myTable" src=""></iframe></div>');
        $mform->addElement("html", "</div>");


        // Set the initial number of answers to 0; add answers one by one
        $this->add_per_answer_fields($mform, get_string('choiceno', 'qtype_multichoice', '{no}'),
            question_bank::fraction_options_full(), 4, 1);

        $this->add_combined_feedback_fields(true);
        $mform->disabledIf('shownumcorrect', 'single', 'eq', 1);

        // default interactive settings
        $this->add_interactive_settings(true, true);

        // Set the editing mode
        $mform->setType("editing_mode", PARAM_BOOL);
        $mform->addElement('hidden', 'editing_mode', 'true');

        //
        $mform->setType("current_selected_roi", PARAM_RAW);
        $mform->addElement('hidden', 'current_selected_roi', 'none');

        //
        $mform->setType("visible_rois", PARAM_RAW);
        $mform->addElement('hidden', 'visible_rois', 'none');

        //
        $mform->setType("roi_based_answers", PARAM_RAW);
        $mform->addElement('hidden', 'roi_based_answers', 'none');

        //
        $mform->setType("available_rois", PARAM_RAW);
        $mform->addElement('hidden', 'available_rois', 'none');

        //
        $mform->setType("omero_image_url", PARAM_RAW);
        $mform->addElement('hidden', 'omero_image_url', 'none');

        // Initialize hidden textarea for localized strings
        $this->add_localized_fields_for($this->localized_strings);//
    }


    protected function add_localized_fields_for($string_names)
    {
        $mform = $this->_form;
        // Initialize hidden textarea for localized strings
        $languages = get_string_manager()->get_list_of_translations();
        foreach ($string_names as $localized_string) {
            foreach ($languages as $lang_id => $lang_string) {
                $mform->addElement('textarea', $localized_string . "_" . $lang_id,
                    "", array("style" => "display: none;", "lang" => $lang_id, "class" => "$localized_string"));
            }
        }
    }

    /**
     * Language string to use for 'Add {no} more {whatever we call answers}'.
     */
    protected function get_more_choices_string()
    {
        return get_string('add_roi_answer', 'qtype_omeromultichoice');
    }


    /**
     * Build the repeated elements of the form
     * (i.e., form elements for setting answers)
     *
     * @param $mform
     * @param $label
     * @param $gradeoptions
     * @param $repeatedoptions
     * @param $answersoption
     * @return array
     */
    protected function get_per_answer_fields($mform, $label, $gradeoptions,
                                             &$repeatedoptions, &$answersoption)
    {
        if ((isset($_REQUEST['answertype']) && $_REQUEST['answertype'] == qtype_omeromultichoice::ROI_BASED_ANSWERS) ||
            (isset($this->question->options) && $this->question->options->answertype == qtype_omeromultichoice::ROI_BASED_ANSWERS)
        )
            return $this->get_per_roi_based_answer_fields($mform, $label, $gradeoptions,
                $repeatedoptions, $answersoption);
        else return $this->get_per_plaintext_answer_fields($mform, $label, $gradeoptions,
            $repeatedoptions, $answersoption);
    }

    protected function get_per_plaintext_answer_fields($mform, $label, $gradeoptions,
                                                       &$repeatedoptions, &$answersoption)
    {
        $repeated = array();
        $repeated[] = $mform->createElement('editor', 'answer',
            $label, array('rows' => 1), $this->editoroptions);
        $repeated[] = $mform->createElement('select', 'fraction',
            get_string('grade'), $gradeoptions);
        $repeated[] = $mform->createElement('editor', 'feedback',
            get_string('feedback', 'question'), array('rows' => 1), $this->editoroptions);
        $repeatedoptions['answer']['type'] = PARAM_RAW;
        $repeatedoptions['fraction']['default'] = 0;
        $answersoption = 'answers';

        $languages = get_string_manager()->get_list_of_translations();

        foreach ($languages as $lang_id => $lang_string) {
            $repeated[] = $mform->createElement('textarea', "answer" . "_" . $lang_id,
                "", array("style" => "display: none; margin: 0; padding: 0;", "lang" => $lang_id, "class" => "answer"));
            $repeated[] = $mform->createElement('textarea', "feedback" . "_" . $lang_id,
                "", array("style" => "display: none; margin: 0; padding: 0;", "lang" => $lang_id, "class" => "feedback"));
        }

        return $repeated;
    }


    /**
     * Build the repeated elements of the form
     * (i.e., form elements for setting answers)
     *
     * @param $mform
     * @param $label
     * @param $gradeoptions
     * @param $repeatedoptions
     * @param $answersoption
     * @return array
     */
    protected function get_per_roi_based_answer_fields($mform, $label, $gradeoptions,
                                                       &$repeatedoptions, &$answersoption)
    {
        $repeated = array();

        $repeated[] = $mform->createElement('html', '<div class="fitem roi-based-answer">');

        // ROI choice label
        $repeated[] = $mform->createElement('static', "description", $label);

        $repeated[] = $mform->createElement('html', '<div class="felement felement-roi-based-answer">');

        // Main DIV container
        $repeated[] = $mform->createElement('html', '<div class="omeromultichoice-qanswer-roi-based-answer-container">');


        // hidden field for storing answer/roi ID
        $repeated[] = $mform->createElement('hidden', 'answer', "none");

        // ROI details
        $repeated[] = $mform->createElement('html', '<div class="omeromultichoice-qanswer-container">');
        $repeated[] = $mform->createElement('html', '<div class="omeromultichoice-qanswer-roi-container">');

        // Image container
        //$repeated[] = $mform->createElement('html', '<div class="omeromultichoice-qanswer-roi-image-container">');
        //$repeated[] = $mform->createElement('html', '<img src="" class="roi_thumb shape_thumb" style="vertical-align: top;" color="f00" width="150px" height="150px">');
        //$repeated[] = $mform->createElement('html', '</div>'); // -> Close 'qanswer-roi-image-container

        // ROI description
        $repeated[] = $mform->createElement('html', '<div class="omeromultichoice-qanswer-roi-details-container">');
        $repeated[] = $mform->createElement('html', '<div class="omeromultichoice-qanswer-roi-details-text-container">');
        // Adds ROI description fields
        $roi_description_fields = array("id", "comment", "type", "width", "height");
        foreach ($roi_description_fields as $field) {
            $repeated[] = $mform->createElement('html', '<div class="omeromultichoice-qanswer-roi-details-text">');
            $repeated[] = $mform->createElement('html', '<span class="roi-field-label">' . get_string("roi_" . $field, "qtype_omeromultichoice") . ':</span>');
            $repeated[] = $mform->createElement('html', '<span class="roi-field-value">...</span>');
            $repeated[] = $mform->createElement('html', '</div>');
        }

        $repeated[] = $mform->createElement('html', '</div>'); // -> Close 'details-text-container'
        $repeated[] = $mform->createElement('html', '</div>'); // -> Close 'qanswer-roi-details-container
        $repeated[] = $mform->createElement('html', '</div>'); // -> Close 'qanswer-roi-container'

        // ROI-based answer grade selector
        $repeated[] = $mform->createElement('select', 'fraction', get_string('grade'), $gradeoptions);

        // Feedback editor
        $repeated[] = $mform->createElement('editor', 'feedback',
            get_string('feedback', 'question'), array('rows' => 1), $this->editoroptions);

        $repeated[] = $mform->createElement('html', '</div>'); // -> Close 'qanswer-roi-details-container'
        $repeated[] = $mform->createElement('html', '</div>'); // -> Close 'qanswer-roi-based-answer-container'

        $repeated[] = $mform->createElement('html', '</div>'); // -> Close 'felement'

        $repeated[] = $mform->createElement('html', '</div>'); // -> Close 'fitem'

        // Default values
        $repeatedoptions['answer']['type'] = PARAM_RAW;
        $repeatedoptions['fraction']['default'] = 0;
        $answersoption = 'answers';

        return $repeated;
    }


    protected function data_preprocessing($question)
    {
        $question = parent::data_preprocessing($question);
        if (isset($this->question->options) && $question->options->answertype == qtype_omeromultichoice::ROI_BASED_ANSWERS) {
            $question = $this->data_preprocessing_answers($question, false);
        } else {
            $question = $this->data_preprocessing_answers($question, true);
        }
        $question = $this->data_preprocessing_combined_feedback($question, true);
        $question = $this->data_preprocessing_hints($question, true, true);

        if (!empty($question->options)) {
            $question->single = $question->options->single;
            $question->shuffleanswers = $question->options->shuffleanswers;
            $question->answernumbering = $question->options->answernumbering;

            // Prepare the roi_based_answers field
            if (isset($this->question->options)
                && $question->options->answertype == qtype_omeromultichoice::ROI_BASED_ANSWERS
            ) {
                $roi_based_answers = [];
                foreach ($question->options->answers as $answer) {
                    array_push($roi_based_answers, $answer->answer);
                }
            }

            $question->visible_rois = $question->options->visiblerois;
            if (isset($roi_based_answers)) {
                $question->roi_based_answers = implode(",", $roi_based_answers);
            }
            $question->answertype = $question->options->answertype;
            $question->omero_image_url = $question->options->omeroimageurl;
        }
        return $question;
    }


    public function get_data()
    {
        $data = parent::get_data();
        $this->update_roi_based_answers($data);
        $this->update_localized_strings($data);
        return $data;
    }


    private function update_roi_based_answers(&$data)
    {
        if (!empty($data) && isset($_REQUEST['answertype']) &&
            $_REQUEST['answertype'] == qtype_omeromultichoice::ROI_BASED_ANSWERS
        ) {
            if (is_array($data)) {
                $answers = &$data["answer"];
            } else {
                $answers = &$data->{"answer"};
            }
            if (isset($_POST["roi_based_answers"])) {
                $roi_based_answers_el = $_POST["roi_based_answers"];
                $roi_based_answers = explode(",", $roi_based_answers_el);
                foreach ($roi_based_answers as $k => $a) {
                    $answers[$k] = array("text" => "$a", "format" => 1, "itemid" => "x");
                }
            }
        }
    }


    private function update_localized_strings(&$data)
    {
        $languages = array();
        $languages += get_string_manager()->get_list_of_translations();

        if (!empty($data)) {
            foreach ($this->localized_strings as $localized_string) {

                if (is_array($data)) {
                    $obj = &$data["$localized_string"];
                } else {
                    $obj = &$data->{"$localized_string"};
                }

                $text = "";
                foreach ($languages as $lang_id => $lang_description) {
                    $txt = "";
                    if (is_array($data))
                        $txt = &$data[$localized_string . "_" . $lang_id];
                    else
                        $txt = &$data->{$localized_string . "_" . $lang_id};
                    // removes YUI ids
                    $txt = preg_replace('/id="([^"]+)"/i', "", $txt);
                    $text .= '<span class="multilang" lang="' . $lang_id . '">' . $txt . '</span>';
                }
                if (isset($obj["text"]))
                    $obj["text"] = $text;
                else $obj = $text;
            }

            if (!isset($_REQUEST['answertype']) || $_REQUEST['answertype'] != qtype_omeromultichoice::ROI_BASED_ANSWERS) {
                if (is_array($data)) {
                    $answer = &$data["answer"];
                } else {
                    $answer = &$data->{"answer"};
                }

                for ($i = 0; $i < count($answer); $i++) {
                    $answer[$i]["text"] = "";
                    foreach ($languages as $lang_id => $lang_description) {
                        if (is_array($data)) {
                            $answer_lang = &$data["answer_" . $lang_id];
                        } else {
                            $answer_lang = &$data->{"answer_" . $lang_id};
                        }
                        if (isset($answer_lang[$i]) && !empty($answer_lang[$i])) {
                            // removes YUI ids
                            $txt = preg_replace('/id="([^"]+)"/i', "", $answer_lang[$i]);
                            $answer[$i]["text"] .= '<span class="multilang" lang="' . $lang_id . '">' . $txt . '</span>';
                        }
                    }
                }
            }
        }
    }

    public function set_data($question)
    {
        foreach ($this->localized_strings as $localized_string)
            $this->set_localized_string($question, $localized_string);

        $count = 0;
        if (isset($question->options) && isset($question->options->answers)) {
            foreach ($question->options->answers as $i => $answer) {
                $matches = $this->getLocaleStrings($answer->answer);
                if (count($matches[0]) > 0) {
                    for ($i = 0; $i < count($matches); $i++) {
                        $language = $matches[$i][0];
                        $localized_string = $matches[$i][1];
                        if (!isset($question->{"answer_" . $language}))
                            $question->{"answer_" . $language} = array();
                        array_push($question->{"answer_" . $language}, $localized_string);
                    }
                } else {
                    $languages = get_string_manager()->get_list_of_translations();
                    foreach ($languages as $language => $lang_name) {
                        if (!isset($question->{"answer_" . $language}))
                            $question->{"answer_" . $language} = array();
                        if (strcmp($language, current_language()) === 0) {
                            array_push($question->{"answer_" . $language}, $answer->answer);
                        } else {
                            array_push($question->{"answer_" . $language}, "");
                        }
                    }
                }
                $count++;
            }
        }

        parent::set_data($question);
    }


    /**
     * Returns the list of span[@multilang]
     * contained within the given <pre>$html</pre>
     *
     * @param $html
     * @return array array of pairs (language, string)
     */
    private function getLocaleStrings($html)
    {
        $result = array();
        $dom = new DOMDocument();
        $dom->strictErrorChecking = FALSE;
        $dom->loadHTML('<?xml version="1.0" encoding="UTF-8"?><html><body>' . $html . '</body></html>');
        $finder = new DomXPath($dom);
        $classname = "multilang";
        $nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
        foreach ($nodes as $node) {
            $data = [$node->getAttribute("lang"), $this->DOMinnerHTML($node)];
            array_push($result, $data);
        }
        return $result;
    }

    /**
     * Returns the innerHTML of a given DOMNode
     *
     * @param DOMNode $element
     * @return string
     */
    private function DOMinnerHTML(DOMNode $element)
    {
        $innerHTML = "";
        $children = $element->childNodes;

        foreach ($children as $child) {
            $innerHTML .= $element->ownerDocument->saveHTML($child);
        }

        return $innerHTML;
    }


    public function set_localized_string($obj, $property_name)
    {
        if ($obj == null) return;

        $languages = get_string_manager()->get_list_of_translations();
        $query_obj = null;

        if (isset($obj->{$property_name}))
            $query_obj = $obj;

        else if (isset($obj->options) && isset($obj->options->{$property_name}))
            $query_obj = $obj->options;

        if ($query_obj != null) {
            $matches = $this->getLocaleStrings($query_obj->{$property_name});
            if (count($matches[0]) == 0) {
                foreach ($languages as $language => $lang_name) {
                    if (strcmp($language, current_language()) === 0) {
                        $obj->{$property_name . "_" . $language} = $query_obj->{$property_name};
                    } else {
                        $obj->{$property_name . "_" . $language} = "";
                    }
                }
            } else {
                for ($i = 0; $i < count($matches); $i++) {
                    $language = $matches[$i][0];
                    $localized_string = $matches[$i][1];
                    $obj->{$property_name . "_" . $language} = $localized_string;
                }
            }
        } else {
            foreach ($languages as $language) {
                $obj->{$property_name . "_" . $language} = "";
            }
        }

        if (isset($obj->{$property_name . "_" . current_language()})) {
            $obj->{$property_name} = $obj->{$property_name . "_" . current_language()};
        }
    }


    protected
    function data_preprocessing_answers($question, $withanswerfiles = false)
    {
        if (empty($question->options->answers)) {
            return $question;
        }

        $key = 0;
        foreach ($question->options->answers as $answer) {
            if ($withanswerfiles) {
                // Prepare the feedback editor to display files in draft area.
                $draftitemid = file_get_submitted_draft_itemid('answer[' . $key . ']');
                $question->answer[$key]['text'] = file_prepare_draft_area(
                    $draftitemid,          // Draftid
                    $this->context->id,    // context
                    'question',            // component
                    'answer',              // filarea
                    !empty($answer->id) ? (int)$answer->id : null, // itemid
                    $this->fileoptions,    // options
                    $answer->answer        // text.
                );
                $question->answer[$key]['itemid'] = $draftitemid;
                $question->answer[$key]['format'] = $answer->answerformat;
            } else {
                $question->answer[$key] = $answer->answer;
            }

            $question->fraction[$key] = 0 + $answer->fraction;
            $question->feedback[$key] = array();

            // Evil hack alert. Formslib can store defaults in two ways for
            // repeat elements:
            //   ->_defaultValues['fraction[0]'] and
            //   ->_defaultValues['fraction'][0].
            // The $repeatedoptions['fraction']['default'] = 0 bit above means
            // that ->_defaultValues['fraction[0]'] has already been set, but we
            // are using object notation here, so we will be setting
            // ->_defaultValues['fraction'][0]. That does not work, so we have
            // to unset ->_defaultValues['fraction[0]'].
            unset($this->_form->_defaultValues["fraction[{$key}]"]);

            // Prepare the feedback editor to display files in draft area.
            $draftitemid = file_get_submitted_draft_itemid('feedback[' . $key . ']');
            $question->feedback[$key]['text'] = file_prepare_draft_area(
                $draftitemid,          // Draftid
                $this->context->id,    // context
                'question',            // component
                'answerfeedback',      // filarea
                !empty($answer->id) ? (int)$answer->id : null, // itemid
                $this->fileoptions,    // options
                $answer->feedback      // text.
            );
            $question->feedback[$key]['itemid'] = $draftitemid;
            $question->feedback[$key]['format'] = $answer->feedbackformat;
            $key++;
        }

        // Now process extra answer fields.
        $extraanswerfields = question_bank::get_qtype($question->qtype)->extra_answer_fields();
        if (is_array($extraanswerfields)) {
            // Omit table name.
            array_shift($extraanswerfields);
            $question = $this->data_preprocessing_extra_answer_fields($question, $extraanswerfields);
        }

        return $question;
    }

    protected
    function get_hint_fields($withclearwrong = false, $withshownumpartscorrect = false)
    {
        list($repeated, $repeatedoptions) = parent::get_hint_fields($withclearwrong, $withshownumpartscorrect);
        $repeatedoptions['hintclearwrong']['disabledif'] = array('single', 'eq', 1);
        $repeatedoptions['hintshownumcorrect']['disabledif'] = array('single', 'eq', 1);
        return array($repeated, $repeatedoptions);
    }

    /**
     * Perform the form validation
     *
     * @param $data
     * @param $files
     * @return mixed
     */
    public
    function validation($data, $files)
    {
        if (isset($_REQUEST['answertype']) && $_REQUEST['answertype'] == qtype_omeromultichoice::ROI_BASED_ANSWERS) {
            $this->update_roi_based_answers($data);
        }

        if ($_REQUEST['noanswers'] < 3)
            $errors["generic"] = "At least 2 answers";

        // checks specific errors
        $errors = array();
        if (!isset($data["answer"]) || count($data["answer"]) < 3)
            $errors["generic"] = "At least 2 answers";

        // question multichoice validation
        if ($_REQUEST['noanswers'] > 0)
            $errors = parent::validation($data, $files);

        // return found errors
        return $errors;
    }


    ///////////////////////////////////////////////////////////////////////////////////////////////////////////


    /**
     * Add a set of form fields, obtained from get_per_answer_fields, to the form,
     * one for each existing answer, with some blanks for some new ones.
     * @param object $mform the form being built.
     * @param $label the label to use for each option.
     * @param $gradeoptions the possible grades for each answer.
     * @param $minoptions the minimum number of answer blanks to display.
     *      Default QUESTION_NUMANS_START.
     * @param $addoptions the number of answer blanks to add. Default QUESTION_NUMANS_ADD.
     */
    protected
    function add_per_answer_fields(&$mform, $label, $gradeoptions,
                                   $minoptions = QUESTION_NUMANS_START, $addoptions = QUESTION_NUMANS_ADD)
    {
        $mform->addElement('header', 'answerhdr',
            get_string('answers', 'question'), '');
        $mform->setExpanded('answerhdr', 1);
        $answersoption = '';
        $repeatedoptions = array();
        $repeated = $this->get_per_answer_fields($mform, $label, $gradeoptions,
            $repeatedoptions, $answersoption);

        if (isset($this->question->options)) {
            $repeatsatstart = count($this->question->options->$answersoption);
        } else {
            $repeatsatstart = $minoptions;
        }

        $this->repeat_elements($repeated, $repeatsatstart, $repeatedoptions,
            'noanswers', 'addanswers', $addoptions,
            $this->get_more_choices_string(), true);
    }


    ///////////////////////////////////////////////////////////////////////////////////////////////////////////


    /**
     * FIXME: is it really needed?
     * Override method to add a repeating group of elements to a form,
     * only for disabling 'addanswers' button
     *
     * @param array $elementobjs Array of elements or groups of elements that are to be repeated
     * @param int $repeats no of times to repeat elements initially
     * @param array $options a nested array. The first array key is the element name.
     *    the second array key is the type of option to set, and depend on that option,
     *    the value takes different forms.
     *         'default'    - default value to set. Can include '{no}' which is replaced by the repeat number.
     *         'type'       - PARAM_* type.
     *         'helpbutton' - array containing the helpbutton params.
     *         'disabledif' - array containing the disabledIf() arguments after the element name.
     *         'rule'       - array containing the addRule arguments after the element name.
     *         'expanded'   - whether this section of the form should be expanded by default. (Name be a header element.)
     *         'advanced'   - whether this element is hidden by 'Show more ...'.
     * @param string $repeathiddenname name for hidden element storing no of repeats in this form
     * @param string $addfieldsname name for button to add more fields
     * @param int $addfieldsno how many fields to add at a time
     * @param string $addstring name of button, {no} is replaced by no of blanks that will be added.
     * @param bool $addbuttoninside if true, don't call closeHeaderBefore($addfieldsname). Default false.
     * @return int no of repeats of element in this page
     */
    function repeat_elements($elementobjs, $repeats, $options, $repeathiddenname,
                             $addfieldsname, $addfieldsno = 5, $addstring = null, $addbuttoninside = false)
    {
        if ($addstring === null) {
            $addstring = get_string('addfields', 'form', $addfieldsno);
        } else {
            $addstring = str_ireplace('{no}', $addfieldsno, $addstring);
        }
        $repeats = optional_param($repeathiddenname, $repeats, PARAM_INT);
        $addfields = optional_param($addfieldsname, '', PARAM_TEXT);
        if (!empty($addfields)) {
            $repeats += $addfieldsno;
        }
        $mform =& $this->_form;
        $mform->registerNoSubmitButton($addfieldsname);
        $mform->addElement('hidden', $repeathiddenname, $repeats);
        $mform->setType($repeathiddenname, PARAM_INT);
        //value not to be overridden by submitted value
        $mform->setConstants(array($repeathiddenname => $repeats));
        $namecloned = array();
        for ($i = 0; $i < $repeats; $i++) {
            foreach ($elementobjs as $elementobj) {
                $elementclone = fullclone($elementobj);
                $this->repeat_elements_fix_clone($i, $elementclone, $namecloned);

                if ($elementclone instanceof HTML_QuickForm_group && !$elementclone->_appendName) {
                    foreach ($elementclone->getElements() as $el) {
                        $this->repeat_elements_fix_clone($i, $el, $namecloned);
                    }
                    $elementclone->setLabel(str_replace('{no}', $i + 1, $elementclone->getLabel()));
                }

                $mform->addElement($elementclone);
            }
        }
        for ($i = 0; $i < $repeats; $i++) {
            foreach ($options as $elementname => $elementoptions) {
                $pos = strpos($elementname, '[');
                if ($pos !== FALSE) {
                    $realelementname = substr($elementname, 0, $pos) . "[$i]";
                    $realelementname .= substr($elementname, $pos);
                } else {
                    $realelementname = $elementname . "[$i]";
                }
                foreach ($elementoptions as $option => $params) {
                    switch ($option) {
                        case 'default' :
                            $mform->setDefault($realelementname, str_replace('{no}', $i + 1, $params));
                            break;
                        case 'helpbutton' :
                            $params = array_merge(array($realelementname), $params);
                            call_user_func_array(array(&$mform, 'addHelpButton'), $params);
                            break;
                        case 'disabledif' :
                            foreach ($namecloned as $num => $name) {
                                if ($params[0] == $name) {
                                    $params[0] = $params[0] . "[$i]";
                                    break;
                                }
                            }
                            $params = array_merge(array($realelementname), $params);
                            call_user_func_array(array(&$mform, 'disabledIf'), $params);
                            break;
                        case 'rule' :
                            if (is_string($params)) {
                                $params = array(null, $params, null, 'client');
                            }
                            $params = array_merge(array($realelementname), $params);
                            call_user_func_array(array(&$mform, 'addRule'), $params);
                            break;

                        case 'type':
                            $mform->setType($realelementname, $params);
                            break;

                        case 'expanded':
                            $mform->setExpanded($realelementname, $params);
                            break;

                        case 'advanced' :
                            $mform->setAdvanced($realelementname, $params);
                            break;
                    }
                }
            }
        }

        // FIXME: disable the button for adding new repeated elements
        $mform->addElement('submit', $addfieldsname, $addstring);

        if (!$addbuttoninside) {
            $mform->closeHeaderBefore($addfieldsname);
        }

        return $repeats;
    }
}

