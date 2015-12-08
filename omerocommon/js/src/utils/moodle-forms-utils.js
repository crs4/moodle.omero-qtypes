/**
 * Created by kikkomep on 12/2/15.
 */
define("qtype_omerocommon/moodle-forms-utils",
    ['jquery'],
    function ($) {
        // Private functions.


        // Public functions
        return {
            initialize: function (str) {

                // defines the basic package
                M.qtypes = M.qtypes || {};

                // defines the specific package of this module
                M.qtypes.omerocommon = M.qtypes.omerocommon || {};


                /**
                 * Defines MoodleFormUtils class
                 * @type {{}}
                 */
                M.qtypes.omerocommon.MoodleFormUtils = function () {

                    var me = this;

                    // the list elements added by this utility class
                    me._dynamic_elements = {};

                    /**
                     * Dynamically appends a new element to a given element
                     * identified by its id, assigning a label to it.
                     *
                     * @param container_id
                     * @param label the label to assign to the element
                     * @param element html || jQuery element
                     */
                    me.appendElementByContainerId = function (container_id,
                                                              label, element) {
                        var element_obj = $(element);
                        var existingContainer = $("#" + editor_container_id + " div.fcontainer");

                        // Checks whether the fieldset exists or not
                        if (!editor_container_id.length) {
                            console.error("FieldSet " + editor_container_id + " not found!!!");
                        }

                        // checks the existing id (or generates it)
                        if (!(element_obj.attr("id")))
                            element_obj.attr("id", me.generateGuid());

                        // builds the root element to append to the fieldset
                        var newContainerId = me.generateGuid();
                        var newContainer = $('<div class="fitem" id="' + newContainerId + '"></div>');

                        // sets the id of the wrapped element
                        newContainer.attr("container-of", element_obj.attr("id"));

                        // appends inner content
                        newContainer.html([
                            '<div class="fitemtitle"><label for="' + element_obj.attr("id") + '">' + label + '</label></div>',
                            '<div class="felement">',
                            '<div>',
                            element_obj.get(0).outerHTML,
                            '</div>',
                            '</div>'
                        ].join(" "));

                        // appends the element
                        existingContainer.append(newContainer);
                        // updates the list elements generated by this utility instance
                        me._dynamic_elements[element_obj.attr("id")] = newContainer;

                        // returns the element created
                        return newContainer;
                    };


                    me.appendElement = function (container,
                                                 label, element, append_loacale_map_name) {
                        var element_obj = $(element);
                        var elementContainer = $(container);

                        // checks the existing id (or generates it)
                        if (!(elementContainer.attr("id")))
                            elementContainer.attr("id", me.generateGuid());

                        // checks the existing id (or generates it)
                        if (!(element_obj.attr("id")))
                            element_obj.attr("id", me.generateGuid());

                        // builds the root element to append to the fieldset
                        var newContainerId = me.generateGuid();
                        var newContainer = $('<div class="fitem" id="' + newContainerId + '"></div>');

                        // sets the id of the wrapped element
                        newContainer.attr("container-of", element_obj.attr("id"));

                        // appends inner content
                        newContainer.html([
                            '<div class="fitemtitle"><label for="' + element_obj.attr("id") + '">' + label + '</label></div>',
                            '<div class="felement">',
                            '<div>',
                            element_obj.get(0).outerHTML,
                            ((append_loacale_map_name && append_loacale_map_name.length >0)
                                ? '<input type="hidden" name="' + append_loacale_map_name + '" value="{}" />'
                                : ""),
                            '</div>',
                            '</div>'
                        ].join(" "));

                        // appends the element
                        elementContainer.append(newContainer);
                        // updates the list elements generated by this utility instance
                        me._dynamic_elements[element_obj.attr("id")] = newContainer;

                        // returns the element created
                        return newContainer;
                    };


                    /**
                     * Removes the an element previously added to a fieldset using this utility class
                     *
                     * @param fieldset_container_id
                     * @param element_id
                     */
                    me.removeElementFromFieldSet = function (element_id) {
                        var el = $("#" + element_id);

                        if (!el.length) {
                            console.error("Element " + element_id + " doesn't exist");
                            return false;
                        }

                        el.parent().parent().remove();

                        // Removes the elements from the list of added elements
                        // when the 'remove' event occurs
                        delete me._dynamic_elements[element_id];
                        return true;
                    };


                    /**
                     * Private function to generate a UUID to identify HTML elements
                     *
                     * @returns {string}
                     * @private
                     */
                    me.generateGuid = function () {
                        function s4() {
                            return Math.floor((1 + Math.random()) * 0x10000)
                                .toString(16)
                                .substring(1);
                        }

                        return s4() + s4() + '-' + s4() + '-' + s4() + '-' +
                            s4() + '-' + s4() + s4() + s4();
                    };


                    me.initDropdown = function () {
                        $('[data-toggle="popover"]').popover();


                        $("#enableModal").click(function () {
                            $('#myModal').modal();
                            //$('#myInput').focus();
                        });


                        $(".dropdown-toggle").dropdown();

                        //$('#username').editable({
                        //
                        //    success: function (response, newValue) {
                        //        alert("Changed: !!!");
                        //    }
                        //});
                    };
                };
            }
        };
    }
);