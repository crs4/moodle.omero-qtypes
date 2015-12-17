/**
 * Created by kikkomep on 12/2/15.
 */

define("qtype_omerocommon/multilanguage-attoeditor",
    [
        'jquery',
        'qtype_omerocommon/moodle-forms-utils',
        'qtype_omerocommon/multilanguage-element',
        'qtype_omerocommon/moodle-attoeditor',
    ],
    function ($, Element, Editor) {
        // Private functions.


        // Public functions
        return {
            initialize: function (str) {

                console.log("Initialized", this);

                /**
                 * Builds a new MultilanguageAttoEditor
                 *
                 * @param element_id
                 * @param locale_map_element_name
                 * @constructor
                 */
                M.qtypes.omerocommon.MultilanguageAttoEditor = function (element_id, locale_map_element_name, avoid_editor_init) {

                    // the reference to this scope
                    var me = this;

                    // Call the parent constructor
                    M.qtypes.omerocommon.MultilanguageElement.call(this, element_id, locale_map_element_name);

                    // A new instance of MoodleAttoEditor
                    me._editor = new M.qtypes.omerocommon.MoodleAttoEditor("id_" + me.input_data_element_name);

                    // avoids YUI editor initialization
                    // (useful when the editor already exists)
                    if (!avoid_editor_init)
                        me._editor.init();


                    /**
                     * Clear the current text area
                     */
                    me.clear = function () {
                        me._editor.clear();
                    };
                };


                // inherit
                M.qtypes.omerocommon.MultilanguageAttoEditor.prototype = new M.qtypes.omerocommon.MultilanguageElement();

                // correct the constructor
                M.qtypes.omerocommon.MultilanguageAttoEditor.prototype.constructor = M.qtypes.omerocommon.MultilanguageAttoEditor;

                // set the parent
                M.qtypes.omerocommon.MultilanguageAttoEditor.prototype.parent = M.qtypes.omerocommon.MultilanguageElement.prototype;


                var prototype = M.qtypes.omerocommon.MultilanguageAttoEditor.prototype;

                /**
                 * Save the current string
                 */
                prototype.save = function () {
                    var text = this._editor.getText();
                    this.setLocaleText(text, this._current_language);
                };

                /**
                 * Updates the viewer to show the current localized text
                 *
                 * @param language the new language
                 */
                prototype.changeLanguage = function (language) {
                    // call the default behaviour
                    this.parent.changeLanguage.call(this, language);

                    // update the editor with the current locale text
                    var text = this._locale_text_map[language] || "";
                    this._editor.setText(text);
                };

            }
        };
    }
);