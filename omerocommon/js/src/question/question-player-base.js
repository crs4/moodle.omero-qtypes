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
                };


                /* Statics methods */

                M.qtypes.omerocommon.QuestionPlayerBase.getInstance = function () {
                    return M.qtypes.omerocommon.QuestionPlayerBase.instance;
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
                        config.image_server, config.image_viewer_container);
                    this._image_viewer_controller = viewer_ctrl;
                };

                /**
                 * Start the question player
                 */
                prototype.start = function () {
                    this._image_viewer_controller.open(true, function(){
                        console.log("ImageViewer initialized!!!");
                    });
                };
            }
        };
    }
);