/**
 * Created by kikkomep on 12/3/15.
 */
/**
 * Created by kikkomep on 12/2/15.
 */

define("qtype_omeromultichoice/question-editor-multichoice",
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
                M.qtypes.omeromultichoice = M.qtypes.omeromultichoice || {};

                /**
                 * Builds a new instance
                 *
                 * @constructor
                 */
                M.qtypes.omeromultichoice.QuestionEditorMultichoice = function (answers_section_id, fraction_options) {

                    // parent constructor
                    M.qtypes.omerocommon.QuestionEditorBase.call(this, answers_section_id, fraction_options);

                    // the reference to this scope
                    var me = this;

                    // section id
                    me._answers_section_id = answers_section_id;
                };

                // inherit
                M.qtypes.omeromultichoice.QuestionEditorMultichoice.prototype = new M.qtypes.omerocommon.QuestionEditorBase();

                // A local reference to the prototype
                var prototype = M.qtypes.omeromultichoice.QuestionEditorMultichoice.prototype;

                // correct the constructor
                prototype.constructor = M.qtypes.omeromultichoice.QuestionEditorMultichoice;

                // parent prototype
                prototype.parent = M.qtypes.omerocommon.QuestionEditorBase.prototype;


                prototype.buildAnswer = function (answer_number, fraction_options) {
                    return new M.qtypes.omeromultichoice.AnswerPlaintext(this._answers_section_id, answer_number, fraction_options);
                }
            },


            main: function (answers_section_id, fraction_options, question) {

                console.log(fraction_options);
                console.log(question);
                $(document).ready(function () {
                        var instance = new M.qtypes.omeromultichoice.QuestionEditorMultichoice(
                            answers_section_id,
                            fraction_options
                        );
                        instance.initialize();
                        window.qem = instance;
                    }
                );
            }
        }
    }
);

