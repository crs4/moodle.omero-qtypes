M.omero_multichoice_handler = {
    init: function () {

        console.log($);
        //alert("Initialized!");


        // ToDO: change the frameLoaded event
        document.addEventListener("frameLoaded", function (e) {
            var frame_id = e.detail.frame_id;
            var frame = document.getElementById(frame_id);
            var omero_image_viewer_controller = frame.contentWindow.omero_repository_image_viewer_controller;
            if (!omero_image_viewer_controller)
                throw new EventException("omero_repository_image_viewer_controller not found!");

            console.log("Controller loaded!!!", omero_image_viewer_controller);

            omero_image_viewer_controller.getModel().addEventListener(M.omero_multichoice_handler);

        }, true);
    },

    "onImageModelRoiLoaded": function () {
        console.log("Handler: OK!!!");
    }
};