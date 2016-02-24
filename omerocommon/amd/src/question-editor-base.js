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
 * UI Controller with basic logic for the question editor.
 *
 * @package    qtype
 * @subpackage omerocommon
 * @copyright  2015-2016 CRS4
 * @license    https://opensource.org/licenses/mit-license.php MIT license
 */
define([
        'jquery',
        'qtype_omerocommon/moodle-forms-utils',
        'qtype_omerocommon/answer-base',
        'qtype_omerocommon/multilanguage-element',
        'qtype_omerocommon/multilanguage-attoeditor',
        'qtype_omerocommon/roi-shape-model',
        'qtype_omerocommon/roi-shape-table',
    ],
    /* jshint curly: false */
    /* globals console, jQuery, EventException */
    function ($/*, FormUtils*/) {

        // override jQuery definition
        $ = jQuery;

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
                var language_selector = document.forms[0].elements.question_language; //.["question_language"];
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
            $("#myModal").modal("show");
        }

        function hideDialogMessage() {
            $("#myModal").modal("hide");
        }


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
            var i, counter;

            // the ID of the answer serction
            me._answers_section_id = answers_section_id;
            me._fraction_options = fraction_options;
            me._show_roishape_column_group = false;

            me._answers = [];
            me._answer_ids = {};

            // TODO: initialize me!!!!
            //var frame_id = "omero-image-viewer";
            var visible_roi_list = [];


            initializeSupportedLanguages();

            me._build_answer_controls();


            me._editor = {};
            for (i in me._localized_string_names) {
                var localized_string_name = me._localized_string_names[i];
                var editor = new M.qtypes.omerocommon.MultilanguageAttoEditor(
                    localized_string_name, localized_string_name + "_locale_map", true);
                editor.init(language_selector.val(), localized_string_name + "_locale_map");
                editor.loadDataFromFormInputs(localized_string_name + "_locale_map");
                editor.onLanguageChanged(language_selector.val());
                // registers a reference to the editor instance
                me._editor[localized_string_name] = editor;
            }

            me._answers_counter_element = document.forms[0].elements.noanswers;//["noanswers"];
            if (!me._answers_counter_element) {
                counter = document.createElement("input");
                counter.setAttribute("type", "hidden");
                counter.setAttribute("name", "noanswers");
                counter.setAttribute("value", "0");
                document.forms[0].appendElement(counter);
                me._answers_counter_element = counter;

            } else {
                counter = me._answers_counter_element.getAttribute("value");
                if (counter) {
                    counter = parseInt(counter);
                    for (i = 0; i < counter; i++) {
                        me.addAnswer(true, i);
                    }
                }
            }

            me._image_locked_element = $("[name^=omeroimagelocked]");
            me._image_locked = me._image_locked_element.val() == "1";

            $('#omero-image-view-lock').bootstrapToggle(me._image_locked ? 'on' : 'off');
            $('#omero-image-view-lock').change(function () {
                me._image_locked_element.val($(this).prop('checked') ? 1 : 0);
            });

            me._visible_roi_list = [];
            me.initElementList("visiblerois", me._visible_roi_list);

            me._focusable_roi_list = [];
            me.initElementList("focusablerois", me._focusable_roi_list);

            $('html, body').animate({
                scrollTop: $("#" + document.forms[0].getAttribute("id")).offset().top - 200
            }, 500);


            // registers the editor as listener of the 'LanguageChanged' event
            language_selector.on("change",
                function (event) {
                    me.onLanguageChanged($(event.target).val());
                }
            );


            // register the frame when loaded
            document.addEventListener("frameLoaded", function (e) {
                me.onViewerFrameLoaded(e.detail.frame_id, visible_roi_list, e.detail);
            }, true);

            // procedure for pre-processing and validating data to submit
            var submit_function = function () {
                try {
                    me.saveAll();
                    var errors = me.validate();

                    // prepare and display error messages
                    if (errors.length > 0) {
                        var errorMessage = "";
                        for (var i in errors)
                            errorMessage += '<i class="glyphicon glyphicon-thumbs-down red"></i>  ' + errors[i] + '<br>';
                        me._showDialogMessage(errorMessage);
                    }
                    return errors.length === 0;

                } catch (er) {
                    console.error(er);
                    me._showDialogMessage(er.message);
                }
            };

            // attach the the pre-submit procedure
            $("input[name=updatebutton]").on("click", submit_function);
            $("input[name=submitbutton]").on("click", submit_function);
        };


        /**
         * Validate data before submission
         * @returns {array} a string array containing error messages
         */
        prototype.validate = function (errors) {

            errors = errors || [];
            if (!this._image_viewer_controller
                || this._image_viewer_controller._image_id === null
                || this._image_viewer_controller._image_id === undefined) {
                errors.push(M.util.get_string('validate_no_image', 'qtype_omerocommon'));
            }

            if (this._answers.length < 1) {
                errors.push(M.util.get_string('validate_no_answers', 'qtype_omerocommon'));
            }

            var single_correct_answer = this.hasSingleCorrectAnswer();
            var max_fraction = 0;
            var total_fraction = 0;
            var found_max = 0;

            for (var i in this._answers) {
                var data = this._answers[i].getDataToSubmit();
                var fraction = parseFloat(data.fraction);
                if (fraction > max_fraction)
                    max_fraction = fraction;
                if (fraction == 1)
                    found_max += 1;
                if (fraction > 0) // only positive matter
                    total_fraction += fraction;
            }

            if (single_correct_answer) {
                if (found_max === 0)
                    errors.push(M.util.get_string('validate_at_least_one_100', 'qtype_omerocommon'));
                else if (found_max > 1)
                    errors.push(M.util.get_string('validate_at_least_one_100', 'qtype_omerocommon'));
            } else {
                if (total_fraction != 1)
                    errors.push(M.util.get_string('validate_sum_of_grades', 'qtype_omerocommon'));
            }

            return errors;
        };

        prototype._showDialogMessage = function (message) {
            showDialogMessage(message);
        };


        prototype.hasSingleCorrectAnswer = function () {
            return $("[name=single]").val() == 1;
        };

        prototype.saveAll = function () {
            this.saveMultilanguageElements();
            for (var i in this._answers) {
                this._answers[i].saveDataToFormInputs(i);
            }
        };


        prototype._build_answer_controls = function () {
            var me = this;
            try {
                $("#add_answer_button li").click(
                    function (e) {
                        var no_answers = $(e.target).attr("value");
                        console.log("Click on ADD Answer no.", no_answers);
                        window.last = e.target;
                        if (!no_answers)
                            console.warn("The number of answer to add seems to be undefined!!!");
                        else {
                            no_answers = parseInt(no_answers);
                            for (var i = 1; i <= no_answers; i++) {
                                me.addAnswer(i !== 1);
                            }
                        }
                    }
                );
            } catch (e) {
                console.error("Error while creating the toolbar", e);
            }
        };

        prototype.getSelectedROIIds = function () {
            return this._roi_shape_table ?
                this._roi_shape_table.getIdSelections() : [];
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
            for (var editor_name in this._editor) {
                this._editor[editor_name].onLanguageChanged(language);
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
            if (locked) this.lockImage();
            else this.unlockImage();
        };

        prototype.updateViewCenter = function () {
        };


        prototype.saveMultilanguageElements = function () {
            console.log("Saving multi language elements...");
            for (var element_name in this._editor) {

                var editor = this._editor[element_name];
                var locale_map_name = element_name + "_locale_map";
                var id = 'id_' + locale_map_name;
                console.log("Saving editor data...", id, locale_map_name);

                var hidden = document.forms[0].elements[locale_map_name];
                if (!hidden) //hidden.val(value);
                {
                    hidden = '<input ' +
                        'id="' + id + '" ' + 'name="' + locale_map_name + '" type="hidden" >';
                    console.log("Creating the hidden field", id, name, locale_map_name);
                    M.qtypes.omerocommon.MoodleFormUtils.appendHiddenElement(document.forms[0], hidden);
                    console.log("Created the hidden field", id, name, locale_map_name);
                } else {
                    console.log("Found hidden field to save editor data...", id, name, locale_map_name);
                }

                editor.saveDataToFormInputs(locale_map_name);
            }
        };


        prototype.buildAnswer = function (answer_number, fraction_options) {
            console.error("You need to implement this method!!!", answer_number, fraction_options);
        };

        prototype.addAnswer = function (disable_animiation, answer_index) {
            var i;
            var me = this;
            var answer_uuid = M.qtypes.omerocommon.MoodleFormUtils.generateGuid();
            var answer = this.buildAnswer(answer_uuid, this._fraction_options);

            if (answer) {
                this._answers.push(answer);
                this._answer_ids[answer_uuid] = answer;
                answer.show();
                if (typeof answer_index !== 'undefined') {
                    answer.loadDataFromFormInputs(answer_index, true);
                }
                answer.remove = function () {
                    me.removeAnswer(this._answer_number);
                };
                this.updateAnswerCounter();
                var editors = answer.getEditorsMap();
                for (i in editors) {
                    this._editor[i] = editors[i];
                }
                for (i in this._answers) {
                    this._answers[i].updateHeader((parseInt(i) + 1));
                }
            }

            if (!disable_animiation)
                $('html, body').animate({
                    scrollTop: $("#" + answer_uuid).offset().top - 125
                }, 1000);

            // callback for the event onAddAnswer
            if (this.onAddAnswer)
                this.onAddAnswer(answer);

            return answer;
        };


        prototype.removeAnswer = function (answer_id) {
            console.log(this._answers);
            var i;
            if (answer_id in this._answer_ids) {
                var answer = this._answer_ids[answer_id];
                if (answer) {
                    answer.hide();
                    this._answers.splice(answer_id, 1);
                    delete this._answer_ids[answer_id];
                    this.updateAnswerCounter();
                    var editors = answer.getEditorsMap();
                    for (i in editors) {
                        var editor = editors[i];
                        editor.destroy();
                        delete this._editor[i];
                    }

                    var last_answer_header = null;

                    for (i in this._answers) {
                        this._answers[i].updateHeader((parseInt(i) + 1));
                        last_answer_header = this._answers[i]._answer_head;
                    }

                    if (last_answer_header !== null)
                        $('html, body').animate({
                            scrollTop: last_answer_header.offset().top - 125
                        }, 1000);

                    // callback for the event onRemoveAnswer
                    if (this.onRemoveAnswer) this.onRemoveAnswer(answer);

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

            if (frame_details === undefined) {
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

            me._image_viewer_controller.onViewerInitialized(function () {
                me._initImagePropertiesControls();
                me._image_viewer_controller.updateViewFromProperties(me._image_properties);
            });

            console.log("OnFrameInitialized", me, frame_id, image_details, visible_roi_list);
        };


        prototype.onImageModelRoiLoaded = function (e) {

            var roi_list = M.qtypes.omerocommon.RoiShapeModel.toRoiShapeModel(e.detail,
                this._visible_roi_list, this._focusable_roi_list);
            console.log("Loaded ROI Shapes Models", roi_list);

            if (!this._roi_shape_table) {
                this._roi_shape_table = new M.qtypes.omerocommon.RoiShapeTableBase(
                    "roi-shape-inspector-table");
                this._roi_shape_table.initTable(false, this._show_roishape_column_group);
                this._roi_shape_table.addEventListener(this);
            }
            this._roi_shape_table.removeAll();
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
                throw new EventException("Frame " + frame_id + " not found!!!");
            }

            // Registers a reference to the frame
            me._omero_viewer_frame = omero_viewer_frame;

            // Register a reference to the Omero Repository Controller
            var frameWindow = me._omero_viewer_frame.contentWindow;
            me._image_viewer_controller = frameWindow.omero_repository_image_viewer_controller;
        };


        prototype.initElementList = function (list_name, list) {
            var input_list = $("[name=" + list_name + "]").val();
            if (input_list && input_list != "none") {
                var temp_list = input_list.split(",");
                for (var i in temp_list) {
                    list[i] = parseInt(temp_list[i]);
                }
            }

            document.forms[0].addEventListener("submit", function () {
                $("[name=" + list_name + "]").val(list.join(","));
            });

            console.log("Initialized list", list);
        };


        prototype.onRoiShapeVisibilityChanged = function (event) {
            console.log(event);
            this.onRoiShapePropertyChanged(event, "visible", this._visible_roi_list);
        };


        prototype.onRoiShapeFocusabilityChanged = function (event) {
            console.log(event);
            this.onRoiShapePropertyChanged(event, "focusable", this._focusable_roi_list);
        };

        prototype.onRoiShapePropertyChanged = function (event, property, visible) {
            if (event.shape[property]) {
                if (property === "visible") this._image_viewer_controller.showRoiShapes([event.shape.id]);
                if (visible.indexOf(event.shape.id) === -1)
                    visible.push(event.shape.id);
            } else {
                if (property === "visible") this._image_viewer_controller.hideRoiShapes([event.shape.id]);
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
            if (ip.center) {
                result += ("center (x,y): " + ip.center.x + ", " + ip.center.y);
                result += (", zoom: " + ip.zoom_level);
                result += (", t: " + ip.t);
                result += (", z: " + ip.z);
            }
            return result;
        };

        prototype.updateImageProperties = function () {
            this._image_properties = this._image_viewer_controller.getImageProperties();
            this._image_properties_element.val(JSON.stringify(this._image_properties));
            $("#omero-image-viewer-properties").html(this.getFormattedImageProperties());
        };

        return M.qtypes.omerocommon.QuestionEditorBase;
    }
);