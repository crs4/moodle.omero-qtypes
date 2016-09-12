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
define(['qtype_omerocommon/image-viewer',
        'qtype_omerocommon/image-viewer-model',
        'qtype_omerocommon/roi-shape-table'
    ],
    /* jshint curly: false */
    /* globals $,console */
    function (ImageViewer/*, ImageModelManager*/) {

        // debug_mode
        //var debug_mode = M.cfg.developerdebug;

        // defines the basic package
        M.qtypes = M.qtypes || {};

        // defines the specific package of this module
        M.qtypes.omerocommon = M.qtypes.omerocommon || {};

        // the logger instance
        M.qtypes.omerocommon.logger = {};

        /**
         * Build a new instance of ModalImagePanel.
         *
         * @param modal_image_selector_panel_id
         * @constructor
         */
        M.qtypes.omerocommon.ModalImagePanel = function (modal_image_selector_panel_id, image_server, image_model_server) {
            var me = this;

            // URLs of the required servers
            me._image_server = image_server;
            me._image_model_server = image_model_server;

            // set IDs of the main HTML elements
            me._modal_image_selector_id = modal_image_selector_panel_id || M.qtypes.omerocommon.DEFAULT_ELEMENT_NAME;
            me._image_viewer_container_id = me._modal_image_selector_id + "-image-viewer-container";
            me._image_viewer_annotations_container_id = me._modal_image_selector_id + "-annotations_canvas";
            me._language_selector_id = me._modal_image_selector_id + "-language-selector";
            me._description_textarea_id = me._modal_image_selector_id + "-image-description";
            me._locale_description_id = me._modal_image_selector_id + "-image-locale-description-panel";
            me._locale_description_container_id = me._modal_image_selector_id + "-image-description-panel-container";

            // set references to HTML elements
            me._modal_image_panel = $("#" + me._modal_image_selector_id);
            me._image_info_container = $("#" + me._image_viewer_container_id);
            me._header = $("#" + me._modal_image_selector_id + "-header");
            me._header_title = $("#" + me._modal_image_selector_id + "-header-title");
            me._body = $("#" + me._modal_image_selector_id + "-body");
            me._footer = $("#" + me._modal_image_selector_id + "-footer");
            me._locale_description = $("#" + me._locale_description_id);
            me._locale_description_container = $("#" + me._locale_description_container_id);
            me._language_selector = $("#" + me._language_selector_id);
            me._description_textarea = $("#" + me._description_textarea_id);

            // init the description editor
            if ($("#id_" + me._description_textarea_id).length !== 0) {
                me._description_editor = new M.qtypes.omerocommon.MultilanguageAttoEditor(
                    me._description_textarea_id, "description-feedback-image-locale-mep");

                // handler of the 'change-language' event
                me._language_selector.on("change", function () {
                    me._description_editor.onLanguageChanged(me._language_selector.val());
                });
            }

            // save the original title
            me._initial_title = me._header_title.html();

            // default padding
            me._default_padding = 100;

            // set the reference to the 'view-lock' HTML element
            // and register the callback to handle its value changes
            if (!me._image_locked_element) {
                me._image_locked_element = $("#" + me._modal_image_selector_id + "-view-lock");
                console.log(me._image_locked_element);
                me._image_locked_element.change(function () {
                    me._image_locked_element.val($(this).prop('checked') ? 1 : 0);
                    me._image_lock = $(this).prop('checked') ? true : false;
                    me._image_viewer_controller.setNavigationLock(me._image_lock);
                });
            }

            // set the reference to the 'image-properties' HTML element
            // and register the callback to handle its value changes
            if (!me._image_properties_element) {
                me._image_properties_element = $("[name^=" + me._modal_image_selector_id + "-update-image-properties]");
                // register the listener of the event "update-image-properties"
                $("#" + me._modal_image_selector_id + "-update-image-properties").click(function () {
                    me.updateImageProperties();
                });
            }

            // notify the parent controller when the 'save' button is clicked
            // triggering the event 'save'
            $("#" + me._modal_image_selector_id + " .save").click(function (/*data*/) {
                me._description_editor.save();
                if (me._parent && me._parent.onSave) {
                    me._parent.onSave(
                        me._image_id, me._description_editor.getLocaleTextMap(),
                        me._image_properties, me._image_lock,
                        me._visible_roi_list, me._focusable_roi_list
                    );
                }
            });

            // notify the parent controller when this panel is closed
            $("#" + me._modal_image_selector_id + " .close").click(function (/*data*/) {
                if (me._parent && me._parent.onClose) {
                    me._parent.onClose();
                }
            });

            // register the handler of the 'window resize' event
            $(window).resize(function () {
                me._auto_resize();
            });
        };

        // private reference to the prototype of the ModalImagePanel class.
        var prototype = M.qtypes.omerocommon.ModalImagePanel.prototype;


        /**
         * Initialize and enable visibility of this modal panel.
         *
         * @param parent
         * @param image_id
         * @param image_properties
         * @param image_lock
         * @param visible_rois
         * @param focusable_rois
         */
        prototype.show = function (parent,
                                   image_id, image_name, image_description_locale_map,
                                   image_properties, image_lock,
                                   visible_rois, focusable_rois, current_language, show_locale_description,
                                   disable_roi_table, disable_image_properties, disable_image_lock, disable_description_editor) {
            // the reference to current scope
            var me = this;

            // set inner properties
            me._parent = parent;
            me._image_id = image_id;
            me._visible_roi_list = visible_rois ? visible_rois : [];
            me._focusable_roi_list = focusable_rois ? focusable_rois : [];
            me._image_properties = image_properties || {};
            me._image_lock = image_lock || false;
            me._show_locale_description = show_locale_description || false;
            me._disable_roi_table = disable_roi_table === true;
            me._disable_image_lock = disable_image_lock === true;
            me._disable_image_properties = disable_image_properties === true;
            me._disable_description_editor = disable_description_editor === true;

            // clear
            me._header_title.html(me._initial_title);
            me._body.scrollTop(0);
            me._image_info_container.html("");
            me._locale_description.html("");
            me._body.css("overflow", "hidden");
            me._locale_description_container.hide();

            // show the modal panel
            $("#" + this._modal_image_selector_id).modal("show");

            // clean the old canvas
            me._image_info_container.html(me._image_info_container_template);
            // clean the old table if it exists
            if (me._roi_shape_table)
                me._roi_shape_table.removeAll();

            // inzialize the viewer
            var viewer_ctrl = new ImageViewer(
                image_id, undefined,
                me._image_server,
                me._image_viewer_container_id, me._image_viewer_annotations_container_id,
                me._image_model_server);
            me._image_viewer_controller = viewer_ctrl;

            // show image description
            if (show_locale_description) {
                me._locale_description.html(image_description_locale_map);
            }

            // load and show image and its related ROIs
            viewer_ctrl.open(true, function (data) {
                // update panel title
                me._header_title.html(me._initial_title + ": \"" + image_name + "\"");

                // initialize marking tools
                me._image_viewer_controller.configureMarkingTool({}, 0);

                // update ROI view
                me.onImageModelRoiLoaded(data);

                // recenter image
                me._image_viewer_controller.updateViewFromProperties(me._image_properties);
                // configure image navigation
                me._image_viewer_controller.setNavigationLock(me._image_lock);

                // init controls
                if (!disable_image_properties)
                    me._updateImageProperties();
                if (!disable_image_lock)
                    me._image_locked_element.bootstrapToggle(me._image_lock ? 'on' : 'off');
                if (me._description_textarea && !disable_description_editor) {
                    // set the current language
                    me._language_selector.val(current_language);
                    // init editor
                    me._description_editor.init(current_language, undefined, image_description_locale_map);
                }

                if (!disable_image_lock)
                    $("#modalImageDialogPanel-toolbar").removeClass("hidden");

                // restore body overflow
                me._body.scrollTop(0);
                me._body.css("overflow", "auto");
                me._locale_description_container.show();
            });
        };

        /**
         * Hide this modal panel.
         */
        prototype.hide = function () {
            $("#" + this._modal_image_selector_id).modal("hide");
        };

        /**
         * Return the current Image Server URL
         *
         * @param string
         */
        prototype.setImageServer = function (image_server) {
            this._image_server = image_server;
        };

        /**
         * Set the URL of the Image Server to be used.
         *
         * @param image_model_server
         */
        prototype.setImageModelServer = function (image_model_server) {
            this._image_model_server = image_model_server;
        };

        /**
         * Set the desired height of this modal panel.
         *
         * @param height
         */
        prototype.setHeight = function (height) {
            var new_height = height + 110;
            this._body.css("min-height", height);
            this._body.css("height", height);

            this._modal_image_panel.css("min-height", new_height);
            this._modal_image_panel.css("height", new_height);
        };

        /**
         * Set the width of this modal panel.
         *
         * @param width
         */
        prototype.setWidth = function (width) {
            this._modal_image_panel.css("min-width", width);
            this._modal_image_panel.css("width", width);
        };

        /**
         * Center vertically this modal panel.
         */
        prototype.vcenter = function (auto) {
            var me = this;
            this._modal_image_panel.css(
                {
                    top: ($(window).height() - me._modal_image_panel.outerHeight()) / 2 + $(window).scrollTop()
                });
            if (auto !== undefined)
                this._auto_vcenter = auto === true;
        };

        /**
         * Center horizontally this modal panel.
         */
        prototype.hcenter = function (auto) {
            var me = this;
            this._modal_image_panel.css(
                {
                    left: ($(window).width() - me._modal_image_panel.outerWidth()) / 2 + $(window).scrollLeft()
                });
            if (auto !== undefined)
                this._auto_hcenter = auto === true;
        };

        /**
         * Center this modal panel.
         *
         * @param auto
         */
        prototype.center = function (auto) {
            this.vcenter(auto);
            this.hcenter(auto);
        };

        /**
         * Maximize width
         */
        prototype.maximizeHeight = function (auto) {
            this.setHeight($(window).height() - (2 * this._default_padding));
            if (auto !== undefined) this._auto_maximize_height = auto === true;
        };


        /**
         * Maximize height
         */
        prototype.maximizeWidth = function (auto) {
            this.setWidth($(window).width() - (2 * this._default_padding));
            if (auto !== undefined) this._auto_maximize_width = auto === true;
        };


        /**
         * Maximize the ModalPanel (height & width).
         */
        prototype.maximize = function (auto) {
            this.maximizeWidth(auto);
            this.maximizeHeight(auto);
        };


        /**
         * Update size and position of the panel.
         * @private
         */
        prototype._auto_resize = function () {
            if (this._auto_maximize_height)
                this.maximizeHeight();
            if (this._auto_maximize_width)
                this.maximizeWidth();
            if (this._auto_hcenter)
                this.hcenter();
            if (this._auto_vcenter)
                this.vcenter();
        };


        /**
         * Handler for the event 'ImageModelRoiLoaded'.
         *
         * @param data
         * @returns {{}}
         */
        prototype.onImageModelRoiLoaded = function (data) {
            // reference to the current scope
            var me = this;

            // removed_rois
            var removed_rois = {};

            console.log("ImageROI loaded", data);

            // validate the list of visible ROIs
            //removed_rois.visible = me._image_viewer_controller.checkRois(me._visible_roi_list, true);
            console.log("Validated ROI Shape List", me._visible_roi_list);

            // validate the list of focusable ROIs
            //removed_rois.focusable = me._image_viewer_controller.checkRois(me._focusable_roi_list, true);
            console.log("Validated Focusable ROI List", me._focusable_roi_list);

            var roi_list = M.qtypes.omerocommon.RoiShapeModel.toRoiShapeModel(data,
                me._visible_roi_list, me._focusable_roi_list);
            console.log("Loaded ROI Shapes Models", roi_list);

            if (!me._disable_roi_table) {
                if (!me._roi_shape_table) {
                    // FIXME: the tableID must be configurable
                    me._roi_shape_table = new M.qtypes.omerocommon.RoiShapeTableBase(
                        "modalImageDialogPanel-roi-shape-inspector-table");
                    me._roi_shape_table.initTable(false, me._show_roishape_column_group, false);
                    me._roi_shape_table.addEventListener(me);
                }

                me._roi_shape_table.removeAll();
                me._roi_shape_table.appendRoiShapeList(roi_list);
            }

            me._image_viewer_controller.showRoiShapes(me._visible_roi_list);
            console.log("Updated ROI table!!!");

            return removed_rois;
        };


        /**
         * Handler for the event 'RoiShapeVisibilityChanged'.
         *
         * @param event
         */
        prototype.onRoiShapeVisibilityChanged = function (event) {
            console.log(event);
            this.onRoiShapePropertyChanged(event, "visible", this._visible_roi_list);
        };


        /**
         * Handler for the event 'RoiShapeFocusabilityChanged'.
         *
         * @param event
         */
        prototype.onRoiShapeFocusabilityChanged = function (event) {
            console.log(event);
            this.onRoiShapePropertyChanged(event, "focusable", this._focusable_roi_list);
        };


        /**
         * Reausable method to handle the visibility state of a given ROI.
         *
         * @param event
         * @param property
         * @param visible
         */
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


        /**
         * Listener which handles the 'changedFocusEvent' triggered on a given ROI.
         *
         * @param event
         */
        prototype.onRoiShapeFocus = function (event) {
            this._image_viewer_controller.setFocusOnRoiShape.call(
                this._image_viewer_controller,
                event.shape.id
            );
        };

        /**
         * Utility function to update the image properties
         *
         * @private
         */
        prototype._updateImageProperties = function () {
            $("#modalImageDialogPanel-image-properties").html(this.getFormattedImageProperties());
        };

        /**
         * Set the current ImageModelManager in use.
         *
         * @param ImageModelManager
         */
        prototype.setImageModelManager = function (image_model_mgt) {
            this._image_model_manager = image_model_mgt;
        };

        /**
         * Return the current ImageModelManager in use.
         *
         * @returns {ImageModelManager}
         */
        prototype.getImageModelManager = function () {
            return this._image_model_manager || this._image_viewer_controller.getImageModelManager();
        };

        /**
         * Returns the details of the current image.
         *
         * @returns {*}
         */
        prototype.getImageDetails = function () {
            return this._image_viewer_controller.getImageDetails();
        };

        /**
         * Return the current image properties
         *
         * @returns {*|{}|{id, center, t, z, zoom_level}|{id: *, center: {x: *, y: *}, t: number, z: number, zoom_level: *}}
         */
        prototype.getImageProperties = function () {
            return this._image_properties;
        };

        /**
         * Get the list of visible ROIs
         *
         * @returns {*|Array}
         */
        prototype.getVisibleROIs = function () {
            return this._visible_roi_list;
        };

        /**
         * Return the list of focusable ROIs
         *
         * @returns {*|Array}
         */
        prototype.getFocusableROIs = function () {
            return this._focusable_roi_list;
        };

        /**
         * Return <code>true</code> whether the image is locked; <code>false</code> otherwise.
         *
         * @returns {boolean}
         */
        prototype.isImageLocked = function () {
            return $("#modalImageDialogPanel-view-lock").val() === "1";
        };

        /**
         * Return the image properties, formatted as a single string
         *
         * @returns {string}
         */
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

        /**
         * Utility method to update the image properties
         * accordingly to the current image state
         */
        prototype.updateImageProperties = function () {
            this._image_properties = this._image_viewer_controller.getImageProperties();
            this._image_properties_element.val(JSON.stringify(this._image_properties));
            // FIXME: the ID must be configurable; use a method to update the image properties
            $("#modalImageDialogPanel-image-properties").html(this.getFormattedImageProperties());
        };


        /**
         * The name of the current default instance of ModalImagePanel.
         *
         * @type {string}
         */
        M.qtypes.omerocommon.ModalImagePanel.DEFAULT_ELEMENT_NAME = "modalImageDialogPanel";

        /**
         * Return the current default instance of ModelImagePanel.
         *
         * @static
         * @returns {M.qtypes.omerocommon.ModalImagePanel}
         */
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