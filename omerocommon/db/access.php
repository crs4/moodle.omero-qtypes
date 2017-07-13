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
 * Defines capabilities for the 'qtype_omerocommon' question type.
 *
 * @package    qtype
 * @subpackage omerocommon
 * @copyright  2015-2016 CRS4
 * @license    https://opensource.org/licenses/mit-license.php MIT license
 */

defined('MOODLE_INTERNAL') || die();


$CAPABILITY_PREFIX = "mod/qtype_omerocommon";
$QUESTION_AUTHOR = "$CAPABILITY_PREFIX:author";
$QUESTION_TRANSLATOR = "$CAPABILITY_PREFIX:translator";


$capabilities = array(

    $QUESTION_AUTHOR => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'teacher' => CAP_ALLOW
        )
    )
);


$languages = get_string_manager()->get_list_of_languages();
foreach ($languages as $lang_code => $language) {
    $locale_translator_capability = get_question_translator_capability($lang_code);
    $capabilities[$locale_translator_capability] = array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'teacher' => CAP_ALLOW
        )
    );
}

function get_question_translator_capability($lang_code)
{
    global $QUESTION_TRANSLATOR;
    return $QUESTION_TRANSLATOR . "_" . $lang_code;
}

function is_question_author($context, $lang_code = null)
{
    global $CFG, $QUESTION_AUTHOR;
    if (!has_capability($QUESTION_AUTHOR, $context))
        return false;

    // set the default system language if $lang_code is undefined
    if (is_null($lang_code))
        $lang_code = $CFG->lang;

    // check translation capabilities
    $lang_codes = is_string($lang_code) ? array($lang_code) : $lang_code;
    foreach ($lang_codes as $lang) {
        if (!array_key_exists($lang))
            throw new RuntimeException("The language code '$lang' is not supported");
        if (!has_capability(get_question_translator_capability($lang), $context))
            return false;
    }
    return true;
}


function is_question_translator($context, $lang_code=null)
{
    if (is_null($lang_code)) $lang_codes = array_keys(get_string_manager()->get_list_of_languages());
    else $lang_codes = is_string($lang_code) ? array($lang_code) : $lang_code;
    foreach ($lang_codes as $lang) {
        if (!array_key_exists($lang))
            throw new RuntimeException("The language code '$lang' is not supported");
        if (!has_capability(get_question_translator_capability($lang), $context))
            return false;
    }
    return true;
}

