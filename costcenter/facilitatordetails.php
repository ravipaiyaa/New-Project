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
 * Version details
 *
 * @package    local_skillrepository
 * @copyright  2016 eAbyas info solutions
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define('AJAX_SCRIPT', true);
require_once(dirname(__FILE__).'/../../config.php');
require_once($CFG->dirroot.'/course/renderer.php');
$PAGE->requires->js('/local/costcenter/js/jquery.dataTables.min.js',true);
$PAGE->requires->css('/local/costcenter/css/jquery.dataTables.min.css',true);
$action = required_param('action', PARAM_TEXT);
$id = optional_param('id', 0, PARAM_INT);
$update=optional_param('edit', 0, PARAM_INT);
$name = optional_param('name', '', PARAM_TEXT);
$employeeid = optional_param('employeeid', 0, PARAM_INT);
$percentage = optional_param('percentage', 0, PARAM_RAW);
$credits = optional_param('credits', '', PARAM_TEXT);
$contenttype = optional_param('contenttype', '', PARAM_INT);
$courseid = optional_param('courseid', 0, PARAM_INT);
$facetofaceid = optional_param('facetofaceid', 0, PARAM_INT);
$shortname = optional_param('shortname', '', PARAM_TEXT);
$timecreated = optional_param('timecreated', 0, PARAM_INT);
$usercreated = optional_param('usercreated', 0, PARAM_INT);
$table = optional_param('table', '', PARAM_RAW);
$data = optional_param('data', '', PARAM_RAW);
	$record = new stdClass();
	$record->employeeid = $employeeid;
	$record->percentage = $percentage;
	$record->credits = $credits;
	$record->contenttype = $contenttype;
	$record->courseid = $courseid;
	$record->facetofaceid = $facetofaceid;
	$record->timecreated = $timecreated;
	$record->usercreated = $usercreated;
	//$record->edit = $update;
	 global $CFG, $PAGE, $DB, $OUTPUT;
	$renderer = new coursecat_helper();
	switch($action) {
	case 'insert':    //Facilitator Insertion and Update
		if($update > 0){
			$record->id = $update;
			$record->courseid = $courseid;
			$course_facilitator=$DB->update_record('course_facilitator', $record);
			$templatedisplay =  $renderer->displaytypeview($courseid);
		}else if($update == 0){
		   $course_facilitator=$DB->insert_record('course_facilitator', $record);
		   $record->courseid = $courseid;
		   $templatedisplay =  $renderer->displaytypeview($courseid);
			//echo $templatedisplay;
	
    }

		$return = $templatedisplay;
		break;
		
		case 'edit': // Facilitator need values for edit
			$edit = $DB->get_record('course_facilitator', array('id'=> $id));
			$editemployee = $DB->get_record('user', array('id'=> $edit->employeeid));
			$editreturn = ['data' => ['edit'=>$edit->id, 'employeeid' => $edit->employeeid, 'percentage' => $edit->percentage,'employeefirstname' => $editemployee->firstname,'employeelastname' => $editemployee->lastname, 'facetofaceid' => $edit->facetofaceid, 'contenttype' => $edit->contenttype]];			
		$return = $editreturn;
		break;

	}
	
echo json_encode($return);