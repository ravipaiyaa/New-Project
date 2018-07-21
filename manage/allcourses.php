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
 * @copyright  2017  hemalathacarun <hemalatha@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
global $DB, $USER, $CFG, $PAGE, $OUTPUT;
require_once($CFG->dirroot . '/blocks/manage/renderer.php');
require_once($CFG->dirroot . '/local/includes.php');



$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
require_login();
$PAGE->requires->jquery();

$PAGE->set_url('/local/manage/allcourses.php');
$PAGE->set_title(get_string('e_learning_courses','block_costcenterstructure'));
$PAGE->set_pagelayout('context_image');
$PAGE->navbar->add(get_string('e_learning_courses','block_costcenterstructure'));
$category = optional_param('category', -1, PARAM_INT);
$type = optional_param('type', 0, PARAM_INT);
$global_search = optional_param('g_search', 0, PARAM_RAW);

$renderer = $PAGE->get_renderer('block_manage');
$includes = new user_course_details();
$PAGE->requires->css('/blocks/manage/css/bootstrap.min.css');
echo $OUTPUT->header();


$PAGE->requires->js('/blocks/manage/js/angular.min.js');
$PAGE->requires->js('/blocks/manage/js/custom.js');
$PAGE->requires->js('/blocks/manage/js/dirPagination.js');
$options = array();

				
	echo "<div ng-app = 'hello' >";			
	echo "<div ng-controller = 'courseController'>";			
	echo "<div class='col-md-12 rowtab'><div class='col-md-2'></div>
                    <div class='col-md-8'>
                        <a ng-href='#here' ng-click='tabfunction(1, 0, null,null, 0,1)' class='tab1'><div class='col-lg-3 col-md-3 col-xs-12 course-category' data-color='red' align='center'>
                            <i class='fa fa-desktop'></i><br>
                            <span>E-LEARNING</span>
                        </div></a>
                        <a ng-href='#here' ng-click='tabfunction(2, 0, null,null, 0,1)' class='tab2'><div class='col-lg-3 col-md-3 col-xs-12 course-category' data-color='yellow' align='center'>
                            <i class='fa fa-graduation-cap'></i><br>
                            <span>CLASSROOM</span>
                        </div></a>
                        <a ng-href='#here' ng-click='tabfunction(3, 0, null,null, 0,1)' class='tab3' ><div class='col-lg-3 col-md-3 col-xs-12 course-category' data-color='blue' align='center'>
                            <i class='fa fa-map'></i><br>
                            <span>LEARNING PATH</span>
                        </div></a>
                       <a ng-href='#here' ng-click='tabfunction(4, 0, null,null, 0,1)'  class='tab4'> <div class='col-lg-3 col-md-3 col-xs-12 course-category' data-color='green' align='center'>
                            <i class='fa fa-list-ul'></i><br>
                            <span>ALL</span>
                        </div></a>
                    </div>
                    <div class='col-md-2'></div></div>";
	echo "<div class=list box text-shadow>";
	
		echo "<div id=demo class='box jplist'>";
			//echo "<div ng-app = '' ng-controller = 'courseController' ng-init='init(1)'>  ";
			 echo "<div   class=list ng-init='init(1)'>";
				 // echo "<div ng-repeat = 'employee in employees'>";
					//   echo "<div class=top>";
                  
              echo  '<div class="col-lg-12 col-xs-12">
                        <div class="col-lg-1 col-xs-12"></div>
                        <div id="avaliable_courses" class="col-lg-10 col-xs-12">
                            <div id="avaliable_courses" class="col-lg-6 col-xs-12">
                                <span class="fa fa-search"></span>
                                <input  id="search" ng-keyup="filterbyname(tab)" class="form-control" placeholder="Search......">
                            </div>';
              
                    echo  $response =block_manage_get_enrollment_selectbox('{{tab}}');
                    echo '<div ng-if=" tab == 1">';
                    echo $response = block_manage_get_elearning_courses_dropdown();
                    echo '</div>';
                    echo '<div ng-if=" tab == 2" class="pull-left ml-10" id="categoryid_select_container">';
                    echo $response = block_manage_get_classroom_courses_dropdown();
                    echo '</div>';
                    
                    echo '</div>
                        </div>';
                    
                    echo "<div class='col-lg-12 col-xs-12 course_view_list_container'><div class='col-lg-1 col-xs-12'></div>
                            <div class='col-lg-10 col-xs-12'>";
                    echo "<div ng-show='showLoader' class='loader_container'>
                            <img src= $CFG->wwwroot/blocks/achievements/pix/loading.gif ></img>
                    </div>";
                    
                    echo '<div ng-if="numberofrecords > 0">';/*for empty records*/
                    
                            echo "<div dir-paginate='record in courseinfo | filter:q | itemsPerPage: 3' total-items=numberofrecords class='list-item col-lg-4 col-sm-6 col-xs-12 course_view_list'  >";
                                echo  '<div ng-if="record.id >=1">';
                                echo "<div class='course-body'>";	  
                                
                                       echo  '<div ng-if=" tab == 1">';
                                       
                                       echo $response = block_manage_to_elearning_courses();
                                       echo "</div>"; // end of if condition
                                        
                                       echo  '<div ng-if=" tab == 2">';
                                       echo $response =block_manage_to_display_iltlist();
                                       echo '</div>';
                                       
                                       echo  '<div ng-if=" tab == 3">';
                                       
                                       echo $response =block_manage_to_display_learningplans();
                                     
                                       echo '</div>';
                                       
                                       echo  '<div ng-if=" tab == 4">';
                                            echo "<div ng-if='record.type == 1'>";
                                                 echo $response = block_manage_to_elearning_courses();
                                            echo "</div>";
                                            echo "<div ng-if='record.type == 2'>";
                                                 echo $response = block_manage_to_display_iltlist();
                                            echo "</div>";
                                             echo "<div ng-if='record.type == 3'>";
                                                 echo $response = block_manage_to_display_learningplans();
                                            echo "</div>";
                                       echo '</div>';
                                       
                                echo "</div>"; // end of class course-body
                                echo  "</div>"; // end of if condition to check if course info available or not  
                            echo "</div>";  //end of list-item  dir paginate
                            
                            echo "</div>";
                            //else for no of rec s
                            echo '<div ng-if="numberofrecords <= 0" >';
                                echo  '<div ng-if=" tab == 1" class="alert alert-info text-center">No e-Learning Courses Found</div>';
                                echo  '<div ng-if=" tab == 2" class="alert alert-info text-center">No Classroom Trainings Found</div>';
                                echo  '<div ng-if=" tab == 3" class="alert alert-info text-center">No Learning Plan Courses Found</div>';
                                echo  '<div ng-if=" tab == 4" class="alert alert-info text-center">No Records Found</div>';
                            echo '</div>';
                            
                        echo "</div>"; // end of class=col-md-10
                        echo "<div class='col-lg-1 col-xs-12'></div>";
                    echo "</div>"; // end  class='col-md-12	   
						   
							 
					 //  echo "</div>"; // end of top
				 //  echo "</div>"; // end of ng-repeat
				echo "</div>";  // end of list
			//echo "</div>"; // end of ng-app
		echo "</div>"; // end of demo
	echo "</div>"; // end of  list box text-shadow
    
	//--------pagination code starts from here-----------------
    echo '<div ng-if="numberofrecords > 0">';/*for empty records*/
    echo  "<div class='col-lg-12 col-xs-12'>
                <div class='col-lg-1 col-xs-12'></div>
                <div class='col-lg-10 col-xs-12 text-center'>
                    <dir-pagination-controls boundary-links='true' on-page-change='pageChangeHandler(newPageNumber, tab)' template-url='dirPagination.tpl.html'>
                    </dir-pagination-controls>
                </div> 
                <div class='col-lg-1 col-xs-12'></div>
            </div>";
    echo "</div>";//else for no of rec s
	
		  
