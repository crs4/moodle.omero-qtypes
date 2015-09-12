/* Omero Multichoice QType Helper */
M.omero_multichoice_helper = {};

// private shorthand for M.omero_multichoice_helper
var me = M.omero_multichoice_helper;

/**
 * Initialization function
 *
 * @param module_name
 * @param options
 */
me.init = function (module_name, options) {

    // init instance properties
    me.module_name = module_name; // module name
    me.selected_roi_shapes = [];  // list of current selected rois

    // Register event handlers
    document.addEventListener("frameLoaded", function (e) {
        me._registerFrameWindowEventHandlers(e.detail.frameId);
    }, true);

    // register jquery
    require(['jquery'], function ($) {
        me.$ = $;
    });

    // Perform form form enhancements
    if (me.isEditingMode()) {
        me._initQuestionEditorForm();
    }

    console.log("omero_multichoice_helper js helper initialized!!!");
};


/**
 * Initialize event listeners
 * for some elements of the form for editing a question
 *
 * @private
 */
me._initQuestionEditorForm = function () {

    var form = me._getForm();
    if (!form) return;

    var add_roi_button = form.elements['add-roi-answer'];
    add_roi_button.onclick = me.addRoiBasedAnswer;
}


me.addRoiBasedAnswer = function () {

    var form = me._getForm();
    if (!form) throw Error("No 'question-editor' form found!!!");

    // Add the new ROI
    var n = document.createElement("input");
    n.setAttribute("name", "roi[" + no_answers + "]");
    n.setAttribute("id", "id_roi_" + no_answers);
    n.setAttribute("value", "0.1");
    form.appendChild(n);

    // Increment the number of answers
    var no_answers = parseInt(form.elements['noanswers'].value);
    form.elements['noanswers'].value = no_answers + 1;

    // Disable the client-side validation
    skipClientValidation = true;

    // form submission
    form.submit();
}


me.isEditingMode = function () {
    return me._getForm() != null;
}

/**
 * Return the question editor form
 *
 * @returns {*}
 * @private
 */
me._getForm = function () {
    for (var i in document.forms) {
        var f = document.forms[i];
        if (f.elements['editing_mode'])
            return f;
    }
    return null;
}


/**
 * Register listeners for events triggered
 * by the frame identified by 'frameId'
 *
 * @param frameId
 * @private
 */
me._registerFrameWindowEventHandlers = function (frameId) {

    var omero_viewer_frame = document.getElementById(frameId);
    if (!omero_viewer_frame) {
        throw EventException("Frame " + frameId + " not found!!!");
    }

    var frameWindow = omero_viewer_frame.contentWindow;
    frameWindow.addEventListener("roiShapeSelected", M.omero_multichoice_helper.roiShapeSelected);
    frameWindow.addEventListener("roiShapeDeselected", M.omero_multichoice_helper.roiShapeDeselected);
}


/**
 * Handle the RoiShapeSelection Event
 *
 * @param info
 */
me.roiShapeSelected = function (info) {
    me.selected_roi_shapes.push(info.detail);
    console.log("Selected RoiShape", info, "Current Selected ROIS", me.selected_roi_shapes);
    alert("Selezionata ROI: " + info.detail.id);
}

/**
 * Handle the RoiShapeDeselection event
 *
 * @param info
 */
me.roiShapeDeselected = function (info) {
    me.selected_roi_shapes = me.$.grep(me.selected_roi_shapes, function (v) {
        return v.id != info.detail.id;
    });
    console.log("DeSelected RoiShape", info, "Current DeSelected ROIS", me.selected_roi_shapes);
}