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
    function (j, a, b, c, d, e, f) {

        // Private functions.
        // ...
        var $ = jQuery;

        // Public functions
        return {
            initialize: function (str) {

                $(document).ready(function () {

                    console.log("Initialized", this);

                    // defines the basic package
                    M.qtypes = M.qtypes || {};

                    // defines the specific package of this module
                    M.qtypes.omerointeractive = M.qtypes.omerointeractive || {};

                    //var common = M.qtypes.omerocommon;
                    //var interactive = M.qtypes.omerointeractive;
                    //
                    //var question_editor = new interactive.QuestionEditorInteractive();
                    //question_editor.initialize();
                    //
                    //
                    //// TODO: remove me!!!!!
                    //var mgt = new ImageModelManager("http://omero-test.crs4.it:8080", "1");
                    //mgt.addEventListener(question_editor);
                    //mgt.loadRoisInfo(function(){
                    //    console.log("Loaded ROIs");
                    //});
                    //
                    //
                    ////TODO: remove me... just to debug
                    //window.theTable = question_editor._roi_shape_table;
                    //window.question_editor = question_editor;
                    //window.model_manager = mgt;
                });
            },


            test: function(question){
                console.log($("#omero-image-view-lock").bootstrapToggle(), $(document));
                alert("TEST");

                $("#omero-image-view-lock").on("change", function(){
                    console.log("Changed!!!");
                })
            }
        };
    }
);