echo "</div>"; // end of ngcontroller
echo '</div>'; // end of ng app
	
	



// Display function starts-----------------

function block_manage_to_display_learningplans(){
    global $CFG;
    
  /* 	$response ="<div class='col-md-12'><div class='col-md-1'></div><div class='col-md-10'>";
	$response .="<div dir-paginate='employee in employees | itemsPerPage: 3' total-items=numberofrecords class='list-item col-md-4'  >";
	$response .="<div class='course-body'>";	*/
			    $coursefileurl = "{{record.fileurl}}";
		        $img = html_writer:: empty_tag('img',array('src'=>$coursefileurl, 'width'=>'100%', 'height'=>'124px'));
                //<div class="course-toast" title="{{record.names}}">{{record.formattedcategoryname}}</div>
	$response ='<div class="course-img">'.$img.'
                 </div>';
                 
    $response.='<div class="coursepage-name">';
    $response.='<a class="coursesubtitle" title="{{record.name}}" ng-href="'.$CFG->wwwroot.'/local/learningplan/view.php?id={{record.id}}">{{record.fullname}}</a><br>';

    $response.='<div class="course-desc" ng-bind-html="record.description | unsafe"></div>';
    $response.='<div class="course-author">
                   <div class="row">
                       <div class="col-md-12 author-name">';
               $response .= '<div class="course_maindetails">
                                <span class="course_details pull-left">
                                    <span title="Credits" class="mr-20">
                                        <i class="fa fa-star"></i> {{record.credits}}
                                    </span>
                                    <span title="Courses Count">
                                        <i class="fa fa-desktop"></i> {{record.coursecount}}
                                    </span>
                                </span>
                                <span class="course_details pull-right" title="Learning Path course">
                                    <i class="fa fa-map"></i>
                                </span>
                            </div>';
               //$response.='<div class="course_maindetails"><span class="course_details">'.get_string('numofcourses', 'block_manage')." : {{record.coursecount}}<br></span></div>";
               //$response.='<div class="course_maindetails"><span class="course_details">'.get_string('points', 'block_manage')." : {{record.credits}}</span></div>";
               
    //$response .= "<div ng-if='record.enroll == 1'>";
    //    $response.='<a class="coursesubtitle" ng-href="'.$CFG->wwwroot.'/local/learningplan/view.php?id={{record.id}}"><button class="btn btn-sm btn-info pull-right btn-enrol">'.get_string('view_button','block_manage').'</button></a><br>';
    //$response .= "</div>";
        
    //$response .= "<div ng-if='record.enroll == 0'>";
    //    $response.='<a class="coursesubtitle" ng-href="'.$CFG->wwwroot.'/local/learningplan/view.php?id={{record.id}}&couid={{record.id}}"><button class="btn btn-sm btn-info pull-right btn-enrol">'.get_string('enrollment_button','block_manage').'</button></a><br>';
    //$response .= "</div>";
     
    //$response .= "<div ng-if='record.enroll == 2'>";
    //    $response.='<a class="coursesubtitle" ng-href="'.$CFG->wwwroot.'/local/learningplan/view.php?id={{record.id}}&couid={{record.id}}"><button class="btn btn-sm btn-info pull-right btn-enrol">Request For Approval</button></a><br>';
    //$response .= "</div>";
                 
    $response.="</div> 
                    </div>
                </div>"; // end of course author
    $response.="</div>"; // end of class coursepage-name		  
    //$response.="</div>
    //           </div>
    //           </div>"; // end of list-item  dir paginate
    //$response .="<div class='col-md-1'></div>
    //$response .= </div>"; // end  class='col-md-12
   return $response; 
}  // end of to_display_learningplans;

