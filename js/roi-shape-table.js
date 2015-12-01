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
        return "type/omeromultichoice/tests/data.json";
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
            height: "500",
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
                        footerFormatter: me.totalTextFormatter
                    },
                    {
                        title: 'ROI Shape Details',
                        colspan: 3,
                        align: 'center'
                    }
                ],
                [
                    {
                        field: 'description',
                        title: 'Description',
                        //sortable: true,
                        align: 'left',
                        editable: {
                            type: 'textarea',
                            title: 'ROI Shape Description',
                            width: '200px',
                            resize: 'none',
                            validate: function (value) {
                                value = $.trim(value);
                                if (!value) {
                                    return 'This field is required';
                                }

                                var data = me.table_container.bootstrapTable('getData'),
                                    index = $(this).parents('tr').data('index');
                                console.log(data[index]);
                                return '';
                            }
                        },
                        footerFormatter: me.descriptionFormatter
                    },
                    {
                        field: 'visible',
                        title: 'Visibility',
                        width: "20px",
                        align: 'center',
                        valign: 'middle',
                        events: me.eventHandler,
                        formatter: me.visibilityFormatter
                    },
                    {
                        field: 'answerGroup',
                        title: 'Group',
                        align: 'center',
                        valign: 'middle',
                        width: "40px",
                        events: me.eventHandler,
                        formatter: me.answerClassFormatter
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
            selections = me.getIdSelections();
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
            var ids = me.getIdSelections();
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


    me.answerClassFormatter = function (value, row, index) {
        return [
            '<select class="answer-class form-control">',
            '<option>1</option>',
            '<option>2</option>',
            '<option>3</option>',
            '<option>4</option>',
            '</select>'
        ].join('');
    };

    me.eventHandler = {
        'click .like': function (e, value, row, index) {
            if ($(e.target).attr("class").indexOf("glyphicon-plus-sign") !== -1)
                $(e.target).attr("class", "red glyphicon glyphicon-minus-sign");
            else
                $(e.target).attr("class", "green glyphicon glyphicon-plus-sign");
            alert('You click like action, row: ' + JSON.stringify(row));
        },

        /**
         * Handle the visibility change event !!!
         *
         * @param e
         * @param value
         * @param row
         * @param index
         */
        'click .roi-shape-visibility': function (e, value, row, index) {
            row.visible = !row.visible;
            if (row.visible)
                $(e.target).attr("class", "red glyphicon glyphicon-minus-sign");
            else
                $(e.target).attr("class", "green glyphicon glyphicon-plus-sign");
        },

        'click .remove': function (e, value, row, index) {
            me.table_container.bootstrapTable('remove', {
                field: 'id',
                values: [row.id]
            });
        },

        'change .answer-class': function (e, value, row, index) {
            console.log(e, value, row, index);
            console.log("ROW: ", row);

            alert("Changed!!!");

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

    me.descriptionFormatter = function (data) {
        return data;
    };

    me.visibilityFormatter = function (data) {
        return [
            '<a class="roi-shape-visibility" href="javascript:void(0)" title="Like">',
            (data ?
                '<i class="red glyphicon glyphicon-minus-sign"></i>' :
                '<i class="green glyphicon glyphicon-plus-sign"></i>'),
            '</a> '
        ].join(" ");
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


