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
 * List the tool provided 
 *
 * @package   blocks
 * @subpackage  block_recent_activity
 * @copyright  2016  shivani
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
global $PAGE,$CFG,$OUTPUT,$DB;
require_once(dirname(__FILE__) . '/../../config.php');
//require_once($CFG->dirroot.'/local/costcenter/courses.php');
//$PAGE->set_context(context_system::instance());
//$PAGE->set_url('/local/costcenter/featured_courses.php');
$PAGE->requires->js('/local/costcenter/js/custom.js');
$featured_course = optional_param('id', 0, PARAM_INT);
$featured = optional_param('featured', 0, PARAM_INT);


if($featured==1 && $featured_course){
	$course_feature = new stdClass();
	$course_feature->id = $featured_course;
	$course_feature->requestcourseid = $featured;
	//print_r($course_feature);
  $DB->update_record('local_coursedetails', $course_feature);
  $requestid->id=$featured_course;
  		$featured_value = 0;
		
			$html = html_writer::tag('a',
					html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('colored', 'local_costcenter'),
					'title' => 'Featured Courses', 'alt' => 'Featured Courses','onClick' => 'featuredcourses(' . $requestid->id . ','.$featured_value.')', 'class'=>'myFunction','style'=>'width:18px;height:18px;padding: 0px;')),
					array('href' => 'javascript:void(0)','featured_id' => $requestid->id, 'featured' =>$featured_value, 'sesskey' => sesskey() ));
			echo $html;
}elseif($featured==0 && $featured_course){
	$course_feature = new stdClass();
	$course_feature->id = $featured_course;
	$course_feature->requestcourseid = $featured;
  $DB->update_record('local_coursedetails', $course_feature);
  $requestid->id=$featured_course;
  		$featured_value = 1;
		$html = html_writer::tag('a',
				html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('coloredIcon', 'local_costcenter'),
				'title' => 'Featured Courses', 'alt' => 'Featured Courses','onClick' => 'featuredcourses(' . $requestid->id . ','.$featured_value.')', 'class'=>'myFunction','style'=>'width:18px;height:18px;padding: 0px;')),
								array('href' => 'javascript:void(0)','featured_id' => $requestid->id, 'featured' =>$featured_value, 'sesskey' => sesskey() ));
		echo $html;
}
 ?>
