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

    // list of supported languages
    me._supported_languages = [];

    // list of names of localized strings
    me._localized_string_names = [
        "questiontext",
        "generalfeedback",
        "correctfeedback", "partiallycorrectfeedback", "incorrectfeedback",
        "answer"
    ];

    // list of localized strings
    me._localized_strings = [];

    // list of localized textareas
    me._localized_textareas = [];

    // register frame object is already loaded
    if (frame_id) {
        me._registerFrameObject(frame_id, visible_roi_list);
    }

    // register the frame when loaded
    document.addEventListener("frameLoaded", function (e) {
        var frame_id = e.detail.frame_id;
        console.log("frame Loaded!!!");
        me._registerFrameObject(frame_id, visible_roi_list, e.detail);
    }, true);

    // init localized strings
    if (me.isEditingMode()) {

        me._updateQuestionEditorType();

        // registers language selector event listener
        // and initializes the list of supported languages
        me._initLanguageSelector();

        // initializes localized strings
        me._initLocalizedStrings();

        // initialize the current language
        me._updateCurrentLanguage();

        // Registers the submit function
        document.forms[0].onsubmit = me._on_question_submitted;
    }

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


me._updateQuestionEditorType = function () {

    var form = me._getForm();
    if (!form) return;

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

/**
 * Updates the reference to the frame containing OmeroImageViewer
 *
 * @param frame_id
 * @returns {Element|*|omero_viewer_frame}
 * @private
 */
me._registerFrameObject = function (frame_id, visible_roi_list, frame_details) {
    var omero_viewer_frame = document.getElementById(frame_id);
    if (!omero_viewer_frame) {
        throw ("Frame " + frame_id + " not found!!!");
    }
    // Registers a reference to the frame
    me._omero_viewer_frame = omero_viewer_frame;

    if (frame_details == undefined) {
        // Register the main listener for the 'omeroViewerInitialized' event
        me._omero_viewer_frame.contentWindow.addEventListener("omeroViewerInitialized", function (e) {
            me._initialize(frame_id, e.detail, visible_roi_list);
            console.log("OmeroImageViewer init loaded!!!");
        }, true);
    } else {
        me._initialize(frame_id, frame_details, visible_roi_list);
    }

    // Log message (for debugging)
    console.log("Frame Object Registered!!!");

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
};


/**
 * Initializes the list of supported languages
 * and sets the currently selected language
 *
 * @private
 */
me._initLanguageSelector = function () {

    // initializes the list of supported languages
    me._supported_languages = [];
    var language_selector = document.forms[0].elements["question_language"];
    var language_options = language_selector.options;
    for (var i = 0; i < language_options.length; i++) {
        me._supported_languages.push(language_options[i].value);
    }

    // handles the event 'language changed'
    document.forms[0].elements["question_language"].onchange = me._updateCurrentLanguage;
};


/**
 * Updates the currently selected language
 * @param index
 * @returns {string|*|Number} the "id" of the current language
 * @private
 */
me._updateCurrentLanguage = function () {
    var previous_language = me._current_language;
    var language_selector = document.forms[0].elements["question_language"];
    me._current_language = language_selector.options[language_selector.selectedIndex].value;
    me._updateLocalizedStrings(previous_language, me._current_language);
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
    for (var j in me.current_rois_info) {
        me.available_rois.push(me.current_rois_info[j].id);
    }

    // Initialize the list of ROIs to show
    var visible_rois_input_field = document.forms[0].elements['visible_rois'].value;
    if (visible_rois_input_field
        && visible_rois_input_field.length > 0
        && visible_rois_input_field != "none") {
        me._visible_roi_list = visible_rois_input_field.split(",");
    } else {
        me._visible_roi_list = [];
    }

    // Registers the handler for the question type
    //document.forms[0].elements['answertype'].onchange = me._on_question_type_changed;

    // Initializes the ROI based answers
    me._initRoiBasedAnswers();

    // Logs the current state of ROI lists
    console.log("Available ROIs:", me.available_rois);
    console.log("ROI based answers:", me.roi_based_answers);
    console.log("Visible ROIs:", me._visible_roi_list);
};


me.enableNewRoiBasedAnswerButton = function (enabled) {
    var form = me._getForm();
    if (!form) return;

    var add_roi_button = form.elements['add-roi-answer'];
    if (add_roi_button) {
        add_roi_button.disabled = !enabled;
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


me._on_question_type_changed = function () {
    document.forms[0].elements['noanswers'].value = 0;
    me._on_question_submitted(true);
    me._getForm().submit();
};


/**
 * Performs several controls before the form submition
 *
 * @private
 */
me._on_question_submitted = function (disable_validation) {

    // encode localized strings to be submitted
    me._prepareLocalizedStringsForSubmission();

    // get the form element containing the url of the current selected image
    var image_url_input_element = document.forms[0].elements["omero_image_url"];
    document.forms[0].elements["omeroimagefilereference"].value = image_url_input_element.value;

    // get the relative path to the current image selection:
    // including references to the current zoom level, displayed area, etc.
    var image_relative_path = me._build_image_link();
    if (disable_validation != true && image_relative_path == null) {

        var errMsgCtn = document.getElementById("omeroimagefilereferencechoose-errMsg");
        if (!errMsgCtn) {
            var errMsgCtn = document.createElement("span");
            errMsgCtn.id = "omeroimagefilereferencechoose-errMsg";
            errMsgCtn.className = "error";
            document.forms[0].elements["omeroimagefilereferencechoose"].parentNode.appendChild(errMsgCtn);
        }
        errMsgCtn.innerHTML = "No image selected!!!";
        return false;
    }

    if (image_relative_path != null && image_relative_path.length > 0) {
        // update the current input element value
        var old = image_url_input_element.value;
        var newurl = me.omero_viewer_controller.omero_server + "/webgateway/render_thumbnail/" + image_relative_path;

        // update the list of ROIs to display
        document.forms[0].elements['visible_rois'].value = me._visible_roi_list.join(",");

        // update the current URL with image params (i.e., zoom, channels, etc.)
        console.log("Updating URL...", old, newurl);
        image_url_input_element.value = newurl.replace("/?", "?");
        //if (newurl == null || newurl.length == 0) {
        //    // FIXME: a better message view
        //    alert("No image selected!!!");
        //    return false;
        //}
    }
};


/**
 * Builds the link to the current portion of the displayed image
 *
 * @returns {string} the relative path of the image
 * @private
 */
me._build_image_link = function () {
    var link = null;
    if (me.omero_viewer_controller && me.omero_viewer_controller.viewport) {
        var viewport = me.omero_viewer_controller.viewport;
        link = viewport.getCurrentImgUrlPath() + '?' + viewport.getQuery(true, true, true);
        console.log("Current image link", link);
    }
    return link;
};


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
};


me.removeVisibleRoi = function (roi_id) {
    console.log("Removing a visible ROI...", roi_id, me._visible_roi_list);
    roi_id = roi_id.toString();
    var index = me._visible_roi_list.indexOf((roi_id));
    if (index > -1) {
        me._visible_roi_list.splice(index, 1);
    }
};


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
    if (event.detail.visible) {
        me.addVisibleRoi(roi_info.id);
    } else {
        me.removeVisibleRoi(roi_info.id);
    }

    console.log("Changed vibility to " + event.detail.visible
        + " of RoiShape", roi_info, "Visible ROIs: " + me._visible_roi_list.join(","));
};


me._initLocalizedStrings = function () {


    for (var i = 0; i < me._localized_string_names.length; i++) {

        //
        console.log("Initializing Localized string: " + me._localized_string_names[i]);

        // localized string
        var localized_string_name = me._localized_string_names[i];
        me._localized_strings[localized_string_name] = [];

        // init locale strings with the default empty string
        for (var j = 0; j < me._supported_languages.length; j++) {
            me._localized_strings[localized_string_name][me._supported_languages[j]] = "";
        }

        // Updates the localized string with actual values
        me._localized_textareas[localized_string_name] = document.querySelectorAll("[class^=" + localized_string_name + "]");
        for (var j = 0; j < me._localized_textareas[localized_string_name].length; j++) {
            var locale_textarea = me._localized_textareas[localized_string_name][j];
            if (locale_textarea.className == "answer") {
                var pattern = /answer_(\w+)_(\d+)/;
                var matches = pattern.exec("" + locale_textarea.id);
                var answer_lang = matches[1];
                var answer_number = matches[2];
                var answer_name = "answer_" + answer_number;

                // Initializes array to host localized strings and textareas
                if (me._localized_strings[answer_name] == undefined)
                    me._localized_strings[answer_name] = [];
                if (me._localized_textareas[answer_name] == undefined)
                    me._localized_textareas[answer_name] = [];

                me._localized_textareas[answer_name].push(locale_textarea);
                me._localized_strings[answer_name][locale_textarea.getAttribute("lang")] = locale_textarea.innerHTML;

            } else {
                me._localized_strings[localized_string_name][locale_textarea.getAttribute("lang")] = locale_textarea.innerHTML;
            }
        }
    }
};


me._updateLocalizedStrings = function (previous_language, current_language) {
    for (var localized_string_name in me._localized_strings) {
        var string_editor = me._getStringEditor(localized_string_name);
        if (string_editor != null) {
            me._localized_strings[localized_string_name][previous_language] = string_editor.innerHTML;
            //console.log("PREVIOUS: " + me._localized_strings[localized_string_name][previous_language]);
            //console.log("CURRENT: " + me._localized_strings[localized_string_name][current_language]);
            string_editor.innerHTML = me._html_entity_decode(me._localized_strings[localized_string_name][current_language]);
        } else if (localized_string_name != "answer") {
            console.error("Not Found editor for: " + localized_string_name);
        }
    }
};

me._prepareLocalizedStringsForSubmission = function () {
    try {

        for (var localized_string_name in me._localized_strings) {

            // last update
            var string_editor = me._getStringEditor(localized_string_name);
            if (string_editor != null) {
                me._localized_strings[localized_string_name][me._current_language] = string_editor.innerHTML;
            }

            // updates textareas
            for (var j = 0; j < me._localized_textareas[localized_string_name].length; j++) {
                var localized_textarea = me._localized_textareas[localized_string_name][j];
                localized_textarea.innerHTML = me._localized_strings[localized_string_name][localized_textarea.getAttribute("lang")];
                console.log("Updating...", localized_textarea.innerHTML, localized_textarea.getAttribute("lang"));
            }
        }
    } catch (e) {
        console.error(e.message);
        return false;
    }
};


/**
 * Returns the editor for a given editor
 *
 * @param propertyName
 * @returns {*}
 * @private
 */
me._getStringEditor = function (propertyName) {
    var string_editor = document.querySelectorAll("div" + '[id^=id_' + propertyName + 'editable]');
    if (string_editor.length > 0) {
        return string_editor[0];
    }
    return null;
};


me._html_entity_decode = function (string, quote_style) {
    //  discuss at: http://phpjs.org/functions/get_html_translation_table/
    // original by: Philip Peterson
    //  revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // bugfixed by: noname
    // bugfixed by: Alex
    // bugfixed by: Marco
    // bugfixed by: madipta
    // bugfixed by: Brett Zamir (http://brett-zamir.me)
    // bugfixed by: T.Wild
    // improved by: KELAN
    // improved by: Brett Zamir (http://brett-zamir.me)
    //    input by: Frank Forte
    //    input by: Ratheous
    //        note: It has been decided that we're not going to add global
    //        note: dependencies to php.js, meaning the constants are not
    //        note: real constants, but strings instead. Integers are also supported if someone
    //        note: chooses to create the constants themselves.
    //   example 1: get_html_translation_table('HTML_SPECIALCHARS');
    //   returns 1: {'"': '&quot;', '&': '&amp;', '<': '&lt;', '>': '&gt;'}

    var hash_map = {},
        symbol = '',
        tmp_str = '',
        entity = '';
    tmp_str = string.toString();

    if (false === (hash_map = me._get_html_translation_table('HTML_ENTITIES', quote_style))) {
        return false;
    }

    // fix &amp; problem
    // http://phpjs.org/functions/get_html_translation_table:416#comment_97660
    delete(hash_map['&']);
    hash_map['&'] = '&amp;';

    for (symbol in hash_map) {
        entity = hash_map[symbol];
        tmp_str = tmp_str.split(entity)
            .join(symbol);
    }
    tmp_str = tmp_str.split('&#039;')
        .join("'");

    return tmp_str;
};

me._get_html_translation_table = function (table, quote_style) {
    //  discuss at: http://phpjs.org/functions/get_html_translation_table/
    // original by: Philip Peterson
    //  revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // bugfixed by: noname
    // bugfixed by: Alex
    // bugfixed by: Marco
    // bugfixed by: madipta
    // bugfixed by: Brett Zamir (http://brett-zamir.me)
    // bugfixed by: T.Wild
    // improved by: KELAN
    // improved by: Brett Zamir (http://brett-zamir.me)
    //    input by: Frank Forte
    //    input by: Ratheous
    //        note: It has been decided that we're not going to add global
    //        note: dependencies to php.js, meaning the constants are not
    //        note: real constants, but strings instead. Integers are also supported if someone
    //        note: chooses to create the constants themselves.
    //   example 1: get_html_translation_table('HTML_SPECIALCHARS');
    //   returns 1: {'"': '&quot;', '&': '&amp;', '<': '&lt;', '>': '&gt;'}

    var entities = {},
        hash_map = {},
        decimal;
    var constMappingTable = {},
        constMappingQuoteStyle = {};
    var useTable = {},
        useQuoteStyle = {};

    // Translate arguments
    constMappingTable[0] = 'HTML_SPECIALCHARS';
    constMappingTable[1] = 'HTML_ENTITIES';
    constMappingQuoteStyle[0] = 'ENT_NOQUOTES';
    constMappingQuoteStyle[2] = 'ENT_COMPAT';
    constMappingQuoteStyle[3] = 'ENT_QUOTES';

    useTable = !isNaN(table) ? constMappingTable[table] : table ? table.toUpperCase() : 'HTML_SPECIALCHARS';
    useQuoteStyle = !isNaN(quote_style) ? constMappingQuoteStyle[quote_style] : quote_style ? quote_style.toUpperCase() :
        'ENT_COMPAT';

    if (useTable !== 'HTML_SPECIALCHARS' && useTable !== 'HTML_ENTITIES') {
        throw new Error('Table: ' + useTable + ' not supported');
        // return false;
    }

    entities['38'] = '&amp;';
    if (useTable === 'HTML_ENTITIES') {
        entities['160'] = '&nbsp;';
        entities['161'] = '&iexcl;';
        entities['162'] = '&cent;';
        entities['163'] = '&pound;';
        entities['164'] = '&curren;';
        entities['165'] = '&yen;';
        entities['166'] = '&brvbar;';
        entities['167'] = '&sect;';
        entities['168'] = '&uml;';
        entities['169'] = '&copy;';
        entities['170'] = '&ordf;';
        entities['171'] = '&laquo;';
        entities['172'] = '&not;';
        entities['173'] = '&shy;';
        entities['174'] = '&reg;';
        entities['175'] = '&macr;';
        entities['176'] = '&deg;';
        entities['177'] = '&plusmn;';
        entities['178'] = '&sup2;';
        entities['179'] = '&sup3;';
        entities['180'] = '&acute;';
        entities['181'] = '&micro;';
        entities['182'] = '&para;';
        entities['183'] = '&middot;';
        entities['184'] = '&cedil;';
        entities['185'] = '&sup1;';
        entities['186'] = '&ordm;';
        entities['187'] = '&raquo;';
        entities['188'] = '&frac14;';
        entities['189'] = '&frac12;';
        entities['190'] = '&frac34;';
        entities['191'] = '&iquest;';
        entities['192'] = '&Agrave;';
        entities['193'] = '&Aacute;';
        entities['194'] = '&Acirc;';
        entities['195'] = '&Atilde;';
        entities['196'] = '&Auml;';
        entities['197'] = '&Aring;';
        entities['198'] = '&AElig;';
        entities['199'] = '&Ccedil;';
        entities['200'] = '&Egrave;';
        entities['201'] = '&Eacute;';
        entities['202'] = '&Ecirc;';
        entities['203'] = '&Euml;';
        entities['204'] = '&Igrave;';
        entities['205'] = '&Iacute;';
        entities['206'] = '&Icirc;';
        entities['207'] = '&Iuml;';
        entities['208'] = '&ETH;';
        entities['209'] = '&Ntilde;';
        entities['210'] = '&Ograve;';
        entities['211'] = '&Oacute;';
        entities['212'] = '&Ocirc;';
        entities['213'] = '&Otilde;';
        entities['214'] = '&Ouml;';
        entities['215'] = '&times;';
        entities['216'] = '&Oslash;';
        entities['217'] = '&Ugrave;';
        entities['218'] = '&Uacute;';
        entities['219'] = '&Ucirc;';
        entities['220'] = '&Uuml;';
        entities['221'] = '&Yacute;';
        entities['222'] = '&THORN;';
        entities['223'] = '&szlig;';
        entities['224'] = '&agrave;';
        entities['225'] = '&aacute;';
        entities['226'] = '&acirc;';
        entities['227'] = '&atilde;';
        entities['228'] = '&auml;';
        entities['229'] = '&aring;';
        entities['230'] = '&aelig;';
        entities['231'] = '&ccedil;';
        entities['232'] = '&egrave;';
        entities['233'] = '&eacute;';
        entities['234'] = '&ecirc;';
        entities['235'] = '&euml;';
        entities['236'] = '&igrave;';
        entities['237'] = '&iacute;';
        entities['238'] = '&icirc;';
        entities['239'] = '&iuml;';
        entities['240'] = '&eth;';
        entities['241'] = '&ntilde;';
        entities['242'] = '&ograve;';
        entities['243'] = '&oacute;';
        entities['244'] = '&ocirc;';
        entities['245'] = '&otilde;';
        entities['246'] = '&ouml;';
        entities['247'] = '&divide;';
        entities['248'] = '&oslash;';
        entities['249'] = '&ugrave;';
        entities['250'] = '&uacute;';
        entities['251'] = '&ucirc;';
        entities['252'] = '&uuml;';
        entities['253'] = '&yacute;';
        entities['254'] = '&thorn;';
        entities['255'] = '&yuml;';
    }

    if (useQuoteStyle !== 'ENT_NOQUOTES') {
        entities['34'] = '&quot;';
    }
    if (useQuoteStyle === 'ENT_QUOTES') {
        entities['39'] = '&#39;';
    }
    entities['60'] = '&lt;';
    entities['62'] = '&gt;';

    // ascii decimals to real symbols
    for (decimal in entities) {
        if (entities.hasOwnProperty(decimal)) {
            hash_map[String.fromCharCode(decimal)] = entities[decimal];
        }
    }

    return hash_map;
};