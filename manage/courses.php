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
 * @package    local
 * @subpackage like
 * @copyright  2014 Vinodkumar <avinod@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
global $DB, $USER, $CFG, $PAGE, $OUTPUT;
require_once($CFG->dirroot . '/blocks/manage/renderer.php');
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
require_login();
$PAGE->requires->jquery();
$PAGE->set_url('/local/manage/courses.php');
$PAGE->set_title(get_string('courses'));
$PAGE->set_pagelayout('admin');
//Header and the navigation bar
$PAGE->set_heading(get_string('courses'));

echo $OUTPUT->header();

$course_categories = $DB->get_records('course_categories', array());

$category = "<select id='course_cat' name='category'>";
$category .= "<option class='myclass' value=''>--select--</option>";
foreach($course_categories as $course_category){
    $category .= "<option class='myclass' value='".$course_category->id."'>".$course_category->name."</option>";
}
$category .= "</select><div id='Schedule'></div>";
echo $category;
echo html_writer::script("
                        $(document).ready(function() {
                            $('#course_cat').change(function(e){
                               var catid = $( '#course_cat option:selected').val();
                               
                               var dataString = {category:catid}; 
                               //alert(dataString);
                               
                               
                                $.ajax({
                                    type: 'POST',
                                    url: 'category_courses.php',
                                    data: dataString,
                                    cache: false,
                                    success: function(data) {
                                       $('#Schedule').html(data);
                                      
                                    }
                                });                  
                            });
                        });
                    ");

echo $OUTPUT->footer();
