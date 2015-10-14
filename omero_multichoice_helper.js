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
me.init = function (module_name, frame_id, visible_roi_list, options) {

    // init instance properties
    me.module_name = module_name; // module name
    me.last_roi_shape_selected = null;
    me.selected_roi_shapes = [];  // list of current selected rois
    me.available_rois = [];
    me.roi_based_answers = [];
    me.current_rois_info = null;

    // register frame object is already loaded
    if (frame_id) {
        me._registerFrameObject(frame_id, visible_roi_list);
    }

    // register the frame when loaded
    document.addEventListener("frameLoaded", function (e) {
        var frame_id = e.detail.frame_id;
        console.log("frame Loaded!!!");
        console.log(me._registerFrameObject(frame_id, visible_roi_list));
    }, true);

    console.log("omero_multichoice_helper js helper initialized!!!");
};


/**
 * Module initialization
 *
 * @param frame_id
 * @param image_details
 * @private
 */
me._initialize = function (frame_id, image_details, visible_roi_list) {
    me.current_image_info = image_details;
    me._registerFrameWindowEventHandlers(frame_id);
    me._loadROIsInfo();

    // Performs form enhancements
    if (me.isEditingMode()) {
        me._initQuestionEditorForm();
    } else {
        console.log("Loaded ROIs", me.current_rois_info);

        // FIXME: maximize viewport when it starts
        me.omero_viewer_controller.maximize();

        // FIXME: use a better way to identify the answer type
        if (visible_roi_list == "all") {
            var all = [];
            for (var i in me.current_rois_info)
                all.push(me.current_rois_info[i].id);
            me.omero_viewer_controller.showRois(all);
        } else {
            me.omero_viewer_controller.showRois(visible_roi_list);
        }
    }
};


/**
 * Updates the reference to the frame containing OmeroImageViewer
 *
 * @param frame_id
 * @returns {Element|*|omero_viewer_frame}
 * @private
 */
me._registerFrameObject = function (frame_id, visible_roi_list) {
    var omero_viewer_frame = document.getElementById(frame_id);
    if (!omero_viewer_frame) {
        throw ("Frame " + frame_id + " not found!!!");
    }
    // Registers a reference to the frame
    me._omero_viewer_frame = omero_viewer_frame;

    // Register the main listener for the 'omeroViewerInitialized' event
    me._omero_viewer_frame.contentWindow.addEventListener("omeroViewerInitialized", function (e) {
        me._initialize(frame_id, e.detail, visible_roi_list);
        console.log("OmeroImageViewer init loaded!!!");
    }, true);

    // enable chaining
    return me._omero_viewer_frame;
};

/**
 * Focus on the ROI shape
 *
 * @param roi_id
 */
me.moveToRoiShape = function (roi_id) {
    me.omero_viewer_controller._handleShapeRowClick({id: roi_id});
}


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
    for (var j in me.current_rois_info) {
        me.available_rois.push(me.current_rois_info[j].id);
    }

    // Initialize the list of ROIs to show
    var visible_rois_input_field = document.forms[0].elements['visible_rois'].value;
    if(visible_rois_input_field
        && visible_rois_input_field.length>0
        && visible_rois_input_field!="none"){
        me._visible_roi_list = visible_rois_input_field.split(",");
    }else{
        me._visible_roi_list = [];
    }

    // Registers the submit function
    document.forms[0].onsubmit = me._on_question_submitted;

    // Initializes the ROI based answers
    me._initRoiBasedAnswers();

    // Logs the current state of ROI lists
    console.log("Available ROIs:", me.available_rois);
    console.log("ROI based answers:", me.roi_based_answers);
    console.log("Visible ROIs:", me._visible_roi_list);

    // FIXME: use a better way to identify the answer type
    if (document.forms[0].elements['answertype'].value == "1") {
        // Registers the listener for the button 'add-roi-answer'
        var add_roi_button = form.elements['add-roi-answer'];
        add_roi_button.onclick = me.addRoiBasedAnswerAction;
        me.enableNewRoiBasedAnswerButton(false);
        // Hides the server-side button for adding answers
        form.elements['addanswers'].style.display = "none";
    } else {
        form.elements['addanswers'].style.display = "visible";
    }
};


me.enableNewRoiBasedAnswerButton = function (enabled) {
    if (me.form) {
        var add_roi_button = me.form.elements['add-roi-answer'];
        if (add_roi_button) {
            add_roi_button.disabled = !enabled;
        }
    }
};


