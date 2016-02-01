/**
 * Created by kikkomep on 12/2/15.
 */

define("qtype_omeromultichoice/answer-plaintext",
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
                 * Defines MoodleFormUtils class
                 * @type {{}}
                 */
                M.qtypes.omeromultichoice.AnswerPlaintext = function (answer_list_container_id, answer_number, fraction_options) {

                    // the reference to this scope
                    var me = this;

                    // Call the parent constructor
                    M.qtypes.omerocommon.AnswerBase.call(this, answer_list_container_id, answer_number, fraction_options);

                };


                // inherit
                M.qtypes.omeromultichoice.AnswerPlaintext.prototype = new M.qtypes.omerocommon.AnswerBase();

                // correct the constructor
                M.qtypes.omeromultichoice.AnswerPlaintext.prototype.constructor = M.qtypes.omeromultichoice.AnswerPlaintext;

                // short
                var prototype = M.qtypes.omeromultichoice.AnswerPlaintext.prototype;


                /**
                 * Builds the answer
                 *
                 * @private
                 */
                prototype._build = function () {
                    var me = this;

                    var panel = $('<div class="panel panel-default"></div>');
                    me._answer_list_container.append(panel);

                    var panel_heading = $('<div class="panel-heading">' +
                        '<h4 class="panel-title">' +
                        '<span id="head-answer-' + this._answer_number + '">Answer ' + (this._answer_number + 1) + '</span>'+
                        '<div style="display: inline-block; float: right; text-align: right;">' +
                        '<a href="javascript:void(0)" id="delete-answer-' + me._answer_number + '" >' +
                        '<i class="red glyphicon glyphicon-remove-sign" style="font-size: 2m;"></i></a>' +
                        '</h4>' +
                        '</div>' +
                        '<div style="display: block; float: right; margin: 20px 30px;">' +
                        '</div>');
                    panel.append(panel_heading);

                    var panel_body = $('<div class="panel-body"></div>');
                    panel.append(panel_body);

                    me._answer_container = $('<div class="fitem" id="' + me._answer_number + '"></div>');
                    panel_body.append(me._answer_container);

                    // answer text
                    me._build_textarea_of("answer", "Text");

                    // answer format
                    me._build_hidden_of("answerformat", "1");

                    // answer grade
                    me._build_select_of("fraction", M.util.get_string("answer_grade", "qtype_omerocommon"));

                    // answer feedback
                    me._build_textarea_of("feedback",  M.util.get_string("feedback", "question"));

                    // answer format
                    me._build_hidden_of("feedbackformat", "1");

                    // reference to the head
                    me._answer_head = $('#head-answer-' + this._answer_number);

                    // register the delete event
                    $("#delete-answer-" + me._answer_number).on("click", function () {
                        me.remove();
                    });

                    // registers the panel as main container
                    me._answer_container = panel;
                };
            }
        };
    }
)
;