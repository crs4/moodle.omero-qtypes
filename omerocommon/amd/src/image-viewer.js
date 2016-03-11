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
 * Image viewer controller.
 *
 * @package    qtype
 * @subpackage omerocommon
 * @copyright  2015-2016 CRS4
 * @license    https://opensource.org/licenses/mit-license.php MIT license
 */
/* jshint curly: false */
/* globals console, ViewerController, ImageModelManager, TransformMatrixHelper, TransformMatrixHelper */
/* globals AnnotationsEventsController, AnnotationsController */
define(['jquery'], function ($) {

        /**
         * Utility class
         *
         * @param listeners
         * @param callback
         */
        function notifyListeners(listeners, callback, data) {
            for (var i in listeners) {
                var listenerCallback = listeners[i];
                if (listenerCallback) {
                    listenerCallback(data);
                }
            }
            if (callback)
                callback(data);
        }

        // defines the basic package
        M.qtypes = M.qtypes || {};

        // defines the specific package of this module
        M.qtypes.omerocommon = M.qtypes.omerocommon || {};

        /**
         * Defines an ImageViewer
         *
         * @constructor
         */
        M.qtypes.omerocommon.ImageViewer = function (image_id, image_properties,
                                                     image_server, image_viewer_container_id,
                                                     image_viewer_annotations_canvas_id,
                                                     viewer_model_server,
                                                     viewer_config) {
            this._image_server = image_server;
            this._image_viewer_container_id = image_viewer_container_id;
            this._image_viewer_annotations_canvas_id = image_viewer_annotations_canvas_id;
            this._image_id = image_id;
            this._image_properties = image_properties;
            this._viewer_model_server = viewer_model_server;
            this._listeners = [];
            this._lock_navigation = false;
            this._visible_roi_shape_ids = [];

            // default viewer configuration
            this._viewer_config = {
                'showNavigator': true,
                'showFullPageControl': false,
                'animationTime': 0.01
            };

            this._scalebar_config = {
                "xOffset": 10,
                "yOffset": 10,
                "barThickness": 5,
                "color": "#777777",
                "fontColor": "#000000",
                "backgroundColor": 'rgba(255, 255, 255, 0.5)'
            };

            // update the default configuration
            if (viewer_config) {
                for (var prop in viewer_config) {
                    this._viewer_config[prop] = viewer_config[prop];
                }
            }

            // init loading message
            var container = $("#" + this._image_viewer_container_id);
            if (container) {
                container = container.parent();
                if (container) {
                    var dialog_container = $("#" + this._image_viewer_container_id + '-loading-dialog');
                    this._waiting_dialog = dialog_container;
                }
            }
        };

        // reference to the prototype
        var prototype = M.qtypes.omerocommon.ImageViewer.prototype;

        /**
         *
         * @param callback
         */
        prototype.open = function (load_rois, callback) {
            var me = this;

            // TODO: to change with the controller initialization
            me._viewer_controller = new ViewerController(
                me._image_viewer_container_id,
                me._image_server + "/static/ome_seadragon/img/openseadragon/",
                me._image_server + "/ome_seadragon/deepzoom/get/" + me._image_id + ".dzi",
                me._viewer_config
            );

            // initialize the viewer
            me._viewer_controller.buildViewer();

            // initializes the ImageModelManager
            me._model = new ImageModelManager(me._viewer_model_server, me._image_id);

            // open
            me._viewer_controller.viewer.addHandler("open", function () {

                // Ignore lowest-resolution levels in order to improve load times
                me._viewer_controller.setMinDZILevel(8);

                // Adds the annotation controller
                me._annotations_controller = new AnnotationsController(me._image_viewer_annotations_canvas_id);
                window.annotation_canvas = me._annotations_controller;
                me._annotations_controller.buildAnnotationsCanvas(me._viewer_controller);
                me._viewer_controller.addAnnotationsController(me._annotations_controller, true);

                // update the center and zoom level
                me.updateViewFromProperties(me._image_properties);

                // initialize the scalebar
                me._model.getImageDZI(function (data) {
                    console.log("Loading openseadragon viewer");
                    var image_mpp = data.image_mpp ? data.image_mpp : 0;
                    me._viewer_controller.enableScalebar(image_mpp, me._scalebar_config);
                });

                // loads rois if required
                if (load_rois)
                    me.loadROIs(callback);
                else {
                    // notifies listeners
                    notifyListeners(me._listeners, callback);
                    // hide loading dialog
                    me._waiting_dialog.hide();
                }
            });
        };


        /**
         *
         * @param callback
         */
        prototype.loadROIs = function (callback) {
            var me = this;
            me._model.loadRoisInfo(function (data) {

                //me._roi_id_list = data;
                me._current_roi_list = data;

                // Initialize the list of ROIs
                for (var roi in data) {
                    var shapes = data[roi].shapes;
                    for (var shape in shapes) {
                        var shape_type = shapes[shape].type;
                        var shape_config = {
                            'fill_color': shapes[shape].fillColor,
                            'fill_alpha': shapes[shape].fillAlpha,
                            'stroke_color': shapes[shape].strokeColor,
                            'stroke_alpha': shapes[shape].strokeAlpha,
                            'stroke_width': shapes[shape].strokeWidth
                        };

                        switch (shape_type) {
                            case "Rectangle":
                                me._annotations_controller.drawRectangle(
                                    shapes[shape].id, shapes[shape].x, shapes[shape].y, shapes[shape].width,
                                    shapes[shape].height,
                                    TransformMatrixHelper.fromOMETransform(shapes[shape].transform),
                                    shape_config, false
                                );
                                break;
                            case "Ellipse":
                                me._annotations_controller.drawEllipse(
                                    shapes[shape].id, shapes[shape].cx, shapes[shape].cy,
                                    shapes[shape].rx, shapes[shape].ry,
                                    TransformMatrixHelper.fromOMETransform(shapes[shape].transform),
                                    shape_config,
                                    false
                                );
                                break;
                            case "Line":
                                me._annotations_controller.drawLine(
                                    shapes[shape].id, shapes[shape].x1, shapes[shape].y1,
                                    shapes[shape].x2, shapes[shape].y2,
                                    TransformMatrixHelper.fromOMETransform(shapes[shape].transform),
                                    shape_config,
                                    false
                                );
                                break;
                            default:
                                console.warn('Unable to handle shape type ' + shape_type);
                        }
                    }
                }

                // Hide all shapes
                me._annotations_controller.hideShapes(undefined, false);

                // notify listeners
                notifyListeners(me._listeners, callback, data);

                // hide loading dialog
                me._waiting_dialog.hide();

                // handle resize event
                me._viewer_controller.viewer.addHandler("resize",
                    function (event) {

                        // retrieve the list of shapes to display
                        var marker_ids = me.getMarkerIds();
                        var shapes = me._annotations_controller.getShapesJSON().filter(
                            function (value) {
                                return (marker_ids.indexOf(value.shape_id) !== -1)
                                    || (me._visible_roi_shape_ids.indexOf(String(value.shape_id)) !== -1);
                            }
                        );

                        // clean and rebuild canvas
                        me._annotations_controller.clear();
                        me._annotations_controller.canvas = undefined;
                        me._annotations_controller.buildAnnotationsCanvas(me._viewer_controller);

                        // update the center
                        var img_zoom = me._viewer_controller.getImageZoom();
                        me._annotations_controller.setZoom(img_zoom);
                        var center = me._viewer_controller.getCenter();
                        me._annotations_controller.setCenter(center.x, center.y);

                        // redraw shapes
                        me._annotations_controller.drawShapesFromJSON(shapes);
                    });
            });
        };

        prototype.isNavigationLocked = function () {
            return this._lock_navigation === true;
        };

        prototype.setNavigationLock = function (enable) {
            this._lock_navigation = enable;
            if (enable) {
                this._annotations_controller.disableMouseEvents();
                this._annotation_events_controller.activateTool(this._annotation_events_controller.DUMMY_TOOL, false);
            } else {
                this._annotations_controller.disableMouseEvents();
            }
        };

        prototype.configureMarkingTool = function (markers_config, markers_limit) {
            this._annotation_events_controller =
                new AnnotationsEventsController(this._annotations_controller);
            this._annotation_events_controller.initializeImageMarkingTool(
                markers_config.markers_size, markers_config, markers_limit);
        };

        prototype.enableAddMarkers = function () {
            this._annotations_controller.disableMouseEvents();
            this._annotation_events_controller.activateTool(this._annotation_events_controller.IMAGE_MARKING_TOOL);
        };

        prototype.enableMoveMarkers = function () {
            this._annotation_events_controller.activateTool(this._annotation_events_controller.DUMMY_TOOL, false);
            this._annotations_controller.enableEventsOnShapes(this.getMarkerIds());
        };

        prototype.disableMarkingTool = function () {
            if (!this._lock_navigation)
                this._annotations_controller.disableMouseEvents();
        };

        prototype.removeMarker = function (marker_id) {
            this._annotations_controller.removeMarker(marker_id);
        };

        prototype.removeMarkers = function () {
            var marker_ids = this.getMarkerIds();
            // TODO: check whether the annotation_controller remove is correct or not
            for (var i = marker_ids.length - 1; i >= 0; i--) {
                this.removeMarker(marker_ids[i]);
            }
        };

        prototype.onViewerInitialized = function (listener) {
            this._listeners.push(listener);
        };


        /**
         * Returns the modelManager related to this controller
         *
         * @returns {ImageModelManager|*}
         */
        prototype.getModel = function () {
            return this._model;
        };

        prototype.getMarkerIds = function () {
            return this._annotations_controller.markers_id;
        };

        prototype.getMarkers = function () {
            return this._annotations_controller.getShapes(this._annotations_controller.markers_id);
        };

        prototype.toShapeJSON = function (shape_id) {
            return this._annotations_controller.getShapesJSON([shape_id])[0];
        };

        prototype.getShape = function (shape_id) {
            return this._annotations_controller.getShape(shape_id);
        };

        prototype.getShapes = function (filter) {
            var result = [];
            var markers = this._annotations_controller.markers_id;
            console.log("markers ids...", markers);
            var shapes = this._annotations_controller.getShapes(filter);
            for (var i in shapes) {
                var shape = shapes[i];
                if (markers.indexOf(shape.id) !== -1) continue;
                result.push(shape);
            }
            return result;
        };


        prototype.drawMarker = function (marker, marker_config) {
            // TODO: replace with the more general drawShape
            if (marker.type === "circle") {
                this._annotations_controller.drawCircle(
                    marker.shape_id, marker.center_x, marker.center_y,
                    marker.radius, undefined, marker_config, true);
                this._visible_roi_shape_ids.push(String(marker.shape_id));
            } else console.warn("Marker not supported yet", marker);
        };

        /**
         * Return the properties of the image
         *
         * @returns {{id: *, center: {x: *, y: *}, t: number, z: number, zoom_level: *}}
         */
        prototype.getImageProperties = function () {
            var p = this._viewer_controller.getViewportDetails();
            return {
                "id": this._image_id,
                "center": {
                    "x": p.center_x,
                    "y": p.center_y,
                },
                "t": 1,
                "z": 1,
                "zoom_level": p.zoom_level
            };
        };

        /**
         * Returns the image size
         *
         * @returns {*}
         */
        prototype.getImageSize = function () {
            return this._viewer_controller.getImageDimensions();
        };

        /**
         * Update the view accordingly to the image_properties parameter
         *
         * @param image_properties
         * @returns {boolean}
         */
        prototype.updateViewFromProperties = function (image_properties) {
            var me = this;
            if (!image_properties || !image_properties.center) {
                console.warn("incomplete image properties");
                return false;
            }

            var image_center = me._viewer_controller.getViewportCoordinates(
                image_properties.center.x, image_properties.center.y
            );
            if (image_properties.zoom_level) {
                me._viewer_controller.jumpTo(image_properties.zoom_level, image_center.x, image_center.y);
                console.log("Setting zoom level: " + image_properties.zoom_level);
            } else {
                me._viewer_controller.jumpToPoint(image_center.x, image_center.y);
            }

            console.log("Jumping to " + image_center.x + " -- " + image_center.y);
        };

        /**
         * Returns a relative URL containing all relevant info to display
         * the image currently managed by this ViewerController:
         *
         *  i.e., <IMAGE_ID>?t=<T level>&z=<Z level>&zm=<ZOOM level>
         *                              &x=<X center>
         *                              &y=<Y center>
         *
         * @returns {*}
         */
        prototype.buildDetailedImageRelativeUrl = function () {
            var result = null;
            var viewport_details = this._viewer_controller.getViewportDetails();
            if (viewport_details) {
                return "/omero-image-repository/" + this._image_id
                    + "?"
                    + "id=" + this._image_id + "&"
                    + "t=" + 1 + "&" // TODO: to update with the actual T value (we not support only T=1)
                    + "z=" + 1 + "&" // TODO: to update with the actual Z value (we not support only Z=1)
                    + "zm=" + viewport_details.zoom_level + "&"
                    + "x=" + viewport_details.center_x + "&"
                    + "y=" + viewport_details.center_y;
            }
            return result;
        };


        /**
         * Returns the list of ROI shapes related to the current image
         * @returns {*}
         */
        prototype.getRoiList = function () {
            return this._current_roi_list;
        };


        /**
         * Display the list of ROI shapes identified by their ID
         *
         * @param shape_id_list
         */
        prototype.showRoiShapes = function (shape_id_list, fixed) {
            this._annotations_controller.showShapes(shape_id_list, true);
            if (fixed) {
                for (var i in shape_id_list) {
                    var shape = this._annotations_controller.getShape(shape_id_list[i]);
                    if (shape)
                        shape.disableEvents();
                }
            }
            for (var j in shape_id_list) {
                if (this._visible_roi_shape_ids.indexOf(String(shape_id_list[j])) === -1)
                    this._visible_roi_shape_ids.push(String(shape_id_list[j]));
            }
        };

        /**
         * Hide the list of ROIs identified by their ID
         * @param shape_id_list
         */
        prototype.hideRoiShapes = function (shape_id_list) {
            this._annotations_controller.hideShapes(shape_id_list, true);
            for (var j in shape_id_list) {
                var index = this._visible_roi_shape_ids.indexOf(String(shape_id_list[j]));
                if (index !== -1)
                    this._visible_roi_shape_ids.splice(index, 1);
            }
        };

        /**
         * Set focus on a given ROI shape
         * @param shape_id
         */
        prototype.setFocusOnRoiShape = function (shape_id, zoom_to_shape) {
            this._viewer_controller.jumpToShape(shape_id, zoom_to_shape || false);
            this._annotations_controller.selectShape(shape_id, true, true);
        };


        /**
         * Add a list of ROIs to the list of ROI to show
         *
         * @param roi_ids
         * @private
         */
        prototype._addVisibleRoiShapes = function (roi_ids) {
            if (!roi_ids.split) roi_ids = "" + [roi_ids];
            if (roi_ids !== undefined && roi_ids.length > 0) {
                var roi_id_list = roi_ids.split(",");
                for (var i in roi_id_list) {
                    var roi_id = roi_id_list[i];
                    for (var j in this._current_roi_list) {
                        var e = this._current_roi_list[j];
                        if (e.id == roi_id) {
                            // FIXME: a better mechanism for selecting a shape
                            this._visible_roi_shape_list[e.id] = [e.shapes[0]];
                            break;
                        }
                    }
                }
            }
            console.log("Visible ROI list", this._visible_roi_shape_list);
        };


        /**
         * Removes a list of ROIs to the list of ROI to show
         *
         * @param roi_ids
         * @private
         */
        prototype._removeVisibleRoiShapes = function (roi_ids) {
            if (!roi_ids.split)
                delete this._visible_roi_shape_list[roi_ids];
            else if (roi_ids !== undefined && roi_ids.length > 0) {
                var roi_id_list = roi_ids.split(",");
                for (var i in roi_id_list) {
                    var roi_id = roi_id_list[i];
                    console.log("ARRAY: ", this._visible_roi_shape_list);
                    //var index = this._visible_roi_shape_list.indexOf(roi_id);
                    delete this._visible_roi_shape_list[roi_id];
                    console.log("Removed visible roi element: ", this._visible_roi_shape_list);
                }
            }
            console.log("Visible ROI list", this._visible_roi_shape_list);
        };


        /**
         * Checks whether a list of ROIs is valid,
         * i.e., every ROI in the list exists.
         *
         * @param annotations_controller
         * @param shape_id_list
         * @returns {Array}
         */
        prototype.checkRois = function (shape_id_list, fix) {
            var result = [];
            if (shape_id_list) {
                var list = shape_id_list.slice(0);
                for (var i in list) {
                    var shape = this._annotations_controller.getShape(list[i]);
                    if (!shape) {
                        result.push(list[i]);
                        if (fix) {
                            var index = shape_id_list.indexOf(list[i]);
                            if (index !== -1) {
                                shape_id_list.splice(index, 1);
                            }
                        }
                    }
                }
            }
            return result;
        };

        //
        console.log("Initialized qtype_omerocommon/image-viewer");

        // returns the class definition
        return M.qtypes.omerocommon.ImageViewer;
    }
);
