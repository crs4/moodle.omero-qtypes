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
                 * Defines MoodleFormUtils class
                 * @type {{}}
                 */
                M.qtypes.omerocommon.MultilanguageAttoEditor = function (element_id) {

                    // the reference to this scope
                    var me = this;

                    // Call the parent constructor
                    M.qtypes.omerocommon.MultilanguageElement.call(this, element_id);


                    me._editor = new M.qtypes.omerocommon.MoodleAttoEditor(element_id);
                    me._editor.init();

                    me.save = function(){
                        var text = me._editor.getText();
                        alert("Text: " + text);
                        me.setLocaleText(text, me._current_language);
                    };

                    me.changeLanguage = function (language) {
                        // call the default behaviour
                        this.parent.changeLanguage.call(me, language);

                        // update the editor with the current locale text
                        var text = me._locale_text_map[language] || "";
                        me._editor.setText(text);
                    };

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
            }
        };
    }
);