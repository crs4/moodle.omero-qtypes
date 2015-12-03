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

                M.qtypes.omerocommon.MultilanguageElement = function (element_id) {

                    // registers the id of the container element
                    this.element_id = element_id;

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
            }
        };
    }
);