function block_manage_to_elearning_courses(){
 global $CFG;
	//    $response  ="<div class='col-md-12'><div class='col-md-1'></div><div class='col-md-10'>";
	//    $response .="<div dir-paginate='employee in employees | itemsPerPage: 3' total-items=numberofrecords class='list-item col-md-4'  >";
	//	$response .="<div class='course-body'>";	
			         $coursefileurl = "{{record.fileurl}}";
		             $img = html_writer:: empty_tag('img',array('src'=>$coursefileurl, 'width'=>'100%', 'height'=>'124px'));
		$response ='<div class="course-img">'.$img.'<div class="course-toast" title="{{record.categoryname}}" >{{record.formattedcategoryname}}</div></div>';
		$response .='<div class="progress progress-striped">
                        <div ng-if="record.progressbarpercent_width == 0" class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100" style="width:{{record.progressbarpercent}}%;min-width:0px;">{{record.progressbarpercent | number:0}}%</div>
                        <div ng-if="record.progressbarpercent_width == 1" class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100" style="width:{{record.progressbarpercent}}%;min-width:20px;">{{record.progressbarpercent | number:0}}%</div>
                     </div>';
										 
		$response.='<div class="coursepage-name">';
        //$response .= "<div ng-if='record.enroll == 0'>";
            $response.='<a ng-if="record.enroll == 0" class="coursesubtitle" title="{{record.coursename}}" ng-href="'.$CFG->wwwroot.'/blocks/manage/courseinfo.php?id={{record.id}}">{{record.course_fullname}}<br/></a>';
        //$response .= "</div>";
        //$response .= "<div ng-if='record.enroll == 1'>";
            $response.='<a ng-if="record.enroll == 1" class="coursesubtitle" title="{{record.coursename}}" ng-href="'.$CFG->wwwroot.'/course/view.php?id={{record.id}}">{{record.course_fullname}}<br></a>';
        //$response .= "</div>";
        $response.='<div class="course-desc"  ng-bind-html="record.summary | unsafe" ></div>';
        $response.='<div class="course-author"><div class="row"><div class="col-md-12 author-name">';
        $response.='<div class="course_maindetails">
                        <span class="pull-left">
                            <span class="course_details_new mr-20" title="Credits" >
                                <i class="fa fa-star"></i> {{record.coursecredits}}
                            </span>
                            <span class="course_details_new" title="Learning Hours" >
                                <i class="fa fa-clock-o" ></i> {{record.coursecompletiondays}}
                            </span>
                        </span>
                        <span class="pull-right">
                            <span class="course_details_new">
                                <i class="fa fa-desktop" title="e-Learning Course" ></i>
                            </span>
                        </span>
                    </div>';
        
        //$response .= "<div ng-if='record.enroll == 1'>";
        //   $response.='<a class="coursesubtitle" ng-href="'.$CFG->wwwroot.'/course/view.php?id={{record.id}}"><button class="btn btn-sm btn-info pull-right btn-enrol">'.get_string('view_button','block_manage').'</button></a><br>';                      
        //$response .= "</div>";
        
        //$response .= "<div ng-if='record.enroll == 0'>";
        //    $response.='<a class="coursesubtitle" ng-href="'.$CFG->wwwroot.'/blocks/manage/courseinfo.php?id={{record.id}}"><button class="btn btn-sm btn-info pull-right btn-enrol">'.get_string('enrollment_button','block_manage').'</button></a><br>';                      
        //$response .= "</div>";
        
        
        $response.="</div></div></div>";
		$response.="</div>";		  
		//$response.="</div></div></div>"; // end of list-item  dir paginate
		//$response .="<div class='col-md-1'></div></div>";
     return $response; 
} // end of block_manage_to_elearning_courses;


