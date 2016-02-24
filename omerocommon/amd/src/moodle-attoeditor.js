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
 * Simple controller to wrap the Moodle AttoEditor.
 *
 * @package    qtype
 * @subpackage omerocommon
 * @copyright  2015-2016 CRS4
 * @license    https://opensource.org/licenses/mit-license.php MIT license
 */
/* jshint curly: false */
/* globals console */
define([
        'jquery',
        'qtype_omerocommon/moodle-forms-utils'
    ],
    function (/*$, Editor, FormUtils*/) {

        // defines the basic package
        M.qtypes = M.qtypes || {};

        // defines the specific package of this module
        M.qtypes.omerocommon = M.qtypes.omerocommon || {};


        /**
         * Defines MoodleFormUtils class
         * @type {{}}
         */
        M.qtypes.omerocommon.MoodleAttoEditor = function (editor_container_id) {

            // the reference to this scope
            var me = this;

            // the input element containing data to edit
            me.input_data_element_name = editor_container_id;

            // initialization function
            me.init = function () {
                me._yuiEditor = Y.use(
                    "moodle-editor_atto-editor", "moodle-atto_collapse-button",
                    "moodle-atto_title-button", "moodle-atto_bold-button", "moodle-atto_italic-button",
                    "moodle-atto_unorderedlist-button", "moodle-atto_orderedlist-button",
                    "moodle-atto_link-button", "moodle-atto_image-button", "moodle-atto_media-button",
                    "moodle-atto_managefiles-button", "moodle-atto_underline-button",
                    "moodle-atto_strike-button", "moodle-atto_subscript-button",
                    "moodle-atto_superscript-button", "moodle-atto_align-button",
                    "moodle-atto_indent-button", "moodle-atto_equation-button",
                    "moodle-atto_charmap-button", "moodle-atto_table-button",
                    "moodle-atto_clear-button", "moodle-atto_undo-button",
                    "moodle-atto_accessibilitychecker-button", "moodle-atto_accessibilityhelper-button",
                    "moodle-atto_html-button", function () {
                        Y.M.editor_atto.Editor.init({
                            "elementid": me.input_data_element_name,
                            "contextid": 1,
                            "autosaveEnabled": true,
                            "autosaveFrequency": "15",
                            "language": "en",
                            "directionality": "ltr",
                            "filepickeroptions": [],
                            "plugins": [{
                                "group": "collapse",
                                "plugins": [{
                                    "name": "collapse",
                                    "params": [],
                                    "showgroups": "5"
                                }]
                            }, {
                                "group": "style1",
                                "plugins": [{
                                    "name": "title",
                                    "params": []
                                }, {
                                    "name": "bold",
                                    "params": []
                                }, {
                                    "name": "italic",
                                    "params": []
                                }]
                            }, {
                                "group": "list",
                                "plugins": [{
                                    "name": "unorderedlist",
                                    "params": []
                                }, {
                                    "name": "orderedlist",
                                    "params": []
                                }]
                            }, {
                                "group": "links",
                                "plugins": [{
                                    "name": "link",
                                    "params": []
                                }]
                            }, {
                                "group": "files",
                                "plugins": [{
                                    "name": "image",
                                    "params": []
                                }, {
                                    "name": "media",
                                    "params": []
                                }, {
                                    "name": "managefiles",
                                    "params": [],
                                    "disabled": true,
                                    "area": [],
                                    "usercontext": null
                                }]
                            }, {
                                "group": "style2",
                                "plugins": [{
                                    "name": "underline",
                                    "params": []
                                }, {
                                    "name": "strike",
                                    "params": []
                                }, {
                                    "name": "subscript",
                                    "params": []
                                }, {
                                    "name": "superscript",
                                    "params": []
                                }]
                            }, {
                                "group": "align",
                                "plugins": [{
                                    "name": "align",
                                    "params": []
                                }]
                            }, {
                                "group": "indent",
                                "plugins": [{
                                    "name": "indent",
                                    "params": []
                                }]
                            }, {
                                "group": "insert",
                                "plugins": [{
                                    "name": "equation",
                                    "params": [],
                                    "texfilteractive": true,
                                    "contextid": 1,
                                    "library": {
                                        "group1": {
                                            "groupname": "librarygroup1",
                                            "elements": "\n\\cdot\n\\times\n\\ast\n\\div\n\\diamond\n\\pm\n\\mp\n\\" +
                                            "oplus\n\\ominus\n\\otimes\n\\oslash\n\\odot\n\\circ\n\\bullet\n\\" +
                                            "asymp\n\\equiv\n\\subseteq\n\\supseteq\n\\leq\n\\geq\n\\preceq\n\\" +
                                            "succeq\n\\sim\n\\simeq\n\\approx\n\\subset\n\\supset\n\\ll\n\\gg\n\\" +
                                            "prec\n\\succ\n\\infty\n\\in\n\\ni\n\\forall\n\\exists\n\\neq\n"
                                        },
                                        "group2": {
                                            "groupname": "librarygroup2",
                                            "elements": "\n\\leftarrow\n\\rightarrow\n\\uparrow\n\\downarrow\n\\" +
                                            "leftrightarrow\n\\nearrow\n\\searrow\n\\swarrow\n\\nwarrow\n\\" +
                                            "Leftarrow\n\\Rightarrow\n\\Uparrow\n\\Downarrow\n\\Leftrightarrow\n"
                                        },
                                        "group3": {
                                            "groupname": "librarygroup3",
                                            "elements": "\n\\alpha\n\\beta\n\\gamma\n\\delta\n\\epsilon\n\\zeta\n\\" +
                                            "eta\n\\theta\n\\iota\n\\kappa\n\\lambda\n\\mu\n\\nu\n\\xi\n\\pi\n\\" +
                                            "rho\n\\sigma\n\\tau\n\\upsilon\n\\phi\n\\chi\n\\psi\n\\omega\n\\" +
                                            "Gamma\n\\Delta\n\\Theta\n\\Lambda\n\\Xi\n\\Pi\n\\Sigma\n\\Upsilon\n\\" +
                                            "Phi\n\\Psi\n\\Omega\n"
                                        },
                                        "group4": {
                                            "groupname": "librarygroup4",
                                            "elements": "\n\\sum{a,b}\n\\int_{a}^{b}{c}\n\\iint_{a}^{b}{c}\n\\" +
                                            "iiint_{a}^{b}{c}\n\\oint{a}\n(a)\n[a]\n\\lbrace{a}\\rbrace\n\\left| \\" +
                                            "begin{matrix} a_1 & a_2 \\ a_3 & a_4 \\end{matrix} \\right|\n"
                                        }
                                    },
                                    "texdocsurl": "http:\/\/docs.moodle.org\/29\/en\/Using_TeX_Notation"
                                }, {
                                    "name": "charmap",
                                    "params": []
                                }, {
                                    "name": "table",
                                    "params": []
                                }, {
                                    "name": "clear",
                                    "params": []
                                }]
                            }, {
                                "group": "undo",
                                "plugins": [{
                                    "name": "undo",
                                    "params": []
                                }]
                            }, {
                                "group": "accessibility",
                                "plugins": [{
                                    "name": "accessibilitychecker",
                                    "params": []
                                }, {
                                    "name": "accessibilityhelper",
                                    "params": []
                                }]
                            }, {
                                "group": "other",
                                "plugins": [{
                                    "name": "html",
                                    "params": []
                                }]
                            }]
                        });
                    });
            };

            me.clear = function () {
                me.setText("");
            };

            me.setText = function (text) {
                console.log("Setting text: " + me.input_data_element_name + "editable");
                var data_element = document.getElementById(me.input_data_element_name + "editable");
                if (!data_element) console.warn("AttoEditor of " + me.input_data_element_name + " seems not initialized!");
                else data_element.innerHTML = text;
            };

            me.getText = function () {
                console.log("Getting text from " + "#" + me.input_data_element_name + "editable");
                var data_element = document.getElementById(me.input_data_element_name + "editable");
                if (!data_element) {
                    console.warn("AttoEditor of " + me.input_data_element_name + " seems not initialized!");
                    return undefined;
                } else return data_element.innerHTML;
            };

            me.on = function (eventName, callback) {
                if (me._yuiEditor)
                    me._yuiEditor.on(eventName, callback);
            };
        };

        // returns the class
        return M.qtypes.omerocommon.MoodleAttoEditor;
    }
);