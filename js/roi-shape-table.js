/**
 * Created by kikkomep on 11/29/15.
 */

function RoiShapeTableController(image_id) {

    // Registers a reference to the current scope
    var me = this;

    //
    me.selections = [];

    // Builds the URL to retrieve ROI data
    me.getRoiShapeDetailInfoUrl = function () {
        return "type/omeromultichoice/data.json";
    };

    // table setup
    me.initTable = function (container_id) {

        // Registers a reference to the table container
        me.table_container = $("#" + container_id);
        me.remove_container = $('#remove');

        // Sets the endpoint to get the ROI infos
        me.table_container.attr("data-url", me.getRoiShapeDetailInfoUrl());


        // Setup the responseHandler
        me.table_container.attr("data-response-handler", me.getReferenceName() + ".responseHandler");
        // Register the detailsFormatter
        me.table_container.attr("data-detail-formatter", me.getReferenceName() + ".detailFormatter");

        // Initializes the bootstrap table
        me.table_container.bootstrapTable({
            height: "400",
            columns: [
                [
                    {
                        field: 'state',
                        checkbox: true,
                        rowspan: 2,
                        align: 'center',
                        valign: 'middle'
                    }, {
                    title: 'Item ID',
                    field: 'id',
                    rowspan: 2,
                    align: 'center',
                    valign: 'middle',
                    sortable: true,
                    footerFormatter: me.totalTextFormatter
                }, {
                    title: 'Item Detail',
                    colspan: 4,
                    align: 'center'
                }
                ],
                [
                    {
                        field: 'name',
                        title: 'Item Name',
                        sortable: true,
                        editable: true,
                        footerFormatter: me.totalNameFormatter,
                        align: 'center'
                    }, {
                    field: 'price',
                    title: 'Item Price',
                    sortable: true,
                    align: 'center',
                    editable: {
                        type: 'text',
                        title: 'Item Price',
                        validate: function (value) {
                            value = $.trim(value);
                            if (!value) {
                                return 'This field is required';
                            }
                            if (!/^$/.test(value)) {
                                return 'This field needs to start width $.'
                            }
                            var data = me.table_container.bootstrapTable('getData'),
                                index = $(this).parents('tr').data('index');
                            console.log(data[index]);
                            return '';
                        }
                    },
                    footerFormatter: me.totalPriceFormatter
                },
                    {
                        field: 'visibility',
                        title: 'Item Visibility',
                        align: 'center',
                        events: me.operateEvents,
                        formatter: function () {
                            return [
                                '<a class="like" href="javascript:void(0)" title="Like">',
                                '<i class="glyphicon glyphicon-plus-sign"></i>',
                                '</a>  '
                            ].join(" ");
                        }
                    },


                    {
                        field: 'operate',
                        title: 'Item Operate',
                        align: 'center',
                        events: me.operateEvents,
                        formatter: me.operateFormatter
                    }
                ]
            ]
        });
        // sometimes footer render error.
        setTimeout(function () {
            me.table_container.bootstrapTable('resetView');
        }, 200);

        me.table_container.on('check.bs.table uncheck.bs.table ' +
            'check-all.bs.table uncheck-all.bs.table', function () {
            me.remove_container.prop('disabled', !me.table_container.bootstrapTable('getSelections').length);

            // save your data, here just save the current page
            selections = getIdSelections();
            // push or splice the selections if you want to save all data selections
        });
        me.table_container.on('expand-row.bs.table', function (e, index, row, $detail) {
            if (index % 2 == 1) {
                $detail.html('Loading from ajax request...');
                $.get('LICENSE', function (res) {
                    $detail.html(res.replace(/\n/g, '<br>'));
                });
            }
        });
        me.table_container.on('all.bs.table', function (e, name, args) {
            console.log(name, args);
        });
        me.remove_container.click(function () {
            var ids = getIdSelections();
            me.table_container.bootstrapTable('remove', {
                field: 'id',
                values: ids
            });
            me.remove_container.prop('disabled', true);
        });
        $(window).resize(function () {
            me.table_container.bootstrapTable('resetView', {
                height: me.getHeight()
            });
        });
    };


    me.getIdSelections = function () {
        return $.map(me.table_container.bootstrapTable('getSelections'), function (row) {
            return row.id
        });
    };

    me.responseHandler = function (res) {
        $.each(res.rows, function (i, row) {
            row.state = $.inArray(row.id, me.selections) !== -1;
        });
        return res;
    };

    me.detailFormatter = function (index, row) {
        var html = [];
        $.each(row, function (key, value) {
            html.push('<p><b>' + key + ':</b> ' + value + '</p>');
        });
        return html.join('');
    };

    me.operateFormatter = function (value, row, index) {
        return [
            '<a class="like" href="javascript:void(0)" title="Like">',
            '<i class="glyphicon glyphicon-heart"></i>',
            '</a>  ',
            '<a class="remove" href="javascript:void(0)" title="Remove">',
            '<i class="glyphicon glyphicon-remove"></i>',
            '</a>'
        ].join('');
    };

    me.operateEvents = {
        'click .like': function (e, value, row, index) {
            console.log(e);
            if ($(e.target).attr("class").indexOf("glyphicon-plus-sign") !== -1)
                $(e.target).attr("class", "red glyphicon glyphicon-minus-sign");
            else
                $(e.target).attr("class", "green glyphicon glyphicon-plus-sign");
            alert('You click like action, row: ' + JSON.stringify(row));
        },
        'click .remove': function (e, value, row, index) {
            me.table_container.bootstrapTable('remove', {
                field: 'id',
                values: [row.id]
            });
        }
    };

    me.totalTextFormatter = function (data) {
        return 'Total';
    };

    me.totalNameFormatter = function (data) {
        return data.length;
    };

    me.totalNameFormatter = function (data) {
        return data.length;
    };

    me.totalPriceFormatter = function (data) {
        var total = 0;
        $.each(data, function (i, row) {
            total += +(row.price.substring(1));
        });
        return '$' + total;
    };

    me.getHeight = function () {
        return $(window).height() - $('h1').outerHeight(true);
    };


    me.getReferenceName = function () {
        if (!me._name) {
            for (var i in window) {
                if (window[i] === me) {
                    me._name = i;
                    break;
                }
            }
        }
        return me._name;
    }
}


