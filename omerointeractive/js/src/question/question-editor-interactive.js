/**
 * Created by kikkomep on 12/3/15.
 */
/**
 * Created by kikkomep on 12/2/15.
 */

define("qtype_omerointeractive/question-editor-interactive",
    [
        'jquery',
        'qtype_omerocommon/moodle-forms-utils',
        'qtype_omerocommon/answer-base',
        'qtype_omerocommon/multilanguage-element',
        'qtype_omerocommon/multilanguage-attoeditor',
        'qtype_omerocommon/question-editor-base'
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
                M.qtypes.omerointeractive = M.qtypes.omerointeractive || {};

                /**
                 * Defines MoodleFormUtils class
                 * @type {{}}
                 */
                M.qtypes.omerointeractive.QuestionEditorInteractive = function () {

                    // the reference to this scope
                    var me = this;

                    // Call the parent constructor
                    M.qtypes.omerocommon.QuestionEditorBase.call(this);
                };

                // inherit
                M.qtypes.omerointeractive.QuestionEditorInteractive.prototype =
                    new M.qtypes.omerocommon.QuestionEditorBase();

                // correct the constructor
                M.qtypes.omerointeractive.QuestionEditorInteractive.prototype.constructor =
                    M.qtypes.omerointeractive.QuestionEditorInteractive;

                M.qtypes.omerointeractive.QuestionEditorInteractive.prototype.parent =
                    M.qtypes.omerocommon.QuestionEditorBase.prototype;

                // local reference to the current prototype
                var prototype = M.qtypes.omerointeractive.QuestionEditorInteractive.prototype;

                /**
                 * Performs the initialization
                 */
                prototype.initialize = function () {
                    this.parent.initialize.call(this);
                }
            }
        };
    }
);