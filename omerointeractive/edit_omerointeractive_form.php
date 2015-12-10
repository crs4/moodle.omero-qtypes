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
 * Defines the editing form for the omerointeractive question type.
 *
 * @package    qtype
 * @subpackage omerointeractive
 * @copyright  2015 CRS4
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later //FIXME: check the licence
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/omerocommon/edit_omerocommon_form.php');

/**
 * omerointeractive question editing form definition.
 *
 * @copyright  2015 CRS4
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later // FIXME: check the licence
 */
class qtype_omerointeractive_edit_form extends qtype_omerocommon_edit_form
{

    public function qtype()
    {
        return 'omerointeractive';
    }

    /**
     * Updates the CSS/JS requirements for this form
     */
    protected function set_form_requirements()
    {
        parent::set_form_requirements();
        init_js_modules("omerointeractive");
    }

    protected function definition()
    {
        global $CFG, $PAGE;
        parent::definition();


        //--------------------------------------------------------------------------------------------
        //FIXME: just for debugging
        //$PAGE->requires->js(new moodle_url("$CFG->wwwroot/repository/omero/viewer/viewer-model.js"));
        //--------------------------------------------------------------------------------------------

        global $PAGE;
        $PAGE->requires->js_call_amd("qtype_omerointeractive/question-editor-interactive", "main",
            array(
                "id_answergroupsheader",
                question_bank::fraction_options_full()
            )
        );
    }

    protected function define_answer_section_header()
    {
        return array(
            "answergroupsheader",
            get_string('answer_groups', 'qtype_omerointeractive')
        );
    }


    protected function define_roi_table_inspector()
    {
        $mform = $this->_form;
        $mform->addElement('html', '

                        <div id="omero-image-viewer-toolbar" class="hidden">
                        <div class="checkboxx">
                          <div style="display: inline-block;">
                          <a id="omero-image-properties-update" href="javascript:void(0)" title="Update image center">
                            <i class="glyphicon glyphicon-screenshot"></i>
                          </a>
                          <span id="omero-image-viewer-properties">x: 123123, y: 12312312, zm: 123123123</span>
                          </div>
                          <div id="omero-image-view-lock-container">
                              <label for="omero-image-view-lock">
                                image navigation:
                              </label>
                              <input id="omero-image-view-lock" name="omero-image-view-lock" data-toggle="toggle"
                                     type="checkbox" data-onstyle="success" data-offstyle="danger">
                          </div>
                        </div>


            <script>
                $(\'#omero-image-view-lock\').bootstrapToggle($(\'[name^=omeroimagelocked]\').val()=="1" ? "on" : "off");
                $(\'#omero-image-view-lock\').change(function(){
                    M.qtypes.omerocommon.QuestionEditorBase.getInstance().onLockImageChanged($(this).is(\':checked\'));
                });
            </script>
        ');
        $mform->addElement('html', '</div>');


        $mform->addElement('header', 'roitableinspectorheader',
            get_string('roi_shape_inspector', 'qtype_omeromultichoice'), '');
        $mform->setExpanded('roitableinspectorheader', 0);

        $mform->addElement('html', '
            <div class="fitem" id="roi-shape-inspector-table-container" class="hidden">
                <div class="fitemtitle"><label for="roi-shape-inspector-table"></label></div>
                <div class="felement">

                <!-- TOOLBAR -->
                <div id="roi-shape-inspector-table-toolbar" class="hidden">

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
                <!-- ROI TABLE -->
                <table id="roi-shape-inspector-table"
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
');
    }

}
