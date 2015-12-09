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

    //
    me.selections = [];

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
prototype.initTable = function (hideToolbar) {

    var me = this;

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

    // Initializes the bootstrap table
    me.table_element.bootstrapTable({
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
                    formatter: me.idFormatter
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

                            var data = me.table_element.bootstrapTable('getData'),
                                index = $(this).parents('tr').data('index');
                            console.log(data[index]);
                            return '';
                        }
                    },
                    formatter: me.descriptionFormatter
                },
                {
                    field: 'visible',
                    title: 'Visibility',
                    width: "20px",
                    align: 'center',
                    valign: 'middle',
                    events: me.eventHandler(me),
                    formatter: me.visibilityFormatter
                },
                {
                    field: 'answerGroup',
                    title: 'Group',
                    align: 'center',
                    valign: 'middle',
                    width: "40px",
                    events: me.eventHandler(me),
                    formatter: me.answerClassFormatter
                }
            ]
        ]
    });
    // sometimes footer render error.
    setTimeout(function () {
        me.table_element.bootstrapTable('resetView');
    }, 200);

    me.table_element.on('check.bs.table uncheck.bs.table ' +
        'check-all.bs.table uncheck-all.bs.table', function () {
        me.remove_element.prop('disabled', !me.table_element.bootstrapTable('getSelections').length);

        // save your data, here just save the current page
        selections = me.getIdSelections();
        // push or splice the selections if you want to save all data selections
    });
    me.table_element.on('expand-row.bs.table', function (e, index, row, $detail) {
        if (index % 2 == 1) {
            $detail.html('Loading from ajax request...');
            $.get('LICENSE', function (res) {
                $detail.html(res.replace(/\n/g, '<br>'));
            });
        }
    });
    me.table_element.on('all.bs.table', function (e, name, args) {
        console.log(name, args);
    });
    me.remove_element.click(function () {
        var ids = me.getIdSelections();
        me.table_element.bootstrapTable('remove', {
            field: 'id',
            values: ids
        });
        me.remove_element.prop('disabled', true);
    });
    $(window).resize(function () {
        me.table_element.bootstrapTable('resetView', {
            height: me.getHeight()
        });
    });

    me._initialized = true;
};


prototype.getIdSelections = function () {
    return $.map(this.table_element.bootstrapTable('getSelections'), function (row) {
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

/**
 * Build an event handler
 *
 * @param table
 * @returns {{[click .like]: 'click .like', [click .roi-shape-visibility]: 'click .roi-shape-visibility', [click .remove]: 'click .remove', [change .answer-class]: 'change .answer-class'}}
 */

prototype.eventHandler = function (table) {
    return {
        'click .like': function (e, value, row, index) {
            if ($(e.target).attr("class").indexOf("glyphicon-plus-sign") !== -1)
                $(e.target).attr("class", "green glyphicon glyphicon-eye-open");
            else
                $(e.target).attr("class", "red glyphicon glyphicon-eye-close");
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
                $(e.target).attr("class", "green glyphicon glyphicon-eye-open");
            else
                $(e.target).attr("class", "red glyphicon glyphicon-eye-close");
            console.log("THIS", table, e, row);
            notifyListeners(table, {
                type: "roiShapeVisibilityChanged",
                shape: row,
                event: value,
                visible: row.visible
            });
        },

        'click .remove': function (e, value, row, index) {
            me.table_element.bootstrapTable('remove', {
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
        '<a class="roi-shape-visibility" href="javascript:void(0)" title="Like">',
        (data ?
            '<i class="green glyphicon glyphicon-eye-open"></i>' :
            '<i class="red glyphicon glyphicon-eye-close"></i>'),
        '</a> '
    ].join(" ");
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


