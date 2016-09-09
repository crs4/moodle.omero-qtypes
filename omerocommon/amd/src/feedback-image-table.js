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
 * UI controller of the ROI shape table inspector.
 *
 * @package    qtype
 * @subpackage omerocommon
 * @copyright  2015-2016 CRS4
 * @license    https://opensource.org/licenses/mit-license.php MIT license
 */


define(['jquery', 'qtype_omerocommon/roi-shape-model'],

    /* jshint curly: false */
    /* globals console, jQuery */
    function ($ /*, RoiShapeModel*/) {

        // override jQuery
        $ = jQuery;

        // defines the basic package
        M.qtypes = M.qtypes || {};

        // defines the specific package of this module
        M.qtypes.omerocommon = M.qtypes.omerocommon || {};

        /**
         * utility function to notify listeners
         *
         * @param table
         * @param event
         */
        function notifyListeners(table, event) {
            for (var i in table._event_listener_list) {
                var callback_name = "on" + event.type.charAt(0).toUpperCase() + event.type.slice(1);
                console.log("Listener", i, table._event_listener_list[i], callback_name);
                var callback = table._event_listener_list[i][callback_name];
                if (callback) {
                    console.log("Calling ", callback);
                    callback.call(table._event_listener_list[i], event);
                }
            }
        }

        // constructor
        M.qtypes.omerocommon.FeedbackImageTable = function (table_id, table_container_id, table_container_toolbar_id) {

            // Registers a reference to the current scope
            var me = this;

            me._table_id = table_id;

            me._table_container_toolbar_id = table_container_toolbar_id || (table_id + "-toolbar");

            me._table_container_id = table_container_id || (table_id + "-container");

            me._event_listener_list = [];
        };


        M.qtypes.omerocommon.FeedbackImageTable.responseHandler = function (res) {
            $.each(res.rows, function (i, row) {
                row.state = $.inArray(row.id, this.selections) !== -1;
            });
            return res;
        };

        M.qtypes.omerocommon.FeedbackImageTable.detailFormatter = function (index, row) {
            var html = [];
            $.each(row, function (key, value) {
                html.push('<p><b>' + key + ':</b> ' + value + '</p>');
            });
            return html.join('');
        };


        var prototype = M.qtypes.omerocommon.FeedbackImageTable.prototype;

        /**
         *
         * @returns {string}
         */
        prototype.drawHtmlTable = function () {
            var html = '' +
                '<div id="' + this._table_container_id + '">' +
                '<table data-toggle="table" width="100%" id="' + this._table_id + '">' +
                '</table>' +
                '</div>';
            return html;
        };


        // table setup
        prototype.initTable = function (hideToolbar, showColumnSelector) {

            var me = this;

            console.log("showColumnSelector: " + showColumnSelector);

            // Registers a reference to the table container
            me.table_element = $("#" + me._table_id);
            me.remove_element = $('#remove');
            me.table_container_element = $("#" + me._table_container_id);
            me.table_toolbar_container_element = $("#" + me._table_container_toolbar_id);

            // Sets the endpoint to get the ROI infos
            //me.table_container.attr("data-url", _getRoiShapeDetailInfoUrl());

            if (!hideToolbar) me.showToolbar();
            else me.hideToolbar();

            // Setup the responseHandler
            //me.table_element.attr("data-response-handler", "M.qtypes.omerocommon.FeedbackImageTable.responseHandler");
            // Register the detailsFormatter
            //me.table_element.attr("data-detail-formatter", "M.qtypes.omerocommon.FeedbackImageTable.detailFormatter");

            var bootstrap_config = {
                height: "160",
                columns: [
                    [
                        {
                            title: 'ID',
                            field: 'id',
                            align: 'center',
                            valign: 'middle',
                            width: '30px',
                            sortable: true,
                            formatter: me.idFormatter
                        },
                        {
                            field: 'name',
                            title: M.util.get_string('feedbackimagename', 'qtype_omerocommon'),
                            align: 'center',
                            valign: 'middle',
                            formatter: me.nameFormatter,
                            editable: {
                                type: 'textarea',
                                title: M.util.get_string('feedbackimagename', 'qtype_omerocommon'),
                                width: '200px',
                                resize: 'none',
                                validate: function (value) {
                                    value = $.trim(value);
                                    if (!value) {
                                        return M.util.get_string('validate_field_required', 'qtype_omerocommon');
                                    }
                                    me.table_element.bootstrapTable('resetView');
                                    var data = me.table_element.bootstrapTable('getData'),
                                        index = $(this).parents('tr').data('index');
                                    console.log("The updated DATA....", data[index]);
                                    return '';
                                }
                            }
                        },
                        {
                            field: 'visiblerois',
                            title: M.util.get_string('roi_visible', 'qtype_omerocommon'),
                            align: 'center',
                            valign: 'middle',
                            formatter: me.visibleRoisFormatter
                        },
                        {
                            field: 'focusablerois',
                            title: M.util.get_string('roi_focusable', 'qtype_omerocommon'),
                            align: 'center',
                            valign: 'middle',
                            formatter: me.focusableRoisFormatter
                        },
                        {
                            title: M.util.get_string('edit', 'core'),
                            width: "8%",
                            align: 'center',
                            valign: 'middle',
                            events: me.eventHandler(me),
                            formatter: me.editActionFormatter
                        },
                        {
                            title: M.util.get_string('delete', 'core'),
                            width: "8%",
                            align: 'center',
                            valign: 'middle',
                            events: me.eventHandler(me),
                            formatter: me.deleteActionFormatter
                        }
                    ]
                ]
            };

            // Initializes the bootstrap table
            me.table_element.bootstrapTable(bootstrap_config);
            // sometimes footer render error.
            setTimeout(function () {
                me.table_element.bootstrapTable('resetView');
            }, 200);

            // fix table height to fit its content
            me.table_element.on('reset-view.bs.table', function (event) {
                $(event.target).closest(".fixed-table-container").css("height", "auto");
                $(event.target).closest(".fixed-table-container").css("padding-bottom", "0");
            });

            me.table_element.on('all.bs.table', function (e, row, args) {
                console.log(row, args);
            });

            me.remove_element.click(function () {
                var ids = me.getSelectionIDs();
                me.table_element.bootstrapTable('remove', {
                    field: 'id',
                    values: ids
                });
                me.remove_element.prop('disabled', true);
            });

            // Adapt the table to window width
            $(window).resize(function () {
                me.table_element.bootstrapTable('resetWidth');
            });

            me._initialized = true;
        };


        prototype.getSelectionIDs = function () {
            return $.map(this.table_element.bootstrapTable('getSelections'), function (row) {
                return row.id;
            });
        };

        prototype.selectAll = function () {
            this.table_element.bootstrapTable('checkAll');
        };

        prototype.deselectAll = function () {
            this.table_element.bootstrapTable('uncheckAll');
        };


        /**
         * Build an event handler
         *
         * @param table
         * @returns {{[click .like]: 'click .like',
         *           [click .roi-shape-visibility]: 'click .roi-shape-visibility',
         *           [click .remove]: 'click .remove',
         *           [change .answer-class]: 'change .answer-class'}}
         */

        prototype.eventHandler = function (table) {
            var me = this;
            return {

                /**
                 * Handle the visibility change event !!!
                 *
                 * @param e
                 * @param value
                 * @param row
                 * @param index
                 */
                'click .edit-image-action': function (e, value, row /*, index*/) {
                    console.log("Editing image: " + row.id);
                    notifyListeners(table, {
                        type: "editImage",
                        image: row,
                        event: value
                    });
                },

                /**
                 * Handle the focus change event !!!
                 *
                 * @param e
                 * @param value
                 * @param row
                 * @param index
                 */
                'click .delete-image-action': function (e, value, row /*, index*/) {
                    console.log("Deleting image: " + row.id);
                    notifyListeners(table, {
                        type: "deletedImage",
                        image: row,
                        event: value
                    });
                    me.remove(row.id);
                }
            };
        };


        prototype.addEventListener = function (listener) {
            if (listener) {
                this._event_listener_list.push(listener);
            }
        };

        prototype.removeEventListener = function (listener) {
            if (listener) {
                var index = this._event_listener_list.indexOf(listener);
                if (index > -1) {
                    this._event_listener_list.splice(index, 1);
                }
            }
        };


        prototype.show = function () {
            this.table_container_element.removeClass("hidden");
        };

        prototype.hide = function () {
            this.table_container_element.addClass("hidden");
        };


        prototype.showToolbar = function () {
            this.table_toolbar_container_element.removeClass("hidden");
        };

        prototype.hideToolbar = function () {
            this.table_toolbar_container_element.addClass("hidden");
        };


        prototype.idFormatter = function (data) {
            return [
                '<span class="highlight-roi">', data, '</span>'
            ].join(" ");
        };

        prototype.nameFormatter = function (data) {
            return (data) || " ";
        };

        prototype.focusableRoisFormatter = function (data) {
            return data.join(", ") || " ";
        };

        prototype.visibleRoisFormatter = function (data) {
            return data.join(", ") || " ";
        };

        prototype.editActionFormatter = function (/*data*/) {
            return [
                '<a class="edit-image-action" href="javascript:void(0)" title="Edit">',
                '<i class="glyphicon glyphicon-edit" style="color: orange"></i>',
                '</a> '
            ].join(" ");
        };

        prototype.deleteActionFormatter = function (/*data*/) {
            return [
                '<a class="delete-image-action" href="javascript:void(0)" title="Delete">',
                '<i class="glyphicon glyphicon-remove-circle" style="color: red"></i>',
                '</a> '
            ].join(" ");
        };


        // UTILITY METHODS
        prototype.getHeight = function () {
            return $(window).height() - $('h1').outerHeight(true);
        };

        prototype.getBootstrapTable = function () {
            return this.table_element.bootstrapTable;
        };


        // DATA MANAGEMENT Methods
        prototype.setData = function (data) {
            this.removeAll();
            this.append(data);
        };

        prototype.updateRow = function (data) {
            this.table_element.bootstrapTable('updateRow', {index: data.id, row: data});
        };

        prototype.append = function (data) {
            return this.table_element.bootstrapTable('append', data);
        };

        prototype.getImageInfo = function (image_id) {
            return this.table_element.bootstrapTable('getRowByUniqueId', image_id);
        };

        prototype.getData = function () {
            return this.table_element.bootstrapTable('getData');
        };

        prototype.remove = function (image) {
            var image_id = (image instanceof Object) ? image.id : image;
            this.table_element.bootstrapTable('remove', {field: 'id', values: [image_id]});
        };

        prototype.removeAll = function () {
            return this.table_element.bootstrapTable('removeAll');
        };

        // returns the class
        return M.qtypes.omerocommon.FeedbackImageTable;
    }
);

