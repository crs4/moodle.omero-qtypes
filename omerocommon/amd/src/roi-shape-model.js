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
define(['jquery'], function ($) {

        // defines the basic package
        M.qtypes = M.qtypes || {};

        // defines the specific package of this module
        M.qtypes.omerocommon = M.qtypes.omerocommon || {};

        // constructor
        M.qtypes.omerocommon.RoiShapeModel = function (raw_roi_shape, visible, focusable) {
            for (var i in raw_roi_shape) {
                this[i] = raw_roi_shape[i];
            }
            this.visible = visible;
            this.focusable = focusable || false;
        };

        // shortcut for the 'class'
        var theClass = M.qtypes.omerocommon.RoiShapeModel;

        //
        theClass.toRoiShapeModel = function (roi_shape_list, visible_roi_list, focusable_roi_list) {
            var result = [];
            for (var i in roi_shape_list) {
                var roi = roi_shape_list[i];
                result.push(new theClass(
                    roi,
                        visible_roi_list && visible_roi_list.indexOf(roi.id) !== -1,
                        focusable_roi_list && focusable_roi_list.indexOf(roi.id) !== -1
                    )
                );
            }
            return result;
        };

        // returns the class
        return M.qtypes.omerocommon.RoiShapeModel;
    }
);

