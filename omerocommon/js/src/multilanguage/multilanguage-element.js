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

                M.qtypes.omerocommon.MultilanguageElement = function (container_id) {

                    // registers the id of the container element
                    this.container_id = container_id;

                    // initializes the map of localized strings
                    this._locale_text_map = {};

                    // instance
                    this._form_utils = new M.qtypes.omerocommon.MoodleFormUtils();
                };


                var prototype = M.qtypes.omerocommon.MultilanguageElement.prototype;
                prototype.init = function (locale_text_map, current_language) {
                    this._current_language = current_language;
                    if (locale_text_map && !this._locale_text_map) {
                        this._locale_text_map = locale_text_map;
                    }
                };

                prototype.setLocaleText = function (text, language) {
                    language = language || this._current_language;
                    console.log("Setting locale string: ", language, text);
                    this._locale_text_map[language] = text;
                };

                prototype.getLocaleText = function (language) {
                    language = language || this._current_language;
                    return this._locale_text_map[language];
                };

                prototype.changeLanguage = function (language) {
                    this.save();
                    this._current_language = language;
                    console.info("Changing language to: " + language);
                };

                prototype.onLanguageChanged = function (language) {
                    this.changeLanguage(language);
                };

                prototype.clear = function () {
                    console.warn("Not implemented at this level");
                }
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