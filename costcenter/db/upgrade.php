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
 * This file keeps track of upgrades to the ltiprovider plugin
 *
 * @package    local
 * @subpackage Cost center
 * @copyright  2013 Naveen <naveen@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

function xmldb_local_costcenter_upgrade($oldversion) {
    global $CFG, $DB, $OUTPUT;
    $dbman = $DB->get_manager();
    //if ($oldversion > 2013010701) {
    //    upgrade_plugin_savepoint(true, 2013010707, 'local', 'costcenter');
    //}
    	//==============ILT Prerequisite Courses courses @@@@ RAJU TUMMOJI @@@@============//
    if ($oldversion < 2014051210) {
       // Define field parentid to be added to facetoface.
       $table = new xmldb_table('local_coursedetails');
       $field = new xmldb_field('prerequisite_courses',XMLDB_TYPE_TEXT, 'big', null,null,null,null);
    
       if (!$dbman->field_exists($table, $field)) {
           $dbman->add_field($table, $field);
       }
         upgrade_plugin_savepoint(true, 2014051210, 'local', 'costcenter');
    }
    	//==============ILT Prerequisite Courses courses @@@@ RAJU TUMMOJI @@@@============//
    return true;
}
