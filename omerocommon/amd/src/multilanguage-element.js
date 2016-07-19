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
 * UI Controller of a generic multilanguage HTML element.
 *
 * @package    qtype
 * @subpackage omerocommon
 * @copyright  2015-2016 CRS4
 * @license    https://opensource.org/licenses/mit-license.php MIT license
 */
define(['qtype_omerocommon/moodle-forms-utils'],
    /* jshint curly: false */
    /* globals console */
    function ($/* FormUtils */) {

        // override reference to jQuery
        $ = jQuery;

        /**
         * Defines a MultilanguageElement
         *
         * @param input_data_element_name
         * @param locale_map_element_name
         * @constructor
         */
        M.qtypes.omerocommon.MultilanguageElement = function (input_data_element_name, locale_map_element_name) {

            this._current_language = null;

            // registers the id of the localized version of the element
            if (input_data_element_name)
                this.input_data_element_name = input_data_element_name;

            // id of the input element containing data
            this.input_data_locale_map_name = !locale_map_element_name
                ? input_data_element_name + "_locale_map"
                : locale_map_element_name;

            // instance
            this._form_utils = new M.qtypes.omerocommon.MoodleFormUtils();
        };


        var prototype = M.qtypes.omerocommon.MultilanguageElement.prototype;
        prototype.init = function (current_language, input_data_element_name) {

            // initializes the map of localized strings
            this._locale_text_map = {};
            console.log("Multilanguage data element: ", input_data_element_name, "language:", current_language);

            // clear textarea
            this._editor.setText("");

            // process initialization
            if (typeof input_data_element_name !== 'undefined'
                && input_data_element_name.length > 0) {
                this.loadDataFromFormInputs(input_data_element_name);
            }

            // update language
            if (current_language)
                this.onLanguageChanged(current_language);

            // register the serialization
            var me = this;
            console.log("Registering onsubmit for " + me.input_data_element_name, me);
            me._update_listener = function () {
                M.qtypes.omerocommon.MultilanguageElement.serializeToFormInputs(me);
                console.log("Object before submission", me);
            };
            //document.forms[0].addEventListener("submit", me._update_listener);
        };

        prototype.setLocaleText = function (text, language) {
            language = language || this._current_language;
            console.log("Setting locale string: ", this.input_data_element_name, language, text);
            this._locale_text_map[language] = text;
        };

        prototype.getLocaleText = function (language) {
            language = language || this._current_language;
            return this._locale_text_map[language];
        };

        prototype.getLocaleTextMap = function () {
            var tmp = {};
            for (var i in this._locale_text_map) tmp[i] = this._locale_text_map[i];
            return tmp;
        };

        prototype.changeLanguage = function (language) {
            if (this._current_language !== null && this._current_language !== language) this.save();
            this._current_language = language;
        };

        prototype.onLanguageChanged = function (language) {
            this.changeLanguage(language);
        };

        prototype.clear = function () {
            console.warn("Not implemented at this level");
        };


        prototype.destroy = function () {
            console.log("destroying multilang element", this);
            document.forms[0].removeEventListener("submit", this._update_listener);
        };

        prototype.getLocaleTextMapElement = function () {
            var input_element = document.forms[0].elements[this.input_data_locale_map_name];
            console.log("Input element name", this.input_data_locale_map_name, input_element);
            return input_element;
        };


        prototype.loadDataFromFormInputs = function (input_data_element_name) {
            var map_element = document.forms[0].elements[input_data_element_name];
            console.log(map_element);
            if (map_element) {
                var value = map_element.getAttribute("value");
                if (value && value.length > 0) {
                    this._locale_text_map = JSON.parse(value);
                } else {
                    this._locale_text_map = {};
                }
            } else {
                console.error("Map element for " + input_data_element_name + " not found!!!");
            }
        };

        prototype.saveDataToFormInputs = function (input_data_element_name, encode_text) {

            input_data_element_name = input_data_element_name || this.input_data_element_name;
            console.log("Serializing " + this.input_data_element_name + " -- " + input_data_element_name);

            // save the last changes
            this.save();

            var input_elements = document.getElementsByName(input_data_element_name);
            console.log("Found input element", input_elements);

            var serialized_text = JSON.stringify(this.getLocaleTextMap());
            for (var i = 0; i < input_elements.length; i++) {
                var input_element = input_elements[i];
                if (encode_text)
                    serialized_text = $("<div>").text(serialized_text).html();
                input_element.setAttribute("value", serialized_text);
            }

            console.log("After update...", input_elements);
        };

        /**
         *
         */
        M.qtypes.omerocommon.MultilanguageElement.serializeToFormInputs = function (mel) {
            mel.saveDataToFormInputs();
        };

        // returns the class
        return M.qtypes.omerocommon.MultilanguageElement;
    }
);