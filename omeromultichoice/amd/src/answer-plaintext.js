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
 * UI Controller of the multichoice-answer editor.
 *
 * @package    qtype
 * @subpackage omeromultichoice
 * @copyright  2015-2016 CRS4
 * @license    https://opensource.org/licenses/mit-license.php MIT license
 */
define([
        'jquery',
        'qtype_omerocommon/moodle-forms-utils',
        'qtype_omerocommon/answer-base',
        'qtype_omerocommon/multilanguage-element',
        'qtype_omerocommon/multilanguage-attoeditor'
    ],

    /* jshint curly: false */
    function ($) {

        // defines the basic package
        M.qtypes = M.qtypes || {};

        // defines the specific package of this module
        M.qtypes.omeromultichoice = M.qtypes.omeromultichoice || {};


        /**
         * Defines MoodleFormUtils class
         * @type {{}}
         */
        M.qtypes.omeromultichoice.AnswerPlaintext = function (answer_list_container_id, answer_number, fraction_options) {

            // Call the parent constructor
            M.qtypes.omerocommon.AnswerBase.call(this, answer_list_container_id, answer_number, fraction_options);
        };


        // inherit
        M.qtypes.omeromultichoice.AnswerPlaintext.prototype = new M.qtypes.omerocommon.AnswerBase();

        // correct the constructor
        M.qtypes.omeromultichoice.AnswerPlaintext.prototype.constructor = M.qtypes.omeromultichoice.AnswerPlaintext;

        // short
        var prototype = M.qtypes.omeromultichoice.AnswerPlaintext.prototype;


        /**
         * Builds the answer
         *
         * @private
         */
        prototype._build = function () {
            var me = this;

            var panel = $('<div class="panel panel-default"></div>');
            me._answer_list_container.append(panel);

            var panel_heading = $('<div class="panel-heading">' +
                '<h4 class="panel-title">' +
                '<span id="head-answer-' + this._answer_number + '">Answer ' + (this._answer_number + 1) + '</span>' +
                '<div style="display: inline-block; float: right; text-align: right;">' +
                '<a href="javascript:void(0)" id="delete-answer-' + me._answer_number + '" >' +
                '<i class="red glyphicon glyphicon-remove-sign" style="font-size: 2m;"></i></a>' +
                '</h4>' +
                '</div>' +
                '<div style="display: block; float: right; margin: 20px 30px;">' +
                '</div>');
            panel.append(panel_heading);

            var panel_body = $('<div class="panel-body"></div>');
            panel.append(panel_body);

            me._answer_container = $('<div class="fitem" id="' + me._answer_number + '"></div>');
            panel_body.append(me._answer_container);

            // answer text
            me._build_textarea_of("answer", M.util.get_string("answer_text", "qtype_omeromultichoice"));

            // answer format
            me._build_hidden_of("answerformat", "1");

            // answer grade
            me._build_select_of("fraction", M.util.get_string("answer_grade", "qtype_omerocommon"));

            // answer feedback
            me._build_textarea_of("feedback", M.util.get_string("feedback", "question"));

            // answer format
            me._build_hidden_of("feedbackformat", "1");

            // answer feedback images
            var selector_ids = me._add_image_selector("add_images", me._answer_number, "Feedback Images");
            me._answer_feedback_filepicker = new M.omero_filepicker({
                buttonid: selector_ids.button_id,
                buttonname: selector_ids.button_name,
                elementid: selector_ids.data_id,
                elementname: selector_ids.data_name,
                filename_element: undefined
            }, {}, true);
            me._answer_feedback_filepicker.addListener(me);

            // answer feedback image table
            me._feedback_image_table = new M.qtypes.omerocommon.FeedbackImageTable("table-" + me._answer_number);
            me._form_utils.appendElement(me._answer_container, "", me._feedback_image_table.drawHtmlTable());
            me._feedback_image_table.initTable();

            // reference to the head
            me._answer_head = $('#head-answer-' + this._answer_number);

            // register the delete event
            $("#delete-answer-" + me._answer_number).on("click", function () {
                me.remove();
            });

            // registers the panel as main container
            me._answer_container = panel;
        };

        prototype.onSelectedImage = function (image_info, picker) {
            console.log("Selected image", image_info);
            this._feedback_images.push(image_info.image_id);
            this._feedback_image_table.append({
                id: image_info.image_id,
                description: "Image name...",
                visiblerois: ""
            });
        };

        // returns the class
        return M.qtypes.omeromultichoice.AnswerPlaintext;
    }
);