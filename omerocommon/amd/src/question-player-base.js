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
        'qtype_omerocommon/image-viewer',
        'qtype_omerocommon/message-dialog',
        'qtype_omerocommon/modal-image-panel'
    ],
    /* jshint curly: false */
    /* globals console, jQuery, document */
    function (jQ, FormUtils, ab, mle, mlat, ImageViewer) {

        // jQuery reference
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

            function handleFocusClick(event) {
                me._image_viewer_controller.setFocusOnRoiShape(event.data.marker_id, true);
            }

            for (var i in config.focusable_rois) {
                // as a intial assumption, we consider every ROI to show as a focus area
                var focus_area_id = config.focusable_rois[i];
                var focus_area_details = me._image_viewer_controller.getShape(focus_area_id);
                if (focus_area_details) {
                    var color = focus_area_details.toJSON().stroke_color;
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
                        handleFocusClick
                    );
                }
            }

            me._image_viewer_controller.showRoiShapes(config.focusable_rois, true);
        }


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
            if (M.cfg.developerdebug) {
                if (me.constructor != M.qtypes.omerocommon.QuestionPlayerBase)
                    M.qtypes.omerocommon.QuestionPlayerBase.instances.push(me);
            }
        };


        // list of player instances
        if (M.cfg.developerdebug)
            M.qtypes.omerocommon.QuestionPlayerBase.instances = [];

        /* Static methods */

        /**
         * Returns the list of player instances
         * Note that is available for debug
         *
         * @returns {Array}
         */
        if (M.cfg.developerdebug) {
            M.qtypes.omerocommon.QuestionPlayerBase.getInstances = function () {
                return M.qtypes.omerocommon.QuestionPlayerBase.instances;
            };
        }

        // local reference to the current prototype
        var prototype = M.qtypes.omerocommon.QuestionPlayerBase.prototype;

        /* Instance methods */

        /**
         * Initialization
         */
        prototype.initialize = function (config) {
            // scope reference
            var me = this;

            // set tht configuration
            me._config = config;
            console.log("Configuration", config);

            // identifier of the focus area container
            me._focus_areas_container = $("#" + config.focus_areas_container);

            // build the ImaveViewer controller
            var viewer_ctrl = new ImageViewer(
                config.image_id, config.image_properties,
                config.image_server, config.image_viewer_container, config.image_annotations_canvas_id,
                config.viewer_model_server);
            me._image_viewer_controller = viewer_ctrl;

            me._message_dialog = new M.qtypes.omerocommon.MessageDialog(config.image_frame_id);

            me._modal_image_panel = M.qtypes.omerocommon.ModalImagePanel.getInstance();
            me._modal_image_panel.setImageServer(config.image_server);
            me._modal_image_panel.setImageModelServer(config.viewer_model_server);
            me._modal_image_panel.setImageModelManager(viewer_ctrl.getImageModelManager());
            $(".feedback-image").click(function (event) {
                var img_el = $(event.target);
                if (img_el.prop("tagName").toUpperCase() == "I")
                    img_el = img_el.parent();
                me._modal_image_panel.show(me,
                    img_el.attr("imageid"),
                    JSON.parse((img_el.attr("imageproperties"))),
                    img_el.attr("imagelock"),
                    img_el.attr("visiblerois").split(","),
                    img_el.attr("focusablerois").split(","));
            });
            me._modal_image_panel.setHeight(500);
            me._modal_image_panel.setDefaultOffeset(115);
            me._modal_image_panel.center();
            me._modal_image_panel.enableCenterAuto();

            me._invalidator_panel = $("#" + config.question_answer_container + "-invalidator-panel");
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

        /**
         * utility function to check ROIs validity
         * @private
         */
        prototype._checkRoisValidity = function (unavailable_roi_list) {
            unavailable_roi_list = unavailable_roi_list || [];
            unavailable_roi_list = unavailable_roi_list.concat(
                this._image_viewer_controller.checkRois(this._config.visible_rois)
            );
            unavailable_roi_list = unavailable_roi_list.concat(
                this._image_viewer_controller.checkRois(this._config.focusable_rois)
            );

            var unavailable_rois = [];
            $.each(unavailable_roi_list, function (i, el) {
                if ($.inArray(String(el), unavailable_rois) === -1) unavailable_rois.push(String(el));
            });
            if (unavailable_rois.length > 0) {
                if (document.location.pathname.indexOf("preview.php") !== -1) {
                    this._message_dialog.showDialogMessage(
                        M.util.get_string('validate_question', 'qtype_omerocommon') +
                        ' "' + this._config.qname + '" ' +
                        M.util.get_string('validate_editor_not_valid', 'qtype_omerocommon') + '!!!<br>' +
                        M.util.get_string('validate_editor_not_existing_rois', 'qtype_omerocommon') +
                        unavailable_rois.join(',') + '.' + '<br>' +
                        M.util.get_string('validate_editor_check_question', 'qtype_omerocommon')
                    );
                } else
                    this._message_dialog.showDialogMessage(
                        M.util.get_string('validate_question', 'qtype_omerocommon') +
                        ' "' + this._config.qname + '" ' +
                        M.util.get_string('validate_player_not_existing_rois', 'qtype_omerocommon')
                    );
                this._invalidator_panel.show();
            }
        };

        // returns the class
        return M.qtypes.omerocommon.QuestionPlayerBase;
    }
);