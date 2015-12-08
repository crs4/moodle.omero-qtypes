/**
 * Created by kikkomep on 11/29/15.
 */
// defines the basic package
M.qtypes = M.qtypes || {};

// defines the specific package of this module
M.qtypes.omerocommon = M.qtypes.omerocommon || {};


/**
 *
 * @returns {string}
 * @private
 */
function _getRoiShapeDetailInfoUrl() {
    return "type/omeromultichoice/tests/data.json";
}


// constructor
M.qtypes.omerocommon.RoiShapeTableBase = function (container_id) {

    // Registers a reference to the current scope
    var me = this;

    //
    me.selections = [];

    me._container_id = editor_container_id;
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
prototype.initTable = function () {

    var me = this;

    // Registers a reference to the table container
    me.table_container = $("#" + me._container_id);
    me.remove_container = $('#remove');

    // Sets the endpoint to get the ROI infos
    //me.table_container.attr("data-url", _getRoiShapeDetailInfoUrl());


    // Setup the responseHandler
    me.table_container.attr("data-response-handler", "M.qtypes.omerocommon.RoiShapeTableBase.responseHandler");
    // Register the detailsFormatter
    me.table_container.attr("data-detail-formatter", "M.qtypes.omerocommon.RoiShapeTableBase.detailFormatter");

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

    me._initialized = true;
};


prototype.getIdSelections = function () {
    return $.map(this.table_container.bootstrapTable('getSelections'), function (row) {
        return row.id
    });
};


prototype.answerClassFormatter = function (value, row, index) {
    return [
        '<select class="answer-class form-control">',
        '<option>1</option>',
        '<option>2</option>',
        '<option>3</option>',
        '<option>4</option>',
        '</select>'
    ].join('');
};

prototype.eventHandler = {
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


prototype.totalTextFormatter = function (data) {
    return 'Total';
};

prototype.totalNameFormatter = function (data) {
    return data.length;
};

prototype.totalNameFormatter = function (data) {
    return data.length;
};

prototype.descriptionFormatter = function (data) {
    return data;
};

prototype.visibilityFormatter = function (data) {
    return [
        '<a class="roi-shape-visibility" href="javascript:void(0)" title="Like">',
        (data ?
            '<i class="red glyphicon glyphicon-minus-sign"></i>' :
            '<i class="green glyphicon glyphicon-plus-sign"></i>'),
        '</a> '
    ].join(" ");
};


prototype.getHeight = function () {
    return $(window).height() - $('h1').outerHeight(true);
};

prototype.getBootstrapTable = function () {
    return this.table_container.bootstrapTable;
};


prototype.appendRoiShapeList = function (data) {
    return this.table_container.bootstrapTable('append', data);
};

prototype.setRoiShapeList = function (data) {
    this.removeAll();
    this.appendRoiShapeList(data);
};


prototype.getRoiShape = function (roi_shape_id) {
    return this.table_container.bootstrapTable('getRowByUniqueId', roi_shape_id);
};

prototype.getRoiShapeList = function () {
    return this.table_container.bootstrapTable('getData');
};

prototype.removeRoiShape = function (roi_shapes) {
    roi_shapes = (roi_shapes instanceof Array) ? roi_shapes : [roi_shapes];
    return this.table_container.bootstrapTable('remove', {field: 'id', values: roi_shapes});
};

prototype.removeAll = function () {
    return this.table_container.bootstrapTable('removeAll');
};


