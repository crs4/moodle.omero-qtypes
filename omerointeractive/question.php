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
 * omeromultichoice question definition class.
 *
 * @package    qtype
 * @subpackage omeromultichoice
 * @copyright  2015 CRS4
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later // FIXME: check the licence
 */


defined('MOODLE_INTERNAL') || die();


require_once($CFG->dirroot . '/question/type/omerocommon/question.php');


/**
 * Represents a omeromultichoice question.
 *
 * @copyright  2015 CRS4
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later // FIXME: check the licence
 */


class qtype_omerointeractive_single_question extends qtype_multichoice_single_question {
    public function get_renderer(moodle_page $page) {
        return $page->get_renderer('qtype_omerointeractive', 'single');
    }
}


class qtype_omerointeractive_multi_question extends qtype_multichoice_multi_question {
    public function get_renderer(moodle_page $page) {
        return $page->get_renderer('qtype_omerointeractive', 'multi');
    }
}