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

require_once($CFG->dirroot . '/question/type/omerocommon/edit_omerocommon_form.php');

/**
 * omeromultichoice question editing form definition.
 *
 * @copyright  2015 CRS4
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later // FIXME: check the licence
 */
class qtype_omeromultichoice_edit_form extends qtype_omerocommon_edit_form
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
     * Updates the CSS/JS requirements for this form
     */
    protected function set_form_requirements()
    {
        parent::set_form_requirements();
        init_js_modules("omeromultichoice");
    }




    protected function definition()
    {
        global $PAGE;
        parent::definition();

        //--------------------------------------------------------------------------------------------
        //FIXME: just for debugging
        global $CFG, $PAGE;
        $PAGE->requires->js(new moodle_url("$CFG->wwwroot/repository/omero/viewer/viewer-model.js"));
        //--------------------------------------------------------------------------------------------
    }

    protected function define_answers_section()
    {
        $mform = $this->_form;

        // header
        $mform->addElement('header', 'answergroupsheader',
            get_string('answer_groups', 'qtype_omerointeractive'));
        // call default behaviour
        parent::define_answers_section();
    }
}
