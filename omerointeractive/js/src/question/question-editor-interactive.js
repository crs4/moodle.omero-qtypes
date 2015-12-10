/**
 * Created by kikkomep on 12/3/15.
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
                };


                M.qtypes.omerointeractive.QuestionEditorInteractive.getInstance = function () {
                    if (!M.qtypes.omerocommon.QuestionEditorBase.instance) {
                        M.qtypes.omerocommon.QuestionEditorBase.instance =
                            new M.qtypes.omerointeractive.QuestionEditorInteractive();
                    }
                    return M.qtypes.omerocommon.QuestionEditorBase.instance;
                };
            },


            main: function (answers_section_id, fraction_options) {

                console.log(fraction_options);
                $(document).ready(
                    function () {
                        var instance = M.qtypes.omerointeractive.QuestionEditorInteractive.getInstance();
                        instance.initialize(answers_section_id, fraction_options);
                        window.qem = instance;

                        console.log($("#omero-image-view-lock"), document.getElementById("omero-image-view-lock"));

                        $(function () {
                            //$('#omero-image-view-lock').bootstrapToggle();
                        });
                        //$("#omero-image-view-lock").bootstrapToggle('on');
                        document.getElementById("omero-image-view-lock").addEventListener("change", function () {
                            alert("Changed!!!");
                        });
                    }
                );
            }
        };
    }
);