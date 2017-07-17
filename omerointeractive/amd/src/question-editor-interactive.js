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
                    } else {
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

        /**
         * Performs the initialization
         */
        prototype.initialize = function (config) {
            this._show_roishape_column_group = true;
            this._add_to_group_element_id = config.add_to_group_element_id;
            this._add_to_group_element = $("#" + config.add_to_group_element_id);
            this._add_to_group_list_element_id = config.add_to_group_list_element_id;
            this._add_to_group_list_element = $("#" + config.add_to_group_list_element_id);

            this.parent.initialize.call(this, config);
            this._add_to_group_list_element.dropdown();
        };

        prototype.onImageModelRoiLoaded = function (e) {
            var removed_rois = this.parent.onImageModelRoiLoaded.call(this, e);
            var removed_rois_from_groups = [];
            for (var i  in this._answers) {
                var answer = this._answers[i];
                var unavailable_rois = this._image_viewer_controller.checkRois(answer.getROIsWithinGroup());
                console.log(answer, unavailable_rois);
                if (unavailable_rois && unavailable_rois.length > 0) {
                    answer.removeROIsFromGroup(unavailable_rois);
                    removed_rois_from_groups.push([(parseInt(i) + 1), unavailable_rois]);
                    console.log("Removing not valid ROIs", unavailable_rois);
                }
            }

            var j;
            var message = "";
            if (removed_rois.visible.length > 0)
                message += "<br> - " + removed_rois.visible.join(", ") + " ( " +
                    M.util.get_string('roi_visible', 'qtype_omerocommon') + " )";
            if (removed_rois.focusable.length > 0)
                message += "<br> - " + removed_rois.focusable.join(", ") + " ( " +
                    M.util.get_string('roi_focusable', 'qtype_omerocommon') + " )";
            for (j in removed_rois_from_groups) {
                message += "<br> - " + removed_rois_from_groups[j][1].join(", ") + " ( " +
                    M.util.get_string('answer', 'core') + " " + removed_rois_from_groups[j][0] + " )";
            }

            if (message.length > 0) {
                message = M.util.get_string('answer_group_removed_invalid_rois', 'qtype_omerointeractive') + message;
                this._showDialogMessage(message);
            }
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
            if (this._config.view_mode != "author") {
                answer.setAllowedEditingLanguages(this._allowed_editing_languages);
                answer.enableEditingControls(false);
            }
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
                    if (answer && answer.getROIsWithinGroup().length === 0) {
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


        M.qtypes.omerointeractive.QuestionEditorInteractive.main = function (config_element_id) {
            // extract configuration
            var c = document.getElementsByName(config_element_id)[0];
            var config = JSON.parse(c.value);
            console.log("QuestionEditorInteractive configuration", config);

            $(document).ready(
                function () {
                    var instance = new M.qtypes.omerointeractive.QuestionEditorInteractive();
                    instance.initialize(config);
                    if (M.cfg.developerdebug)
                        window.qei = instance;
                }
            );
        };

        return M.qtypes.omerointeractive.QuestionEditorInteractive;
    }
);