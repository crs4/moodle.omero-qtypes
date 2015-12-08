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
         * Initializes the list of supported languages
         * and sets the currently selected language
         *
         * @private
         */
        var _supported_languages = function () {

            // initializes the list of supported languages
            var supported_languages = [];
            var language_selector = document.forms[0].elements["question_language"];
            var language_options = language_selector.options;
            for (var i = 0; i < language_options.length; i++) {
                supported_languages.push(language_options[i].value);
            }

            // handles the event 'language changed'
            //document.forms[0].elements["question_language"].onchange = me._updateCurrentLanguage;
            return supported_languages;
        }();


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
                M.qtypes.omerocommon.QuestionEditorBase = function (answers_section_id, fraction_options) {

                    // the reference to this scope
                    var me = this;

                    // the ID of the answer serction
                    me._answers_section_id = answers_section_id;

                    me._fraction_options = fraction_options;

                    me._localized_string_names = [
                        "questiontext",
                        "generalfeedback",
                        "correctfeedback", "partiallycorrectfeedback", "incorrectfeedback"
                    ];
                };


                // A local reference to the prototype
                var prototype = M.qtypes.omerocommon.QuestionEditorBase.prototype;


                /**
                 * Initializes this questionEditor controller
                 */
                prototype.initialize = function () {
                    var me = this;
                    me._editor = {};
                    for (var i in me._localized_string_names) {
                        var localized_string_name = me._localized_string_names[i];
                        var editor = new M.qtypes.omerocommon.MultilanguageAttoEditor("id_" + localized_string_name, true);
                        editor.init({}, "en");
                        // registers a reference to the editor instance
                        me._editor[localized_string_name] = editor;
                    }


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
                            me.onViewerFrameInitialized(frame_id, e.detail, visible_roi_list);
                            console.log("OmeroImageViewer init loaded!!!");
                        }, true);
                    } else {
                        me.onViewerFrameInitialized(frame_id, frame_details, visible_roi_list);
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
                prototype.onViewerFrameInitialized = function (frame_id, image_details, visible_roi_list) {
                    var me = this;
                    me.current_image_info = image_details;
                    me._registerFrameWindowEventHandlers(frame_id);
                    me._image_viewer_controller.getModel().addEventListener(me);
                };


                /**
                 * Register listeners for events triggered
                 * by the frame identified by 'frame_id'
                 *
                 * @param frame_id
                 * @private
                 */
                prototype._registerFrameWindowEventHandlers = function (frame_id) {
                    var me = this;
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
                    frameWindow.addEventListener("roiShapeSelected", M.omero_multichoice_helper.roiShapeSelected);
                    frameWindow.addEventListener("roiShapeDeselected", M.omero_multichoice_helper.roiShapeDeselected);
                    frameWindow.addEventListener("roiVisibilityChanged", M.omero_multichoice_helper.roiVisibilityChanged);
                };
            }
        };
    }
);