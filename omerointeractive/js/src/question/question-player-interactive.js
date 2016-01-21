/**
 * Created by kikkomep on 12/3/15.
 */

define("qtype_omerointeractive/question-player-interactive",
    [
        'jquery',
        'qtype_omerocommon/moodle-forms-utils',
        'qtype_omerocommon/answer-base',
        'qtype_omerocommon/multilanguage-element',
        'qtype_omerocommon/multilanguage-attoeditor',
        'qtype_omerocommon/question-player-base'
    ],
    function (j, Editor, FormUtils) {
        // Private functions.
        var $ = jQuery;

        var initialized = false;

        var last_control = null;

        var CONTROL_KEYS = {
            ADD: "enable_add_makers_ctrl_id",
            EDIT: "enable_edit_markers_ctrl_id",
            DEL: "remove_marker_ctrl_id",
            CLEAR: "clear_marker_ctrl_id",
            GOTO: "goto_marker_ctrl_id"
        };

        var markers_config = {
            'fill_color': "#ffffff",
            'fill_alpha': 0.4,
            'stroke_width': 10
        };

        var COLORS = {
            correct: "#5AB55A",
            wrong: "#C44540",
            partially_correct: "#F9A125"
        };

        function getControlColorClass(config, control) {
            if (config[CONTROL_KEYS.ADD] === control || CONTROL_KEYS.ADD === control) return "btn-success";
            else if (config[CONTROL_KEYS.EDIT] === control || CONTROL_KEYS.EDIT === control) return "btn-warning";
            else if (config[CONTROL_KEYS.CLEAR] === control || CONTROL_KEYS.CLEAR === control) return "btn-danger";
            else return "btn-default";
        }


        function cid(config, control) {
            return config.answer_input_name.replace(":", "-") + "-" + control;
        }

        function setEnabledMarkerControl(player, control, enabled) {
            var config = player._config;
            if (enabled)
                $("#" + config[control]).removeClass("disabled");
            else $("#" + config[control]).addClass("disabled");
            console.log("Changing the " + control + " controller!!", $("#" + config[control]));
        }

        function switchToActive(control) {
            if (last_control !== null) {
                last_control.removeClass("active");
                last_control = null;
            }
            if (control) {
                last_control = $("#" + control);
                last_control.addClass("active");
            }
        }

        function isMaxMarkerNumber(player) {
            return player._image_viewer_controller.getMarkerIds().length == player._config.max_markers;
        }


        function checkAnswers(player) {
            var me = player;
            var result = {};
            var config = player._config;
            console.log(config);
            var shapes = me._image_viewer_controller.getShapes(me._config.available_shapes);
            var markers = me._image_viewer_controller.getMarkers();
            console.log("Shapes", shapes);
            console.log("Markers", markers);
            for (var i in markers) {
                console.log("Index: " + i);
                var marker = markers[i];
                var center = marker.getCenter();
                console.log("Checking marker", marker.id, center);
                var matched_shape = "none";
                for (var group in config.shape_groups) {
                    console.log("Group: " + group);
                    for (var k in config.shape_groups[group]) {
                        console.log("Shape id: " + k);
                        var shape = me._image_viewer_controller.getShape(config.shape_groups[group][k]);
                        console.log("Checking against ", shape);
                        if (shape.contains(center.x, center.y)) {
                            console.log("Contained in shape " + shape.id);
                            matched_shape = {shape_id: shape.id, shape_group: group};
                            break;
                        }
                    }
                }
                result[i] = matched_shape;
            }

            var value = M.qtypes.omerocommon.MoodleFormUtils.htmlentities(M.qtypes.omerointeractive
                .QuestionPlayerInteractive.serializeResponse(markers, result, me._image_viewer_controller));
            var input = [
                '<input ',
                'type="hidden" ',
                'id="' + me._answer_input_name + '0" ',
                'name="' + me._answer_input_name + '" ',
                'value="' + value + '" ',
                '>'
            ].join("");

            me._response_form.append($(input));

            console.log(result);
        }


        function addMarkerInfo(player, marker_id, editable, color) {
            var me = player;
            var config = player._config;
            var marker_info_container = cid(config, CONTROL_KEYS.DEL) + "-" + marker_id + '_container';
            var label = marker_id.replace("_", " ");
            label = label.charAt(0).toUpperCase() + label.substring(1);
            color = color ? 'style="color: ' + color + ';"' : '';
            var $delm_btn = $('<div id="' + marker_info_container + '">' +
                '<i id="' + cid(config, CONTROL_KEYS.GOTO) + "-" + marker_id + '_btn" ' +
                ' class="glyphicon glyphicon-map-marker" ' + color + '></i> ' +
                label +
                (editable ? ' <i id="' + cid(config, CONTROL_KEYS.DEL) + "-" + marker_id + '_btn" ' +
                ' class="red glyphicon glyphicon-remove"></i> ' : "") +
                "</div>");
            me._remove_markers_container.append($delm_btn);


            $("#" + cid(config, CONTROL_KEYS.DEL) + "-" + marker_id + '_btn').bind(
                'click', {
                    'marker_id': marker_id,
                    'btn_id': 'del_' + marker_id,
                    'marker_info_container': marker_info_container
                },
                function (event) {
                    me._image_viewer_controller.removeMarker(event.data.marker_id);
                    switchToActive();
                }
            );

            $("#" + cid(config, CONTROL_KEYS.GOTO) + "-" + marker_id + '_btn').bind(
                'click', {'marker_id': marker_id},
                function (event) {
                    me._image_viewer_controller.setFocusOnRoiShape(event.data.marker_id);
                }
            );
        }


        function showResults(player) {
            if (!player) {
                console.warn("You have to provide the player object");
                return;
            }

            var config = player._config;
            player._image_viewer_controller.showRoiShapes(config.visible_rois);
            player._image_viewer_controller.showRoiShapes(config.available_shapes);
            if (!config.answers)
                console.log("No answer found!!!");
            else {
                for (var i in config.answers.markers) {
                    var marker = config.answers.markers[i];
                    var marker_color = undefined;
                    console.log(marker, markers_config);
                    if (config.answers.shapes[i] != "none") {
                        if (config.answer_fraction[config.answers.shapes[i].shape_id] == 1)
                            marker_color = COLORS.correct;
                        else marker_color = COLORS.partially_correct;
                    } else marker_color = COLORS.wrong;

                    if (marker_color) {
                        markers_config.fill_color = marker_color;
                        markers_config.stroke_color = marker_color;
                    }

                    player._image_viewer_controller.drawMarker(marker, markers_config);
                    addMarkerInfo(player, marker.shape_id, !config.correction_mode, markers_config.stroke_color);
                }

                var selector = " .roi-shape-info";
                var f = $("#" + config.question_answer_container).parents("form");
                selector = "#" + ((f && f.attr("id")) ?
                        f.attr("id") : config.question_answer_container) + selector;
                $(selector).each(function () {
                    console.log($(this).attr("roi-shape-id"));
                    var shape_id = $(this).attr("roi-shape-id");
                    var shape = player._image_viewer_controller.getShape(shape_id);
                    console.log("Check the current shape", shape);
                    if (shape) {
                        $(this).css("color", shape.toJSON().stroke_color);
                        $(this).click(function () {
                            player._image_viewer_controller.setFocusOnRoiShape(shape_id);
                        });
                    }
                });

                $("#" + config.question_answer_container + " .question-summary").removeClass("hidden");
            }
        }


        /**
         * Starts the question player
         */
        function start(player) {
            var me = player;
            var config = me._config;
            me._image_viewer_controller.open(true, function () {
                console.log("Question Player initialized!!!", config);

                var img_size = me._image_viewer_controller.getImageSize();
                var markers_size = ((img_size.width + img_size.height) / 2) * 0.0025;
                markers_config.markers_size = markers_size;
                console.log(markers_size);

                me._image_viewer_controller
                    .configureMarkingTool(markers_config, parseInt(config.max_markers));

                player._image_viewer_controller.setNavigationLock(config.image_navigation_locked);

                if (!config.correction_mode) {
                    me._image_viewer_controller.showRoiShapes(config.visible_rois, true);
                } else {
                    console.log("Answers: ", config.answers);
                    showResults(me);
                }

                // show focus areas
                me.showFocusAreas();
            });
        }

        // Public functions
        return {

            initialize: function () {
                console.log("Initialized", this);

                // defines the basic package
                M.qtypes = M.qtypes || {};

                // defines the specific package of this module
                M.qtypes.omerointeractive = M.qtypes.omerointeractive || {};

                /**
                 * Defines MoodleFormUtils class
                 * @type {{}}
                 */
                M.qtypes.omerointeractive.QuestionPlayerInteractive = function () {

                    // the reference to this scope
                    //this._initialized = false;

                    // Call the parent constructor
                    M.qtypes.omerocommon.QuestionPlayerBase.call(this);
                };

                // inherit
                M.qtypes.omerointeractive.QuestionPlayerInteractive.prototype =
                    new M.qtypes.omerocommon.QuestionPlayerBase();

                // correct the constructor
                M.qtypes.omerointeractive.QuestionPlayerInteractive.prototype.constructor =
                    M.qtypes.omerointeractive.QuestionPlayerInteractive;

                M.qtypes.omerointeractive.QuestionPlayerInteractive.prototype.parent =
                    M.qtypes.omerocommon.QuestionPlayerBase.prototype;

                M.qtypes.omerointeractive.QuestionPlayerInteractive.getInstance = function () {
                    if (!M.qtypes.omerocommon.QuestionPlayerBase.instance) {
                        M.qtypes.omerocommon.QuestionPlayerBase.instance =
                            new M.qtypes.omerointeractive.QuestionPlayerInteractive();
                    }
                    return M.qtypes.omerocommon.QuestionPlayerBase.instance;
                };

                // local reference to the current prototype
                var prototype = M.qtypes.omerointeractive.QuestionPlayerInteractive.prototype;

                /**
                 * Performs the initialization
                 */
                prototype.initialize = function (config) {

                    var me = this;

                    if (me.initialized) console.log("Already Initialized");
                    else {

                        this.parent.initialize.call(this, config);

                        me._answer_input_name = config.answer_input_name;
                        console.log("Setted the answer prefix", me._answer_input_name);

                        $("#" + config[CONTROL_KEYS.ADD]).click(function () {
                            switchToActive(config[CONTROL_KEYS.ADD]);
                            me._image_viewer_controller.enableAddMarkers();
                        });

                        $("#" + config[CONTROL_KEYS.EDIT]).click(function () {
                            switchToActive(config[CONTROL_KEYS.EDIT]);
                            me._image_viewer_controller.enableMoveMarkers();
                        });

                        $("#" + config[CONTROL_KEYS.CLEAR]).click(function () {
                            switchToActive();
                            me._image_viewer_controller.disableMarkingTool();
                            me._image_viewer_controller.removeMarkers();
                        });

                        me._remove_markers_container = $("#" + config["marker_removers_container"]);

                        // initialize controls
                        setEnabledMarkerControl(this, CONTROL_KEYS.ADD, !config.correction_mode);
                        setEnabledMarkerControl(this, CONTROL_KEYS.EDIT, false);
                        setEnabledMarkerControl(this, CONTROL_KEYS.CLEAR, false);

                        // initialize image positioning control
                        $("#" + config.question_answer_container + " .restore-image-center-btn").click(function () {
                            me._image_viewer_controller.updateViewFromProperties(config.image_properties);
                        });

                        // configure the test mode
                        if (!config.correction_mode) {
                            var form = $("#" + config.enable_add_makers_ctrl_id).parents("form");
                            if (!form) console.error("Unable to find the form");
                            else {
                                me._response_form = form;
                                form.on("submit", function () {
                                    checkAnswers(me);
                                });
                            }

                            $("#" + config.image_annotations_canvas_id).on('marker_created', function (event, marker_id) {
                                console.log('A new marker with ID ' + marker_id + ' was created', event);
                                if (isMaxMarkerNumber(me)) {
                                    console.log("Reached max number of markers: " + config.max_markers);
                                    setEnabledMarkerControl(me, CONTROL_KEYS.ADD, false);
                                    me._image_viewer_controller.disableMarkingTool();
                                }
                                setEnabledMarkerControl(me, CONTROL_KEYS.EDIT, true);
                                setEnabledMarkerControl(me, CONTROL_KEYS.CLEAR, true);

                                addMarkerInfo(me, marker_id, !config.correction_mode);
                            });

                            $("#" + config.image_annotations_canvas_id).on('marker_deleted', function (event, marker_id) {
                                console.log("Remove marker with ID '" + marker_id + "'", event);
                                if (!isMaxMarkerNumber(me))
                                    setEnabledMarkerControl(me, CONTROL_KEYS.ADD, true);
                                if (me._image_viewer_controller.getMarkers().length === 0) {
                                    setEnabledMarkerControl(me, CONTROL_KEYS.EDIT, false);
                                    setEnabledMarkerControl(me, CONTROL_KEYS.CLEAR, false);
                                }
                                $("#" + cid(config, CONTROL_KEYS.DEL) + "-" + marker_id + '_container').remove();
                            });
                        }
                    }

                    me.initialized = true;

                    start(me);
                };


                prototype.checkAnswers = function (config) {
                    checkAnswers(this);
                };


                var qpiClass = M.qtypes.omerointeractive.QuestionPlayerInteractive;

                qpiClass.serializeResponse = function (markers, shapes, image_viewer_controller) {
                    var result = {
                        markers: $.map(markers, function (val, i) {
                            var marker = image_viewer_controller.toShapeJSON(val.id);
                            //return {
                            //    "id": val.id,
                            //    "center": {"x": marker.center_x, "y": marker.center_y},
                            //    "radius": marker.radius
                            //};
                            return marker;
                        }),
                        shapes: $.map(shapes, function (val, i) {
                            return val;
                        })
                    };
                    console.log(result);
                    return JSON.stringify(result);
                };

                qpiClass.deserializeResponse = function (response) {
                    var response = JSON.parse(response);
                    return response;
                };
            },


            /**
             *
             *
             */
            start: function (config) {
                var instance = new M.qtypes.omerointeractive.QuestionPlayerInteractive();
                instance.initialize(config);
                console.log("Question interactive player initialized!!!");
            }
        };
    }
);