/**
 * Created by kikkomep on 12/3/15.
 */

define("qtype_omerointeractive/question-player-interactive",
    [
        'jquery',
        'qtype_omerocommon/moodle-forms-utils',
        'qtype_omerocommon/answer-base',
        'qtype_omerocommon/multilanguage-element',
        'qtype_omerocommon/multilanguage-attoeditor',
        'qtype_omerocommon/question-player-base'
    ],
    function (j, Editor, FormUtils) {
        // Private functions.
        var $ = jQuery;

        // Public functions
        return {
            initialize: function (str) {
                console.log("Initialized", this);

                // defines the basic package
                M.qtypes = M.qtypes || {};

                // defines the specific package of this module
                M.qtypes.omerointeractive = M.qtypes.omerointeractive || {};

                /**
                 * Defines MoodleFormUtils class
                 * @type {{}}
                 */
                M.qtypes.omerointeractive.QuestionPlayerInteractive = function () {

                    // the reference to this scope
                    var me = this;

                    // Call the parent constructor
                    M.qtypes.omerocommon.QuestionPlayerBase.call(this);
                };

                // inherit
                M.qtypes.omerointeractive.QuestionPlayerInteractive.prototype =
                    new M.qtypes.omerocommon.QuestionPlayerBase();

                // correct the constructor
                M.qtypes.omerointeractive.QuestionPlayerInteractive.prototype.constructor =
                    M.qtypes.omerointeractive.QuestionPlayerInteractive;

                M.qtypes.omerointeractive.QuestionPlayerInteractive.prototype.parent =
                    M.qtypes.omerocommon.QuestionPlayerBase.prototype;

                M.qtypes.omerointeractive.QuestionPlayerInteractive.getInstance = function () {
                    if (!M.qtypes.omerocommon.QuestionPlayerBase.instance) {
                        M.qtypes.omerocommon.QuestionPlayerBase.instance =
                            new M.qtypes.omerointeractive.QuestionPlayerInteractive();
                    }
                    return M.qtypes.omerocommon.QuestionPlayerBase.instance;
                };

                // local reference to the current prototype
                var prototype = M.qtypes.omerointeractive.QuestionPlayerInteractive.prototype;

                /**
                 * Performs the initialization
                 */
                prototype.initialize = function () {
                    this.parent.initialize.call(this);
                };

                /**
                 * Starts the question player
                 */
                prototype.start = function () {
                    this.parent.start.call(this);
                };
            },


            /**
             *
             *
             */
            start: function () {

                $(document).ready(
                    function () {
                        var instance = M.qtypes.omerointeractive.QuestionPlayerInteractive.getInstance();
                        instance.initialize();
                        window.qpi = instance;

                        console.log("Question interactive player initialized!!!");
                    }
                );
            }
        };
    }
);