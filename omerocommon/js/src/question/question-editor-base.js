/**
 * Created by kikkomep on 12/3/15.
 */
/**
 * Created by kikkomep on 12/2/15.
 */

define("qtype_omerocommon/question-editor-base",
    [
        'jquery',
        'qtype_omerocommon/moodle-forms-utils',
        'qtype_omerocommon/answer-base',
        'qtype_omerocommon/multilanguage-element',
        'qtype_omerocommon/multilanguage-attoeditor'
    ],
    function ($, Editor, FormUtils) {
        // Private functions.


        // A reference to the languageSelector
        var language_selector = $("#id_question_language");

        /**
         * Initializes the list of supported languages
         * and sets the currently selected language
         *
         * @private
         */
        var _supported_languages = function () {

            // initializes the list of supported languages
            var supported_languages = [];
            var language_selector = document.forms[0].elements["question_language"];
            var language_options = language_selector.options;
            for (var i = 0; i < language_options.length; i++) {
                supported_languages.push(language_options[i].value);
            }

            // handles the event 'language changed'
            //document.forms[0].elements["question_language"].onchange = me._updateCurrentLanguage;
            return supported_languages;
        }();


        // Public functions
        return {
            initialize: function (str) {
                console.log("Initialized", this);

                // defines the basic package
                M.qtypes = M.qtypes || {};

                // defines the specific package of this module
                M.qtypes.omerocommon = M.qtypes.omerocommon || {};


                /**
                 * Builds a new instance
                 *
                 * @constructor
                 */
                M.qtypes.omerocommon.QuestionEditorBase = function () {

                    // the reference to this scope
                    var me = this;
                };


                // A local reference to the prototype
                var prototype = M.qtypes.omerocommon.QuestionEditorBase.prototype;

            }
        };
    }
);