function block_manage_to_display_iltlist(){
 global $CFG;
	//    $response  ="<div class='col-md-12'><div class='col-md-1'></div><div class='col-md-10'>";
	//		$response .="<div dir-paginate='employee in employees | itemsPerPage: 3' total-items=numberofrecords class='list-item col-md-4'  >";
	//			$response .="<div class='course-body'>";	
							 $coursefileurl = "{{record.fileurl}}";
                           //  $name=$DB->get_field('local_facetoface_category',,array('id'=>record.customcategory));
							 $img = html_writer:: empty_tag('img',array('src'=>$coursefileurl, 'width'=>'100%', 'height'=>'124px'));
				$response ='<div class="course-img">'.$img.'<div class="course-toast" title="{{record.categoryname}}">{{record.formattedcategoryname}}</div></div>';			
												 
				$response.='<div class="coursepage-name">';
				$response.='<a class="coursesubtitle" title="{{record.iltformatname}}" ng-href="'.$CFG->wwwroot.'/mod/facetoface/viewinfo.php?id={{record.id}}">{{record.name}}</a><br>';
				
				//---- facetoface description--------
				$response.='<div class="course-desc" ng-bind-html="record.intro | unsafe">
							</div>';		
				
				$response.='<div class="course-author">
							<div class="row">
							<div class="col-md-12 author-name">';
				$response.= "<div class='course_maindetails'>
                                <span class='course_details' title='Start date - End date'>
                                    <i class='fa fa-calendar'></i> {{record.date}}<br>
                                </span>
                            </div>";
				$response.= "<div class='course_maindetails'>
                                <span class='course_details' title='Classroom Location'>
                                    <i class='fa fa-map-marker'></i> {{record.iltlocation}}<br>
                                </span>
                            </div>";
				$response.= "<div class='course_maindetails'>
                                <span class='course_details' title='Classroom Bands'>
                                    <i class='fa fa-circle-o-notch'></i> {{record.bands}}<br>
                                </span>
                            </div>";
				
				$response.= "<div class='course_maindetails'>
                                <span class='course_details' title='Classroom Shortname' >
                                    <i class='fa fa-slack'></i> {{record.shortname}}
                                </span>
                            </div>";
                $response.= "<div class='course_maindetails pull-right'>
                                <i class='fa fa-graduation-cap' title='Classroom Training Course'></i>
                            </div>";
				
				//$response .= "<div ng-if='record.enroll == 1'>";
                //   $response.='<a class="coursesubtitle" ng-href="'.$CFG->wwwroot.'/mod/facetoface/viewinfo.php?id={{record.id}}"><button class="btn btn-sm btn-info pull-right btn-enrol">'.get_string('view_button','block_manage').'</button></a><br>';                      
                //$response .= "</div>";
        
                //$response .= "<div ng-if='record.enroll == 0'>";
                //   $response.='<a class="coursesubtitle" ng-href="'.$CFG->wwwroot.'/mod/facetoface/viewinfo.php?id={{record.id}}"><button class="btn btn-sm btn-info pull-right btn-enrol">'.get_string('enrollment_button','block_manage').'</button></a><br>';                      
                //$response .= "</div>";    
                
				$response.="</div></div></div>";
				$response.="</div>";		  
		//		$response.="</div>";
		//		$response .="</div>"; 
		//	$response .="</div>"; // end of list-item  dir paginate
		// $response .="<div class='col-md-1'></div>";
		//
		//$response .= "</div>";
     return $response; 
} // end of block_manage_to_elearning_courses;


