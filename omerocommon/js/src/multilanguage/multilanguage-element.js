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

                    // a reference to this scope
                    var me = this;

                    //
                    me.element_id = element_id;

                    // instance
                    me._form_utils = new M.qtypes.omerocommon.MoodleFormUtils();

                    //
                    //me._form_utils.initTextArea(element_id);
                }
            }
        };
    }
);