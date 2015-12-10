/**
 * Created by kikkomep on 12/3/15.
 */

define("qtype_omeromultichoice/question-player-multichoice",
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
                M.qtypes.omeromultichoice = M.qtypes.omeromultichoice || {};

                /**
                 * Defines MoodleFormUtils class
                 * @type {{}}
                 */
                M.qtypes.omeromultichoice.QuestionPlayerMultichoice = function () {

                    // the reference to this scope
                    var me = this;

                    // Call the parent constructor
                    M.qtypes.omerocommon.QuestionPlayerBase.call(this);
                };

                // inherit
                M.qtypes.omeromultichoice.QuestionPlayerMultichoice.prototype =
                    new M.qtypes.omerocommon.QuestionPlayerBase();

                // correct the constructor
                M.qtypes.omeromultichoice.QuestionPlayerMultichoice.prototype.constructor =
                    M.qtypes.omeromultichoice.QuestionPlayerMultichoice;

                M.qtypes.omeromultichoice.QuestionPlayerMultichoice.prototype.parent =
                    M.qtypes.omerocommon.QuestionPlayerBase.prototype;

                M.qtypes.omeromultichoice.QuestionPlayerMultichoice.getInstance = function () {
                    if (!M.qtypes.omerocommon.QuestionPlayerBase.instance) {
                        M.qtypes.omerocommon.QuestionPlayerBase.instance =
                            new M.qtypes.omeromultichoice.QuestionPlayerMultichoice();
                    }
                    return M.qtypes.omerocommon.QuestionPlayerBase.instance;
                };

                // local reference to the current prototype
                var prototype = M.qtypes.omeromultichoice.QuestionPlayerMultichoice.prototype;

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
                        var instance = M.qtypes.omeromultichoice.QuestionPlayerMultichoice.getInstance();
                        instance.initialize();
                        window.qpm = instance;

                        console.log("Question multichoice player initialized!!!");
                    }
                );
            }
        };
    }
);