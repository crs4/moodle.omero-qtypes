/**
 * Created by kikkomep on 12/2/15.
 */

define("qtype_omerocommon/multilanguage-element",
    [
        'jquery',
        'qtype_omerocommon/moodle-forms-utils'
    ],
    function ($) {
        // Private functions.

        // Public functions
        return {
            initialize: function (str) {

                console.log("Initialized", this);

                M.qtypes.omerocommon.MultilanguageElement = function (input_data_element_name, locale_map_element_name) {

                    if (input_data_element_name) {

                        // registers the id of the localized version of the element
                        this.input_data_element_name = input_data_element_name;

                        // id of the input element containing data
                        this.input_data_locale_map_name = !locale_map_element_name
                            ? input_data_element_name + "_locale_map"
                            : locale_map_element_name;

                        // instance
                        this._form_utils = new M.qtypes.omerocommon.MoodleFormUtils();


                    }
                };


                var prototype = M.qtypes.omerocommon.MultilanguageElement.prototype;
                prototype.init = function (current_language) {

                    // initializes the map of localized strings
                    this._locale_text_map = {};

                    this._current_language = current_language;
                    var map_element = this.getLocaleTextMapElement();
                    if (map_element) {
                        var value = map_element.getAttribute("value");
                        console.log(value);
                        if (value && value.length > 0) {
                            this._locale_text_map = JSON.parse(value);
                        }
                        var txt = this._locale_text_map[this._current_language];

                        this.onLanguageChanged(this._current_language);
                    } else {
                        console.error("Map element for " + this.input_data_element_name + " not found!!!");
                    }


                    // register the serialization
                    var me = this;
                    console.log("Registering onsubmit for " + me.input_data_element_name, me);
                    me._update_listener = function () {
                        M.qtypes.omerocommon.MultilanguageElement.serialize_text(me);
                        console.log("Object before submission", me);
                        //alert("Check: " + me.input_data_element_name);
                    };
                    document.forms[0].addEventListener("submit", me._update_listener);
                };

                prototype.setLocaleText = function (text, language) {
                    //language = language || this._current_language;
                    console.log("Setting locale string: ", this.input_data_element_name, language, text);
                    this._locale_text_map[language] = text;
                };

                prototype.getLocaleText = function (language) {
                    language = language || this._current_language;
                    return this._locale_text_map[language];
                };

                prototype.changeLanguage = function (language) {
                    if (this._current_language !== language) {
                        this.save();
                        this._current_language = language;
                    }
                    console.info("Changing language to: " + language);
                };

                prototype.onLanguageChanged = function (language) {
                    this.changeLanguage(language);
                };

                prototype.clear = function () {
                    console.warn("Not implemented at this level");
                };


                prototype.destroy = function () {
                    alert("removing");
                    document.forms[0].removeEventListener("submit", this._update_listener);
                };

                prototype.getLocaleTextMapElement = function () {
                    var input_element = document.forms[0].elements[this.input_data_locale_map_name];
                    console.log("Input element name", this.input_data_locale_map_name, input_element);
                    return input_element;
                };

                /**
                 *
                 */
                M.qtypes.omerocommon.MultilanguageElement.serialize_text = function (mel) {
                    try {
                        console.log("Serializing " + mel.input_data_element_name);

                        mel.save();

                        var input_element = mel.getLocaleTextMapElement();

                        console.log("Found input element", input_element);
                        if (input_element) {
                            var serialized_text = JSON.stringify(mel._locale_text_map);
                            if (input_element.type === "textarea")
                                input_element.innerHTML = serialized_text;
                            else input_element.setAttribute("value", serialized_text);
                        } else console.error("Error during the serialization: " +
                            "element " + mel.input_data_element_name + " not found!!!");
                    } catch (e) {
                        console.error(e);
                        alert("ERROR detected");
                    }
                };

            }
        };
    }
);