function block_manage_get_classroom_courses_dropdown(){
    global $DB, $CFG, $USER;   
  
            
    if (is_siteadmin()) {
     $sql="select id,fullname from {local_facetoface_category} ORDER BY fullname asc";
    }
	else{
     $costcenterid=$DB->get_field('local_userdata','costcenterid',array('userid'=>$USER->id));
     $costcenter=$DB->get_field('local_costcenter','id', array('shortname'=>'ACD'));
     $id=$DB->get_field('local_costcenter','category',array('id'=>$costcenterid));
     $sql="select id,fullname from {local_facetoface_category} where costcenterid IN ($costcenterid,$costcenter) ORDER BY fullname asc";
    }
    $categorylist = $DB->get_records_sql($sql);
    
    $response ='<select name="modelid" ng-model="modelid" id="categoryid" class="ml-10" ng-init="modelid=0"  ng-change="modelidchange(2)" >';
         $response .= "<option value=0 > ----Select Category-----</option>"; 
        foreach( $categorylist as $list){                    
                $response  .= "<option value=$list->id data-type=$list->type>$list->fullname </option>";           
        } // end  of foreach  
    $response .= "</select>";    
    
    return $response;
    
} // end of block_manage_get_elearning_courses_dropdown function





function block_manage_get_elearning_courses_dropdown(){
    global $DB, $CFG, $USER;   
  
            
    if (is_siteadmin()) {
     $sql="select id,name from {course_categories} order by name ASC";
    }
	else{
     $costcenterid=$DB->get_field('local_userdata','costcenterid',array('userid'=>$USER->id));
     $id=$DB->get_field('local_costcenter','category',array('id'=>$costcenterid));
     $sql="select id,name from {course_categories} where  parent=$id order by name ASC";
    }
    $categorylist = $DB->get_records_sql($sql);
    
    $response ='<select name="modelid" ng-model="modelid" id="categoryid" class="ml-10" ng-init="modelid=0"  ng-change="modelidchange(1)" >';
         $response .= "<option value=0 > ----Select Category-----</option>"; 
        foreach( $categorylist as $list){                    
                $response  .= "<option value=$list->id>$list->name </option>";           
        } // end  of foreach  
    $response .= "</select>";    
    
    return $response;
    
} // end of block_manage_get_elearning_courses_dropdown function

function block_manage_get_enrollment_selectbox($tab){
    $options = array('1'=>'Enrolled','2'=>'Yet to Enroll');
     $response ='<select  name="enrolltype" ng-model="enrolltype" ng-init="enrolltype=0"  id="enrolltype"  ng-change="enrolltypechange('.$tab.')" >';
            $response .= "<option value=0 > ----Select status-----</option>";  
        foreach( $options as $key=>$value){                    
                $response  .= "<option value=$key> $value </option>";           
        } // end  of foreach  
    $response .= "</select>";        
    return $response;   
}


echo $OUTPUT->footer();

	 
