/**
 * Created by kikkomep on 12/2/15.
 */

define("qtype_omerointeractive/main",
    [
        'jquery',
        'qtype_omerocommon/multilanguage-element',
        'qtype_omerocommon/multilanguage-attoeditor',
        'qtype_omerocommon/moodle-forms-utils',
        'qtype_omerointeractive/answer-group',
        'qtype_omerocommon/question-editor-base',
        'qtype_omerointeractive/question-editor-interactive'
    ],
    function ($, a, b, c, d, e, f) {

        // Private functions.
        // ...

        // Public functions
        return {
            initialize: function (str) {

                $(document).ready(function () {

                    console.log("Initialized", this);

                    // defines the basic package
                    M.qtypes = M.qtypes || {};

                    // defines the specific package of this module
                    M.qtypes.omerointeractive = M.qtypes.omerointeractive || {};

                    var common = M.qtypes.omerocommon;
                    var interactive = M.qtypes.omerointeractive;

                    var question_editor = new interactive.QuestionEditorInteractive();
                    question_editor.initialize();
                });
            }
        };
    }
);