/**
 * Created by kikkomep on 12/2/15.
 */

define("qtype_omeromultichoice/answer-plaintext",
    [
        'jquery',
        'qtype_omerocommon/moodle-forms-utils',
        'qtype_omerocommon/answer-base',
        'qtype_omerocommon/multilanguage-element',
        'qtype_omerocommon/multilanguage-attoeditor'
    ],
    function ($, Editor, FormUtils) {
        // Private functions.


        // Public functions
        return {
            initialize: function (str) {
                console.log("Initialized", this);

                // defines the basic package
                M.qtypes = M.qtypes || {};

                // defines the specific package of this module
                M.qtypes.omeromultichoice = M.qtypes.omeromultichoice || {};


                /**
                 * Defines MoodleFormUtils class
                 * @type {{}}
                 */
                M.qtypes.omeromultichoice.AnswerPlaintext = function (answer_list_container_id, answer_number, fraction_options) {

                    // the reference to this scope
                    var me = this;

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
                        'Answer ' + (this._answer_number + 1) +
                        '</h4>' +
                        '</div>' +
                        '<div style="display: block; float: right; margin: 20px 30px;">' +
                        '<button type="button" id="delete-answer-' + me._answer_number + '" ' +
                        'class="btn btn-danger delete-answer">Delete</button></div>');
                    panel.append(panel_heading);

                    var panel_body = $('<div class="panel-body"></div>');
                    panel.append(panel_body);

                    me._answer_container = $('<div class="fitem" id="' + me._answer_number + '"></div>');
                    panel_body.append(me._answer_container);

                    // answer text
                    me._build_textarea_of("answer", "Text");

                    // answer format
                    me._build_hidden_of("answerformat", "1");

                    // answer grade
                    me._build_select_of("fraction", "Grade");

                    // answer feedback
                    me._build_textarea_of("feedback", "Feedback");

                    // answer format
                    me._build_hidden_of("feedbackformat", "1");

                    // register the delete event
                    $("#delete-answer-" + me._answer_number).on("click", function () {
                        me.remove();
                    });

                    // registers the panel as main container
                    me._answer_container = panel;
                };


                prototype._build_name_of = function (element_name) {
                    return element_name + '[' + this._answer_number + "]";
                };

                prototype._build_locale_map_name_of = function (element_name) {
                    return element_name + '_locale_map[' + this._answer_number + "]";
                };

                prototype._build_id_of = function (element_name) {
                    return 'id_' + this._build_name_of(element_name);
                };

                prototype._build_textarea_of = function (element_name, label) {
                    var id = this._build_id_of(element_name);
                    var name = this._build_name_of(element_name);
                    var value = null;

                    var old = $("[name*=" + element_name + "\\[" + this._answer_number + "\\]]");
                    console.log("query", "[name*=" + element_name + "\\[" + this._answer_number + "\\]]", old);
                    if (old.length > 0) {
                        console.log("query", "[name*=" + element_name + "\\[" + this._answer_number + "\\]]");
                        value = old.val();
                        old.remove();
                    }

                    var element = '<textarea ' +
                        'id="' + this._build_id_of(element_name) + '" ' +
                        'name="' + this._build_name_of(element_name) + '" ' +
                        'rows="2"' +
                        '></textarea>';

                    this._form_utils.appendElement(
                        this._answer_container, label, element, !value ? this._build_locale_map_name_of(element_name) : false);
                    this._init_textarea_editor(element_name);
                };

                prototype._build_select_of = function (element_name, label) {
                    var id = this._build_id_of(element_name);
                    var name = this._build_name_of(element_name);
                    var value = "";

                    var old = $("[name*=" + element_name + "\\[" + this._answer_number + "\\]]");
                    if (old.length > 0) {
                        value = parseFloat(old.val());
                        //old.remove();
                    } else {
                        old = null;
                    }


                    var select = '<select ' +
                        'id="' + id + '_select" ' + 'name="' + name + '_select">';

                    for (var i in this._fraction_options)
                        select += '<option value="' + i + '" ' +
                            (value == i ? 'selected="selected"' : "") + '>' +
                            this._fraction_options[i] + '</option>';
                    select += '</select>';
                    var fraction_selector = $(select);
                    this._form_utils.appendElement(this._answer_container, label, fraction_selector, false);
                    fraction_selector.val(value);
                    if (old == null) {
                        old = $('<input type="hidden" name="' + name + '" value="">');
                        old.insertAfter(fraction_selector);
                    }

                    fraction_selector = document.getElementById(id + "_select");
                    fraction_selector.onchange = function (data) {
                        console.log("Changed grade", data);
                        old.val(fraction_selector.options[fraction_selector.selectedIndex].value);
                        console.log(old, fraction_selector);
                    }
                };

                prototype._build_hidden_of = function (element_name, value) {
                    var id = this._build_id_of(element_name);
                    var name = this._build_name_of(element_name);

                    var old = $("[name*=" + element_name + "\\[" + this._answer_number + "\\]]");
                    if (old.length > 0) {
                        value = old.val();
                        old.remove();
                    } else {
                        var hidden = '<input ' +
                            'id="' + id + '" ' + 'name="' + name + '" type="hidden" >';
                        this._form_utils.appendElement(this._answer_container, "", hidden, false);
                    }
                };


                prototype._init_textarea_editor = function (element_name) {
                    var name = this._build_name_of(element_name);
                    var editor = new M.qtypes.omerocommon.MultilanguageAttoEditor(name, this._build_locale_map_name_of(element_name), false);
                    editor.init("en");
                    this._editors_map[name] = editor;
                };
            }
        };
    }
)
;