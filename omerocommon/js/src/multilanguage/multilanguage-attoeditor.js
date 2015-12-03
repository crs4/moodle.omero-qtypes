/**
 * Created by kikkomep on 12/2/15.
 */

define("qtype_omerocommon/multilanguage-attoeditor",
    [
        'jquery',
        'qtype_omerocommon/moodle-forms-utils',
        'qtype_omerocommon/multilanguage-element'
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

                };


                // inherit
                M.qtypes.omerocommon.MultilanguageAttoEditor.prototype = new M.qtypes.omerocommon.MultilanguageElement();

                // correct the constructor
                M.qtypes.omerocommon.MultilanguageAttoEditor.prototype.constructor = M.qtypes.omerocommon.MultilanguageAttoEditor;
            }
        };
    }
);