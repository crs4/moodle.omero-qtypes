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
 * UI Controller of the answer editor.
 *
 * @package    qtype
 * @subpackage omerocommon
 * @copyright  2015-2016 CRS4
 * @license    https://opensource.org/licenses/mit-license.php MIT license
 */
define([
        'jquery',
        'qtype_omerocommon/moodle-forms-utils',
        'qtype_omerocommon/multilanguage-element',
        'qtype_omerocommon/multilanguage-attoeditor',
        'qtype_omerocommon/modal-image-panel',
        'qtype_omerocommon/feedback-image-table'
    ],
    /* jshint curly: false */
    /* globals console, jQuery */
    function ($, FormUtils) {

        // override reference to jQuery
        $ = jQuery;

        /**
         * Utility function: notify listeners
         *
         * @param answer
         * @param event
         */
        function notifyListeners(answer, event) {
            console.log("notifying event...", event);
            for (var i in answer._listeners) {
                var listener = answer._listeners[i];
                var callbackName = "on" + event.name.charAt(0).toUpperCase() + event.name.substr(1);
                if (listener && listener[callbackName]) {
                    listener[callbackName](event);
                }
            }
        }

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

            me._inputs = {};

            // map of editors in use
            me._editors_map = {};

            me._data = {};

            // TODO: relocate the array
            me._feedback_images = {};

            me._fraction_options = fraction_options;

            // reference to the container of all answers
            me._answer_list_container = $("#" + answer_list_container_id + " .fcontainer");

            // listeners
            me._listeners = [];

            //
            me._form_utils = new M.qtypes.omerocommon.MoodleFormUtils();

            // the id of this answerContainer
            me._answer_number = answer_number === undefined
                ? M.qtypes.omerocommon.MoodleFormUtils.generateGuid() : answer_number;
        };


        // reference to the prototype
        var prototype = M.qtypes.omerocommon.AnswerBase.prototype;


        /**
         * Returns the ID of this answer
         *
         * @returns {string|*}
         */
        prototype.getId = function () {
            return this._answer_number;
        };


        prototype._answer_properties = ["answer", "fraction", "feedback", "feedbackimages"];

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

        prototype.addListener = function (listener) {
            this._listeners.push(listener);
        };

        prototype.removeListener = function (listener) {
            var index = this._listeners.indexOf(listener);
            if (index !== -1)
                this._listeners.splice(index, 1);
        };

        prototype._notifyListeners = function (event) {
            notifyListeners(this, event);
        };

        prototype.getDataToSubmit = function () {
            var data = {};
            for (var n in this._data)
                data[n] = this._data[n];
            return data;
        };

        /**
         * Returns the list of feedback images related to this answer.
         *
         * @returns {{}|*}
         * @private
         */
        prototype._getFeedbackImages = function () {
            var images = [];
            for (var i in this._feedback_images)
                images.push(this._feedback_images[i]);
            return images;
        };

        /**
         * Return the image with the value 'image_id' as ID
         *
         * @param image_id
         * @returns object representing an image
         * @private
         */
        prototype._getFeedbackImage = function (image_id) {
            return image_id ? this._feedback_images[image_id] : null;
        };

        /**
         * Add a feedback image.
         *
         * @param image
         * @private
         */
        prototype._addFeedbackImage = function (image) {
            if (image && !this._feedback_images[image.id])
                this._feedback_images[image.id] = image;
        };

        /**
         * Remove a feedback iamge.
         *
         * @param image
         * @private
         */
        prototype._removeFeedbackImage = function (image) {
            console.log("Deleting image", image);
            if (image)
                delete this._feedback_images[image.id];
        };

        /**
         * Returns the map <language, editor>
         * related to this question
         *
         * @returns {{}}
         */
        prototype.getEditorsMap = function () {
            var result = {};
            for (var i in this._editors_map)
                result[this._answer_number + "_" + i] = this._editors_map[i];
            return result;
        };


        /**
         *
         *
         * @param index
         */
        prototype.updateHeader = function (index) {
            // reference to the head
            this._answer_head.html(M.util.get_string("answer_choiceno", "qtype_omerocommon") + index);
        };


        /**
         *
         *
         * @param answer_index
         * @param remove_form_inputs
         */
        prototype.loadDataFromFormInputs = function (answer_index, remove_form_inputs) {
            console.log("Loading data from inputs...", this);
            var me = this;
            var data = {};
            for (var i in me._answer_properties) {
                var element_name = this._answer_properties[i];
                var element = me.findFormInputElement(element_name, answer_index);
                data[element_name] = element.val();
                if (remove_form_inputs) element.remove();

                element = me._inputs[element_name];
                if (element) {
                    var value = parseFloat(data[element_name]);
                    value = ((value === 1 || value === 0) ? value.toFixed(1) : value);
                    document.getElementById($(element).attr("id")).value = value;
                }
            }

            // decode the list of feedback images
            var image;
            var images = JSON.parse(data["feedbackimages"]);
            // append every feedback image to the table
            for (var j in images) {
                image = images[j];
                this._feedback_image_table.append(image);
                me._feedback_images[image.id] = image;
            }

            console.log("Loading multi language elements...");
            for (var editor_element_name in me._editors_map) {
                var editor = me._editors_map[editor_element_name];
                var locale_map_name = me._build_locale_map_name_of(editor_element_name, answer_index);
                var id = 'id_' + locale_map_name;
                console.log("Loading editor data...", id, locale_map_name);
                editor.loadDataFromFormInputs(locale_map_name);
            }
            this._data = data;
        };

        prototype.saveDataToFormInputs = function (answer_index) {

            var id, hidden, element_name;
            var form = document.forms[0];

            if (!form) {
                console.warn("Form not found!!!");
                return;
            }

            // serialize answer_feedback_images
            this._data["feedbackimages"] = JSON.stringify(this._getFeedbackImages());

            // set
            for (var i in this._answer_properties) {
                element_name = this._answer_properties[i];
                id = this._build_id_of(element_name, answer_index);
                var name = this._build_name_of(element_name, answer_index);
                var value = this._data[element_name];

                hidden = document.getElementById(id);
                value = FormUtils.htmlspecialchars(value);

                if (hidden) hidden.setAttribute("value", value);
                else {
                    hidden = '<input ' + 'id="' + id + '" ' + 'name="' + name + '" type="hidden" value="' + value + '">';
                    M.qtypes.omerocommon.MoodleFormUtils.appendHiddenElement(this._answer_container, hidden);
                }
            }

            console.log("Saving multi language elements...", this._answer_number);
            for (element_name in this._editors_map) {
                var editor = this._editors_map[element_name];
                var locale_map_name = this._build_locale_map_name_of(element_name, answer_index);
                id = 'id_' + locale_map_name;
                console.log("Saving editor data...", id, locale_map_name);

                hidden = document.getElementById(id);
                if (!hidden) //hidden.val(value);
                {
                    hidden = '<input ' +
                        'id="' + id + '" ' + 'name="' + locale_map_name + '" type="hidden" >';
                    console.log("Creating the hidden field", id, element_name, locale_map_name);
                    M.qtypes.omerocommon.MoodleFormUtils.appendHiddenElement(this._answer_container, hidden);
                    console.log("Created the hidden field", id, element_name, locale_map_name);
                } else {
                    console.log("Found hidden field to save editor data...", id, element_name, locale_map_name);
                }

                editor.saveDataToFormInputs(locale_map_name);
            }
        };

        prototype.findFormInputElement = function (element_name, answer_index) {
            return $("[name*=" + element_name + "\\[" + answer_index + "\\]]");
        };

        prototype._build_name_of = function (element_name, answer_index) {
            answer_index = (typeof answer_index !== 'undefined') ? answer_index : this._answer_number;
            return element_name + '[' + answer_index + "]";
        };

        prototype._build_locale_map_name_of = function (element_name, answer_index) {
            answer_index = (typeof answer_index !== 'undefined') ? answer_index : this._answer_number;
            return element_name + '_locale_map[' + answer_index + "]";
        };

        prototype._build_id_of = function (element_name, answer_index) {
            return 'id_' + this._build_name_of(element_name, answer_index);
        };

        prototype._add_image_selector = function (element_name, answer_index, label, local_map_name, on_click) {
            var button_name = this._build_name_of("button_" + element_name, answer_index);
            var button_id = this._build_id_of("button_" + element_name, answer_index);

            var data_name = this._build_name_of("data_" + element_name, answer_index);
            var data_id = this._build_id_of("data_" + element_name, answer_index);

            var element = '<div style="float: right;">';
            element += '<input type="hidden" id="' + data_id + '" name="' + data_name + '" ' + ' />';
            element += '<input ' +
                'id="' + button_id + '" ' +
                'name="' + button_name + '" ' +
                'value="Add Image" ' +
                'type="button" ' +
                '/>';
            element += '</div>';

            this._form_utils.appendElement(this._answer_container, label, element);
            return {
                button_id: button_id,
                button_name: button_name,
                data_id: data_id,
                data_name: data_name
            };
        };

        prototype._build_textarea_of = function (element_name, label, local_map_name) {
            var id = this._build_id_of(element_name);
            var name = this._build_name_of(element_name);

            local_map_name = (typeof local_map_name === 'undefined')
                ? this._build_locale_map_name_of(element_name) : local_map_name;

            var element = '<textarea ' +
                'id="' + id + '" ' +
                'name="' + name + '" ' +
                'rows="2"' +
                '></textarea>';

            this._form_utils.appendElement(this._answer_container, label, element, local_map_name);
            var editor = new M.qtypes.omerocommon.MultilanguageAttoEditor(name, local_map_name, false);
            editor.init();
            this._editors_map[element_name] = editor;
            console.log("Editors map", this._editors_map);
        };

        //prototype._init_textarea_editor = function (element_name) {
        //    var name = this._build_name_of(element_name);
        //    var editor = new M.qtypes.omerocommon.MultilanguageAttoEditor(name,
        //        this._build_locale_map_name_of(element_name), false);
        //    editor.init("en");
        //    this._editors_map[name] = editor;
        //};

        prototype._build_select_of = function (element_name, label) {
            var id = this._build_id_of(element_name);
            var name = this._build_name_of(element_name);
            var value = this._data[element_name];

            if (typeof value !== "undefined") value = parseFloat(value);

            var select = '<select ' +
                'id="' + id + '_select" ' + 'name="' + name + '_select">';

            for (var i in this._fraction_options)
                select += '<option value="' + i + '" ' +
                    (value == i ? 'selected="selected"' : "") + '>' +
                    this._fraction_options[i] + '</option>';
            select += '</select>';
            var fraction_selector = $(select);
            this._form_utils.appendElement(this._answer_container, label, fraction_selector, false);

            this._inputs[element_name] = select;

            var me = this;
            fraction_selector = document.getElementById(id + "_select");
            fraction_selector.onchange = function (data) {
                console.log("Changed grade", data);
                me._data[element_name] = fraction_selector.options[fraction_selector.selectedIndex].value;
            };
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

        // return the class
        return M.qtypes.omerocommon.AnswerBase;
    }
);