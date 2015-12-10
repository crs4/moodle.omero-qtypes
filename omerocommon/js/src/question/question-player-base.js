/**
 * Created by kikkomep on 12/3/15.
 */

define("qtype_omerocommon/question-player-base",
    [
        'jquery',
        'qtype_omerocommon/moodle-forms-utils',
        'qtype_omerocommon/answer-base',
        'qtype_omerocommon/multilanguage-element',
        'qtype_omerocommon/multilanguage-attoeditor'
    ],
    function (jQ, Editor, FormUtils) {
        // Private functions.

        var $ = jQuery;

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
                M.qtypes.omerocommon.QuestionPlayerBase = function () {

                    // the reference to this scope
                    var me = this;
                };


                /* Statics methods */

                M.qtypes.omerocommon.QuestionPlayerBase.getInstance = function () {
                    return M.qtypes.omerocommon.QuestionPlayerBase.instance;
                };


                // local reference to the current prototype
                var prototype = M.qtypes.omerocommon.QuestionPlayerBase.prototype;

                /* Instance methods */

                /**
                 * Initialization
                 */
                prototype.initialize = function(){}

                /**
                 * Start the question player
                 */
                prototype.start = function(){}
            }
        };
    }
);