/* Omero Multichoice QType Helper */
M.omero_multichoice_helper = {};

/**
 * Initialization function
 *
 * @param module_name
 * @param options
 */
M.omero_multichoice_helper.init = function (module_name, options) {
    M.omero_multichoice_helper.module_name = module_name;

    document.addEventListener("frameLoaded", function (e) {
        M.omero_multichoice_helper.registerFrameWindowEventHandlers(e.detail.frameId);
    }, true);

    console.log("omero_multichoice_helper js helper initialized!!!");
};


M.omero_multichoice_helper.registerFrameWindowEventHandlers = function(frameId){

    var omero_viewer_frame = document.getElementById(frameId);
    if(!omero_viewer_frame){
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
M.omero_multichoice_helper.roiShapeSelected = function(info){
    console.log("Check message", info);
}

/**
 * Handle the RoiShapeDeselection event
 * @param info
 */
M.omero_multichoice_helper.roiShapeDeselected = function(info){
    console.log("Check message", info);
}
