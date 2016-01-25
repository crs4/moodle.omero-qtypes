/**
 * Created by kikkomep on 12/4/15.
 */
/**
 * Created by kikkomep on 12/2/15.
 */

define("qtype_omerocommon/roi-shape-model",
    [
        'jquery'
    ],
    function ($) {

        // Private functions.
        // ...

        // Public functions
        return {
            initialize: function (str) {

                console.log("Initialized", this);

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
            }
        };
    }
);


/**
 * ROI description
 * subset of a JSON ROI
 *   "height" : 603,
 "id" : 11,
 "strokeAlpha" : 0.765625,
 "strokeColor" : "#c4c4c4",
 "strokeWidth" : 1,
 "textValue" : "<br/>Comment ROI1",
 "theT" : 0,
 "theZ" : 0,
 "transform" : "none",
 "type" : "Rectangle",
 "width" : 604,
 "x" : 24019,
 "y" : 14605
 */