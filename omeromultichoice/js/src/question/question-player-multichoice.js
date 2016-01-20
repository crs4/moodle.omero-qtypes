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

        var initialized = false;

        var markers_config = {
            'fill_color': "#ffffff",
            'fill_alpha': '0.4',
            'stroke_width': 10
        };

        /**
         * Starts the question player
         */
        function start(player) {
            var me = player;
            var config = me._config;
            me._image_viewer_controller.open(true, function () {
                console.log("Question Player initialized!!!", config);

                var img_size = me._image_viewer_controller.getImageSize();
                var markers_size = ((img_size.width + img_size.height) / 2) * 0.0025;
                markers_config.markers_size = markers_size;
                console.log(markers_size);

                me._image_viewer_controller
                    .configureMarkingTool(markers_config, 0);

                player._image_viewer_controller.setNavigationLock(config.image_navigation_locked);

                if (!config.correction_mode) {
                    me._image_viewer_controller.showRoiShapes(config.visible_rois, true);
                } else {
                    console.log("Answers: ", config.answers);
                    showResults(me);
                }
            });
        }

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

                // local reference to the current prototype
                var prototype = M.qtypes.omeromultichoice.QuestionPlayerMultichoice.prototype;

                /**
                 * Performs the initialization
                 */
                prototype.initialize = function (config) {
                    var me = this;

                    if (me.initialized) console.log("Already Initialized");
                    else {

                        this.parent.initialize.call(this, config);

                        me._answer_input_name = config.answer_input_name;
                        console.log("Setted the answer prefix", me._answer_input_name);

                        // initialize image positioning control
                        $("#" + config.question_answer_container + " .restore-image-center-btn").click(function () {
                            me._image_viewer_controller.updateViewFromProperties(config.image_properties);
                        });

                        me.initialized = true;
                        console.log("Question multichoice player initialized!!!");

                        // automatically start the player
                        start(me);
                    }
                };
            },


            /**
             *
             *
             */
            start: function (config) {

                $(document).ready(
                    function () {
                        var instance = new M.qtypes.omeromultichoice.QuestionPlayerMultichoice();
                        instance.initialize(config);
                    }
                );
            }
        };
    }
);