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
 * UI Controller of the interactive question editor.
 *
 * @package    qtype
 * @subpackage omerointeractive
 * @copyright  2015-2016 CRS4
 * @license    https://opensource.org/licenses/mit-license.php MIT license
 */
define([
        'jquery',
        'qtype_omerocommon/moodle-forms-utils',
        'qtype_omerocommon/question-editor-base',
        'qtype_omerointeractive/answer-group'
    ],

    /* jshint curly: false */
    /* globals console */
    function ($ /*, FormUtils, Editor*/) {


        /*
         function onSelectROIGroup(group) {
         console.log("Selected ROI group", group);
         alert("....");
         }*/

        function updateGroupButton(editor, selected_shapes) {
            if (!selected_shapes)
                selected_shapes = editor.getSelectedROIIds();

            function handleOptionClick(e) {
                var group_id = $(this).attr("value");
                console.log("Selected ROI GROUP: " + group_id, e);
                var selected_rois = editor.getSelectedROIIds();
                if (selected_rois && selected_rois.length > 0) {
                    editor._answers[group_id].addROIsToGroup(selected_rois);
                }
            }

            var toEnable = selected_shapes.length > 0;
            if (toEnable) {
                var group, option;
                var button = editor._add_to_group_list_element;
                button.html(""); // clear
                for (var i in editor._answers) {
                    group = editor._answers[i];
                    console.log(
                        "Selected ROIs", group.containsROIs(editor.getSelectedROIIds()),
                        "Checking GROUp", group, group.containsROIs(editor.getSelectedROIIds()));
                    if (group.containsROIs(editor.getSelectedROIIds())) {
                        toEnable = false;
                        break;
                    }else {
                        option = $('<li value="' + i + '"><a href="#">' + (parseInt(i) + 1) + '</a></li>');
                        toEnable = true;
                        button.append(option);
                        option.click({}, handleOptionClick);
                    }
                }
            }

            return toEnable ?
                editor._add_to_group_element.removeClass("disabled") :
                editor._add_to_group_element.addClass("disabled");
        }


        // defines the basic package
        M.qtypes = M.qtypes || {};

        // defines the specific package of this module
        M.qtypes.omerointeractive = M.qtypes.omerointeractive || {};

        /**
         * Defines MoodleFormUtils class
         * @type {{}}
         */
        M.qtypes.omerointeractive.QuestionEditorInteractive = function () {
            // Call the parent constructor
            M.qtypes.omerocommon.QuestionEditorBase.call(this);
        };

        // inherit
        M.qtypes.omerointeractive.QuestionEditorInteractive.prototype =
            new M.qtypes.omerocommon.QuestionEditorBase();

        // correct the constructor
        M.qtypes.omerointeractive.QuestionEditorInteractive.prototype.constructor =
            M.qtypes.omerointeractive.QuestionEditorInteractive;

        M.qtypes.omerointeractive.QuestionEditorInteractive.prototype.parent =
            M.qtypes.omerocommon.QuestionEditorBase.prototype;

        // local reference to the current prototype
        var prototype = M.qtypes.omerointeractive.QuestionEditorInteractive.prototype;


        M.qtypes.omerointeractive.QuestionEditorInteractive.getInstance = function () {
            if (!M.qtypes.omerocommon.QuestionEditorBase.instance) {
                M.qtypes.omerocommon.QuestionEditorBase.instance =
                    new M.qtypes.omerointeractive.QuestionEditorInteractive();
            }
            return M.qtypes.omerocommon.QuestionEditorBase.instance;
        };


        /**
         * Performs the initialization
         */
        prototype.initialize = function (answers_section_id, fraction_options,
                                         add_to_group_element_id, add_to_group_list_element_id) {

            this._show_roishape_column_group = true;
            this._add_to_group_element_id = add_to_group_element_id;
            this._add_to_group_element = $("#" + add_to_group_element_id);
            this._add_to_group_list_element_id = add_to_group_list_element_id;
            this._add_to_group_list_element = $("#" + add_to_group_list_element_id);
            this.parent.initialize.call(this, answers_section_id, fraction_options);
            this._add_to_group_list_element.dropdown();
        };

        /**
         *
         * @param answer_number
         * @param fraction_options
         * @returns {M.qtypes.omerointeractive.AnswerGroup}
         */
        prototype.buildAnswer = function (answer_number, fraction_options, answer_index) {
            return new M.qtypes.omerointeractive.AnswerGroup(
                this._answers_section_id,
                answer_number, fraction_options, answer_index);
        };

        prototype.onAddAnswer = function (answer) {
            console.log("Added answer", answer);
            answer._init_roi_list();
            answer.updateROIList();
            answer.addListener(this);
            updateGroupButton(this);
        };


        prototype.onRemoveAnswer = function (answer) {
            console.log("Removed answer", answer);
            answer.removeListener(this);
            updateGroupButton(this);
        };

        prototype.onRoiShapesSelected = function (event) {
            console.log("ROI Shape selected...", event);
            updateGroupButton(this, event.shapes);
        };

        prototype.onAnswerROIsAdded = function (e) {
            console.log("Added new ROIs ", e);
            this._roi_shape_table.deselectAll();
        };

        prototype.onAnswerROIsRemoved = function (e) {
            console.log("Removed new ROIs ", e);
            updateGroupButton(this);
        };

        prototype.validate = function () {
            var errors = this.parent.validate.call(this);
            try {
                for (var i in this._answers) {
                    var answer = this._answers[i];
                    if (answer && answer._roi_id_list === 0) {
                        errors.push(M.util.get_string('validation_noroi_per_group', 'qtype_omerointeractive'));
                        break;
                    }
                }
            } catch (e) {
                console.error(e);
                errors.push(e.message);
            }
            return errors;
        };


        M.qtypes.omerointeractive.QuestionEditorInteractive.main =
            function (answers_section_id, fraction_options,
                      add_to_group_element_id, add_to_group_list_element_id) {

                console.log(fraction_options);
                $(document).ready(
                    function () {
                        var instance = M.qtypes.omerointeractive.QuestionEditorInteractive.getInstance();
                        instance.initialize(
                            answers_section_id, fraction_options,
                            add_to_group_element_id, add_to_group_list_element_id
                        );
                        window.qei = instance;
                    }
                );
            };

        return M.qtypes.omerointeractive.QuestionEditorInteractive;
    }
);