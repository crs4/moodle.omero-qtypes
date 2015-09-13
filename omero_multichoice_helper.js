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
    me.last_roi_shape_selected = null;
    me.selected_roi_shapes = [];  // list of current selected rois
    me.available_rois = [];
    me.roi_based_answers = [];
    me.current_rois_info = null;

    document.addEventListener("frameLoaded", function (e) {
        me.current_image_info = e.detail;
        me._registerFrameWindowEventHandlers(e.detail.frame_id);
        me._loadROIsInfo();

        // Performs form enhancements
        if (me.isEditingMode()) {
            me._initQuestionEditorForm();
        }
    }, true);

    // register jquery
    require(['jquery'], function ($) {
        me.$ = $;
    });

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

    // register the reference to the current form
    me.form = form;

    // Initializes the list of available ROIs
    me.available_rois = [];
    for(var j in me.current_rois_info){
        me.available_rois.push(me.current_rois_info[j].id);
    }

    console.log("Available ROIs:", me.available_rois);
    console.log("ROI based answers:", me.roi_based_answers);

    // Registers the listener for the button 'add-roi-answer'
    var add_roi_button = form.elements['add-roi-answer'];
    add_roi_button.onclick = me.addRoiBasedAnswerAction;
    me.enableNewRoiBasedAnswerButton(false);
    // Hides the server-side button for adding answers
    form.elements['addanswers'].style.display = "none";
}


me.enableNewRoiBasedAnswerButton = function (enabled) {
    if (me.form) {
        var add_roi_button = me.form.elements['add-roi-answer'];
        add_roi_button.disabled = !enabled;
    }
}


me._initRoiBasedAnswers = function () {
    if (me.form) {
        me.roi_based_answers = me.form.elements['roi_based_answers'].value.split(",");

        for (var i in me.roi_based_answers) {

            var roi_id = null;

        }
    }
}


me.addRoiBasedAnswerAction = function () {

    var form = me.form;
    if (!form) throw Error("No 'question-editor' form found!!!");

    // Get the number of current answers
    var no_answers = parseInt(form.elements['noanswers'].value);

    // Update the list of roi-answer association adding the current selected ROI
    if (me.last_roi_shape_selected) {
        me.addRoiBasedAnswer(me.last_roi_shape_selected.shapeId);

        // form submission
        var action_el = form.elements['addanswers'];
        action_el.click();
    }
}


/**
 * Adds a new roi to the list of rois associated to answers
 * @param roi_id
 */
me.addRoiBasedAnswer = function (roi_id) {
    var form = me.form;
    if (!form) throw Error("No 'question-editor' form found!!!");
    // Update the list of roi-answer association adding a new ROI
    if (form.elements['roi_based_answers'].value == "none")
        form.elements['roi_based_answers'].value = roi_id;
    else
        form.elements['roi_based_answers'].value += "," + roi_id;

    console.log("Current ROI answers: " + form.elements['roi_based_answers'].value);
}


me.removeRoiBasedAnswer = function (roi_id) {
    // Get the number of current answers
    var no_answers = parseInt(form.elements['noanswers'].value);

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
        if (f != null && f != undefined && f.elements) {
            if (f.elements['editing_mode'])
                return f;
        }
    }
    return null;
}


/**
 * Register listeners for events triggered
 * by the frame identified by 'frame_id'
 *
 * @param frame_id
 * @private
 */
me._registerFrameWindowEventHandlers = function (frame_id) {

    var omero_viewer_frame = document.getElementById(frame_id);
    if (!omero_viewer_frame) {
        throw EventException("Frame " + frame_id + " not found!!!");
    }

    // Registers a reference to the frame
    me._omero_viewer_frame = omero_viewer_frame;

    // Adds listeners
    var frameWindow = omero_viewer_frame.contentWindow;
    frameWindow.addEventListener("roiShapeSelected", M.omero_multichoice_helper.roiShapeSelected);
    frameWindow.addEventListener("roiShapeDeselected", M.omero_multichoice_helper.roiShapeDeselected);
}


/**
 * Loads ROIs info for the current image
 * @private
 */
me._loadROIsInfo = function(){
    var frameWindow = me._omero_viewer_frame.contentWindow;

    // FIXME: remove dependency to the 'omero_viewer_controller' (i.e., see 'repository' module)
    // Register a reference to the Omero Repository Controller
    me.omero_viewer_controller = frameWindow.omero_viewer_controller;
    me.current_rois_info = me.omero_viewer_controller.getCurrentROIsInfo();
    console.log(me.current_rois_info);
}

/**
 * Handle the RoiShapeSelection Event
 *
 * @param info
 */
me.roiShapeSelected = function (info) {
    me.last_roi_shape_selected = info.detail;
    me.selected_roi_shapes.push(info.detail);
    console.log(me.roi_based_answers);
    console.log(me.roi_based_answers.indexOf(info.detail.shapeId));
    // enable button only if the current ROI not in roi_based_answers
    if (me.roi_based_answers.indexOf(info.detail.shapeId.toString()) == -1)
        me.enableNewRoiBasedAnswerButton(true);
    else
        me.enableNewRoiBasedAnswerButton(false);
    console.log("Selected RoiShape", info, "Current Selected ROIS", me.selected_roi_shapes);
}

/**
 * Handle the RoiShapeDeselection event
 *
 * @param info
 */
me.roiShapeDeselected = function (info) {
    me.last_roi_shape_selected = null;
    me.selected_roi_shapes = me.$.grep(me.selected_roi_shapes, function (v) {
        return v.id != info.detail.id;
    });
    me.enableNewRoiBasedAnswerButton(false);
    console.log("DeSelected RoiShape", info, "Current DeSelected ROIS", me.selected_roi_shapes);
}