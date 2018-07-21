<?php

global $CFG;
//require_once($CFG->dirroot . '/blocks/learning_plan/renderer.php');
require_once($CFG->dirroot . '/blocks/manage/lib.php');

class block_manage_renderer extends plugin_renderer_base {   
	// function display_tabs(){
	//	  global $DB, $USER, $OUTPUT, $PAGE;
	//
	//	  $data = '';
	//	  $data = html_writer:: start_tag('div', array('id'=>'mytabs'));
	//		   $data .= html_writer:: start_tag('ul', array());
	//		   
	//				$tab1url = new moodle_url('#mycourses', array());
	//				$tab1link = html_writer:: link($tab1url, get_string('mycourses'), array());
	//				
	//				$tab2url = new moodle_url('#mylearningplans', array());
	//				$tab2link = html_writer:: link($tab2url, 'My Learing plans', array());
	//				
	//				$data .= html_writer:: tag('li', $tab1link,array());
	//				$data .= html_writer:: tag('li', $tab2link ,array());
	//				
	//		   $data .= html_writer:: end_tag('ul');
	//		   
	//		   if(is_siteadmin()){
	//				$mycourses = $DB->get_records_sql("SELECT * FROM {course} where id > 1");
	//		   }else{
	//				$mycourses = enrol_get_users_courses($USER->id);
	//		   }
	//		   
	//		   if($mycourses){
	//				$coursefileurl = $OUTPUT->pix_url('handflag');
	//				$img = html_writer:: empty_tag('img',array('src'=>$coursefileurl, 'width'=>'185px'));		
	//				
	//				$tabledata = array();
	//				$totalrows = count($mycourses);
	//				if($totalrows >= 4){
	//					 $remainder = fmod($totalrows, 4);
	//				}else{
	//					 $remainder = $totalrows;
	//				}
	//				
	//				$i = 1;
	//				$j = 1;
	//				$row = array();
	//				foreach($mycourses as $course){
	//					// if ($i == 1){
	//					//	  $row = array();
	//					// }
	//					 
	//					 
	//					// if($i == 0 || $i == 4){
	//					//	  $i = 0;
	//					//	  $allcourses .= html_writer:: start_tag('tr', array());
	//					// }
	//					//$allcourses .= html_writer:: start_tag('td', array());
	//					$allcourses = html_writer:: tag('div', $img, array());
	//					$courserecord = $DB->get_record('course', array('id'=>$course->id));
	//					$allcourses .= html_writer:: tag('div', $courserecord->fullname, array('style'=>'font-size:16px;'));
	//					if($courserecord->summary){
	//						  $allcourses .= html_writer:: tag('div', $courserecord->summary, array('class'=>'course_description'));
	//					}else{
	//						  $allcourses .= html_writer:: tag('div', '', array('class'=>'course_description'));
	//					}
	//					$row[] = $allcourses;
	//					//$allcourses .= html_writer:: end_tag('td');
	//					//if($i == 3){
	//					//	  $allcourses .= html_writer:: end_tag('tr');
	//					// }
	//					//$i++;
	//					
	//					 if($i == 4  || ($j == $totalrows)){
	//						  if($j == $totalrows){
	//							   if($remainder == 0){
	//									$tabledata[] = $row;
	//							   }elseif($remainder == 1){
	//									$row[] = '';
	//									$row[] = '';
	//									$row[] = '';
	//									$tabledata[] = $row;
	//							   }elseif($remainder == 2){
	//									$row[] = '';
	//									$row[] = '';
	//									$tabledata[] = $row;
	//							   }elseif($remainder == 3){
	//									//print_object($row);
	//									$row[] = '';
	//									$tabledata[] = $row;
	//							   }  
	//						  }else{
	//							   $tabledata[] = $row;
	//						  }
	//						  $row = array();
	//						  $i = 0;
	//					 }
	//					 $i++;
	//					 $j++;
	//					 
	//				}
	//				//$allcourses .= html_writer:: end_tag('table');
	//				$table = new html_table();
	//				$table->id = 'myenrolledcourses';
	//				$table->attributes = array('cellpadding'=>10);
	//				$table->head = array('', '', '', '');
	//				$table->data = $tabledata;
	//				$enrollcourses = html_writer:: table($table);
	//		   }else{
	//				$enrollcourses = '-- No courses --';
	//		   }			   
	//		   
	//		   $data .= html_writer:: tag('div', $enrollcourses, array('id'=>'mycourses'));
	//		   $data .= html_writer:: tag('div', $mylearnignplan_tabcontent, array('id'=>'mylearningplans'));
	//		   //$data .= html_writer:: tag('div', '<p>This is My learning tab content</p>', array('id'=>'mylearningplans'));
	//		   
	//		   
	//	  $data .= html_writer:: end_tag('div');
	//	  
	//	  return $data;
	//	  
	// }
	 
	 
	function display_tabs_and_its_content(){
		  global $DB, $USER, $OUTPUT, $PAGE;

		  $data = '';
		  $data = html_writer:: start_tag('div', array('id'=>'mytabs', 'class'=>'span12'));
		  
			        /*Commented to remove the tab by Ravi_369 */
                    
                    //$data .= html_writer:: start_tag('ul', array());
					//$tab1url = new moodle_url('#mycourses', array());
					//$tab1link = html_writer:: link($tab1url, get_string('mycourses'), array());
                    
                $systemcontext = context_system::instance();
					
					//$data .= html_writer:: tag('li', $tab1link,array());
                    
                    //if(has_capability('block/learning_plan:viewpages',$systemcontext)){
                    //    $tab2url = new moodle_url('#mylearningplans', array());
                    //    $tab2link = html_writer:: link($tab2url, get_string('my_learnig_plans', 'block_manage'), array());
                    //    $data .= html_writer:: tag('li', $tab2link ,array());
                    //}
					
					if(!is_siteadmin()){
                        $tab3url = new moodle_url('#completedcourse', array());
                        $tab3link = html_writer:: link($tab3url, get_string('completed_courses', 'block_manage'), array());
                   
                        $data .= html_writer:: tag('li', $tab3link ,array());
					}
					
                $data .= html_writer:: end_tag('ul');
			   
                //$learningplan_rendr = $PAGE->get_renderer('block_learning_plan'); 
                
               
			   $grid = $this->mycourses_tabcontent($USER->id);
			   $completed_course_tabcontent = $this->completedcourses_tabcontent($USER->id);
			   
			   $data .= html_writer:: tag('div', $grid, array('id'=>'mycourses', 'class'=>'coursesgrid_search')); // first tab content
               
                if(has_capability('block/learning_plan:viewpages',$systemcontext)){
                    $mylearnignplan_tabcontent = learning_plan_information($USER->id);
                    if($mylearnignplan_tabcontent){
                        $mylearnignplan_tabcontent = $mylearnignplan_tabcontent;
                    }else{
                        $mylearnignplan_tabcontent = html_writer:: tag('p',get_string('norecords', 'block_manage'),array('class'=>'norecords_msg'));
                    }
                    
                    $data .= html_writer:: tag('div', $mylearnignplan_tabcontent, array('id'=>'mylearningplans'));
                }
			   //$data .= html_writer:: tag('div', '<p>This is My learning tab content</p>', array('id'=>'mylearningplans'));
			   
			   if(!is_siteadmin()){
					$data .= html_writer:: tag('div', $completed_course_tabcontent, array('id'=>'completedcourse', 'class'=>'coursesgrid_search'));
			   }

			   
		  //$data .= html_writer:: end_tag('div');
		   //$data .= html_writer:: end_tag('div'); 
		  $data .= html_writer:: end_tag('div');
		  
		  return $data;
		  
	}
	 
