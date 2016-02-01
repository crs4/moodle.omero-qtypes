/**
 * Created by kikkomep on 12/3/15.
 */

define("qtype_omerointeractive/question-editor-interactive",
    [
        'jquery',
        'qtype_omerocommon/moodle-forms-utils',
        'qtype_omerocommon/answer-base',
        'qtype_omerocommon/multilanguage-element',
        'qtype_omerocommon/multilanguage-attoeditor',
        'qtype_omerocommon/question-editor-base'
    ],
    function ($, Editor, FormUtils) {
        // Private functions.

        function onSelectROIGroup(group) {
            console.log("Selected ROI group", group);
            alert("....");
        }

        function updateGroupButton(editor, selected_shapes) {
            if (!selected_shapes)
                selected_shapes = editor.getSelectedROIIds();

            var toEnable = selected_shapes.length > 0;
            if (toEnable) {
                var button = editor._add_to_group_list_element;
                button.html(""); // clear
                toEnable = false;
                for (var i in editor._answers) {
                    var group = editor._answers[i];
                    if (group.containsROIs(editor.getSelectedROIIds())) break;
                    var option = $('<li value="' + i + '"><a href="#">' + (parseInt(i) + 1) + '</a></li>');
                    toEnable = true;
                    button.append(option);
                    option.click(function () {
                        var group_id = $(this).attr("value");
                        console.log("Selected ROI GROUP: " + group_id);
                        var selected_rois = editor.getSelectedROIIds();
                        if (selected_rois && selected_rois.length > 0) {
                            editor._answers[group_id].addROIsToGroup(selected_rois);
                        }
                    });
                }
            }

            toEnable ?
                editor._add_to_group_element.removeClass("disabled") :
                editor._add_to_group_element.addClass("disabled");
        }


        // Public functions
        return {
            initialize: function (str) {
                console.log("Initialized", this);

                // defines the basic package
                M.qtypes = M.qtypes || {};

                // defines the specific package of this module
                M.qtypes.omerointeractive = M.qtypes.omerointeractive || {};

                /**
                 * Defines MoodleFormUtils class
                 * @type {{}}
                 */
                M.qtypes.omerointeractive.QuestionEditorInteractive = function () {

                    // the reference to this scope
                    var me = this;

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

                    this.parent.initialize.call(this, answers_section_id, fraction_options);
                    this._show_roishape_column_group = true;
                    this._add_to_group_element_id = add_to_group_element_id;
                    this._add_to_group_element = $("#" + add_to_group_element_id);
                    this._add_to_group_list_element_id = add_to_group_list_element_id;
                    this._add_to_group_list_element = $("#" + add_to_group_list_element_id);
                    this._add_to_group_list_element.dropdown();
                };

                /**
                 *
                 * @param answer_number
                 * @param fraction_options
                 * @returns {M.qtypes.omeromultichoice.AnswerPlaintext}
                 */
                prototype.buildAnswer = function (answer_number, fraction_options, answer_index) {
                    return new M.qtypes.omerointeractive.AnswerGroup(this._answers_section_id, answer_number, fraction_options, answer_index);
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

                prototype.validate = function(){
                    var errors = this.parent.validate.call(this);
                    try {
                        for (var i in this._answers) {
                            var answer = this._answers[i];
                            if (answer && answer._roi_id_list == 0) {
                                errors.push(M.util.get_string('validation_noroi_per_group', 'qtype_omerointeractive'));
                                break;
                            }
                        }
                    } catch (e) {
                        console.error(e);
                        errors.push(e.message);
                    }
                    return errors;
                }
            },


            main: function (answers_section_id, fraction_options, add_to_group_element_id, add_to_group_list_element_id) {

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
            }
        };
    }
);