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
 * Manual user enrolment UI.
 *
 * @package    enrol_manual
 * @copyright  2010 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->dirroot.'/local/costcenter/courselib.php');
require_once($CFG->dirroot.'/local/includes.php');
require_once($CFG->dirroot . '/local/costcenter/lib.php');

global $CFG,$PAGE,$OUTPUT;

require_login($course);

$PAGE->set_pagelayout('context_image');
$PAGE->set_title( get_string('pluginname','local_costcenter'));
$PAGE->set_url('/local/costcenter/course_allocation.php');
$PAGE->requires->jquery();
$PAGE->requires->js('/local/costcenter/js/select2.full.js',true);
$PAGE->requires->js('/local/costcenter/js/jquery.dataTables.min.js',true);
$PAGE->requires->js('/local/costcenter/js/custom.js',true);
$renderer = $PAGE->get_renderer('local_costcenter');

echo $OUTPUT->header();
$table = true;
echo '<div align="center" class="course_header"><h3 class="course_h3">COURSE ALLOCATION</h3></div>';
echo $renderer->courseallocation($table);
echo $OUTPUT->footer();
?>