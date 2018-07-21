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
 * @subpackage costcenter
 * @copyright  2015 Naveen <naveen@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $USER, $PAGE;

require_once($CFG->dirroot . '/local/costcenter/lib.php');
$systemcontext = context_system::instance();

require_login();

$costcenter = new costcenter();

if (!has_capability('local/costcenter:view', $systemcontext)) {
    print_error('nopermission');
}

$PAGE->set_pagelayout('admin');

$PAGE->set_context($systemcontext);
$PAGE->set_url('/local/costcenter/info.php');

$PAGE->set_title(get_string('pluginname', 'local_costcenter'));
$PAGE->navbar->add(get_string('pluginname', 'local_costcenter'), new moodle_url('/local/costcenter/index.php'));
$PAGE->navbar->add(get_string('info', 'local_costcenter'));
echo $OUTPUT->header();

$currenttab = 'info';
echo $OUTPUT->heading(get_string('pluginname', 'local_costcenter'));
$costcenter->print_costcentertabs($currenttab, $id = NULL);

if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('information', 'local_costcenter'));
}
echo '<div class="help_cont">' . get_string('help_des', 'local_costcenter') . '<div>';

echo $OUTPUT->footer();