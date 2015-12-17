/**
 * Created by kikkomep on 12/2/15.
 */

define("qtype_omerocommon/main",
    [
        'jquery',
        'qtype_omerocommon/multilanguage-element',
        'qtype_omerocommon/multilanguage-attoeditor',
        'qtype_omerocommon/moodle-forms-utils'
    ],
    function ($, a, b, c) {

        // Private functions.
        // ...

        // Public functions
        return {
            initialize: function (str) {

                console.log("Initialized", this);
                M.qtypes.omerocommon.moodle_form_utils = new M.qtypes.omerocommon.MoodleFormUtils();
            }
        };
    }
);