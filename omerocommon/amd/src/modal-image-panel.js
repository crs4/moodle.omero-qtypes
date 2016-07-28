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
 * Simple wrapper of the default browser logger.
 *
 * @package    qtype
 * @subpackage omerocommon
 * @copyright  2015-2016 CRS4
 * @license    https://opensource.org/licenses/mit-license.php MIT license
 */
define(['qtype_omerocommon/image-viewer'],
    /* jshint curly: false */
    /* globals $ */
    function (ImageViewer) {

        // debug_mode
        //var debug_mode = M.cfg.developerdebug;

        // defines the basic package
        M.qtypes = M.qtypes || {};

        // defines the specific package of this module
        M.qtypes.omerocommon = M.qtypes.omerocommon || {};

        // the logger instance
        M.qtypes.omerocommon.logger = {};


        M.qtypes.omerocommon.ModalImagePanel = function (modal_image_selector_panel_id) {
            this._modal_image_selector_id = modal_image_selector_panel_id;

            // FIXME: the id of the image container must be configurable
            this._image_info_container = $("#modalImageDialogPanel-image-viewer-container");
            this._image_info_container_template = $("#modalImageDialogPanel-image-viewer-container").html();

            // init properties to host the list of visible/focusable rois
            this._visible_roi_list = [];
            this._focusable_roi_list = [];
        };


        var prototype = M.qtypes.omerocommon.ModalImagePanel.prototype;

        prototype.show = function (image_id, visible_rois, focusable_rois) {

            $("#" + this._modal_image_selector_id).modal("show");

            var me = this;

            me._visible_roi_list = visible_rois ? visible_rois.split(",") : [];
            me._focusable_roi_list = focusable_rois ? focusable_rois.split(",") : [];
            me._image_properties = image_properties || {};
            me._image_lock = image_lock || false;

            me._image_locked_element.bootstrapToggle(me._image_locked ? 'on' : 'off');

            // clean the old canvas
            me._image_info_container.html(me._image_info_container_template);
            // clean the old table if it exists
            if (me._roi_shape_table)
                me._roi_shape_table.removeAll();

            var viewer_ctrl = new ImageViewer(
                image_id, undefined,
                me._image_server || "http://ome-cytest.crs4.it:8080",
                "modalImageDialogPanel-image-viewer-container", "modalImageDialogPanel-annotations_canvas",
                me._viewer_model_server || "http://mep.crs4.it/moodle/question/type/omerocommon/viewer/viewer-model.php");
            me._image_viewer_controller = viewer_ctrl;

            // load and show image and its related ROIs
            viewer_ctrl.open(true, function (data) {
                me.onImageModelRoiLoaded(data);
                me._initImagePropertiesControls();
                me._image_viewer_controller.updateViewFromProperties(me._image_properties);
                // FIXME: configuration
                $("#modalImageDialogPanel-toolbar").removeClass("hidden");
            });
        };


        prototype.hide = function () {
            $("#" + this._modal_image_selector_id).modal("hide");
        };


        prototype.onImageModelRoiLoaded = function (data) {

            var me = this;

            // removed_rois
            var removed_rois = {};

            console.log("ImageROI loaded", data);

            // validate the list of visible ROIs
            removed_rois.visible = me._image_viewer_controller.checkRois(me._visible_roi_list, true);
            console.log("Validated ROI Shape List", me._visible_roi_list);

            // validate the list of focusable ROIs
            removed_rois.focusable = me._image_viewer_controller.checkRois(me._focusable_roi_list, true);
            console.log("Validated Focusable ROI List", me._focusable_roi_list);

            var roi_list = M.qtypes.omerocommon.RoiShapeModel.toRoiShapeModel(data,
                me._visible_roi_list, me._focusable_roi_list);
            console.log("Loaded ROI Shapes Models", roi_list);

            if (!me._roi_shape_table) {

                // FIXME: the tableID must be configurable
                me._roi_shape_table = new M.qtypes.omerocommon.RoiShapeTableBase(
                    "modalImageDialogPanel-roi-shape-inspector-table");
                me._roi_shape_table.initTable(false, me._show_roishape_column_group, true);
                me._roi_shape_table.addEventListener(me);
            }
            me._roi_shape_table.removeAll();
            me._roi_shape_table.appendRoiShapeList(roi_list);
            me._image_viewer_controller.showRoiShapes(me._visible_roi_list);

            console.log("Updated ROI table!!!");

            return removed_rois;
        };


        prototype.onRoiShapeVisibilityChanged = function (event) {
            console.log(event);
            this.onRoiShapePropertyChanged(event, "visible", this._visible_roi_list);
        };


        prototype.onRoiShapeFocusabilityChanged = function (event) {
            console.log(event);
            this.onRoiShapePropertyChanged(event, "focusable", this._focusable_roi_list);
        };

        prototype.onRoiShapePropertyChanged = function (event, property, visible) {
            if (event.shape[property]) {
                if (property === "visible") this._image_viewer_controller.showRoiShapes([event.shape.id]);
                if (visible.indexOf(event.shape.id) === -1)
                    visible.push(event.shape.id);
            } else {
                if (property === "visible") this._image_viewer_controller.hideRoiShapes([event.shape.id]);
                var index = visible.indexOf(event.shape.id);
                if (index > -1)
                    visible.splice(index, 1);
            }
        };


        prototype.onRoiShapeFocus = function (event) {
            this._image_viewer_controller.setFocusOnRoiShape.call(
                this._image_viewer_controller,
                event.shape.id
            );
        };


        prototype._initImagePropertiesControls = function () {
            var me = this;

            // FIXME: image properties has to be initialized using a JSON
            // ...... we are now using the image properties of the main image related to the question
            me._image_properties_element = $("[name^=omeroimageproperties]");
            me._image_properties = me._image_properties_element.val();
            if (me._image_properties && me._image_properties.length !== 0) {
                try {
                    me._image_properties = JSON.parse(me._image_properties);
                } catch (e) {
                    console.error(e);
                    me._image_properties = {};
                }
            } else
                me._image_properties = {};

            // FIXME: the ID must be configurable
            $("#modalImageDialogPanel-image-properties").html(me.getFormattedImageProperties());
            $("#modalImageDialogPanel-update-image-properties").click(function () {
                me.updateImageProperties();
            });
        };

        prototype.getImageProperties = function () {
            return this._image_properties;
        };

        prototype.getFormattedImageProperties = function () {
            var ip = this._image_properties;
            var result = "";
            if (ip.center) {
                result += ("center (x,y): " + ip.center.x + ", " + ip.center.y);
                result += (", zoom: " + ip.zoom_level);
                result += (", t: " + ip.t);
                result += (", z: " + ip.z);
            }
            return result;
        };

        prototype.updateImageProperties = function () {
            this._image_properties = this._image_viewer_controller.getImageProperties();
            this._image_properties_element.val(JSON.stringify(this._image_properties));
            // FIXME: the ID must be configurable; use a method to update the image properties
            $("#modalImageDialogPanel-image-properties").html(this.getFormattedImageProperties());
        };


        M.qtypes.omerocommon.ModalImagePanel.DEFAULT_ELEMENT_NAME = "modalImageDialogPanel";
        M.qtypes.omerocommon.ModalImagePanel.getInstance = function () {
            if (!M.qtypes.omerocommon.ModalImagePanel._default_instance) {
                M.qtypes.omerocommon.ModalImagePanel._default_instance =
                    new M.qtypes.omerocommon.ModalImagePanel(M.qtypes.omerocommon.ModalImagePanel.DEFAULT_ELEMENT_NAME);
            }
            return M.qtypes.omerocommon.ModalImagePanel._default_instance;
        };

        // returns the class
        return M.qtypes.omerocommon.ModalImagePanel;
    }
);