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
        M.qtypes.omerocommon.RoiShapeTableBase = function (table_id, table_container_id, table_container_toolbar_id) {

            // Registers a reference to the current scope
            var me = this;

            me._table_id = table_id;

            me._table_container_toolbar_id = table_container_toolbar_id || (table_id + "-toolbar");

            me._table_container_id = table_container_id || (table_id + "-container");

            me._event_listener_list = [];
        };


        M.qtypes.omerocommon.RoiShapeTableBase.responseHandler = function (res) {
            $.each(res.rows, function (i, row) {
                row.state = $.inArray(row.id, this.selections) !== -1;
            });
            return res;
        };

        M.qtypes.omerocommon.RoiShapeTableBase.detailFormatter = function (index, row) {
            var html = [];
            $.each(row, function (key, value) {
                html.push('<p><b>' + key + ':</b> ' + value + '</p>');
            });
            return html.join('');
        };


        var prototype = M.qtypes.omerocommon.RoiShapeTableBase.prototype;

        // table setup
        prototype.initTable = function (hideToolbar, showColumnSelector, hideFocusAreas) {

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
            me.table_element.attr("data-response-handler", "M.qtypes.omerocommon.RoiShapeTableBase.responseHandler");
            // Register the detailsFormatter
            me.table_element.attr("data-detail-formatter", "M.qtypes.omerocommon.RoiShapeTableBase.detailFormatter");

            var bootstrap_config = {
                height: "400",
                columns: [
                    [
                        {
                            field: 'state',
                            checkbox: true,
                            rowspan: 2,
                            align: 'center',
                            valign: 'middle'
                        },
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
                            title: M.util.get_string('roi_shape_details', 'qtype_omerocommon'),
                            colspan: 3,
                            align: 'center'
                        }
                    ],
                    [
                        {
                            field: 'description',
                            title: M.util.get_string('roi_description', 'qtype_omerocommon'),
                            //sortable: true,
                            align: 'left',
                            //editable: {
                            //    type: 'textarea',
                            //    title: 'ROI Shape Description',
                            //    width: '200px',
                            //    resize: 'none',
                            //    validate: function (value) {
                            //        value = $.trim(value);
                            //        if (!value) {
                            //            return 'This field is required';
                            //        }
                            //
                            //        var data = me.table_element.bootstrapTable('getData'),
                            //            index = $(this).parents('tr').data('index');
                            //        console.log(data[index]);
                            //        return '';
                            //    }
                            //},
                            formatter: me.descriptionFormatter
                        },
                        {
                            field: 'visible',
                            title: M.util.get_string('roi_visibility', 'qtype_omerocommon'),
                            width: "20px",
                            align: 'center',
                            valign: 'middle',
                            events: me.eventHandler(me),
                            formatter: me.visibilityFormatter
                        },
                        {
                            field: 'focusable',
                            title: M.util.get_string('roi_focus', 'qtype_omerocommon'),
                            width: "20px",
                            align: 'center',
                            valign: 'middle',
                            events: me.eventHandler(me),
                            formatter: me.focusAreaFormatter
                        }
                    ]
                ]
            };

            //if (!showColumnSelector)
            if (hideFocusAreas)
                bootstrap_config.columns[1].splice(2, 1);

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

            me.table_element.on('click-cell.bs.table', function (table, field, e, row, index) {
                console.log("Click on a table ROW", e, row, index);
                notifyListeners(me, {
                    type: "roiShapeFocus",
                    shape: row,
                    visible: row.visible
                });
            });

            me.table_element.on('check.bs.table uncheck.bs.table ' +
                'check-all.bs.table uncheck-all.bs.table', function () {
                me.remove_element.prop('disabled', !me.table_element.bootstrapTable('getSelections').length);

                // save your data, here just save the current page
                var selections = me.getIdSelections();

                notifyListeners(me, {
                    type: "roiShapesSelected",
                    shapes: selections
                });
            });
            me.table_element.on('expand-row.bs.table', function (e, index, row, $detail) {
                if (index % 2 == 1) {
                    $detail.html('Loading from ajax request...');
                    $.get('LICENSE', function (res) {
                        $detail.html(res.replace(/\n/g, '<br>'));
                    });
                }
            });
            me.table_element.on('all.bs.table', function (e, row, args) {
                console.log(row, args);
            });
            me.remove_element.click(function () {
                var ids = me.getIdSelections();
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


        prototype.getIdSelections = function () {
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

            // Reusable function to handle visibility change
            function onRoiShapeVisibilityChanged(target, value, row) {
                // if focus is active then visibility cannot be changed
                if (row.visible && row.focusable) return;

                row.visible = !row.visible;
                if (row.visible)
                    $(target).attr("class", "green glyphicon glyphicon-eye-open");
                else
                    $(target).attr("class", "#E9E9E9 glyphicon glyphicon-eye-close");

                notifyListeners(table, {
                    type: "roiShapeVisibilityChanged",
                    shape: row,
                    event: value,
                    visible: row.visible
                });
            }

            return {
                'click .like': function (e, value, row /*, index*/) {
                    if ($(e.target).attr("class").indexOf("glyphicon-plus-sign") !== -1)
                        $(e.target).attr("class", "green glyphicon glyphicon-eye-open");
                    else
                        $(e.target).attr("class", "red glyphicon glyphicon-eye-close");
                    console.log('You click like action, row: ' + JSON.stringify(row));
                },

                /**
                 * Handle the visibility change event !!!
                 *
                 * @param e
                 * @param value
                 * @param row
                 * @param index
                 */
                'click .roi-shape-visibility': function (e, value, row /*, index*/) {
                    onRoiShapeVisibilityChanged(e.target, value, row);
                },

                /**
                 * Handle the focus change event !!!
                 *
                 * @param e
                 * @param value
                 * @param row
                 * @param index
                 */
                'click .roi-shape-focusability': function (e, value, row /*, index*/) {
                    row.focusable = !row.focusable;
                    if (row.focusable)
                        $(e.target).attr("class", "green glyphicon glyphicon-record");
                    else
                        $(e.target).attr("class", "#E9E9E9 glyphicon glyphicon-record");

                    console.log("FOCUSability changed: " + row.focusable);
                    notifyListeners(table, {
                        type: "roiShapeFocusabilityChanged",
                        shape: row,
                        event: value,
                        focusable: row.focusable
                    });

                    onRoiShapeVisibilityChanged($(e.target).parents("tr").find(".roi-shape-visibility i")[0], value, row);
                },

                'click .remove': function (e, value, row /*, index*/) {
                    me.table_element.bootstrapTable('remove', {
                        field: 'id',
                        values: [row.id]
                    });
                },

                'change .answer-class': function (e, value, row, index) {
                    console.log(e, value, row, index);
                    console.log("Changed ROW: ", row);
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

        // prototype.getHeight = function () {
        //     return $(window).height() - $('h1').outerHeight(true);
        // };

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
        return M.qtypes.omerocommon.RoiShapeTableBase;
    }
);

