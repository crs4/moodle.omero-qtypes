/**
 * Created by kikkomep on 12/2/15.
 */

define("qtype_omerocommon/answer-base",
    [
        'jquery',
        'qtype_omerocommon/moodle-forms-utils',
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
                M.qtypes.omerocommon = M.qtypes.omerocommon || {};


                /**
                 * Defines MoodleFormUtils class
                 * @type {{}}
                 */
                M.qtypes.omerocommon.AnswerBase = function (answer_list_container_id) {

                    // the reference to this scope
                    var me = this;

                    // reference to the container of all answers
                    me._answer_list_container = $("#" + answer_list_container_id);

                    //
                    me._form_utils = new M.qtypes.omerocommon.MoodleFormUtils();

                    // the id of this answerContainer
                    me.answerContainerId = me._form_utils.generateGuid();


                };


                M.qtypes.omerocommon.AnswerBase.prototype = {

                    /**
                     * Builds the answer
                     *
                     * @private
                     */
                    _build: function () {
                        // the reference to this scope
                        var me = this;
                        me._answer_container = $('<div class="fitem" id="' + me.answerContainerId + '"></div>');
                        me._answer_list_container.append(me._answer_container);

                        me._form_utils.appendElement(me._answer_container, "Grade", "<select ><option>1</option></select>");
                        me._form_utils.appendElement(me._answer_container, "Feedback", "<textarea>xxx</textarea>");
                    },

                    /**
                     * Shows the answer
                     */
                    show: function () {
                        // the reference to this scope
                        var me = this;
                        if (!me._answer_container)
                            me._build();
                        else
                            me._answer_list_container.append(me._answer_container);
                    },


                    /**
                     * Hides the answer
                     */
                    hide: function () {
                        // the reference to this scope
                        var me = this;
                        if (me._answer_container)
                            me._answer_container.remove();
                    }
                }
            }
        };
    }
);