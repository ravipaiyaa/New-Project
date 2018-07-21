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
 * @package    blocks
 * @subpackage manage
 * @copyright  2014 Anilkumar.Cheguri <anil@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
global $DB, $USER, $CFG, $PAGE, $OUTPUT;
require_once($CFG->dirroot . '/blocks/manage/renderer.php');
require_once($CFG->dirroot . '/local/includes.php');

$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
require_login();


$PAGE->set_url('/local/manage/allcourses.php');
$PAGE->set_title(get_string('e_learning_courses','block_costcenterstructure'));
$PAGE->set_pagelayout('fullpage');
$PAGE->navbar->add(get_string('e_learning_courses','block_costcenterstructure'));
$category = optional_param('category', -1, PARAM_INT);
$type = optional_param('type', 0, PARAM_INT);
$global_search = optional_param('g_search', 0, PARAM_RAW);

$renderer = $PAGE->get_renderer('block_manage');
$includes = new user_course_details();

echo $OUTPUT->header();

?>

<div ng-app="myApp">

 <!--<div class="container">-->
   <!-- <div class="row">-->
     <!-- <div class="col-lg-8">-->
        <div ng-controller="MyController" class="my-controller">
          <h1>Tasty Paginated Menu</h1>

          <small>this is in "MyController"</small>


          <div class="row">
            <div class="col-xs-4">
              <h3>Meals Page: {{ currentPage }}</h3>
            </div>
            <div class="col-xs-4">
              <label for="search">Search:</label>
              <input ng-model="q" id="search" class="form-control" placeholder="Filter text">
            </div>
            <div class="col-xs-4">
              <label for="search">items per page:</label>
              <input type="number" min="1" max="100" class="form-control" ng-model="pageSize">
            </div>
          </div>
          <br>
         <!-- <div class="panel panel-default">
            <div class="panel-body"> -->

              <ul>
                <li dir-paginate="meal in meals | filter:q | itemsPerPage: pageSize" current-page="currentPage">{{ meal }}</li>
                
              </ul>
            </div>
          <!--</div> 
        </div>-->

       
          <small>this is in "OtherController"</small>
          <div class="text-center">
          <dir-pagination-controls boundary-links="true" on-page-change="pageChangeHandler(newPageNumber)" >
          <ul class="pagination" ng-if="1 < pages.length || !autoHide">
    <li ng-if="boundaryLinks" ng-class="{ disabled : pagination.current == 1 }">
        <a href="" ng-click="setCurrent(1)">&laquo;</a>
    </li>
    <li ng-if="directionLinks" ng-class="{ disabled : pagination.current == 1 }">
        <a href="" ng-click="setCurrent(pagination.current - 1)">&lsaquo;</a>
    </li>
    <li ng-repeat="pageNumber in pages track by tracker(pageNumber, $index)" ng-class="{ active : pagination.current == pageNumber, disabled : pageNumber == '...' }">
        <a href="" ng-click="setCurrent(pageNumber)">{{ pageNumber }}</a>
    </li>

    <li ng-if="directionLinks" ng-class="{ disabled : pagination.current == pagination.last }">
        <a href="" ng-click="setCurrent(pagination.current + 1)">&rsaquo;</a>
    </li>
    <li ng-if="boundaryLinks"  ng-class="{ disabled : pagination.current == pagination.last }">
        <a href="" ng-click="setCurrent(pagination.last)">&raquo;</a>
    </li>
</ul>



          </dir-pagination-controls>
          </div>
    
       <!--</div>
    </div>
 </div>-->

  <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css" />
  <script src = "http://ajax.googleapis.com/ajax/libs/angularjs/1.2.15/angular.min.js"></script>
  <script src="js/dirPagination.js"></script>
  <script src="js/script.js"></script>

</div>



<?php



echo $OUTPUT->footer();
?>
    
