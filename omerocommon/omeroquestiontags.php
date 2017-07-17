<?php
/**
 * Drop down for question categories.
 *
 * Extension of the default tags form,
 * to handle custom tags related to OmeroQuestions.
 *
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $CFG;
require_once($CFG->libdir . '/form/group.php');

require_once($CFG->libdir . '/form/tags.php');


/**
 * Form field type for editing tags.
 *
 * HTML class for editing tags, both official and peronal.
 *
 * @package   core_form
 * @category  form
 * @copyright 2009 Tim Hunt
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class MoodleQuickForm_omeroquestiontags extends MoodleQuickForm_tags
{

    /**
     * Constructor
     *
     * @param string $elementName Element name
     * @param mixed $elementLabel Label(s) for an element
     * @param null $editElementLabel
     * @param array $options Options to control the element's display
     * @param mixed $attributes Either a typical HTML attribute string or an associative array.
     */
    function MoodleQuickForm_omeroquestiontags($elementName = null, $elementLabel = null,
                                               $selectElementLabel = null,
                                               $elementDescription = null,
                                               $editElementLabel = null,
                                               $options = array(), $attributes = array())
    {
        parent::MoodleQuickForm_tags($elementName, $elementLabel, $options, $attributes);
        $this->elementLabel = $elementLabel;
        $this->elementDescription = $elementDescription;
        $this->selectElementLabel = $selectElementLabel;
        $this->editElementLabel = $editElementLabel;
    }


    /**
     * Creates the group's elements.
     */
    function _createElements()
    {
        global $CFG, $OUTPUT;
        $this->_elements = array();

        // Official tags.
        $showingofficial = $this->_options['display'] != MoodleQuickForm_tags::NOOFFICIAL;
        if ($showingofficial) {
            $this->_load_official_tags();

            // If the user can manage official tags, give them a link to manage them.
            $label = $this->elementDescription !== null ? $this->elementDescription : get_string('otags', 'tag');
            if (has_capability('moodle/tag:manage', context_system::instance())) {
                $url = $CFG->wwwroot . '/tag/manage.php';
                $label .= ' ' . $OUTPUT->action_link(
                        $url,
                        "( " . ($this->editElementLabel !== null ? $this->editElementLabel : get_string('manageofficialtags', 'tag')) . " )",
                        new popup_action('click', $url, 'managetags'),
                        array('title' => get_string('newwindow'), "style" => "float: right;")) . '';
            }

            // Get the list of official tags.
            $noofficial = false;
            if (empty($this->_officialtags)) {
                $officialtags = array('' => get_string('none'));
                $noofficial = true;
            } else {
                $officialtags = array_combine($this->_officialtags, $this->_officialtags);
            }

            // Create the element.
            $size = min(5, count($officialtags));
            // E_STRICT creating elements without forms is nasty because it internally uses $this
            $officialtagsselect = @MoodleQuickForm::createElement(
                'select', $this->selectElementLabel,
                $label, $officialtags, array('size' => $size, "width" => "100%")
            );
            $officialtagsselect->setMultiple(true);
            if ($noofficial) {
                $officialtagsselect->updateAttributes(array('disabled' => 'disabled'));
            }
            $this->_elements[] = $officialtagsselect;
        }

        // Other tags.
//        if ($this->_options['display'] != MoodleQuickForm_tags::ONLYOFFICIAL) {
//            if ($showingofficial) {
//                $label = get_string('othertags', 'tag');
//            } else {
//                $label = get_string('entertags', 'tag');
//            }
//            // E_STRICT creating elements without forms is nasty because it internally uses $this
//            $othertags = @MoodleQuickForm::createElement('textarea', 'othertags', $label, array('cols' => '40', 'rows' => '5'));
//            $this->_elements[] = $othertags;
//        }

        // Paradoxically, the only way to get labels output is to ask for 'hidden'
        // labels, and then override the .accesshide class in the CSS!
        foreach ($this->_elements as $element) {
            if (method_exists($element, 'setHiddenLabel')) {
                $element->setHiddenLabel(true);
            }
        }
    }
}
