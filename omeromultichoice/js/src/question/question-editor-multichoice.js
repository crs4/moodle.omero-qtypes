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
                M.qtypes.omeromultichoice.QuestionEditorMultichoice = function () {

                    // parent constructor
                    M.qtypes.omerocommon.QuestionEditorBase.call(this);

                    // the reference to this scope
                    var me = this;
                };

                // inherit
                M.qtypes.omeromultichoice.QuestionEditorMultichoice.prototype = new M.qtypes.omerocommon.QuestionEditorBase();

                M.qtypes.omeromultichoice.QuestionEditorMultichoice.getInstance = function () {
                    if (!M.qtypes.omerocommon.QuestionEditorBase.instance) {
                        M.qtypes.omerocommon.QuestionEditorBase.instance =
                            new M.qtypes.omeromultichoice.QuestionEditorMultichoice();
                    }
                    return M.qtypes.omerocommon.QuestionEditorBase.instance;
                };

                // A local reference to the prototype
                var prototype = M.qtypes.omeromultichoice.QuestionEditorMultichoice.prototype;

                // correct the constructor
                prototype.constructor = M.qtypes.omeromultichoice.QuestionEditorMultichoice;

                // parent prototype
                prototype.parent = M.qtypes.omerocommon.QuestionEditorBase.prototype;


                prototype.buildAnswer = function (answer_number, fraction_options) {
                    return new M.qtypes.omeromultichoice.AnswerPlaintext(this._answers_section_id, answer_number, fraction_options);
                };


                prototype._build_answer_controls = function () {
                    try {
                        this._toolbar_container = $('<div id="answers_toolbar" class="panel"></div>');
                        this._toolbar_container_body = $('<div class="panel-body"></div>');
                        this._add_answer_btn = $('<button id="add-answer-btn" type="button" class="btn btn-info">Add answer</button>');

                        $("#" + this._answers_section_id).prepend(this._toolbar_container);
                        this._toolbar_container.prepend(this._toolbar_container_body);
                        this._toolbar_container_body.prepend(this._add_answer_btn);

                        var me = this;
                        this._add_answer_btn.on("click", function () {
                            me.addAnswer();
                        });

                    } catch (e) {
                        console.error("Error while creating the toolbar", e);
                    }
                };
            },


            main: function (answers_section_id, fraction_options) {

                console.log(fraction_options);
                $(document).ready(
                    function () {
                        var instance = M.qtypes.omeromultichoice.QuestionEditorMultichoice.getInstance();
                        instance.initialize(answers_section_id, fraction_options);
                        window.qem = instance;

                        //var myFunc = function(){
                        //
                        //    console.log("Checking ....", jQuery().bootstrapToggle);
                        //    if(jQuery().bootstrapToggle){
                        //        console.log("Found");
                        //
                        //        clearInterval(myFuc);
                        //    }
                        //};

                        //setInterval(myFunc, 1000);

                        //$('#omero-image-view-lock').bootstrapToggle();

                        console.log($("#omero-image-view-lock"), document.getElementById("omero-image-view-lock"));
                        //console.log( $("#omero-image-view-lock").bootstrapToggle);
                        //alert("Check");


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
        }
    }
);

