/**
 * Created by kikkomep on 12/3/15.
 */

define("qtype_omerocommon/question-player-base",
    [
        'jquery',
        'qtype_omerocommon/moodle-forms-utils',
        'qtype_omerocommon/answer-base',
        'qtype_omerocommon/multilanguage-element',
        'qtype_omerocommon/multilanguage-attoeditor',
        'qtype_omerocommon/image-viewer'
    ],
    function (jQ, Editor, FormUtils) {
        // Private functions.

        var $ = jQuery;

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
                M.qtypes.omerocommon.QuestionPlayerBase = function () {

                    // the reference to this scope
                    var me = this;

                    // register the current instance
                    if (me.constructor != M.qtypes.omerocommon.QuestionPlayerBase)
                        M.qtypes.omerocommon.QuestionPlayerBase.instances.push(me);
                };


                // list of player instances
                M.qtypes.omerocommon.QuestionPlayerBase.instances = [];

                /* Static methods */

                /**
                 * Returns the list of player instances
                 * @returns {Array}
                 */
                M.qtypes.omerocommon.QuestionPlayerBase.getInstances = function () {
                    return M.qtypes.omerocommon.QuestionPlayerBase.instances;
                };

                // local reference to the current prototype
                var prototype = M.qtypes.omerocommon.QuestionPlayerBase.prototype;

                /* Instance methods */

                /**
                 * Initialization
                 */
                prototype.initialize = function (config) {
                    // set tht configuration
                    this._config = config;
                    console.log("Configuration", config);

                    // build the ImaveViewer controller
                    var viewer_ctrl = new M.qtypes.omerocommon.ImageViewer(
                        config.image_id, config.image_properties,
                        config.image_server, config.image_viewer_container, config.image_annotations_canvas_id);
                    this._image_viewer_controller = viewer_ctrl;
                };

                /**
                 * Start the question player
                 */
                prototype.start = function () {
                    this._image_viewer_controller.open(true, function () {
                        console.log("ImageViewer initialized!!!");
                    });
                };
            }
        };
    }
);