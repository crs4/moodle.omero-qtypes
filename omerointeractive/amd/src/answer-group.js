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
 * UI Controller of the interactive question type.
 *
 * @package    qtype
 * @subpackage omerointeractive
 * @copyright  2015-2016 CRS4
 * @license    https://opensource.org/licenses/mit-license.php MIT license
 */
define([
        'jquery',
        'qtype_omerocommon/moodle-forms-utils',
        'qtype_omerocommon/answer-base',
        'qtype_omerocommon/multilanguage-attoeditor'
    ],
    /* jshint curly: false */
    /* globals console, jQuery */
    function (/*j, FormUtils, AnswerBase, Editor*/) {

        // jQuery reference
        var $ = jQuery;

        // defines the basic package
        M.qtypes = M.qtypes || {};

        // defines the specific package of this module
        M.qtypes.omerointeractive = M.qtypes.omerointeractive || {};


        /**
         * Defines MoodleFormUtils class
         * @type {{}}
         */
        M.qtypes.omerointeractive.AnswerGroup = function (answer_list_container_id, answer_number, fraction_options) {

            // the reference to this scope
            var me = this;

            // Call the parent constructor
            M.qtypes.omerocommon.AnswerBase.call(this, answer_list_container_id, answer_number, fraction_options);

            // init the list of ROI associated to this answer
            me._roi_id_list = [];
            me._init_roi_list();
            me._initContextMenu();
        };


        // inherit
        M.qtypes.omerointeractive.AnswerGroup.prototype = new M.qtypes.omerocommon.AnswerBase();

        // correct the constructor
        M.qtypes.omerointeractive.AnswerGroup.prototype.constructor = M.qtypes.omerointeractive.AnswerGroup;

        M.qtypes.omerointeractive.AnswerGroup.prototype.parent = M.qtypes.omerocommon.AnswerBase.prototype;


        var prototype = M.qtypes.omerointeractive.AnswerGroup.prototype;

        /**
         * Builds the answer
         *
         * @private
         */
        prototype._build = function () {
            var me = this;

            var panel = $('<div class="panel panel-default"></div>');
            me._answer_list_container.append(panel);

            var panel_heading = $('<div class="panel-heading">' +
                '<h4 class="panel-title">' +
                '<span id="head-answer-' + this._answer_number + '">Answer ' + (this._answer_number + 1) + '</span>' +
                '<div style="display: inline-block; float: right; text-align: right;">' +
                '<a href="javascript:void(0)" id="delete-answer-' + me._answer_number + '" >' +
                '<i class="red glyphicon glyphicon-remove-sign" style="font-size: 2m;"></i></a>' +
                '</h4>' +
                '</div>' +
                '<div style="display: block; float: right; margin: 20px 30px;">' +
                '</div>');
            panel.append(panel_heading);

            var panel_body = $('<div class="panel-body"></div>');
            panel.append(panel_body);

            me._answer_container = $('<div class="fitem" id="' + me._answer_number + '"></div>');
            panel_body.append(me._answer_container);

            // roi id list
            me._build_list_of_rois("answer", M.util.get_string("answer_group_of_rois", "qtype_omerointeractive"));

            // answer grade
            me._build_select_of("fraction", M.util.get_string("answer_grade", "qtype_omerocommon"));

            // answer feedback
            me._build_textarea_of("feedback", M.util.get_string("feedback", "question"));

            // answer format
            me._build_hidden_of("feedbackformat", "1");

            // answer feedback images
            me._build_feedback_image_selector("feedback_image_selector");

            // reference to the head
            me._answer_head = $('#head-answer-' + this._answer_number);

            // register the delete event
            $("#delete-answer-" + me._answer_number).on("click", function () {
                me.remove();
            });

            // registers the panel as main container
            me._answer_container = panel;
        };

        prototype.enableEditingControls = function (enable) {
            this._inputs.fraction.setAttribute("disabled", enable);
            this.enableRoiListContextMenu(enable);
            document.getElementById(this._inputs.feedback_image_selector.button_id)
                .style.visibility = enable ? "visible" : "hidden";
            $("#delete-answer-" + this._answer_number).css("visibility", enable ? "visible" : "hidden");
        };

        prototype.addROIsToGroup = function (roi_id_list) {
            var me = this;
            var changed = false;
            var list = this._roi_id_list;
            for (var i in roi_id_list) {
                if (list.indexOf(roi_id_list[i]) === -1) {
                    list.push(roi_id_list[i]);
                    changed = true;
                }
            }
            if (changed) {
                this.updateROIList();
                this._notifyListeners({name: "AnswerROIsAdded", answer: me, roi_id_list: roi_id_list});
            }
        };

        prototype.removeROIsFromGroup = function (roi_id_list) {
            var me = this;
            var changed = false;
            var list = this._roi_id_list;
            for (var i in roi_id_list) {
                var index = list.indexOf(roi_id_list[i]);
                if (index !== -1) {
                    list.splice(index, 1);
                    changed = true;
                }
            }
            if (changed) {
                this.updateROIList();
                this._notifyListeners({name: "AnswerROIsRemoved", answer: me, roi_id_list: roi_id_list});
            }
        };

        prototype.containsROIs = function (roi_id_list) {
            for (var i in roi_id_list)
                if (this._roi_id_list.indexOf(roi_id_list[i]) !== -1)
                    return true;
            return false;
        };

        prototype.getROIsWithinGroup = function () {
            return this._roi_id_list;
        };


        prototype._init_roi_list = function () {
            console.log("Answer", this._data.answer, this._data.answer);
            var list = this._data.answer;//;$("[name*=answer\\[" + this._answer_number + "\\]]");
            if (list && list.length > 0)
                this._roi_id_list = list.split(",").map(function (e) {
                    return parseInt(e);
                });
        };


        prototype._build_list_of_rois = function (element_name, label) {

            var id = this._build_id_of(element_name);
            var name = this._build_name_of(element_name);

            this._init_roi_list();

            var select = '<div ' + 'id="' + id + '_roi_list" ' + 'name="' + name + '_answer_roi_list"></div>';
            var roi_list = $("[name*=" + element_name + "\\[" + this._answer_number + "\\]]");
            if (roi_list.length === 0)
                select += '<input type="hidden" name="' + name + '" value="">';

            var fraction_selector = $(select);
            this._form_utils.appendElement(this._answer_container, label, fraction_selector, false);

            if (this._roi_id_list) {
                this.updateROIList();
            }
        };


        prototype.updateROIList = function () {
            var list = [];
            var id = this._build_id_of("answer");
            var el_list = document.getElementById(id + '_roi_list');
            for (var i in this._roi_id_list) {
                list.push(['<span id="' + this._roi_id_list[i] + '-roi-shape-answer-option">',
                    '<i class="green glyphicon glyphicon-map-marker"></i> ',
                    this._roi_id_list[i],
                    "</span>"].join(""));
            }
            el_list.innerHTML = (list.join(", "));
            $("[name*=answer\\[" + this._answer_number + "\\]]").val(this._roi_id_list.join(","));
            this._data.answer = this._roi_id_list.join(",");
            this.enableRoiListContextMenu();
        };


        prototype.enableRoiListContextMenu = function (enable) {
            var me = this;

            var onMenuSelected = function (invokedOn, selectedMenu) {
                if (selectedMenu.attr("id") === "roishape-answer-option-delete") {
                    var matches = invokedOn.prop('id').match(/(.+)-roi-shape-answer-option/);
                    var roi_shape_id = matches[1];
                    if (!roi_shape_id) {
                        console.warn("Unable to identify the ROI id!!!");
                        return;
                    }
                    me.removeROIsFromGroup([parseInt(roi_shape_id)]);
                }
            };

            for (var i in this._roi_id_list) {
                var selector = "#" + this._roi_id_list[i] + "-roi-shape-answer-option";
                if (enable) {
                    $(selector).contextMenu({
                        menuSelector: "#roishape-answer-option-ctx-menu",
                        menuSelected: onMenuSelected
                    });
                } else $(selector).contextMenu(false);
            }
        };


        prototype._initContextMenu = function () {

            function getMenuPosition(settings, mouse, direction, scrollDir) {
                var win = $(window)[direction](),
                    scroll = $(window)[scrollDir](),
                    menu = $(settings.menuSelector)[direction](),
                    position = mouse + scroll;

                // opening menu would pass the side of the page
                if (mouse + menu > win && menu < mouse)
                    position -= menu;

                return position;
            }


            (function ($) {

                $.fn.contextMenu = function (settings) {

                    return this.each(function () {

                        // Open context menu
                        $(this).on("contextmenu", function (e) {
                            // return native menu if pressing control
                            if (e.ctrlKey) return;

                            //open menu
                            var $menu = $(settings.menuSelector)
                                .data("invokedOn", $(e.target))
                                .show()
                                .css({
                                    position: "absolute",
                                    left: getMenuPosition(settings, e.clientX, 'width', 'scrollLeft'),
                                    top: getMenuPosition(settings, e.clientY, 'height', 'scrollTop')
                                })
                                .off('click')
                                .on('click', 'a', function (e) {
                                    $menu.hide();
                                    var $invokedOn = $menu.data("invokedOn");
                                    var $selectedMenu = $(e.target);
                                    $invokedOn = $invokedOn.prop("tagName") == "I"
                                        ? $invokedOn.parent() : $invokedOn;
                                    settings.menuSelected.call(this, $invokedOn, $selectedMenu);
                                });

                            return false;
                        });

                        //make sure menu closes on any click
                        $(document).click(function () {
                            $(settings.menuSelector).hide();
                        });
                    });
                };
            })(jQuery, window);
        };

        // returns the class
        return M.qtypes.omerointeractive.AnswerGroup;
    }
);