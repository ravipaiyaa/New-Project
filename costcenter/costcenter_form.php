<?php

// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Edit a tool provided in a course
 *
 * @package    local
 * @subpackage Cost center
 * @copyright  2015 Naveen<naveen@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/lib/formslib.php');
//require_once($CFG->dirroot . '/local/lib.php');

class costcenter_form extends moodleform {

    public function definition() {
        global $USER, $CFG, $PAGE;
        $costcenter = new costcenter();
        $mform = $this->_form;
        $id = $this->_customdata['id'];
        $costcenters = $this->_customdata['tool'];
        $editoroptions = $this->_customdata['editoroptions'];
        //if ($id < 0)
        //    $mform->addElement('header', 'settingsheader', get_string('createcostcenter', 'local_costcenter'));
        //else
        //    $mform->addElement('header', 'settingsheader', get_string('editcostcenter', 'local_costcenter'));
        $tools = array();
        //$department = new department();
       //         $selecttype = array();
       // $selecttype['1'] = get_string('department', 'local_costcenter');
       // $selecttype['2'] = get_string('location', 'local_costcenter');
       //$mform->addElement('select', 'type', get_string('type', 'local_costcenter'), $selecttype);
       //$mform->addHelpButton('type', 'type', 'local_costcenter');
       //$mform->setType('type', PARAM_RAW);
        $items = $costcenter->get_costcenter_items(true);
        $parents = $costcenter->get_costcenter_parent($items, $costcenters->id);
        if (count($parents) <= 1) {
            $mform->addElement('hidden', 'parentid', 0);
            $mform->setType('parentid', PARAM_RAW);
        } else {
            $mform->addElement('select', 'parentid', get_string('parent', 'local_costcenter'), $parents);
            $mform->setType('parentid', PARAM_RAW);
        }
        $mform->addHelpButton('parentid', 'parent', 'local_costcenter');
        $mform->addElement('text', 'fullname', get_string('costcentername', 'local_costcenter'), $tools);
        $mform->setType('fullname', PARAM_TEXT);
        $mform->addRule('fullname', get_string('missingcostcentername', 'local_costcenter'), 'required', null, 'client');
        $mform->addElement('text', 'shortname', get_string('shortname','local_costcenter'), 'maxlength="100" size="20"');
        //$mform->addHelpButton('shortname', 'shortnamelp');
        $mform->addRule('shortname', get_string('missingshortname'), 'required', null, 'client');
        $mform->setType('shortname', PARAM_TEXT);
        $attributes = array('rows' => '8', 'cols' => '40');
        //$mform->addElement('textarea', 'description', get_string('description', 'local_costcenter'), null, $editoroptions);
        //$mform->setType('description', PARAM_RAW);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $now = date("d-m-Y");
        $now = strtotime($now);
        $mform->addElement('hidden', 'timecreated', $now);
        $mform->setType('timecreated', PARAM_RAW);
        $mform->addElement('hidden', 'usermodified', $USER->id);
        $mform->setType('usermodified', PARAM_RAW);
   //   if (!empty($CFG->allowuserthemes)) {
        $choices = array();
        $choices[''] = get_string('default');
        $themes = get_list_of_themes();
        foreach ($themes as $key => $theme) {
            if (empty($theme->hidefromselector)) {
                $choices[$key] = get_string('pluginname', 'theme_'.$theme->name);
            }
        }
        $mform->addElement('select', 'theme', get_string('preferredtheme'), $choices);
   // }
        $submit = ($id > 0) ? get_string('update_costcenter', 'local_costcenter') : get_string('create', 'local_costcenter');
        $this->add_action_buttons('false', $submit);
    }

    public function validation($data, $files) {
        global $COURSE, $DB, $CFG;
        $errors = parent::validation($data, $files);
        if ($costcenter = $DB->get_record('local_costcenter', array('shortname' => $data['shortname']), '*', IGNORE_MULTIPLE)) {
            if (empty($data['id']) || $costcenter->id != $data['id']) {
                $errors['shortname'] = get_string('shortnametakenlp', 'local_costcenter', $costcenter->shortname);
            }
        }
        return $errors;
    }

}
