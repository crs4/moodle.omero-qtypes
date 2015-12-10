/**
 * Created by kikkomep on 12/2/15.
 */

define("qtype_omerocommon/answer-base",
    [
        'jquery',
        'qtype_omerocommon/moodle-forms-utils',
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
                M.qtypes.omerocommon = M.qtypes.omerocommon || {};


                /**
                 * Defines MoodleFormUtils class
                 * @type {{}}
                 */
                M.qtypes.omerocommon.AnswerBase = function (answer_list_container_id, answer_number, fraction_options) {

                    // the reference to this scope
                    var me = this;

                    // map of editors in use
                    me._editors_map = {};

                    me._fraction_options = fraction_options;

                    // reference to the container of all answers
                    me._answer_list_container = $("#" + answer_list_container_id + " .fcontainer");

                    //
                    me._form_utils = new M.qtypes.omerocommon.MoodleFormUtils();

                    // the id of this answerContainer
                    me._answer_number = answer_number === undefined
                        ? M.qtypes.omerocommon.MoodleFormUtils.generateGuid() : answer_number;
                };

                var prototype = M.qtypes.omerocommon.AnswerBase.prototype;

                /**
                 * Builds the answer
                 *
                 * @private
                 */
                prototype._build = function () {
                    // the reference to this scope
                    var me = this;
                    me._answer_container = $('<div class="fitem" id="' + me._answer_number + '"></div>');
                    me._answer_list_container.append(me._answer_container);

                    me._form_utils.appendElement(me._answer_container, "Grade", "<select ><option>1</option></select>");
                    me._form_utils.appendElement(me._answer_container, "Feedback", "<textarea>xxx</textarea>");
                };


                /**
                 * Shows the answer
                 */
                prototype.show = function () {
                    // the reference to this scope
                    var me = this;
                    if (!me._answer_container)
                        me._build();
                    else
                        me._answer_list_container.append(me._answer_container);
                };

                /**
                 * Hides the answer
                 */
                prototype.hide = function () {
                    // the reference to this scope
                    var me = this;
                    if (me._answer_container)
                        me._answer_container.remove();
                };

                /**
                 * Returns the map <language, editor>
                 * related to this question
                 *
                 * @returns {{}}
                 */
                prototype.getEditorsMap = function () {
                    return this._editors_map;
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
);