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
 * omerocommon question definition class.
 *
 * @package    qtype
 * @subpackage omerocommon
 * @copyright  2015-2016 CRS4
 * @license    https://opensource.org/licenses/mit-license.php MIT license
 */


defined('MOODLE_INTERNAL') || die();


require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/engine/lib.php');
require_once($CFG->dirroot . '/question/type/multichoice/questiontype.php');
require_once($CFG->dirroot . '/question/type/multichoice/question.php');


/**
 * Represents a basic omero question type.
 */
abstract class qtype_omerocommon_question extends qtype_multichoice_base
{
    static public function localize_text($text, $format, $qa, $component, $filearea, $itemid,
                                         $clean = false)
    {
        $language = current_language();
        $dom = new DOMDocument();
        $dom->strictErrorChecking = FALSE;
        $dom->loadHTML('<?xml version="1.0" encoding="UTF-8"?><html><body>' . $text . '</body></html>');
        $finder = new DomXPath($dom);
        $classname = "multilang";
        $nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
        foreach ($nodes as $node) {
            if (strcmp($node->getAttribute("lang"), $language) == 0) {
                $text = self::DOMinnerHTML(($node));
                break;
            }
        }
        return $text;
    }


    static private function DOMinnerHTML(DOMNode $element)
    {
        $innerHTML = "";
        $children = $element->childNodes;
        foreach ($children as $child) {
            $innerHTML .= $element->ownerDocument->saveHTML($child);
        }

        return $innerHTML;
    }
}


abstract class qtype_omerocommon_single_question extends qtype_multichoice_single_question
{
    protected function format_multilanguage_text($qa, $field, $format, $language = null)
    {
        $language = empty($language) ? current_language() : $language;
        $text = qtype_omerocommon_renderer_helper::filter_lang($this->{$field}, $language);
        return $this->format_text($text, $format, $qa, 'question', $field, $this->id);
    }

    public function format_questiontext($qa, $language = null)
    {
        return $this->format_multilanguage_text($qa, "questiontext", $this->questiontextformat, $language);
    }

    public function format_generalfeedback($qa, $language = null)
    {
        return $this->format_multilanguage_text($qa, "generalfeeback", $this->questiontextformat, $language);
    }

}

abstract class qtype_omerocommon_multi_question extends qtype_multichoice_multi_question
{
    public function format_text($text, $format, $qa, $component, $filearea, $itemid,
                                $clean = false)
    {
        $text = qtype_omerocommon_question::localize_text($text, $format, $qa, $component, $filearea, $itemid, $clean);
        return parent::format_text($text, $format, $qa, $component, $filearea, $itemid, $clean);
    }
}