	function mycourses_tabcontent($userid, $activate = false){
		global $DB, $OUTPUT;
		  
        if($userid == 2){
            $sql = "SELECT * FROM {course} where id > 1 AND visible = 1";
        }else{
          $sql = "SELECT c.*
                  FROM {course} AS c
                  JOIN {context} AS cxt
                  ON cxt.instanceid = c.id
                  JOIN {role_assignments} AS ras
                  ON ras.contextid = cxt.id
                  WHERE cxt.contextlevel = 50 AND ras.userid = $userid AND c.visible = 1 ORDER BY ras.timemodified DESC";
        }
			       
        $mycourses = $DB->get_records_sql($sql); // all enrolled courses
        
        if($mycourses){
         $grid='<div class="box text-shadow">
            
            <!-- demo -->
            <div id="demo" class="box jplist">
					
	            <!-- ios button: show/hide panel -->
	            <div class="jplist-ios-button">
		            <i class="fa fa-sort"></i>
		            jPList Actions
	            </div>
						
	            <!-- panel -->
	            <div class="jplist-panel box panel-top">						
                   
                    <div class="custom_page_filtes">
                        <!-- filter by title -->
                        <div class="text-filter-box">
                                
                            <i class="fa fa-search  jplist-icon"></i>
                                    
                            <!--[if lt IE 10]>
                            <div class="jplist-label">Filter by Model:</div>
                            <![endif]-->
                                    
                            <input 
                                data-path=".model_mycourses" 
                                type="text" 
                                value="" 
                                placeholder="Filter by Course" 
                                data-control-type="textbox" 
                                data-control-name="model-text-filter" 
                                data-control-action="filter"
                            />
                        </div>
                    </div>
                    
                    <div class="custom_pagenos">
                        <!-- items per page dropdown -->
                        <div 
                            class="jplist-drop-down" 
                            data-control-type="items-per-page-drop-down" 
                            data-control-name="paging" 
                            data-control-action="paging"
                            data-control-animate-to-top="true">
                                    
                            <ul>
                                <li><span data-number="4"> 4 per page </span></li>
                                <li><span data-number="8" data-default="true"> 8 per page </span></li>
                                <li><span data-number="16" > 16 per page </span></li>
                                <li><span data-number="24"> 24 per page </span></li>
                                <li><span data-number="all"> View All </span></li>
                            </ul>
                        </div>
                      
                        <!-- pagination results -->
                        <div 
                            class="jplist-label" 
                            data-type="Page {current} of {pages}" 
                            data-control-type="pagination-info" 
                            data-control-name="paging" 
                            data-control-action="paging">
                        </div>
                                    
                        <!-- pagination -->
                        <div 
                            class="jplist-pagination" 
                            data-control-type="pagination" 
                            data-control-name="paging" 
                            data-control-action="paging">
                        </div>
                    </div>
	            </div>';
	
			$grid.='<div class="list box text-shadow">';
            foreach($mycourses as $course){
                $courserecord = $DB->get_record('course', array('id'=>$course->id));
                
                $coursedetails = $DB->get_record('local_coursedetails', array('courseid'=>$course->id));

                $coursefileurl = $this->get_course_summary_file($course);
                $img = html_writer:: empty_tag('img',array('src'=>$coursefileurl, 'width'=>'100%', 'height'=>'124px'));
                
                $coursename = strip_tags($courserecord->fullname);
                $course_fullname = $coursename;
                if (strlen($coursename) > 13) { 
                    $coursename = substr($coursename, 0, 13).'...';
                    //$c_name = substr($cou rsename, 0, strrpos($coursename, ' ')).'...';
                    $course_fullname = $coursename;
                }
                      
                if($courserecord->summary){
                    $summary = $courserecord->summary;
                    $string = strip_tags($summary);

                    if (strlen($string) > 55) {
                        // truncate string
                        $stringCut = substr($string, 0, 55);
                        $string = $stringCut.'...'; 
                        //$string = substr($stringCut, 0, strrpos($stringCut, ' ')).'...'; 
                    }
                    $course_summary = $string;
                     
                }else{
                    $course_summary = '';
                }
                 
                //$viewbtnurl = new moodle_url('/course/view.php', array('id'=>$course->id));
                $courseurl = new moodle_url('/blocks/manage/courseinfo.php', array('id'=>$course->id));
                //$viewbutton = $OUTPUT->single_button($courseurl, get_string('view'));
                $viewbutton = html_writer::link($courseurl, get_string('view'), array('class'=>'custom_singlebutton'));
                
                //$grid .= '<li>';
                //$grid .= html_writer:: start_tag('div', array('class'=>'singlecourse_data'));
                    $courselink = html_writer::link($courseurl, $course_fullname, array());
                //    $grid .= html_writer:: tag('div', '<h4>'.$courselink.'</h4>', array('class'=>'enroll_coursename'));
                //    $grid .= html_writer:: tag('div', $img, array('class'=>'courseimg'));
                //    $grid .= "<div class='crse_add_info'>
                //                <div class='course_grades'>".get_string('grades', 'block_manage').": ".$coursedetails->grade."</div>
                //                <div class='course_credits'>".get_string('credits', 'block_manage').": ".$coursedetails->credits."</div>
                //            </div>";                    
                //    $grid .= html_writer:: tag('div', '<p>'.$course_summary.'</p>', array('class'=>'course_description'));
                //    $grid .= html_writer:: tag('div', $viewbutton , array('class'=>'course_description'));
                //$grid .= html_writer:: end_tag('div');
                //$grid .= '</li>';
                if(!empty($coursedetails->grade)){
                    if($coursedetails->grade == -1){
                        $coursegrade = get_string('all');
                    }else{
                        $coursegrade = $coursedetails->grade;
                    }
                }else{
                    $coursegrade = get_string('all');
                }
                
                $grid.='<!-- item -->
							<div class="list-item">		
												
								<div class="top">
									<p class="model_mycourses">
										'.$courserecord->fullname.'
									</p>					
									<p class="model" title="'.$courserecord->fullname.'">
										'.$courselink.'
									</p>
									<p>
										<span class="header production-header">'.html_writer:: tag('div', $img, array('class'=>'courseimg')).'</span>
									</p>
									
									
									<p>
										<span class="header transmission-header"></span>
										<span class="transmission" style="display: block;height: 45px;color: #083971;font-size: 12px;margin-left: 8px;">'.$course_summary.'</span>
									</p>
									<p>'.html_writer:: tag('div', $viewbutton , array('class'=>'course_description')).'</p>
									</div>
								<div class="dimensions">
									<p>
										<span class="header length-header"></span>
										<span class="length"><span class="val"></span></span>
									</p>
									<p>
										<span class="header width-header"></span>
										<span class="width"><span class="val"></span></span>
									</p>
									<p>
										<span class="header weight-header"></span>
										<span class="weight"><span class="val"></span></span>
									</p>
								</div>
							</div>';
            }
            $grid.='</div>';
             $grid.='<div class="box jplist-no-results text-shadow align-center">
                        <p>No results found</p>
                    </div>
                            
                    <!-- ios button: show/hide panel -->
                    <div class="jplist-ios-button">
                        <i class="fa fa-sort"></i>
                        jPList Actions
                    </div>
                        
                    <!-- panel -->
                    <div class="jplist-panel box panel-bottom">
                                
                        <!-- items per page dropdown -->
                        <div 
                            class="jplist-drop-down" 
                            data-control-type="items-per-page-drop-down" 
                            data-control-name="paging" 
                            data-control-action="paging"
                            data-control-animate-to-top="true">
                                    
                            <ul>
                                <li><span data-number="4"> 4 per page </span></li>
                                <li><span data-number="8"> 8 per page </span></li>
                                <li><span data-number="16" data-default="true"> 16 per page </span></li>
                                <li><span data-number="24"> 24 per page </span></li>
                                <li><span data-number="all"> View All </span></li>
                            </ul>
                        </div>					
                        
                        <!-- pagination results -->
                        <div 
                            class="jplist-label" 
                            data-type="{start} - {end} of {all}"
                            data-control-type="pagination-info" 
                            data-control-name="paging" 
                            data-control-action="paging">
                        </div>
                                    
                        <!-- pagination -->
                        <div 
                            class="jplist-pagination" 
                            data-control-animate-to-top="true"
                            data-control-type="pagination" 
                            data-control-name="paging" 
                            data-control-action="paging">
                        </div>
                                
                    </div>	
                            
                        </div>
                    </div>';
                      $grid.=html_writer::script('$(document).ready(function() {
                          $("#demo").jplist({
                                itemsBox: ".list"
                                ,itemPath: ".list-item"
                                ,panelPath: ".jplist-panel"
                            });
                       });');
                  
        }else{
            $grid = html_writer::tag('p',get_string('nocourses', 'block_manage'),array('class'=>'norecords_msg'));
        }
        ?>
			<link href="//netdna.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.css" rel="stylesheet" />
		<?php
        return $grid;
	}
	
	 
    function completedcourses_tabcontent($userid){
        global $DB, $OUTPUT;
        $sql = "SELECT c.* FROM
                  {course_completions} as cc
                  JOIN {course} as c
                  ON cc.course = c.id
                  WHERE cc.userid = $userid and c.visible = 1 AND cc.timecompleted is not NULL";
                  
        $completed_courses = $DB->get_records_sql($sql);
        if($completed_courses){			   
            $grid='<div class="box text-shadow">
            
            <!-- demo -->
            <div id="demo1" class="box jplist">
								
	            <!-- ios button: show/hide panel -->
	            <div class="jplist-ios-button">
		            <i class="fa fa-sort"></i>
		            jPList Actions
	            </div>
						
	            <!-- panel -->
	            <div class="jplist-panel box panel-top">						
                   
                    <div class="custom_page_filtes">
                        <!-- filter by title -->
                        <div class="text-filter-box">
                                
                            <i class="fa fa-search  jplist-icon"></i>
                                    
                            <!--[if lt IE 10]>
                            <div class="jplist-label">Filter by Model:</div>
                            <![endif]-->
                                    
                            <input 
                                data-path=".model_completedcourses" 
                                type="text" 
                                value="" 
                                placeholder="Filter by Course" 
                                data-control-type="textbox" 
                                data-control-name="model-text-filter" 
                                data-control-action="filter"
                            />
                        </div>
                    </div>
                    
                    <div class="custom_pagenos">
                        <!-- items per page dropdown -->
                        <div 
                            class="jplist-drop-down" 
                            data-control-type="items-per-page-drop-down" 
                            data-control-name="paging" 
                            data-control-action="paging"
                            data-control-animate-to-top="true">
                                    
                            <ul>
                                <li><span data-number="4"> 4 per page </span></li>
                                <li><span data-number="8" data-default="true"> 8 per page </span></li>
                                <li><span data-number="16" > 16 per page </span></li>
                                <li><span data-number="24"> 24 per page </span></li>
                                <li><span data-number="all"> View All </span></li>
                            </ul>
                        </div>
                  
                        <!-- pagination results -->
                        <div 
                            class="jplist-label" 
                            data-type="Page {current} of {pages}" 
                            data-control-type="pagination-info" 
                            data-control-name="paging" 
                            data-control-action="paging">
                        </div>
								
                        <!-- pagination -->
                        <div 
                            class="jplist-pagination" 
                            data-control-type="pagination" 
                            data-control-name="paging" 
                            data-control-action="paging">
                        </div>
                    </div>
	            </div>';
	
			$grid.='<div class="list box text-shadow">';
            foreach($completed_courses as $completed_course){
                // for get course summary file image
                $coursefileurl = $this->get_course_summary_file($completed_course);
                $img = html_writer:: empty_tag('img',array('src'=>$coursefileurl, 'width'=>'100%', 'height'=>'124px'));
                
                $coursedetails = $DB->get_record('local_coursedetails', array('courseid'=>$completed_course->id));
                
                $coursename = strip_tags($completed_course->fullname);
                $course_fullname = $coursename;
                if (strlen($coursename) > 13) {
                    $coursename = substr($coursename, 0, 13).'...';
                    //$c_name = substr($coursename, 0, strrpos($coursename, ' ')).'...';
                    $course_fullname = $coursename;
                }
                
                
                if($completed_course->summary){
                    $summary = $completed_course->summary;
                    $string = strip_tags($summary);

                    if (strlen($string) > 55) {
                       $stringCut = substr($string, 0, 55);
                       $string = $stringCut.'...';
                       //$string = substr($stringCut, 0, strrpos($stringCut, ' ')).'...'; 
                    }
                    $summary = $string;
                     
                }else {
                    $summary = '';
                }
                  
                $courseurl = new moodle_url('/blocks/manage/courseinfo.php', array('id'=>$completed_course->id));
                //$viewbutton = $OUTPUT->single_button($courseurl, get_string('view'));
                $viewbutton = html_writer::link($courseurl, get_string('view'), array('class'=>'custom_singlebutton'));
                
                
                //$grid .= '<li>';
                //$grid .= html_writer:: start_tag('div', array('class'=>'singlecourse_data'));
                $coursenamelink = html_writer::link($courseurl, $course_fullname, array());
                //$grid .= html_writer:: tag('div', '<h4>'.$coursenamelink.'</h4>', array('class'=>'enroll_coursename'));
                //    $grid .= html_writer:: tag('div', $img, array('class'=>'courseimg'));
                //    $grid .= "<div class='crse_add_info'>
                //                <div class='course_grades'>".get_string('grades', 'block_manage').": ".$coursedetails->grade."</div>
                //                <div class='course_credits'>".get_string('credits', 'block_manage').": ".$coursedetails->credits."</div>
                //            </div>";
                //    $grid .= html_writer:: tag('div', '<p>'.$summary.'</p>', array('class'=>'course_description'));
                //    $grid .= html_writer:: tag('div', $viewbutton , array('class'=>'course_description'));
                //$grid .= html_writer:: end_tag('div');
                //$grid .= '</li>';
                
                if(!empty($coursedetails->grade)){
                    if($coursedetails->grade == -1){
                        $coursegrade = get_string('all');
                    }else{
                        $coursegrade = $coursedetails->grade;
                    }
                }else{
                    $coursegrade = get_string('all');
                }
                
                
                             $grid.='<!-- item -->
							<div class="list-item">		
												
								<div class="top">
									<p class="model_completedcourses">
										'.$completed_course->fullname.'
									</p>				
									<p class="model" title="'.$completed_course->fullname.'">
										'.$coursenamelink.'
									</p>
									<p>
										<span class="header production-header">'.html_writer:: tag('div', $img, array('class'=>'courseimg')).'</span>
									</p>
									<p style="width:65%;float: left;margin:auto;">
										<span class="header manufacturer-header">'.get_string('grades', 'block_manage').' :</span>
										<span class="manufacturer">'.$coursegrade.'</span>
									</p>
									<p>
										<span class="header engine-header">'.get_string('credits', 'block_manage').' :</span>
										<span class="engine">'.$coursedetails->credits.'</span>
									</p>
									<p>
										<span class="header transmission-header"></span>
										<span class="transmission" style="display: block;height: 45px;color: #083971;font-size: 12px;margin-left: 8px;">'.$summary.'</span>
									</p>
									<p>'.html_writer:: tag('div', $viewbutton , array('class'=>'course_description')).'</p>
									</div>
								<div class="dimensions">
									<p>
										<span class="header length-header"></span>
										<span class="length"><span class="val"></span></span>
									</p>
									<p>
										<span class="header width-header"></span>
										<span class="width"><span class="val"></span></span>
									</p>
									<p>
										<span class="header weight-header"></span>
										<span class="weight"><span class="val"></span></span>
									</p>
								</div>
							</div>';
            }
             $grid.='</div>';
             $grid.='<div class="box jplist-no-results text-shadow align-center">
                        <p>No results found</p>
                    </div>
                            
                    <!-- ios button: show/hide panel -->
                    <div class="jplist-ios-button">
                        <i class="fa fa-sort"></i>
                        jPList Actions
                    </div>
                        
                    <!-- panel -->
                    <div class="jplist-panel box panel-bottom">
                                
                        <!-- items per page dropdown -->
                        <div 
                            class="jplist-drop-down" 
                            data-control-type="items-per-page-drop-down" 
                            data-control-name="paging" 
                            data-control-action="paging"
                            data-control-animate-to-top="true">
                                    
                            <ul>
                                <li><span data-number="4"> 4 per page </span></li>
                                <li><span data-number="8"> 8 per page </span></li>
                                <li><span data-number="16" data-default="true"> 16 per page </span></li>
                                <li><span data-number="24"> 24 per page </span></li>
                                <li><span data-number="all"> View All </span></li>
                            </ul>
                        </div>					
                        
                        <!-- pagination results -->
                        <div 
                            class="jplist-label" 
                            data-type="{start} - {end} of {all}"
                            data-control-type="pagination-info" 
                            data-control-name="paging" 
                            data-control-action="paging">
                        </div>
                                    
                        <!-- pagination -->
                        <div 
                            class="jplist-pagination" 
                            data-control-animate-to-top="true"
                            data-control-type="pagination" 
                            data-control-name="paging" 
                            data-control-action="paging">
                        </div>
                                
                    </div>	
                            
                        </div>
                    </div>';
                      $grid.=html_writer::script('$(document).ready(function() {
                          $("#demo1").jplist({
                                itemsBox: ".list"
                                ,itemPath: ".list-item"
                                ,panelPath: ".jplist-panel"
                            });
                       });');
        }else{
            $grid = html_writer:: tag('p',get_string('no_completed_courses', 'block_manage'),array('class'=>'norecords_msg'));
        }
        
        return $grid;
    }
	 
	 
    /*Get uploaded course summary uploaded file
     * @param $course is an obj Moodle course
     * @return course summary file(img) src url if exists else return default course img url
     * */
    function get_course_summary_file($course){  
        global $DB, $CFG, $OUTPUT;
        if ($course instanceof stdClass) {
            require_once($CFG->libdir . '/coursecatlib.php');
            $course = new course_in_list($course);
        }
        
        // set default course image
        $url = $OUTPUT->pix_url('/course_images/courseimg', 'local_costcenter');
        foreach ($course->get_course_overviewfiles() as $file) {
            $isimage = $file->is_valid_image();
            if($isimage){
                $url = file_encode_url("$CFG->wwwroot/pluginfile.php", '/' . $file->get_contextid() . '/' . $file->get_component() . '/' .
                                        $file->get_filearea() . $file->get_filepath() . $file->get_filename(), !$isimage);
            }else{
                $url = $OUTPUT->pix_url('/course_images/courseimg', 'local_costcenter');
            }
        }
        return $url;
    }
    
    
    function allcourses_grid($category, $userid, $type){
		
		global $CFG, $DB, $OUTPUT, $USER;
        
        $enrolled_courses = enrol_get_users_courses($USER->id, true);
        if($enrolled_courses){
            $my_enrolledcourses = array();
            foreach($enrolled_courses as $enrolled_course){
                $my_enrolledcourses[] = $enrolled_course->id;
            }
            $mycourses = implode(',', $my_enrolledcourses);
        }
        
        // *** for category wise filter
        if($category == -1){
            $cat = "";
        }else{
            $cat = " and c.category = $category";
        }
        
        // *** for course type filter
        if($type == 0){
            $coursetype = " cd.identifiedas = 1 OR cd.identifiedas = 3";
        }else{
            $coursetype = " cd.identifiedas = $type";
        }
        
        $userdata = $DB->get_record('local_userdata', array('userid'=>$USER->id));
        
        $systemcontext = context_system::instance();
        
        if (has_capability('local/costcenter:manage',$systemcontext) || is_siteadmin()) {
            $sql = "select c.* from {course} c
                join {local_coursedetails} cd
                on cd.courseid = c.id
                where c.id > 1 and c.visible = 1 $cat
                and ($coursetype)";
        }else{
            
            $sql = "select c.* from {course} c
                join {local_coursedetails} cd
                on cd.courseid = c.id
                where c.id > 1 and c.visible = 1 $cat and (cd.grade = -1 OR FIND_IN_SET( '$userdata->grade', cd.grade) OR cd.grade = ' ')
                and ($coursetype)";
        
            if($enrolled_courses){
                $sql .= " and c.id NOT IN ($mycourses)";
            }
            
        }
        
        
        
        $mycourses = $DB->get_records_sql($sql);
        if($mycourses){
            $grid = '';
            $grid .= html_writer::start_tag('div',array('class'=>'coursesgrid_search'));
            $grid .= '<input type="text" class="search" placeholder="Search" />';
            
            $grid .= "<form style='width:160px;float:right;'>
                        <label style='float: left;margin-right:10px;margin-top:6px;'>Per page: </label>
                        <select>
                          <option>8</option>
                          <option>16</option>
                          <option>20</option>
                        </select>
                      </form>";
      
            $grid .= '<div class="holder"></div>';
            
            $grid .= html_writer:: start_tag('div', array('class'=>'list span12'));
            $grid .= "<ul id='itemContainer'>";
            
            foreach($mycourses as $course){
                $courserecord = $DB->get_record('course', array('id'=>$course->id));
                
                $coursedetails = $DB->get_record('local_coursedetails', array('courseid'=>$course->id));

                $coursefileurl = $this->get_course_summary_file($course);
                $img = html_writer:: empty_tag('img',array('src'=>$coursefileurl, 'width'=>'100%', 'height'=>'124px'));
                
                $coursename = strip_tags($courserecord->fullname);
                $course_fullname = $coursename;
                if (strlen($coursename) > 13) { 
                    $coursename = substr($coursename, 0, 13).'...';
                    //$c_name = substr($cou rsename, 0, strrpos($coursename, ' ')).'...';
                    $course_fullname = $coursename;
                }
                
                      
                if($courserecord->summary){
                    $summary = $courserecord->summary;
                    $string = strip_tags($summary);

                    if (strlen($string) > 55) {
                        // truncate string
                        $stringCut = substr($string, 0, 55);
                        $string = $stringCut.'...'; 
                        //$string = substr($stringCut, 0, strrpos($stringCut, ' ')).'...'; 
                    }
                    $course_summary = $string;
                     
                }else{
                    $course_summary = '';
                }
                
                $viewbtnurl = new moodle_url('/blocks/manage/courseinfo.php', array('id'=>$course->id));
                //$viewbtnurl = new moodle_url('/course/view.php', array('id'=>$course->id));
                //if(is_siteadmin()){
                    //$viewbutton = $OUTPUT->single_button($viewbtnurl, get_string('view'));
                    $viewbutton = html_writer::link($viewbtnurl, get_string('view'), array('class'=>'custom_singlebutton'));
                //}else{
                    //if(in_array($course->id, $enrolls)){
                    //    $viewbutton = $OUTPUT->single_button($viewbtnurl, get_string('view'));
                    //}else{
                    //    $viewbtnurl = new moodle_url('/course/view.php', array('id'=>$course->id));
                    //    $enrol = $DB->get_record('enrol', array('courseid'=>$course->id, 'enrol'=>'self'));
                    //    $viewbutton = '<form action="'.$CFG->wwwroot.'/enrol/index.php" method="post" id="mform1" class="mform" accept-charset="utf-8" autocomplete="off">';
                    //    $viewbutton .= '<input type="hidden" value="'.$course->id.'" name="id">
                    //                    <input name="instance" value="'.$enrol->id.'" type="hidden">
                    //                    <input name="sesskey" value="'.sesskey().'" type="hidden">
                    //                    <input name="_qf__'.$enrol->id.'_enrol_self_enrol_form" value="1" type="hidden">
                    //                    <input name="mform_isexpanded_id_selfheader" value="1" type="hidden">
                    //                    <input type="submit" id="id_submitbutton" value="Enrol" name="submitbutton"></form>';
                    //}
                //}                
                $grid .= '<li>';
                $grid .= html_writer::start_tag('div', array('class'=>'singlecourse_data'));
                    $courseurl = new moodle_url('/blocks/manage/courseinfo.php', array('id'=>$course->id));
                    //$courseurl = new moodle_url('/course/view.php', array('id'=>$course->id));
                    $courselink = html_writer::link($courseurl, $course_fullname, array('style'=>'color:#000'));
                    $grid .= html_writer:: tag('div', '<h4>'.$courselink.'</h4>', array('class'=>'enroll_coursename'));
                    $grid .= html_writer:: tag('div', $img, array('class'=>'courseimg'));
                    
                    if(!empty($coursedetails->grade)){
                        if($coursedetails->grade == -1){
                            $coursegrade = get_string('all');
                        }else{
                            $coursegrade = $coursedetails->grade;
                        }
                    }else{
                        $coursegrade = get_string('all');
                    }
                    
                    if(!empty($coursedetails->credits)){
                        $coursecredits = $coursedetails->credits;
                    }else{
                        $coursecredits = '---';
                    }
                    
                    $grid .= "<div class='crse_add_info'>
                                <div class='course_grades'>".get_string('grades', 'block_manage').": ".$coursegrade."</div>
                                <div class='course_credits'>".get_string('credits', 'block_manage').": ".$coursecredits."</div>
                            </div>";                    
                    $grid .= html_writer:: tag('div', '<p>'.$course_summary.'</p>', array('class'=>'course_description'));
                    $grid .= html_writer:: tag('div', $viewbutton , array('class'=>'course_description'));
                $grid .= html_writer:: end_tag('div');
                $grid .= '</li>';
            }
            $grid .= '</ul>';
            $grid .= html_writer:: end_tag('div');
            $grid .= html_writer:: end_tag('div');
                  
        }else{
            $grid = html_writer::tag('p',get_string('nocourses', 'block_manage'),array('class'=>'norecords_msg'));
        }
			
		return $grid;
	}
    
    
    //function courseinfo_tabs($courseid){
    //    global $DB, $PAGE;       
    //    
    //    $tabscontent =  '<div id="coursedetails">
    //                        
    //                        <div id="courseindex">'
    //                            .$this->course_sections($courseid).
    //                        '</div>
    //                        <div id="courseilts">
    //                          
    //                        </div>
    //                    </div>';
    //                    /*.$this->course_batchesinfo($courseid).---------> This is the function which was removed from the above div to hide the data of Classroom
    //                       Commented By Ravi_369
    //                    */
    //    return  $tabscontent;
    //}
    
    
    function course_sections($courseid){
        global $DB;
        
        $sql = "select cs.id, c.id as courseid, c.fullname, c.format, c.startdate,cs.id as sectionid, cs.section, cs.name, cs.summary, cs.sequence, cs.visible as section_visible
                from {course} c
                join {course_sections} cs
                on c.id = cs.course
                where c.id = $courseid and cs.section != 0";
        
        $course_sections = $DB->get_records_sql($sql);
        
		$sql .= " AND cs.visible = 1 ";
		$visible_course_sections = $DB->get_records_sql($sql);
		
		$visible_course_sections = count($visible_course_sections);
		$section = '';
        $section_names = '';
        $section_content = '';
        $sec_num = 1;
        
        foreach($course_sections as $course_section){
            
            $section_icon = 'fa-folder';
            
			if($course_section->section_visible && !empty($course_section->sequence)){
				$section_names .= '<li>
										<a href="#section-'.$sec_num.'" role="tab" tabindex="0">
												<i class="fa '.$section_icon.' course-icon"></i><br>
												Module '.$sec_num.'
										</a>
									</li>';
			
				$section_content .= '<div id="section-'.$sec_num.'" role="tabpanel" >';
				$section_content .= '<div class="content">
										<h3 class="sectionname">'.$course_section->name.'</h3>';
				if(!empty($course_section->sequence)){
					$c_activities = explode(',', $course_section->sequence);
					if(!empty($c_activities)){
						$section_content .= '<ul class="section">';
						foreach($c_activities as $module){ //In sequence wise modules
							$module_record = $DB->get_record('course_modules', array('id'=>$module, 'visible'=>1));
							if(!empty($module_record)){
								$activity = $DB->get_record('modules', array('id'=>$module_record->module, 'visible'=>1));
								switch($activity->name){
									case 'book':
										$activity_icon_class = 'fa-book';
										break;
									case 'file':
										$activity_icon_class = 'fa-file';
										break;
									case 'folder':
										$activity_icon_class = 'fa-folder';
										break;
									case 'imscp':
										$activity_icon_class = 'fa-cubes';
										break;
									case 'kpoint':
										$activity_icon_class = 'fa-play';
										break;
									case 'label':
										$activity_icon_class = 'fa-tag';
										break;
									case 'page':
										$activity_icon_class = 'fa-file-text-o';
										break;
									case 'url':
										$activity_icon_class = 'fa-globe';
										break;
									case 'assign':
										$activity_icon_class = 'fa-arrows-h';
										break;
									case 'bigbluebuttonbn':
										$activity_icon_class = 'fa-btc';
										break;
									case 'certificate':
										$activity_icon_class = 'fa-file-picture-o';
										break;
									case 'chat':
										$activity_icon_class = 'fa-comments';
										break;
									case 'choice':
										$activity_icon_class = 'fa-question';
										break;
									case 'data':
										$activity_icon_class = 'fa-database';
										break;
									case 'external tool':
										$activity_icon_class = 'fa-puzzle-piece';
										break;
									case 'feedback':
										$activity_icon_class = 'fa-bullhorn';
										break;
									case 'forum':
										$activity_icon_class = 'fa-comment';
										break;
									case 'glossary':
										$activity_icon_class = 'fa-file-word-o';
										break;
									case 'lti':
										$activity_icon_class = 'fa-user';
										break;
									case 'lesson':
										$activity_icon_class = 'fa-file-text-o';
										break;
									case 'quiz':
										$activity_icon_class = 'fa-check-square';
										break;
									case 'scorm':
										$activity_icon_class = 'fa-inbox';
										break;
									case 'secured pdf':
										$activity_icon_class = 'fa-file-pdf-o';
										break;
									case 'survey':
										$activity_icon_class = 'fa-bar-chart-o';
										break;
									case 'wiki':
										$activity_icon_class = 'fa-wikipedia-w';
										break;
									case 'workshop':
										$activity_icon_class = 'fa-users';
										break;
									default:
										$activity_icon_class = 'fa-book';
								}
								if(!empty($activity)){
									$activity_name = $DB->get_record($activity->name, array('id'=>$module_record->instance));
									$activity_icon = '<i class="iconlarge activityicon fa-2x fa '.$activity_icon_class.' iconcourse success" title="'.$activity_name->name.'" role="presentation"></i>';
									$section_content .= "<li class='activity'>".$activity_icon.$activity_name->name."</li>";
								}
							}
						}
						$section_content .= '</ul>';
					}
					
				}else{
					$section_content .= "<p class='sectioninfo'> -- No activities here --</p>";
				}
				$section_content .= '</div>';
				$section_content .= '</div>';
			}else{
				//hidden and no activities
			}
            
            $sec_num++;
        }
        
		$section .= "<div id='courseallsections'>"; 
		
		if($visible_course_sections > 5){
			$section .= '<span class="leftArrow_container"><i id="leftArrow2" class="fa fa-angle-left leftArrow"></i></span>';
		}
        $section .= "<ul>";
        $section .= $section_names;
        $section .= "</ul>";
		if($visible_course_sections > 5){
			$section .= '<span class="rightArrow_container"><i id="rightArrow2" class="fa fa-angle-right rightArrow"></i></span>';
		}
		$section .= '<div>'.$section_content.'</div>';
        
		$section .= "</div>";
				
		return $section;
    }
    
    
    public function course_batchesinfo($id) {
        global $DB, $USER, $OUTPUT, $PAGE;
        
        $course_ilts = $DB->get_records('local_facetoface_courses', array('courseid'=>$id));
        if($course_ilts){
            $details = '';
            foreach($course_ilts as $course_ilt){        
        
                $batchinfo = $DB->get_record('facetoface', array('id'=>$course_ilt->batchid));
                $sql = "select fc.batchid, c.fullname, c.shortname, c.duration
                        from {local_facetoface_courses} as fc
                        join {course} as c
                        on fc.courseid = c.id
                        where fc.batchid = $course_ilt->batchid";
                $batchcourse = $DB->get_record_sql($sql);
            
                if($batchcourse->duration){
                    $hours = floor($batchcourse->duration/ 60);
                    $minutes = ($batchcourse->duration % 60);
                    if(empty($batchcourse->duration)){
                        $coursename_duration = get_string('not_assigned', 'facetoface');
                    }else{
                        $coursename_duration=$hours.': '.$minutes." hrs";
                    }
                }else{
                    $coursename_duration = get_string('not_assigned', 'facetoface');
                }
                
                // for enroll and Nominate users buttons
                $sql = "SELECT f.* from {facetoface} f
                        JOIN {local_facetoface_courses} fc
                        ON f.id=fc.batchid where f.id = $course_ilt->batchid";
                $facetoface = $DB->get_record_sql($sql);
                $exist = $DB->get_record('local_facetoface_users',array('userid'=>$USER->id,'f2fid'=>$course_ilt->batchid));
                
                if($exist){
                    $url = new moodle_url('/mod/facetoface/my_sessions.php', array('id'=>$USER->id,'sessiontype'=>1));
                    //$viewbutton = $OUTPUT->single_button($url, get_string('alreadysignup','facetoface'));
                    $viewbutton = html_writer::link($url, get_string('alreadysignup','facetoface'), array('class'=>'link_color'));
                }else{
                    if($facetoface->active == 1 || $facetoface->active == 8){
                        $user_location = $DB->get_field('local_userdata', 'location', array('userid'=>$USER->id));
                        
                        $ilt_empl_locations = explode(',', $batchinfo->training_at);
                        
                        if(in_array($user_location, $ilt_empl_locations)){
                            $local_facetoface_course=$DB->get_record_sql("SELECT courseid FROM {local_facetoface_courses} WHERE batchid=$facetoface->id");
                            $coursedetails = $DB->get_field('local_coursedetails','prerequisite_courses' ,array('courseid'=>$local_facetoface_course->courseid));
                            $completed = null;
                            $coursedetail = null;
                            if($coursedetails !=null){
                                $coursedetail = explode(',',$coursedetails);
                                $sql = "SELECT userid FROM {course_completions} where course in ($coursedetails) and timecompleted !='' "; 
                                $completed = $DB->get_records_sql($sql);
                            }
                            if(count($coursedetail) == count($completed)){
                                $url = new moodle_url('/mod/facetoface/batchinfo.php', array('id'=>$course_ilt->batchid,'signup_id'=>$course_ilt->batchid));
                                $viewbutton = html_writer::link($url, get_string('enroll','facetoface'), array('id'=>'iltenrol_confirm'.$batchinfo->id, 'class'=>'link_color'));
                                $PAGE->requires->event_handler('#iltenrol_confirm'.$batchinfo->id, 'click', 'M.util.moodle_show_confirmation_dialog', array('message' => get_string('enrolconfirm','facetoface'), 'callbackargs' => array('confirmid' =>$batchinfo->id))); 
                            }else{
                                $viewbutton = $OUTPUT->help_icon('enrollnoteligible', 'facetoface');
                            }
                        }else{
                            $viewbutton = '';
                        }
                    }else{
                        $viewbutton = '';
                    }
                }
                
                $userdepartment_super=$DB->get_record('local_userdata',array('supervisorid'=>$USER->id));
                if(!empty($userdepartment_super)){
                    if($facetoface->active == 1 || $facetoface->active == 8){
                        $url = new moodle_url('/mod/facetoface/users_batches.php', array('batchid'=>$id,'add_ownusers'=>1));
                        $viewbutton1 = html_writer::link($url, 'Nominate Users', array('class'=>'link_color'));
                    }else{
                        $viewbutton1 = '';
                    }
                }else{
                    $viewbutton1 = '';  
                }
                
                $grid = '';
                $grid .= html_writer:: tag('div', $viewbutton , array('class'=>'enrollbutton'));
                  $grid .= html_writer:: tag('div', $viewbutton1 , array('class'=>'enrollbutton'));
                $grid .= html_writer:: end_tag('div');
                
                $batchtrainer = $DB->get_record('user', array('id'=>$batchinfo->trainerid));            
            
                $details .= "<div class='course_ilts'>
                            <table id='batchinfo'>
                                <tr>
                                    <td style='width:40%;'>
                                        <div><span class='headlabel'>Classroom name: </span>".$batchinfo->name."</div>
                                        <div><span class='headlabel'>Course-Duration: </span>".$coursename_duration."</div>
                                        <div><span class='headlabel'>Creator: </span>".$batchtrainer->firstname." ".$batchtrainer->lastname."</div>
                                        
                                    </td>
                                    <td style='width:40%;'>
                                        <div><span class='headlabel'>Start Date : </span>".date('d M Y H:i a', $batchinfo->startdate)."</div>
                                        <div><span class='headlabel'>End Date: </span>".date('d M Y H:i a', $batchinfo->enddate)."</div>
                                    </td>
                                    <td style='width:20%;'>".$grid."</td>
                                </tr>
                            </table>
                            <div class='emp_locations'>
                                <span class='headlabel'>Employee Locations: </span>".$facetoface->training_at."
                            </div>
                        </div>";
            }
        }else{
            $details = html_writer::tag('div', 'No records', array('class'=>'emptymsg'));
        }
        return $details;
    }
}

