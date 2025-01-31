<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Form to add/remove and edit sessions.
 *
 * @package     mod_otopo
 * @copyright   2024 Nantes Université <support-tice@univ-nantes.fr> (Commissioner)
 * @copyright   2024 E-learning Touch' <contact@elearningtouch.com> (Maintainer)
 * @copyright   2022 Kosmos <moodle@kosmos.fr> (Former maintainer)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once("$CFG->libdir/formslib.php");

/**
 * Class of the form used to add/remove and edit sessions.
 *
 * @package     mod_otopo
 * @copyright   2024 Nantes Université <support-tice@univ-nantes.fr> (Commissioner)
 * @copyright   2024 E-learning Touch' <contact@elearningtouch.com> (Maintainer)
 * @copyright   2022 Kosmos <moodle@kosmos.fr> (Former maintainer)
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class sessions_form extends moodleform {
    /**
     * Add elements to form.
     */
    public function definition() {
        $mform = $this->_form;
    
        // Add existing hidden elements
        $mform->addElement('hidden', 'o', $this->_customdata['o']);
        $mform->setType('o', PARAM_INT);
        $mform->addElement('hidden', 'action', 'edit');
        $mform->setType('action', PARAM_TEXT);
        $mform->addElement('hidden', 'object', 'sessions');
        $mform->setType('object', PARAM_TEXT);
        $mform->addElement('hidden', 'sesskey', $this->_customdata['sesskey']);
        $mform->setType('sesskey', PARAM_TEXT);
    
        // Default configuration for date selectors
        $defaultsdate = [
            'optional' => false,
            'startyear' => date('Y'), // Current start year
            'stopyear'  => date('Y') + 10, // Current end year + 10 years
            'timezone'  => 99, // Use site's timezone
            'step'      => 5, // 5-minute steps
        ];
    
        // Set the number of sessions to repeat
        $repeatno = $this->_customdata['count_sessions'];
        if ($repeatno == 0) {
            $repeatno = $this->_customdata['sessions'];
        }
    
        // Define the array of repeated elements
        $repeatarray = [];
        $repeatarray[] = $mform->createElement('text', 'name', get_string('sessionname', 'otopo'), ['size' => '64']);
        $colorel = $mform->createElement(
            'text',
            'color',
            get_string('sessioncolor', 'otopo'),
            ['class' => 'input-colorpicker']
        );
        $repeatarray[] = $colorel;
        $repeatarray[] = $mform->createElement('hidden', 'id', 0);
        $repeatarray[] = $mform->createElement(
            'date_time_selector',
            'allowsubmissionfromdate',
            get_string('sessionallowsubmissionfromdate', 'otopo'),
            $defaultsdate
        );
        $repeatarray[] = $mform->createElement(
            'date_time_selector',
            'allowsubmissiontodate',
            get_string('sessionallowsubmissiontodate', 'otopo'),
            $defaultsdate
        );
        $repeatarray[] = $mform->createElement(
            'button',
            'delete',
            get_string("sessiondelete", 'otopo'),
            ['class' => 'deletesession']
        );
    
        // Define the options for repeated elements
        $repeateloptions = [];
    
        // Define the types of the elements
        $mform->setType('name', PARAM_TEXT);
        $mform->setType('color', PARAM_TEXT);
        $mform->setType('id', PARAM_INT);
    
        // Define the validation rules
        $repeateloptions['allowsubmissionfromdate']['rule'] = 'required';
        $repeateloptions['allowsubmissiontodate']['rule'] = 'required';
    
        // Create the repeated elements
        $this->repeat_elements(
            $repeatarray,
            $repeatno,
            $repeateloptions,
            'option_repeats',    // ID of the repeating container
            'option_add_fields', // ID of the add button
            1,                   // Minimum number of repetitions
            get_string('sessionadd', 'otopo'), // Label of the add button
            true                 // Allow deletion of repetitions
        );
    
        // Prepare default values
        $defaultvalues = [];
    
        // Calculate default dates for each repeated session
        $currenttimestamp = time(); // Current date
    
        for ($i = 0; $i < $repeatno; $i++) {
            // Default opening date: today
            $default_from_date = $currenttimestamp;
    
            // Default closing date: 7 days after the opening date
            $default_to_date = strtotime('+7 days', $default_from_date);
    
            // Assign default values for each session
            $defaultvalues['allowsubmissionfromdate'][$i] = $default_from_date;
            $defaultvalues['allowsubmissiontodate'][$i] = $default_to_date;
    
            // Optional: Default name and color
            $defaultvalues['name'][$i] = "Session " . ($i + 1);
            $defaultvalues['color'][$i] = '#000000';
        }
    
        // Set default values in the form
        $this->set_data($defaultvalues);
    
        // Add action buttons (Submit and Cancel)
        $this->add_action_buttons();
    }

    /**
     * Set the form's default data.
     *
     * @param array Form's default data.
     */
    public function set_data($defaultvalues) {
        if (is_object($defaultvalues)) {
            $defaultvalues = (array)$defaultvalues;
        }

        if (!$defaultvalues) {
            $defaultvalues = [];
        }
        if (!array_key_exists('name', $defaultvalues)) {
            $defaultvalues['name'] = [];
        }
        if (!array_key_exists('color', $defaultvalues)) {
            $defaultvalues['color'] = [];
        }
        for ($i = 0; $i < $this->_form->_constantValues['option_repeats']; $i++) {
            if (!array_key_exists($i, $defaultvalues['name'])) {
                $defaultvalues['name'][$i] = "Session " . strval($i + 1);
            }
            if (!array_key_exists($i, $defaultvalues['color'])) {
                $defaultvalues['color'][$i] = '#000000';
            }
        }
        parent::set_data($defaultvalues);
    }

    /**
     * Validate the form's data.
     *
     * @param array $data The form's data.
     * @param array $files The form's files.
     * @return array of errors.
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        for ($i = 0; $i < $data['option_repeats']; $i++) {
            if (
                $data['allowsubmissionfromdate'][$i] && $data['allowsubmissiontodate'][$i]
                && $data['allowsubmissionfromdate'][$i] >= $data['allowsubmissiontodate'][$i]
            ) {
                $errors["allowsubmissiontodate[{$i}]"] = get_string('allowsubmissiondateerror', 'otopo');
            }
        }
        return $errors;
    }
}
