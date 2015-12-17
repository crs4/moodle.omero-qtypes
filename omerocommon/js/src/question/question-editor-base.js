/**
 * Created by kikkomep on 12/3/15.
 */
/**
 * Created by kikkomep on 12/2/15.
 */

define("qtype_omerocommon/question-editor-base",
    [
        'jquery',
        'qtype_omerocommon/moodle-forms-utils',
        'qtype_omerocommon/answer-base',
        'qtype_omerocommon/multilanguage-element',
        'qtype_omerocommon/multilanguage-attoeditor'
    ],
    function ($, Editor, FormUtils) {
        // Private functions.

        // A reference to the languageSelector
        var language_selector = $("#id_question_language");

        /**
         * List of supported languages
         *
         * @private
         */
        var _supported_languages = null;

        /**
         * Initializes the list of supported languages
         * and sets the currently selected language
         *
         * @private
         */
        function initializeSupportedLanguages() {

            // initializes the list of supported languages
            if (!_supported_languages) {
                _supported_languages = [];
                var language_selector = document.forms[0].elements["question_language"];
                var language_options = language_selector.options;
                for (var i = 0; i < language_options.length; i++) {
                    _supported_languages.push(language_options[i].value);
                }
            }

            // handles the event 'language changed'
            return _supported_languages;
        }


        function showDialogMessage(message) {
            $("#modal-frame-text").html(message);
            $("#myModal").modal();
        }


        // Public functions
        return {
            initialize: function (str) {
                console.log("Initialized", this);

                // defines the basic package
                M.qtypes = M.qtypes || {};

                // defines the specific package of this module
                M.qtypes.omerocommon = M.qtypes.omerocommon || {};

                /**
                 * Builds a new instance
                 *
                 * @constructor
                 */
                M.qtypes.omerocommon.QuestionEditorBase = function () {

                    // the reference to this scope
                    var me = this;


                    me._localized_string_names = [
                        "questiontext",
                        "generalfeedback",
                        "correctfeedback", "partiallycorrectfeedback", "incorrectfeedback"
                    ];

                    $(document).ready(function () {
                        M.qtypes.omerocommon.MoodleFormUtils.initDropdown();
                    });
                };


                M.qtypes.omerocommon.QuestionEditorBase.getInstance = function () {
                    return M.qtypes.omerocommon.QuestionEditorBase.instance;
                };

                // A local reference to the prototype
                var prototype = M.qtypes.omerocommon.QuestionEditorBase.prototype;


                /**
                 * Initializes this questionEditor controller
                 */
                prototype.initialize = function (answers_section_id, fraction_options) {
                    var me = this;

                    initializeSupportedLanguages();

                    // the ID of the answer serction
                    me._answers_section_id = answers_section_id;
                    me._fraction_options = fraction_options;
                    me._show_roishape_column_group = false;

                    $(document).ready(function () {

                        me._build_answer_controls();

                        me._editor = {};
                        for (var i in me._localized_string_names) {
                            var localized_string_name = me._localized_string_names[i];
                            var editor = new M.qtypes.omerocommon.MultilanguageAttoEditor(localized_string_name, localized_string_name + "_locale_map", true);
                            editor.init(language_selector.val(), localized_string_name + "_locale_map");
                            editor.loadDataFromFormInputs(localized_string_name + "_locale_map");
                            editor.onLanguageChanged(language_selector.val());
                            // registers a reference to the editor instance
                            me._editor[localized_string_name] = editor;
                        }

                        me._answers_counter_element = document.forms[0].elements["noanswers"];
                        if (!me._answers_counter_element) {
                            var counter = document.createElement("input");
                            counter.setAttribute("type", "hidden");
                            counter.setAttribute("name", "noanswers");
                            counter.setAttribute("value", "0");
                            document.forms[0].appendElement(counter);
                            me._answers_counter_element = counter;

                        } else {
                            var counter = me._answers_counter_element.getAttribute("value");
                            if (counter) {
                                counter = parseInt(counter);
                                for (var i = 0; i < counter; i++) {
                                    me.addAnswer(i, true);
                                }
                            }
                        }

                        var $ = jQuery;
                        me._image_locked_element = $("[name^=omeroimagelocked]");
                        me._image_locked = me._image_locked_element.val() == "1";

                        $('#omero-image-view-lock').bootstrapToggle(me._image_locked ? 'on' : 'off');
                        $('#omero-image-view-lock').change(function () {
                            me._image_locked_element.val($(this).prop('checked') ? 1 : 0);
                        });

                        me.initVisibleRoiList();

                        $('html, body').animate({
                            scrollTop: $("#" + document.forms[0].getAttribute("id")).offset().top - 200
                        }, 500);
                    });

                    me._answers = [];
                    me._answer_ids = {};

                    // registers the editor as listener of the 'LanguageChanged' event
                    language_selector.on("change",
                        function (event) {
                            me.onLanguageChanged($(event.target).val());
                        }
                    );

                    // TODO: initialize me!!!!
                    var frame_id = "omero-image-viewer";
                    var visible_roi_list = [];




                    // register the frame when loaded
                    document.addEventListener("frameLoaded", function (e) {
                        me.onViewerFrameLoaded(e.detail.frame_id, visible_roi_list, e.detail);
                    }, true);
                };




                prototype._build_answer_controls = function () {
                    try {
                        this._toolbar_container = $('<div id="answers_toolbar" class="panel"></div>');
                        this._toolbar_container_body = $('<div class="panel-body"></div>');
                        this._add_answer_btn = $('<button id="add-answer-btn" type="button" class="btn btn-info">Add answer</button>');

                        $("#" + this._answers_section_id).prepend(this._toolbar_container);
                        this._toolbar_container.prepend(this._toolbar_container_body);
                        this._toolbar_container_body.prepend(this._add_answer_btn);

                        var me = this;
                        this._add_answer_btn.on("click", function () {
                            me.addAnswer();
                        });

                    } catch (e) {
                        console.error("Error while creating the toolbar", e);
                    }
                };

                /**
                 * Returns the list of supported languages
                 *
                 * @returns {*|Array.<T>|string|Blob|ArrayBuffer}
                 */
                prototype.getSupportedLanguages = function () {
                    return _supported_languages.slice();
                };


                /**
                 * Return the current selected language
                 * @returns {*}
                 */
                prototype.getCurrentLanguage = function () {
                    return language_selector.val();
                };


                /**
                 * Handler for ths 'LanguageChanged' event:
                 * it updates the text of all editors
                 * @param language
                 */
                prototype.onLanguageChanged = function (language) {
                    for (var locale_string in this._editor) {
                        this._editor[locale_string].onLanguageChanged(language);
                    }
                };


                prototype.isLockedImage = function () {
                    return this._image_locked;
                };


                prototype.lockImage = function () {
                    this._image_locked_element.val(true);
                    this._image_locked = true;
                };


                prototype.unlockImage = function () {
                    this._image_locked_element.val(false);
                    this._image_locked = false;
                };

                prototype.onLockImageChanged = function (locked) {
                    locked ? this.lockImage() : this.unlockImage();
                };

                prototype.updateViewCenter = function () {

                };


                prototype.buildAnswer = function (answer_number, fraction_options) {
                    console.error("You need to implement this method!!!");
                };

                prototype.addAnswer = function () {
                    var answer_number = this._answers.length;
                    var answer = this.buildAnswer(answer_number, this._fraction_options);
                    if (answer) {
                        answer.show();
                        var me = this;
                        answer.remove = function () {
                            me.removeAnswer(answer_number);
                        };
                        this._answers.push(answer);
                        this.updateAnswerCounter();
                        var editors = answer.getEditorsMap();
                        for (var i in editors) {
                            this._editor[i] = editors[i];
                        }
                    }
                    return answer;
                };


                prototype.removeAnswer = function (answer_number) {
                    if (answer_number >= 0) {
                        var answer = this._answers[answer_number];
                        if (answer) {
                            answer.hide();
                            this._answers.splice(answer_number, 1);
                            this.updateAnswerCounter();
                            var editors = answer.getEditorsMap();
                            for (var i in editors) {
                                var editor = editors[i];
                                editor.destroy();
                                delete this._editor[editor.input_data_element_name];
                            }
                            return true;
                        }
                    }
                    return false;
                };


                prototype.updateAnswerCounter = function () {
                    this._answers_counter_element.setAttribute("value", this._answers.length);
                };


                /**
                 * Updates the reference to the frame containing OmeroImageViewer
                 *
                 * @param frame_id
                 * @returns {Element|*|omero_viewer_frame}
                 * @private
                 */
                prototype.onViewerFrameLoaded = function (frame_id, visible_roi_list, frame_details) {
                    var me = this;
                    var omero_viewer_frame = document.getElementById(frame_id);
                    if (!omero_viewer_frame) {
                        throw ("Frame " + frame_id + " not found!!!");
                    }
                    // Registers a reference to the frame
                    me._omero_viewer_frame = omero_viewer_frame;

                    if (frame_details == undefined) {
                        // Register the main listener for the 'omeroViewerInitialized' event
                        me._omero_viewer_frame.contentWindow.addEventListener("omeroViewerInitialized", function (e) {
                            me.onViewerFrameInitialized(me, frame_id, e.detail, visible_roi_list);
                            console.log("OmeroImageViewer init loaded!!!");
                        }, true);
                    } else {
                        me.onViewerFrameInitialized(me, frame_id, frame_details, visible_roi_list);
                    }

                    // Log message (for debugging)
                    console.log("Frame Object Registered!!!");

                    // enable chaining
                    return me._omero_viewer_frame;
                };


                /**
                 *
                 * @param frame_id
                 * @param frame_details
                 * @param visible_roi_list
                 */
                prototype.onViewerFrameInitialized = function (me, frame_id, image_details, visible_roi_list) {
                    me.current_image_info = image_details;
                    me._registerFrameWindowEventHandlers(me, frame_id);
                    me._image_viewer_controller.getModel().addEventListener(me);

                    $("#" + frame_id + "-toolbar").removeClass("hidden");

                    me._image_viewer_controller.onViewerInitialized(function(){
                        me._initImagePropertiesControls();
                        me._image_viewer_controller.updateViewFromProperties(me._image_properties);
                    });
                };


                prototype.onImageModelRoiLoaded = function (e) {

                    var roi_list = M.qtypes.omerocommon.RoiShapeModel.toRoiShapeModel(e.detail,
                        this._visible_roi_list);
                    console.log("Loaded ROI Shapes Models", roi_list);

                    if (!this._roi_shape_table) {
                        this._roi_shape_table = new M.qtypes.omerocommon.RoiShapeTableBase("roi-shape-inspector-table");
                        this._roi_shape_table.initTable();
                        this._roi_shape_table.addEventListener(this);
                    }
                    this._roi_shape_table.appendRoiShapeList(roi_list);
                    this._image_viewer_controller.showRoiShapes(this._visible_roi_list);

                    console.log("Updated ROI table!!!");
                };

                /**
                 * Register listeners for events triggered
                 * by the frame identified by 'frame_id'
                 *
                 * @param frame_id
                 * @private
                 */
                prototype._registerFrameWindowEventHandlers = function (me, frame_id) {
                    var omero_viewer_frame = document.getElementById(frame_id);
                    if (!omero_viewer_frame) {
                        throw EventException("Frame " + frame_id + " not found!!!");
                    }

                    // Registers a reference to the frame
                    me._omero_viewer_frame = omero_viewer_frame;

                    // Register a reference to the Omero Repository Controller
                    var frameWindow = me._omero_viewer_frame.contentWindow;
                    me._image_viewer_controller = frameWindow.omero_repository_image_viewer_controller;

                    // Adds listeners
                    var frameWindow = omero_viewer_frame.contentWindow;
                    //frameWindow.addEventListener("roiShapeSelected", M.omero_multichoice_helper.roiShapeSelected);
                    //frameWindow.addEventListener("roiShapeDeselected", M.omero_multichoice_helper.roiShapeDeselected);
                    //frameWindow.addEventListener("roiVisibilityChanged", M.omero_multichoice_helper.roiVisibilityChanged);
                };


                prototype.initVisibleRoiList = function () {
                    var me = this;
                    var roi_list = null;
                    me._visible_roi_list = [];
                    var visible_roi_list = $("[name=visiblerois]").val();
                    if (visible_roi_list && visible_roi_list != "none") {
                        roi_list = visible_roi_list.split(",");
                        for (var i in roi_list) {
                            me._visible_roi_list[i] = parseInt(roi_list[i]);
                        }
                    }

                    document.forms[0].addEventListener("submit", function () {
                        $("[name=visiblerois]").val(me._visible_roi_list.join(","));
                    });

                    console.log("Initialized Visible ROI list", me._visible_roi_list);
                };

                prototype.onRoiShapeVisibilityChanged = function (event) {
                    console.log(event);

                    var visible = this._visible_roi_list;
                    if (event.shape.visible) {
                        this._image_viewer_controller.showRoiShapes([event.shape.id]);
                        if (visible.indexOf(event.shape.id) === -1)
                            visible.push(event.shape.id);
                    } else {
                        this._image_viewer_controller.hideRoiShapes([event.shape.id]);
                        var index = visible.indexOf(event.shape.id);
                        if (index > -1)
                            visible.splice(index, 1);
                    }
                };

                prototype.onRoiShapeFocus = function (event) {
                    this._image_viewer_controller.setFocusOnRoiShape.call(
                        this._image_viewer_controller,
                        event.shape.id
                    );
                };


                prototype._initImagePropertiesControls = function () {
                    var me = this;
                    me._image_properties_element = $("[name^=omeroimageproperties]");
                    me._image_properties = me._image_properties_element.val();
                    if (me._image_properties && me._image_properties.length !== 0) {
                        try {
                            me._image_properties = JSON.parse(me._image_properties);
                        } catch (e) {
                            console.error(e);
                            me._image_properties = {};
                        }
                    } else me._image_properties = {};

                    $("#omero-image-viewer-properties").html(me.getFormattedImageProperties());

                    $("#omero-image-properties-update").click(function () {
                        me.updateImageProperties();
                    });
                };


                prototype.getImageProperties = function () {
                    return this._image_properties;
                };

                prototype.getFormattedImageProperties = function () {
                    var ip = this._image_properties;
                    var result = "";
                    if(ip.center) {
                        result += ("center (x,y): " + ip.center.x + ", " + ip.center.y);
                        result += (", zoom: " + ip.zoom_level);
                        result += (", t: " + ip.t);
                        result += (", z: " + ip.z);
                    }
                    return result;
                };

                prototype.updateImageProperties = function () {
                    this._image_properties =  this._image_viewer_controller.getImageProperties();
                    this._image_properties_element.val(JSON.stringify(this._image_properties));
                    $("#omero-image-viewer-properties").html(this.getFormattedImageProperties());
                };
            }
        };
    }
);