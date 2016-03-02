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
 * UI Controller of a multilanguage atto editor.
 *
 * @package    qtype
 * @subpackage omerocommon
 * @copyright  2015-2016 CRS4
 * @license    https://opensource.org/licenses/mit-license.php MIT license
 */
define([
        'qtype_omerocommon/moodle-forms-utils',
        'qtype_omerocommon/multilanguage-element',
        'qtype_omerocommon/moodle-attoeditor'
    ],
    /* jshint curly: false */
    function (/*FormUtils, MultilanguageElement, MoodleAttoEditor*/) {

        /**
         * Builds a new MultilanguageAttoEditor
         *
         * @param element_id
         * @param locale_map_element_name
         * @constructor
         */
        M.qtypes.omerocommon.MultilanguageAttoEditor = function (element_id, locale_map_element_name, avoid_editor_init) {

            // the reference to this scope
            var me = this;

            // Call the parent constructor
            M.qtypes.omerocommon.MultilanguageElement.call(this, element_id, locale_map_element_name);

            // A new instance of MoodleAttoEditor
            me._editor = new M.qtypes.omerocommon.MoodleAttoEditor("id_" + me.input_data_element_name);

            // avoids YUI editor initialization
            // (useful when the editor already exists)
            if (!avoid_editor_init)
                me._editor.init();


            /**
             * Clear the current text area
             */
            me.clear = function () {
                me._editor.clear();
            };
        };


        // inherit
        M.qtypes.omerocommon.MultilanguageAttoEditor.prototype = new M.qtypes.omerocommon.MultilanguageElement();

        // correct the constructor
        M.qtypes.omerocommon.MultilanguageAttoEditor.prototype.constructor = M.qtypes.omerocommon.MultilanguageAttoEditor;

        // set the parent
        M.qtypes.omerocommon.MultilanguageAttoEditor.prototype.parent = M.qtypes.omerocommon.MultilanguageElement.prototype;


        var prototype = M.qtypes.omerocommon.MultilanguageAttoEditor.prototype;

        /**
         * Save the current string
         */
        prototype.save = function () {
            var text = this._editor.getText();
            this.setLocaleText(text, this._current_language);
        };

        /**
         * Updates the viewer to show the current localized text
         *
         * @param language the new language
         */
        prototype.changeLanguage = function (language) {
            // call the default behaviour
            this.parent.changeLanguage.call(this, language);

            // update the editor with the current locale text
            var text = this._locale_text_map[language] || "";
            this._editor.setText(text, true);
        };

        // returns the class
        return M.qtypes.omerocommon.MultilanguageAttoEditor;
    }
);