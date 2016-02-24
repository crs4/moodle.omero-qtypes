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
 * UI Controller of the multichoice-question editor.
 *
 * @package    qtype
 * @subpackage omeromultichoice
 * @copyright  2015-2016 CRS4
 * @license    https://opensource.org/licenses/mit-license.php MIT license
 */
define([
        'jquery',
        'qtype_omerocommon/moodle-forms-utils',
        'qtype_omerocommon/multilanguage-element',
        'qtype_omerocommon/multilanguage-attoeditor',
        'qtype_omerocommon/question-editor-base',
        'qtype_omeromultichoice/answer-plaintext'
    ],

    /* jshint curly: false */
    /* globals console */
    function ($) {

        // defines the basic package
        M.qtypes = M.qtypes || {};

        // defines the specific package of this module
        M.qtypes.omeromultichoice = M.qtypes.omeromultichoice || {};

        /**
         * Builds a new instance
         *
         * @constructor
         */
        M.qtypes.omeromultichoice.QuestionEditorMultichoice = function () {

            // parent constructor
            M.qtypes.omerocommon.QuestionEditorBase.call(this);
        };

        // inherit
        M.qtypes.omeromultichoice.QuestionEditorMultichoice.prototype = new M.qtypes.omerocommon.QuestionEditorBase();

        M.qtypes.omeromultichoice.QuestionEditorMultichoice.getInstance = function () {
            if (!M.qtypes.omerocommon.QuestionEditorBase.instance) {
                M.qtypes.omerocommon.QuestionEditorBase.instance =
                    new M.qtypes.omeromultichoice.QuestionEditorMultichoice();
            }
            return M.qtypes.omerocommon.QuestionEditorBase.instance;
        };

        // A local reference to the prototype
        var prototype = M.qtypes.omeromultichoice.QuestionEditorMultichoice.prototype;

        // correct the constructor
        prototype.constructor = M.qtypes.omeromultichoice.QuestionEditorMultichoice;

        // parent prototype
        prototype.parent = M.qtypes.omerocommon.QuestionEditorBase.prototype;

        prototype.buildAnswer = function (answer_number, fraction_options) {
            return new M.qtypes.omeromultichoice.AnswerPlaintext(this._answers_section_id, answer_number, fraction_options);
        };

        prototype.validate = function () {
            return this.parent.validate.call(this);
        };

        M.qtypes.omeromultichoice.QuestionEditorMultichoice.main = function (answers_section_id, fraction_options) {

            console.log(fraction_options);
            $(document).ready(
                function () {
                    var instance = M.qtypes.omeromultichoice.QuestionEditorMultichoice.getInstance();
                    instance.initialize(answers_section_id, fraction_options);
                    window.qem = instance;
                }
            );
        };

        // returns the class
        return M.qtypes.omeromultichoice.QuestionEditorMultichoice;
    }
);

