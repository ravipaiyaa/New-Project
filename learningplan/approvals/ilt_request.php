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
 * List the tool provided in a course
 *
 * @package     Mod  
 * @subpackage Facetoface
 * @copyright  2014 Sriharsha <sriharsha@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
//define('AJAX_SCRIPT', true);

require_once(dirname(__FILE__) . '/../../../config.php');

global $DB, $USER, $CFG,$PAGE,$OUTPUT;
$systemcontext = context_system::instance();
require_once($CFG->dirroot . '/mod/facetoface/approvals/includes.php');

$PAGE->requires->jquery();
$PAGE->requires->css('/local/learningplan/css/jquery.dataTables.css');
$PAGE->requires->js('/local/learningplan/js/jquery.dataTables.min.js', true);

//get the admin layout
$PAGE->set_pagelayout('admin');

//check the context level of the user and check weather the user is login to the system or not
$PAGE->set_context($systemcontext);
$PAGE->set_url("/mod/facetoface/approvals/ilt_request.php");
$PAGE->set_title(get_string('requested_courses', 'facetoface'));
$PAGE->set_heading(get_string('requested_courses', 'facetoface'));
$PAGE->navbar->add(get_string('requested_courses', 'facetoface'));

require_login();
echo $OUTPUT->header();

$submitted_data =  data_submitted();
if($submitted_data){    
    if($submitted_data->action =='approve'){
        foreach($submitted_data->f2fapprovalids as $approvalid){
            $facetofaceinfo=$DB->get_record('local_facetoface_approval', array('id'=>$approvalid));
            $facetofaceinfo->approvestatus=1;           
            $facetofaceinfo->approvedby =$USER->id;          
            $facetofaceinfo->timemodified = time();
            $facetofaceinfo->usermodified = $USER->id;
            $DB->update_record('local_facetoface_approval', $facetofaceinfo);
        }      
    }    
    else if($submitted_data->action =='reject'){
        $approvalid= $submitted_data->fapprovalid;
        $facetofaceinfo=$DB->get_record('local_facetoface_approval', array('id'=>$approvalid));
        $facetofaceinfo->approvestatus=2;           
        $facetofaceinfo->approvedby =$USER->id;          
        $facetofaceinfo->timemodified = time();
        $facetofaceinfo->usermodified = $USER->id;
        $facetofaceinfo->reject_msg =$submitted_data->text;
        $DB->update_record('local_facetoface_approval', $facetofaceinfo);        
    }
    
} // end of submitted data





$table = new html_table();
$head = array('Classroom Name','Requested Users','Approved Users','Reject Users');
$table->head = $head;
$table->id = 'publishedexams';    
$out =  html_writer::table($table);
echo $out;

require_once($CFG->dirroot . '/local/learningplan/approvals/custom.php?id=0');
echo $OUTPUT->footer();