me._initRoiBasedAnswers = function () {
    if (me.form) {
        me.roi_based_answers = me.form.elements['roi_based_answers'].value.split(",");

        var containers = document.getElementsByClassName("omeromultichoice-qanswer-roi-based-answer-container");
        for (var i in me.roi_based_answers) {

            var roi_id = me.roi_based_answers[i];
            if (roi_id == "none") continue;

            var roi_info = me.current_rois_info[roi_id];
            if (!roi_info) throw Error("ROI info not found (ID: " + roi_id + ")!!!");

            var container = containers[i];

            // set the thumbnail
            var thumbnail = container.getElementsByClassName("roi_thumb shape_thumb")[0];
            thumbnail.src = me.getRoiShapeThumbnailUrl(roi_id);

            // set details
            var details = container.getElementsByClassName("omeromultichoice-qanswer-roi-details-text");
            details[0].getElementsByClassName("roi-field-value")[0].innerHTML = roi_info.id;
            details[1].getElementsByClassName("roi-field-value")[0].innerHTML = roi_info.shapes[0].textValue;
            details[2].getElementsByClassName("roi-field-value")[0].innerHTML = roi_info.shapes[0].type;
            details[3].getElementsByClassName("roi-field-value")[0].innerHTML = roi_info.shapes[0].width;
            details[4].getElementsByClassName("roi-field-value")[0].innerHTML = roi_info.shapes[0].height;
        }
    }
};


/**
 * Performs several controls before the form submition
 *
 * @private
 */
me._on_question_submitted = function () {

    // get the form element containing the url of the current selected image
    var image_url_input_element = document.forms[0].elements["omero_image_url"];

    // get the relative path to the current image selection:
    // including references to the current zoom level, displayed area, etc.
    var image_relative_path = me._build_image_link();

    // update the current input element value
    var old = image_url_input_element.value;
    var newurl = me.omero_viewer_controller.omero_server + "/webgateway/render_thumbnail/" + image_relative_path;

    // update the list of ROIs to display
    document.forms[0].elements['visible_rois'].value = me._visible_roi_list.join(",");

    // update the current URL with image params (i.e., zoom, channels, etc.)
    console.log("Updating URL...", old, newurl);
    image_url_input_element.value = newurl.replace("/?", "?");
}


/**
 * Builds the link to the current portion of the displayed image
 *
 * @returns {string} the relative path of the image
 * @private
 */
me._build_image_link = function () {
    var viewport = me.omero_viewer_controller.viewport;
    var link = viewport.getCurrentImgUrlPath() + '?' + viewport.getQuery(true, true, true);
    console.log("Current image link", link);
    return link;
}


/**
 * Returns the URL of the ROI thumbnail identified by roi_id
 *
 * @param roi_id
 * @returns {string}
 */
me.getRoiShapeThumbnailUrl = function (roi_id) {
    return me.omero_viewer_controller.omero_server +
        "/webgateway/render_shape_thumbnail/" + roi_id + "/?color=f00";
};


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
};


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
};


me.removeRoiBasedAnswer = function (roi_id) {
    // Get the number of current answers
    var no_answers = parseInt(form.elements['noanswers'].value);
};


me.addVisibleRoi = function (roi_id) {
    console.log("Adding new visible ROI...", roi_id, me._visible_roi_list);
    roi_id = roi_id.toString();
    if (me._visible_roi_list.indexOf(roi_id) == -1) {
        me._visible_roi_list.push(roi_id);
    }
}


me.removeVisibleRoi = function (roi_id) {
    console.log("Removing a visible ROI...", roi_id, me._visible_roi_list);
    roi_id = roi_id.toString();
    var index = me._visible_roi_list.indexOf((roi_id));
    if (index > -1) {
        me._visible_roi_list.slice(index, 1);
    }
}


me.isEditingMode = function () {
    return me._getForm() != null;
};

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
};


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
    frameWindow.addEventListener("roiVisibilityChanged", M.omero_multichoice_helper.roiVisibilityChanged);
};


/**
 * Loads ROIs info for the current image
 * @private
 */
me._loadROIsInfo = function () {
    var frameWindow = me._omero_viewer_frame.contentWindow;

    // FIXME: remove dependency to the 'omero_viewer_controller' (i.e., see 'repository' module)
    // Register a reference to the Omero Repository Controller
    me.omero_viewer_controller = frameWindow.omero_viewer_controller;
    me.current_rois_info = [];
    var roi_infos = me.omero_viewer_controller.getCurrentROIsInfo();
    for (var i in roi_infos) {
        var roi_info = roi_infos[i];
        me.current_rois_info[roi_info.id] = roi_info;
    }

    console.log(me.current_rois_info);
};

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
};

/**
 * Handle the RoiShapeDeselection event
 *
 * @param info
 */
me.roiShapeDeselected = function (info) {
    me.last_roi_shape_selected = null;
    var index = me.selected_roi_shapes.indexOf(info.detail.id);
    if (index > -1) {
        me.selected_roi_shapes.slice(index, 1);
    }
    me.enableNewRoiBasedAnswerButton(false);
    console.log("DeSelected RoiShape", info, "Current DeSelected ROIS", me.selected_roi_shapes);
};


me.roiVisibilityChanged = function (event) {
    if (!event) return;

    var roi_info = event.detail.detail;
    if(event.detail.visible){
        me.addVisibleRoi(roi_info.id);
    }else{
        me.removeVisibleRoi(roi_info.id);
    }

    console.log("Changed vibility to " + event.detail.visible
        + " of RoiShape", roi_info, "Visible ROIs: " + me._visible_roi_list.join(","));
};


//
//Array.prototype.contains = function(obj){
//    return this.indexOf(obj) != -1;
//}

