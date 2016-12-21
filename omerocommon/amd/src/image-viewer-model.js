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
 * ROI shape model wrapper with utility methods.
 *
 * @package    qtype
 * @subpackage omerocommon
 * @copyright  2015-2016 CRS4
 * @license    https://opensource.org/licenses/mit-license.php MIT license
 */
/* jshint curly: false */
/* globals console */
define(['jquery'], function ($) {

        // defines the basic package
        M.qtypes = M.qtypes || {};

        // defines the specific package of this module
        M.qtypes.omerocommon = M.qtypes.omerocommon || {};

        // constructor
        M.qtypes.omerocommon.ImageModelManager = function (image_server, image_id) {

            // register the address of the current OMERO server
            this._image_server = image_server;

            // register the ID of the image to manage
            this._image_id = image_id;

            // event listeners
            this._listeners = [];

            // log init status
            console.info("image_model_manager initialized!!!");
        };


        // prototype
        var prototype = M.qtypes.omerocommon.ImageModelManager.prototype;

        /**
         * Registers the <pre>listener</pre> of the model events
         * triggered by this 'model'
         *
         * @param listener
         */
        prototype.addEventListener = function (listener) {
            if (listener) {
                this._listeners.push(listener);
            }
        };


        /**
         * Deregisters the <pre>listener</pre> from this model
         *
         * @param listener
         */
        prototype.removeEventListener = function (listener) {
            if (listener) {
                var index = this._listeners.indexOf(listener);
                if (index > -1)
                    this._listeners.splice(index, 1);
            }
        };


        /**
         * Notifies an event to the registered listeners
         *
         * @param event
         * @private
         */
        prototype._notifyListeners = function (event) {
            if (event) {
                console.log("Event", event);
                for (var i in this._listeners) {
                    var callbackName = "on" + event.type.charAt(0).toUpperCase() + event.type.slice(1);
                    console.log("Listener", i, this._listeners[i], callbackName);
                    var callback = this._listeners[i][callbackName];
                    if (callback) {
                        console.log("Calling ", callback);
                        callback.call(this._listeners[i], event);
                    }
                }
            }
        };


        prototype._makeRequest = function (request, image_id, success_callback, error_callback, extra_params, event_name) {
            var me = this;

            var data = $.extend({
                format: "json",
                m: request,
                id: image_id || me._image_id
            }, extra_params);

            $.ajax({
                // request URL
                url: this._image_server,

                // result format
                dataType: "json",

                // Request parameters
                data: data,

                // Set callback methods
                success: function (data) {

                    if (success_callback) {
                        success_callback(data);
                    }

                    // Notify event
                    if (event_name && event_name.length > 0)
                        me._notifyListeners(new CustomEvent(
                            event_name,
                            {
                                detail: data,
                                bubbles: true
                            })
                        );
                },
                error: error_callback
            });
        };

        /**
         * Load info of ROIs related to the current image
         *
         * @param image_id
         * @param success_callback
         * @param error_callback
         * @private
         */
        prototype.loadRoisInfo = function (success_callback, error_callback, image_id) {

            var me = this;

            me._makeRequest("img_details", image_id, function (data) {

                // post process data:
                // adapt the model removing OMERO complexity
                var result = [];
                $.each(data.rois, function (index) {
                    var obj = $(this)[0];
                    result[index] = obj.shapes[0];
                });

                if (success_callback) {
                    success_callback(data.rois);
                }

                // Notify that ROI info are loaded
                me._notifyListeners(new CustomEvent(
                    "imageModelLoaded",
                    {
                        detail: data,
                        bubbles: true
                    })
                );

                // Notify that ROI info are loaded
                me._notifyListeners(new CustomEvent(
                    "imageModelRoiLoaded",
                    {
                        detail: data.rois,
                        bubbles: true
                    })
                );

            }, error_callback, {rois: true});
        };



        /**
         * Load info of ROIs related to the current image
         *
         * @param image_id
         * @param success_callback
         * @param error_callback
         * @private
         */
        prototype.getImageDZI = function (success_callback, error_callback, image_id) {
            this._makeRequest("dzi", image_id, success_callback, error_callback, {}, "imageDziLoaded");
        };


        /**
         * Load info of ROIs related to the current image
         *
         * @param image_id
         * @param success_callback
         * @param error_callback
         * @private
         */
        prototype.getImageMPP = function (success_callback, error_callback, image_id) {
            this._makeRequest("mpp", image_id, success_callback, error_callback, {}, "imageMppLoaded");
        };


        prototype.getImageDetails = function (success_callback, error_callback, image_id) {
            this._makeRequest("img_details", image_id, success_callback, error_callback, {}, "imageDetailsLoaded");
        };

        // returns the class
        return M.qtypes.omerocommon.ImageModelManager;
    }
);

