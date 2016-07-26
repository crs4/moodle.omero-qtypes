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
                height: "400",
                columns: [
                    [
                        {
                            title: 'ID',
                            field: 'id',
                            rowspan: 2,
                            align: 'center',
                            valign: 'middle',
                            width: '30px',
                            sortable: true,
                            formatter: me.idFormatter
                        },
                        {
                            field: 'description',
                            title: M.util.get_string('roi_description', 'qtype_omerocommon'),
                            rowspan: 2,
                            align: 'left',
                            valign: 'middle',
                            formatter: me.descriptionFormatter
                        },
                        {
                            field: 'visiblerois',
                            title: M.util.get_string('roi_description', 'qtype_omerocommon'),
                            //sortable: true,
                            rowspan: 2,
                            align: 'center',
                            valign: 'middle',
                            formatter: me.descriptionFormatter
                        },
                        {
                            title: "Actions",
                            colspan: 2,
                            align: 'center'
                        }
                    ],
                    [
                        {
                            //field: 'visible',
                            title: M.util.get_string('edit', 'core'),
                            width: "20px",
                            align: 'center',
                            valign: 'middle',
                            events: me.eventHandler(me),
                            formatter: me.editActionFormatter
                        },
                        {
                            //field: 'focusable',
                            title: M.util.get_string('delete', 'core'),
                            width: "20px",
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


            me.table_element.on('click-cell.bs.table', function (table, field, e, row, index) {
                // console.log("Click on a table ROW", e, row, index);
                // notifyListeners(me, {
                //     type: "roiShapeFocus",
                //     shape: row,
                //     visible: row.visible
                // });
            });

            me.table_element.on('check.bs.table uncheck.bs.table ' +
                'check-all.bs.table uncheck-all.bs.table', function () {
                // me.remove_element.prop('disabled', !me.table_element.bootstrapTable('getSelections').length);
                //
                // // save your data, here just save the current page
                // var selections = me.getSelectionIDs();
                //
                // notifyListeners(me, {
                //     type: "imageSelected",
                //     images: selections
                // });
            });
            // me.table_element.on('expand-row.bs.table', function (e, index, row, $detail) {
            //     if (index % 2 == 1) {
            //         $detail.html('Loading from ajax request...');
            //         $.get('LICENSE', function (res) {
            //             $detail.html(res.replace(/\n/g, '<br>'));
            //         });
            //     }
            // });
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
            //$(window).resize(function () {
            //    me.table_element.bootstrapTable('resetView', {
            //        height: me.getHeight()
            //    });
            //});

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
                        type: "deleteImage",
                        image: row,
                        event: value
                    });
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
            console.log(this.table_container_element);
            alert("TableContainerElement....");
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

        prototype.totalNameFormatter = function (data) {
            return data.length;
        };

        prototype.totalNameFormatter = function (data) {
            return data.length;
        };

        prototype.descriptionFormatter = function (data) {
            return data || " ";
        };

        prototype.visibilityFormatter = function (data) {
            return [
                '<a class="roi-shape-visibility" href="javascript:void(0)" title="Visibility">',
                (data ?
                    '<i class="green glyphicon glyphicon-eye-open"></i>' :
                    '<i class="#E9E9E9 glyphicon glyphicon-eye-close"></i>'),
                '</a> '
            ].join(" ");
        };

        prototype.focusAreaFormatter = function (data) {
            return [
                '<a class="roi-shape-focusability" href="javascript:void(0)" title="Focusability">',
                (data ?
                    '<i class="green glyphicon glyphicon-record"></i>' :
                    '<i class="#E9E9E9 glyphicon glyphicon-record"></i>'),
                '</a> '
            ].join(" ");
        };

        prototype.answerClassFormatter = function (/*value, row, index*/) {
            return [
                '<select class="answer-class form-control">',
                '<option>1</option>',
                '<option>2</option>',
                '<option>3</option>',
                '<option>4</option>',
                '</select>'
            ].join('');
        };

        prototype.getHeight = function () {
            return $(window).height() - $('h1').outerHeight(true);
        };

        prototype.getBootstrapTable = function () {
            return this.table_element.bootstrapTable;
        };


        prototype.appendRoiShapeList = function (data) {
            return this.table_element.bootstrapTable('append', data);
        };

        prototype.setRoiShapeList = function (data) {
            this.removeAll();
            this.appendRoiShapeList(data);
        };

        prototype.getRoiShape = function (roi_shape_id) {
            return this.table_element.bootstrapTable('getRowByUniqueId', roi_shape_id);
        };

        prototype.getRoiShapeList = function () {
            return this.table_element.bootstrapTable('getData');
        };

        prototype.removeRoiShape = function (roi_shapes) {
            roi_shapes = (roi_shapes instanceof Array) ? roi_shapes : [roi_shapes];
            return this.table_element.bootstrapTable('remove', {field: 'id', values: roi_shapes});
        };

        prototype.removeAll = function () {
            return this.table_element.bootstrapTable('removeAll');
        };

        // returns the class
        return M.qtypes.omerocommon.FeedbackImageTable;
    }
);

