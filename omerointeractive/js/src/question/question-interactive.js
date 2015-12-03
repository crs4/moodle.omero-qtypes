/**
 * Created by kikkomep on 12/3/15.
 */
/**
 * Created by kikkomep on 12/2/15.
 */

define("qtype_omerointeractive/question-interactive",
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
                 * Defines MoodleFormUtils class
                 * @type {{}}
                 */
                M.qtypes.omerocommon.QuestionInteractive = function () {

                    // the reference to this scope
                    var me = this;

                    // Call the parent constructor
                    M.qtypes.omerocommon.QuestionBase.call(this);
                };

                // inherit
                M.qtypes.omerocommon.QuestionInteractive.prototype = new M.qtypes.omerocommon.QuestionBase();

                // correct the constructor
                M.qtypes.omerocommon.QuestionInteractive.prototype.constructor = M.qtypes.omerocommon.AnswerPlaintext;

                // local reference to the current prototype
                var prototype = M.qtypes.omerocommon.QuestionInteractive.prototype;

            }
        };
    }
);