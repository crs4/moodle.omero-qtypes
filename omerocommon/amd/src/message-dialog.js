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
define([],
    /* jshint curly: false */
    /* globals $ */
    function () {

        // debug_mode
        //var debug_mode = M.cfg.developerdebug;

        // defines the basic package
        M.qtypes = M.qtypes || {};

        // defines the specific package of this module
        M.qtypes.omerocommon = M.qtypes.omerocommon || {};

        // the logger instance
        M.qtypes.omerocommon.logger = {};


        M.qtypes.omerocommon.MessageDialog = function (id_suffix) {
            this._id_suffix = id_suffix;
            this._MODAL_FRAME_ID = "modal-frame" + (id_suffix ? ("-" + id_suffix) : "");
            this._MODAL_FRAME_TEXT_ID = "modal-frame-text" + (id_suffix ? ("-" + id_suffix) : "");
        };


        var prototype = M.qtypes.omerocommon.MessageDialog.prototype;

        prototype.showDialogMessage = function (message) {
            $("#" + this._MODAL_FRAME_TEXT_ID).html(message);
            $("#" + this._MODAL_FRAME_ID).modal("show");
        };


        prototype.hideDialogMessage = function () {
            $("#" + this._MODAL_FRAME_ID).modal("hide");
        };
    }
);