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