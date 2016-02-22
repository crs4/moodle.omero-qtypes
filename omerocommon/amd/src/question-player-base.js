// Copyright (c) 2015-2016, CRS4
//
// Permission is hereby granted, free of charge, to any person obtaining a copy of
// this software and associated documentation files (the "Software"), to deal in
// the Software without restriction, including without limitation the rights to
// use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
// the Software, and to permit persons to whom the Software is furnished to do so,
// subject to the following conditions:
//
// The above copyright notice and this permission notice shall be included in all
// copies or substantial portions of the Software.
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
// FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
// COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
// IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
// CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

/**
 * UI Controller with basic logic for the question player.
 *
 * @package    qtype
 * @subpackage omerocommon
 * @copyright  2015-2016 CRS4
 * @license    https://opensource.org/licenses/mit-license.php MIT license
 */
define([
        'jquery',
        'qtype_omerocommon/moodle-forms-utils',
        'qtype_omerocommon/answer-base',
        'qtype_omerocommon/multilanguage-element',
        'qtype_omerocommon/multilanguage-attoeditor',
        'qtype_omerocommon/image-viewer'
    ],
    function (jQ, Editor, FormUtils, mle, mlat, ImageViewer) {
        // Private functions.

        var $ = jQuery;

        var CONTROL_KEYS = {
            GOTO: "goto_marker_ctrl_id"
        };

        function cid(config, control) {
            return config.answer_input_name.replace(":", "-") + "-" + control;
        }

        function addFocusAreasInfo(player) {
            var me = player;
            var config = me._config;


            for (var i in config.focusable_rois) {
                // as a intial assumption, we consider every ROI to show as a focus area
                var focus_area_id = config.focusable_rois[i];
                var focus_area_details = me._image_viewer_controller.getShape(focus_area_id);
                if (focus_area_details) {
                    var color = focus_area_details.toJSON()["stroke_color"];
                    var marker_info_container = cid(config, CONTROL_KEYS.GOTO) + focus_area_id + '_container';
                    var label = focus_area_id.replace("_", " ");
                    label = label.charAt(0).toUpperCase() + label.substring(1);
                    color = color ? 'style="color: ' + color + ';"' : '';
                    var focus_area_info_el = $('<div id="' + marker_info_container + '">' +
                        '<i id="' + cid(config, CONTROL_KEYS.GOTO) + focus_area_id + '_btn" ' +
                        ' class="glyphicon glyphicon-map-marker" ' + color + '></i>' +
                            //label +
                        ((parseInt(i) + 1) != config.focusable_rois.length ? ", " : " ") +
                        "</div>");
                    me._focus_areas_container.append(focus_area_info_el);

                    // register the listener for the 'jump to'
                    $("#" + cid(config, CONTROL_KEYS.GOTO) + focus_area_id + '_btn').bind(
                        'click', {'marker_id': focus_area_id},
                        function (event) {
                            me._image_viewer_controller.setFocusOnRoiShape(event.data.marker_id, true);
                        }
                    );
                }
            }

            me._image_viewer_controller.showRoiShapes(config.focusable_rois, true);
        }

        // Public functions

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

            // identifier of the focus area container
            this._focus_areas_container = $("#" + config["focus_areas_container"]);

            // build the ImaveViewer controller
            var viewer_ctrl = new ImageViewer(
                config.image_id, config.image_properties,
                config.image_server, config.image_viewer_container, config.image_annotations_canvas_id,
                config.viewer_model_server);
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


        /**
         *
         */
        prototype.showFocusAreas = function () {
            addFocusAreasInfo(this);
        };

        return M.qtypes.omerocommon.QuestionPlayerBase;
    }
);