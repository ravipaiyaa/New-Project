<?php
require_once('../../config.php');
require_once($CFG->dirroot.'/local/learningplan/lib.php');
global $CFG,$DB,$USER,$PAGE;
require_once($CFG->dirroot . '/local/includes.php');
require_once($CFG->dirroot.'/mod/facetoface/lib.php');
require_login();
$tab = optional_param('tab',0,PARAM_INT);
$page= optional_param('page',0, PARAM_INT);
$search= optional_param('search','', PARAM_RAW);
$category = optional_param('category',0,PARAM_INT);
$enrolltype = optional_param('enrolltype',0,PARAM_INT);



define('ELE',1);
define('ILT',2);
define('LP',3);
global $USER, $DB;


if($page>=1)
$page= $page-1;

$includes = new user_course_details();
$tabcontent = new block_managecourse_available_list($includes, $page, $search, $category, $enrolltype);



switch($tab){    

    case 1: $finalresponse=$tabcontent->get_elearning_courselist(3, $page*3);
            echo json_encode($finalresponse);
    	    break;

    case 2: $facetofacelist=$tabcontent->get_facetofacelist(3,$page*3);
            echo json_encode($facetofacelist);
            break;

    case 3: $lplist=$tabcontent->get_lplist(3,$page*3);
		    echo json_encode($lplist);
    	    break;		
    case 4: $alllist=$tabcontent->to_get_alltypesof_courses(3);		
	        echo json_encode($alllist);
    	    break; 
	        
   
} // end of switch statement 


 function to_check_imageexists_ornot($url){
	$response ='';
	//if (@getimagesize($url)) {
	 $response = $url;
	//} else {
	// $response= 0;
	//}
	return $response;
 } // end of o_check_imageexists_ornot


 class block_managecourse_available_list {
	
	public $includesobj;
	public $page;
	public $search;
	public $category;
	public $enrolltype;
	
    function __construct($includesobject, $page, $search, $category, $enrolltype){
		
		$this->includesobj = $includesobject;
		$this->page =  $page;
		$this->search = $search;
		$this->category = $category;
		$this->enrolltype = $enrolltype;
		
	} // end of constructor
	 
	 
	public function get_elearning_courselist_query( $perpage, $startlimit, $return_noofrecords=false, $returnobjectlist=false){
		global $DB,$USER;
		 $sql = "select c.*,cd.enrollstartdate,cd.enrollenddate from {course} c
			join {local_coursedetails} cd
			on cd.courseid = c.id
			where c.id > 1  and find_in_set(3,cd.identifiedas)";
         $systemcontext = context_system::instance();
      
            if(!is_siteadmin() && has_capability('local/assign_multiple_departments:manage', $systemcontext)){

            	//$userdata=$DB->get_record('local_userdata',array('userid'=>$USER->id));
                //$userdata=$DB->get_field('local_userdata','costcenterid',array('userid'=>$USER->id));
				$costcenter=$DB->get_field('local_costcenter','id',array('shortname'=>'ACD'));
				$id=$costcenter;
				$sql .=" AND cd.costcenterid IN($id) AND c.visible=1 ORDER BY c.id DESC ";
                //$sql .=" AND cd.costcenterid = $costcenter";
                
            }elseif(!is_siteadmin()){
				$userdata=$DB->get_field('local_userdata','costcenterid',array('userid'=>$USER->id));
				$costcenter=$DB->get_field('local_costcenter','id',array('shortname'=>'ACD'));
				$id=$userdata.','.$costcenter;
				$sql .=" AND cd.costcenterid IN($id)  ";
				
			}

			
			if($this->search && $this->search!='null'){
			 $sql .= " AND c.fullname LIKE '%$this->search%'";
			}
			
			if($this->category && $this->category>0){
				$sql .= " AND c.category=$this->category";								
			}
			
			if($this->enrolltype && $this->enrolltype>0){
				if($this->enrolltype==1){					
					$coursecondition= "c.id in";				
				}
				else{
					$coursecondition = "c.id not in";
				}
				
				$sql .=" AND $coursecondition (select 
					distinct e.courseid  from {enrol} e
								   JOIN {user_enrolments} ue on ue.enrolid = e.id 
					where e.courseid=c.id and ue.userid=$USER->id)";
		
			}
			$sql .= " AND c.visible=1 ORDER BY c.id DESC  ";
			//echo $sql;
			$numofcourses=$DB->get_records_sql($sql);
			$numberofrecords = sizeof($numofcourses);
            $checkingfloat = ($numberofrecords/3);
		
           /* if(is_float($checkingfloat)){
                $numberofrecords = $numberofrecords-1;
            } */
			
			
		     $sql .=" limit $startlimit,$perpage";
		    //echo '</br>';
			
			//if($startlimit <=$numberofrecords ){
                $courseslist=$DB->get_records_sql($sql);
			//}
			
			if($return_noofrecords && !$returnobjectlist){
				return  array('numberofrecords'=>$numberofrecords);
			}			
			else if($returnobjectlist && !$return_noofrecords){
				return  array('list'=>$courseslist);
			}
			else{
				if($return_noofrecords && $returnobjectlist){
					return  array('numberofrecords'=>$numberofrecords,'list'=>$courseslist);
					
				}
				
			}
           // print_object($courseslist);
		
	} // end of get_elearning_courselist_query
	 
	
    public function get_elearning_courselist($perpage, $startlimit){
		global $DB, $USER;		
           $courseslist_ar= $this->get_elearning_courselist_query( $perpage,$startlimit, true, true);
		   $courseslist=$courseslist_ar['list'];
		    
            $finalresponse= array();
            foreach ($courseslist as $course){
                  
               // $temp = new stdclass();
                $grid="";
                $courserecord = $DB->get_record('course', array('id'=>$course->id));
                $course_category = $DB->get_field('course_categories', 'name', array('id'=>$courserecord->category));                
                $coursedetails = $DB->get_record('local_coursedetails', array('courseid'=>$course->id));      
                $coursefileurl = $this->includesobj->course_summary_files($course);
               // $img = html_writer:: empty_tag('img',array('src'=>$coursefileurl, 'width'=>'100%', 'height'=>'124px'));
			    $coursefileurl=to_check_imageexists_ornot($coursefileurl);
                $course->fileurl =   $coursefileurl;
				
				$progressbarpercent=$this->includesobj->user_course_completion_progress($course->id, $USER->id);
				if(empty($progressbarpercent)){
					$course->progressbarpercent = 0;
					$course->progressbarpercent_width = 0;
				}else{
					$course->progressbarpercent = $progressbarpercent;
					$course->progressbarpercent_width = 1;
				}
				
                $coursename = $courserecord->fullname;
                if (strlen($coursename) >= 30) { 
                    //$coursename = substr($coursename, 0, 30).'...';
                    $course_fullname = substr($coursename, 0, 30).'...';
                }else{
                   $course_fullname = $coursename;
                }
                $course->id = $course->id;
                $course->coursename =$coursename;
                $course->course_fullname = $course_fullname;
                $categoryname = $course_category;
                //if (strlen($categoryname) >= 30) { 
                //    $categoryname = substr($categoryname, 0, 30).'...';
                //}
                $course->categoryname = $categoryname;
				$cat_name_len = strlen($categoryname);
				if($cat_name_len >= 30){
					$formatted_catname = substr($categoryname, 0, 30).'...';
				}else{
					$formatted_catname = $categoryname;
				}
				$course->formattedcategoryname = $formatted_catname;
                   
				if(!empty($courserecord->summary)){
                    $summary = $courserecord->summary;
                    $string = strip_tags($summary);
        
                    if (strlen($string) > 155) {
                        //truncate string
                        $stringCut = substr($string, 0, 155);
                        $string = $stringCut.'...'; 
                    }
                    $course_summary = $string;
                     
                }else{
                    $course_summary = '<p>Description is not available</p>';
                }
				//$course->summary = $this->to_display_description($courserecord->summary);
				$course->summary = $course_summary;
				
				//$course->course_summary =$course_summary;
				
                //$viewbtnurl = new moodle_url('/blocks/manage/courseinfo.php', array('id'=>$course->id));
                //$viewbutton = html_writer::link($viewbtnurl, get_string('view_details', 'block_manage'), array('class'=>'custom_singlebutton'));               
                   $courseurl = new moodle_url('/blocks/manage/courseinfo.php', array('id'=>$course->id));
				   
                   $courselink = html_writer::link($courseurl, $course_fullname, array('style'=>'color:#000;font-weight: 300;cursor:pointer;', 'title'=>$courserecord->fullname, 'class'=>'available_course_link'));
                    if(!empty($coursedetails->grade)){
                        if($coursedetails->grade == -1){
                            $coursegrade = get_string('all');
                        }else{
                            $coursegrade = $coursedetails->grade;
                        }
                    }else{
                        $coursegrade = get_string('all');
                    }
                    
                    $course->course_url =$courseurl;
                    $course->coursegrade =$coursegrade;
                    $course->courselink =$courselink;
                     
                    if(!empty($coursedetails->credits)){
                        $coursecredits = $coursedetails->credits;
                    }else{
                        $coursecredits = 'N/A';
                    }
                      $course->coursecredits =$coursecredits;
                    if(!empty($coursedetails->enrollstartdate)){
                     $enrollstartdate = date('d/m/Y', $coursedetails->enrollstartdate);
                    }else{
                     $enrollstartdate = 'N/A';
                    }
                    $course->enrollstartdate =$enrollstartdate;
					
                    if(!empty($coursedetails->enrollenddate)){
                     $enrollenddate = date('d/m/Y', $coursedetails->enrollenddate);
                    }else{
                     $enrollenddate = 'N/A';
                    }
                     $course->enrollenddate =$enrollenddate;
					// }
					if(!empty($courserecord->duration)){
						if($courserecord->duration >= 60 ){
							$hours = floor($courserecord->duration / 60);
							$minutes = ($courserecord->duration % 60);
							$hformat = $hours > 1 ? $hformat = '%01shrs': $hformat = '%01shr';
							if($minutes == NULL){
								$mformat = '';
							}else{
								$mformat = $minutes > 1 ? $mformat = '%01smins': $mformat = '%01smin';
							}
							$format = $hformat . ' ' . $mformat;
							$course->coursecompletiondays = sprintf($format, $hours, $minutes);
						}else{
							$minutes = $courserecord->duration;
							$course->coursecompletiondays = $courserecord->duration > 1 ? $courserecord->duration.'mins' : $courserecord->duration.'min';
						}
					}else{
						$course->coursecompletiondays = 'N/A';
					}
					 $coursecontext   = context_course::instance($course->id);
					 $enroll=is_enrolled($coursecontext, $USER->id);
					 $course->enroll = $enroll;
					   
                    
					 $course->type = ELE;
                    $finalresponse[]= $course;
            }
			
            $finalresponse['numberofrecords']=$courseslist_ar['numberofrecords'];
			
	
		return $finalresponse;
	} // end of function  get_elearning_courseslist
	
	
	
	public function get_lp_query($perpage, $startlimit, $return_noofrecords=false, $returnobjectlist=false){		
		global $DB, $USER;
		if(is_siteadmin()){
				$sql ="select l.*,(select count(courseid)
				       from {local_learningplan_courses} where planid=l.id) as coursecount,lpc.courseid
					   FROM {local_learningplan} as l JOIN {local_learningplan_courses} as lpc
					   ON lpc.planid=l.id";

				if($this->search && $this->search!='null'){
			    	$sql .= " AND l.name LIKE '%$this->search%'";
		        }

						
		}
        else{						
			$data=$DB->get_record('local_userdata',array('userid'=>$USER->id));
			$sql='SELECT l.id, l.name, l.description,l.approvalreqd, l.credits,l.visible,(select count(courseid)
				       from {local_learningplan_courses} where planid=l.id) as coursecount,lpc.courseid FROM {local_learningplan} AS l
				      JOIN {local_learningplan_courses} as lpc
					  ON lpc.planid=l.id  WHERE l.visible=1 AND
			    FIND_IN_SET('.$data->costcenterid.',l.costcenter)';
			if(isset($data->department) &&  $data->department>0){
			     $sql .=" AND (( l.department is not null and find_in_set('$data->department', l.department)) or l.department is null)";         
			}
			else{        
			    $sql .=" AND l.department is null";
		 	}
			
			if(isset($data->subdepartment) && $data->subdepartment>0){
				$sql .=" AND (( l.subdepartment is not null and find_in_set('$data->subdepartment', l.subdepartment)) or l.subdepartment is null)";           
			}
			else{
			    $sql .=" AND l.subdepartment is null";
			}
			if(isset($data->band) && $data->band){
				$sql .=" AND (( l.band is not null and find_in_set('$data->band', l.band)) or l.band is null)";           
			}
			else{
				$sql .=" AND l.band is null";
			}
			if(isset($data->sub_sub_department) && $data->sub_sub_department>0){
				$sql .=" AND (( l.subsubdepartment is not null and find_in_set('$data->sub_sub_department', l.subsubdepartment)) or l.subsubdepartment is null)";           
			}
		    else{
				$sql .=" AND l.subsubdepartment is null";
			}

			if($this->search && $this->search!='null'){
			 $sql .= " AND l.name LIKE '%$this->search%'";
		    } 
			
			if($this->enrolltype && $this->enrolltype>0 ){					
				if($this->enrolltype==1){					
					$sql .= " AND l.id in (select distinct planid from {local_learningplan_user} where userid=$USER->id)  or (l.id in (select distinct planid from {local_learningplan_user} where userid=$USER->id) and l.name LIKE '%$this->search%')";				
				}
				else{
					$sql .= " AND l.id not in (select distinct planid from {local_learningplan_user} where userid=$USER->id)";
				}		
			}
				
			if($this->enrolltype==0){
				if($this->search && $this->search!='null'){
				    $sql .=  " or (l.id in (select distinct planid from {local_learningplan_user} where userid=$USER->id) and l.name LIKE '%$this->search%')";	
				}
				else{
				    $sql .=  " or (l.id in (select distinct planid from {local_learningplan_user} where userid=$USER->id) )";	
				}
					
			}		
			
		}
			
				
			
		     $sql .=" group by l.id ORDER BY l.id DESC ";
			$numofcourses=$DB->get_records_sql($sql);	
			
		$numberofrecords = sizeof($numofcourses);
	
				
       /* $checkingfloat = ($numberofrecords/3);               
        if(is_float($checkingfloat)){
            $numberofrecords = $numberofrecords-1;
        } */
	     
	
		 		
		//$startlimt = $this->page*$perpage; 
		 $sql .=" limit $startlimit,$perpage";
		//   echo '</br>';
        $learning_plans=$DB->get_records_sql($sql);
		
		if($return_noofrecords && !$returnobjectlist){
			return  array('numberofrecords'=>$numberofrecords);
		}			
	    else if($returnobjectlist && !$return_noofrecords){
			return  array('list'=>$learning_plans);
		}
		else{
			if($return_noofrecords && $returnobjectlist){
				return  array('numberofrecords'=>$numberofrecords,'list'=>$learning_plans);
					
			}
				
		}	
		
	} // end of get_lp_query
	
	
    public function get_lplist($perpage, $startlimit){
		global $DB, $USER;
		$lepclass= new learningplan();
	      $learning_plans_ar=$this->get_lp_query($perpage, $startlimit, true, true);
		  $learning_plans=$learning_plans_ar['list'];
			foreach($learning_plans as $plan){
				//print_object($plan);
				if($plan->visible==1){
					
				
				$course=$DB->get_record('course', array('id'=>$plan->courseid));
				$coursefileurl = $lepclass->get_learningplansummaryfile($plan->id);
               // $img = html_writer:: empty_tag('img',array('src'=>$coursefileurl, 'width'=>'100%', 'height'=>'124px'));
			   // $coursefileurl=to_check_imageexists_ornot($coursefileurl);
			   
			   $plan_name_len = strlen($plan->name);
			   if($plan_name_len >= 30){
					$plan->fullname = substr($plan->name, 0, 30).'...';
			   }else{
					$plan->fullname = $plan->name;
			   }
			   
                $plan->fileurl =   $coursefileurl;
			    $plan->description=$this->to_display_description($plan->description);
				$enrolled =$DB->record_exists('local_learningplan_user',array('planid'=>$plan->id,'userid'=>$USER->id));
				if($plan->approvalreqd==1){
					$plan->enroll=2;
				}elseif($enrolled){
					$plan->enroll=1;
				}else{
					$plan->enroll=0;
				}
				
				$plan->type = LP;
				$finallplist[] =  $plan;
			}
	
		    $finallplist['numberofrecords']=$learning_plans_ar['numberofrecords'];
			}
		    return $finallplist; 
	} // end of get_lplist
	
	
	public function get_facetofacelist_query($perpage,$startlimit,$return_noofrecords=false, $returnobjectlist=false){
		global $DB, $USER, $CFG;
		require_once($CFG->dirroot . '/blocks/training_calendar/lib.php');
		
		//------main queries written here to fetch Classrooms or  session based on condition
	     $finalsql=block_training_calendar_user_sessions(null, true, true);
			//------if not site admin sessions list will be filter by location or bands
			//print_object($final);
		
	/*	$finalsql ="SELECT f.id, f.*, f.startdate as trainingstartdate, f.enddate as trainingenddate from {facetoface} f
		             right join {local_facetoface_users} fu on f.id =fu.f2fid and  userid=$USER->id 
		            where (f.active = 1 or f.active = 8) and ((f.capacity!=0 and (f.capacity > (select count(id) from {local_facetoface_users} u where u.f2fid=f.id))) or f.capacity=0)";
		*/
	     if(is_siteadmin()){
			if($this->search && $this->search!='null'){
				 $finalsql .= " AND f.name LIKE '%$this->search%'";
			}
		 } 
		 
		$usercontext = context_user::instance($USER->id);
		if(!is_siteadmin() || !has_capability('local/assign_multiple_departments:manage', $usercontext) || !has_capability('local/costcenter:manage', $usercontext)){
				
			   // print_object($final);
				//-----filter by costcenter
				$finalsql=block_training_filterby_costcenter($USER->id, $finalsql);
				
				//-----filter by location and bands    
				$finalsql=block_training_filterby_location($USER->id, $finalsql, true, true);
				
				//-----filter by organization, department and subdepartment if the value is setted  while creating Classroom
				$finalsql=block_training_filter_byorganization_department_subdepartment($USER->id, $finalsql);  

			    $finalsql=block_training_filterby_doj($USER->id, $finalsql);  
			    
			   //echo $final;
			    if($this->search && $this->search!='null'){
			        $finalsql .= " AND f.name LIKE '%$this->search%'";
		        }
				if($this->category && $this->category>0){
					$finalsql .= " AND f.customcategory=$this->category ";
				}	

		      
				if($this->enrolltype && $this->enrolltype>0 ){					
					if($this->enrolltype==1){
						if($this->category && $this->category>0){
						$finalsql .= " AND f.customcategory=$this->category ";
						}	
						$finalsql .= " AND f.id in (select distinct f2fid from {local_facetoface_users} where userid=$USER->id)  or (f.id in (select distinct f2fid from {local_facetoface_users} where userid=$USER->id) and f.name LIKE '%$this->search%')";				
					}else{
						$finalsql .= " AND f.id not in (select distinct f2fid from {local_facetoface_users} where userid=$USER->id)";
					}		
			    }
			//	print_object($this->category);
				if($this->enrolltype==0){
					  if($this->search && $this->search!='null'){
				      $finalsql .=  " or (f.id in (select distinct f2fid from {local_facetoface_users} where userid=$USER->id) and f.name LIKE '%$this->search%')";	
					  }
					if($this->category && $this->category>0){
					  $finalsql .= " AND f.customcategory=$this->category ";
					}
						
					 
					 
					
				}else{
					$finalsql .=  " or (f.id in (select distinct f2fid from {local_facetoface_users} where userid=$USER->id) )";	
				}
				
		}       
        $finalsql .= ' and f.active!=3 group by f.id ORDER BY f.id DESC';
	
		$numofilt=$DB->get_records_sql($finalsql);
		$numberofrecords = sizeof($numofilt);
		/*$checkingfloat = ($numberofrecords/3);               
        if(is_float($checkingfloat)){
            $numberofrecords = $numberofrecords-1;
        } */
		//echo 'numbersof records'.$numberofrecords ;
 
		
		//$startlimt = $this->page*$perpage; 
	    $finalsql .=" limit $startlimit,$perpage";
		//  echo '</br>';
			
        $facetofacelist=$DB->get_records_sql($finalsql);
		if($return_noofrecords && !$returnobjectlist){
			return  array('numberofrecords'=>$numberofrecords);
		}			
	    else if($returnobjectlist && !$return_noofrecords){
			return  array('list'=>$facetofacelist);
		}
		else{
			if($return_noofrecords && $returnobjectlist){
				return  array('numberofrecords'=>$numberofrecords,'list'=>$facetofacelist);					
			}		
		}		
		
	} // end  of  get_facetofacelist_query;
	
	
	
	public function get_facetofacelist($perpage,$startlimit){
		global $DB, $USER;
		$facetofacelist_ar =$this->get_facetofacelist_query($perpage, $startlimit, true, true);
		$facetofacelist= $facetofacelist_ar['list'];
	      
		foreach($facetofacelist as $list){
			//print_object($list);
		   $list->iltlocation=$DB->get_field('local_facetoface_institutes','fullname',array('id'=>$list->instituteid));
		  
		    $course=$DB->get_record('course', array('id'=>$list->course));
			
			$name=$DB->get_field('local_facetoface_category','fullname',array('id'=>$list->customcategory));
			
			$coursefileurl = get_ilt_attachment($list->id);
			$list->categoryname = $name;
			$categoryname = $list->categoryname;
			$cat_name_len = strlen($categoryname);
			if($cat_name_len >= 30){
				$formatted_catname = substr($categoryname, 0, 30).'...';
			}else{
				$formatted_catname = $categoryname;
			}
			$list->formattedcategoryname = $formatted_catname;
			
			$ILT_name_len = strlen($list->name);
			if($ILT_name_len >= 30){
				$list->iltformatname = substr($list->name, 0, 30).'...';
			}else{
				$list->iltformatname = $list->name;
			}
			
            $list->fileurl =   $coursefileurl;
			$list->intro=$this->to_display_description($list->intro);
			//------------------Date-----------------------
			if($list->startdate){
				$startdate = date('d M', $list->startdate);
			}
			else{
			    $startdate ='N/A';			
			}			
			if($list->enddate){
				$enddate = date('d M Y', $list->enddate);				
			}
			else{
				$endate ="N/A";
			}			
			$list->date = $startdate.' - '.$enddate;
			
			//-------bands----------------------------
			if(empty($list->bands)){
				//echo"1";
				$list->bands="N/A";
			}
			elseif($list->bands!='-1'){
				//echo"2";	
				$bands = strip_tags($list->bands);                
				if (strlen($bands) > 15) { 
					$list->bands = substr($bands, 0, 15).'...';
					
				}  
			}else{
				//echo"3";
					$list->bands='ALL';
			
			}		
			$list->type = ILT;
		
		    $enrolled =$DB->record_exists('local_facetoface_users',array('f2fid'=>$list->id,'userid'=>$USER->id));
			if($enrolled){
				$list->enroll=1;
			}else{
				$list->enroll=0;
			}
		    
		    $finallist[]= $list;	
		} // end of foreach		
		
		$finallist['numberofrecords']=$facetofacelist_ar['numberofrecords'];
	 
		return $finallist;
		
	} //end of  get_facetofacelist


    private function to_get_level_total($arraykeys, $noofrecords){
		$totalrecords=0;
		foreach($arraykeys as $key){
			$totalrecords = $totalrecords + $noofrecords[$key];		
		}	
		
		return $totalrecords;
	} // end of function
	
	
	
		private function to_get_level_startpagenumber($arraykeys, $noofrecords){
		$totalrecords=0;
		foreach($arraykeys as $key){
			$totalrecords = $totalrecords + $noofrecords[$key];		
		}
		
		$std_perpage=3;
		if($totalrecords==0){
			$level_pageno=0;
		}
		else if($totalrecords<$std_perpage){
			$level_pageno=1;
		}
		else{
		 $level_pageno =floor($totalrecords/$std_perpage);
		 $level_remainder= ($totalrecords % $std_perpage);
			//if($level_remainder){
					$level_pageno = $level_pageno+1;
			//}
		}
		
		return $level_pageno;
	} // end of function
	
	
	private function to_get_level_endpagenumber($arraykeys, $noofrecords){
		$totalrecords=0;
		foreach($arraykeys as $key){
			$totalrecords = $totalrecords + $noofrecords[$key];		
		}
		
		$std_perpage=3;
		if($totalrecords==0){
			$level_pageno=0;
		}
		else if($totalrecords<$std_perpage){
			$level_pageno=1;
		}
		else{
		 $level_pageno =floor($totalrecords/$std_perpage);
		 $level_remainder= ($totalrecords % $std_perpage);
			if($level_remainder){
					$level_pageno = $level_pageno+1;
			}
		}
		
		return $level_pageno;
	} // end of function
	
	
	
public function to_get_alltypesof_courses($perpage){		

		$courseslist_ar= $this->get_elearning_courselist_query( 1, $this->page*1, true, false);
		$learning_plans_ar=$this->get_lp_query(1, $this->page*1,true, false);
		$facetofacelist_ar =$this->get_facetofacelist_query(1, $this->page*1, true, false);

	    $noofrecords = array();		
		
		$noofrecords=array(1=>$courseslist_ar['numberofrecords'],2=>$learning_plans_ar['numberofrecords'],3=>$facetofacelist_ar['numberofrecords']);
		$countofallrecords = $courseslist_ar['numberofrecords']+ $learning_plans_ar['numberofrecords']+ $facetofacelist_ar['numberofrecords'];
		
		$i=1; $std_perpage =3;
		
		$finallist=array();
		
		//----- process of displaying first all elearning courses after completion of this moving to learning plan and facetoface Classroom
		foreach($noofrecords as $key=>$value){		
			if($i==1){				
		        // ---elearning courses  
			    $firsttotal= $value;
			    $firstlevelstart_pageno = $this->to_get_level_startpagenumber(array(1), $noofrecords);
		        $firstlevelend_pageno = $this->to_get_level_endpagenumber(array(1), $noofrecords);
			    if($value> $std_perpage){
					$firstlevel_remainder= ($value % $std_perpage);
			    }
			    if($value < $std_perpage && $value!=0){				
				    $firstlevel_space= ($std_perpage-$value);
			    }		   
		        if($this->page <= ($firstlevelend_pageno-1)){
		            if($value == 0){						
						$eleperpage=0;
						$elestartlimit=0;
						$firstlevel_remainder=0;
					}
					else if($value < $std_perpage){					
						 $eleperpage= $value;
						 $elestartlimit=0;						
					}else{						
						 $firstlevel_remainder= ($value % $std_perpage);					
						$elestartlimit = $this->page * $std_perpage;
						$eleperpage = $std_perpage;									
					}
		        }  // end of main if condition
			} // end elearning course main if condition
			    
		    if($i==2){
				$totalrecords  =$this->to_get_level_total(array(1,2), $noofrecords);
				$secondlevel_remainder= ($totalrecords % $std_perpage);
		
				$secondlevelstart_pageno = $this->to_get_level_startpagenumber(array(1,2),$noofrecords);
				$secondlevelend_pageno = $this->to_get_level_endpagenumber(array(1,2),$noofrecords);
				
			    if($this->page >=($firstlevelstart_pageno-1) && $this->page <= ($secondlevelend_pageno-1)){
			        //---- get learning plan courses---
				    if($value == 0){
						$lpperpage=0;
						$lpstartlimit=0;					
					}
					else if($value < $std_perpage){						
						$secondlevel_remainder = ($totalrecords % $std_perpage);
					
						if($firstlevel_space)
						$lpperpage=$firstlevel_space;
						else
						$lpperpage=$value;
						
						$lpstartlimit=0;
						
					}else{
						if($this->page == ($firstlevelstart_pageno-1)){
							if($firstlevel_space){
								$lpperpage =  $firstlevel_space;
							}
							else{
							$lpperpage = ($std_perpage-$firstlevel_remainder);
							}
							$lpstartlimit=0;
						}else{	
						$secondlevel_remainder= ($totalrecords % $std_perpage);
					
						if($this->page == ($firstlevelstart_pageno-1) && $firstlevel_space  ){
							$lpstartlimit = $firstlevel_space;
						}
						else{
							if($firstlevel_space){
								//------when we can accomodate(not a remainder) values in same page, so always first level page=1
								//---- so, keeping statically $this-page-1								
								$lpstartlimit =(($this->page -1)  * $std_perpage)+$firstlevel_space;								
							}
							else{
								if($firstlevelstart_pageno==0){
									$firstlevelstart_pageno=$firstlevelstart_pageno;
								}
								else{
									$firstlevelstart_pageno=($firstlevelstart_pageno-1);
								}							
								$lpstartlimit =( $this->page - ($firstlevelstart_pageno))* $std_perpage-$firstlevel_remainder;
							}
						}
						
						$lpperpage = $std_perpage;
						}
					} // end of else statement		
			    } // end of if condition
			} // end learning plan  main if condition
			if($i==3){				 
				   $thirdlevel_pageno = $this->to_get_level_endpagenumber(array(1,2,3),$noofrecords);
				   
			       if($this->page >=($secondlevelstart_pageno-1) && $this->page <= ($thirdlevel_pageno-1)){
			        //---- get learning plan courses---
				    if($value == 0){
						$iltperpage=0;
						$iltstartlimit=0;					
					}
					else if($value < $std_perpage &&  $this->page == ($secondlevelstart_pageno-1)){						
						 $thirdlevel_remainder = 0;
						 $iltperpage=$value;
						 $iltstartlimit=0;
						
					}else{
						if($this->page == ($secondlevelstart_pageno-1)){
							
							$iltperpage = ($std_perpage-$secondlevel_remainder);
							$iltstartlimit=0;
						}else{
						
					     	$thirdlevel_remainder= ($value % $std_perpage);
						     if($secondlevelstart_pageno==0){
									$secondlevelstart_pageno=$secondlevelstart_pageno;
								}
								else{
									$secondlevelstart_pageno=($secondlevelstart_pageno-1);
								}	
						
						$iltstartlimit =( $this->page - ($secondlevelstart_pageno))* $std_perpage- $secondlevel_remainder;
						$iltperpage = $std_perpage;
						}
					} // end of else statement		
			      } // end of if condition
			}	// end Classroom  main if condition		
			$i++;
		} // end of foreach
		
	    if($eleperpage)
		$courselist=$this->get_elearning_courselist($eleperpage,$elestartlimit);
		
		if($iltperpage)
		$facetofacelist=$this->get_facetofacelist($iltperpage, $iltstartlimit);
		
		if($lpperpage)
		$lplist=$this->get_lplist( $lpperpage, $lpstartlimit);
		
		
	  
		
		//$countofallrecords= $courselist['numberofrecords']+$lplist['numberofrecords']+$facetofacelist['numberofrecords'];		
		$finalcourselist =$this->get_array_format($courselist);	
		$finallplist =$this->get_array_format($lplist);
		$finalfacetofacelist =$this->get_array_format($facetofacelist);		
		$finallist = array_merge($finalcourselist,$finalfacetofacelist,$finallplist);
		
		$finallist['numberofrecords']=$countofallrecords;	
		//print_object($finallist);
		return $finallist;
	} // end of to_get_alltypesof_courses
	
	

	
	/*
	public function to_get_alltypesof_courses($perpage){
		$eleperpage=1;
		$lpperpage=1;
		$iltperpage=1;
		$courseslist_ar= $this->get_elearning_courselist_query( $eleperpage, $this->page*$eleperpage, true, false);
		$learning_plans_ar=$this->get_lp_query($lpperpage, $this->page*$lpperpage,true, false);
		$facetofacelist_ar =$this->get_facetofacelist_query($iltperpage, $this->page*$iltperpage, true, false);
      /*  echo '</br>';
		echo 'page'.$this->page;
		echo '</br>'; 
		$noofrecords=array(1=>$courseslist_ar['numberofrecords'],2=>$learning_plans_ar['numberofrecords'],3=>$facetofacelist_ar['numberofrecords']);
		//print_object($noofrecords);
		asort($noofrecords);

	   // print_object($noofrecords);
		$i=1;
		//----in starting stage all types
		//-----E-learning type  - set 1 limit  perpage , Classroom ILT type- set 1 limit per page, learning path- set 1 limit per page    
		foreach($noofrecords as $key=>$record){
			//-----it store the first less value. In first stage page limit is 1 perpage of all types
			if($i==1){				
				$firstlessvalue= $record;
			}
		
			if($i==2){
			//------when the current page is greater than the first less value then we go for  increasing limit 2 perpage 	
				$secondlessvalue = $record;
				switch($key){
					case 1: if($this->page >= $firstlessvalue)
						     $eleperpage=2;							
							 $startlimit = $firstlessvalue+($this->page- $firstlessvalue)*$eleperpage;
							 break;
							 
				    case 2:	if($this->page >= $firstlessvalue)
						     $lpperpage=2;
							 $startlimit = $firstlessvalue+($this->page- $firstlessvalue)*$lpperpage;
							 break;
							 
					case 3: if($this->page >= $firstlessvalue)
						     $iltperpage=2;
							 $startlimit = $firstlessvalue+($this->page- $firstlessvalue)*$iltperpage;
							 break;
				  
					
				}// end of switch statement
				
			   $secondvalue = ($firstlessvalue+(($secondlessvalue-$firstlessvalue)/2));
				
			}// end of if condition
			if($i==3){				
			    switch($key){
						//------when the current page is greater than the second less value then we go for  increasing limit 3 perpage 	
					case 1: if($this->page >= $secondvalue)
						     $eleperpage=3;
							 $startlimit = $secondvalue+(($this->page- $secondvalue)*$eleperpage);
							 break;
							 
				    case 2:	if($this->page >= $secondvalue)
						     $lpperpage=3;
							 $startlimit = $secondvalue+(($this->page- $secondvalue)*$lpperpage);
							 break;
							 
					case 3: if($this->page >= $secondvalue)
						     $iltperpage=3;
							 $startlimit = $secondvalue+(($this->page- $secondvalue)*$iltperpage);
							 break;
					
					
					
				} // end of switch   				
				
			} // end of if condition 
			
			$i++;
		} // end of foreach
	/*echo $eleperpage;
    echo $lpperpage;
	echo $iltperpage;
	echo '</br>';
		//
		/*echo $eleperpage;
		echo $lpperpage;
		echo $iltperpage;
		/*$courseslist_ar= $this->get_elearning_courselist_query( $eleperpage, true, false);
		$learning_plans_ar=$this->get_lp_query($lpperpage, true, false);
		$facetofacelist_ar =$this->get_facetofacelist_query($iltperpage, true, false); 
		
		
	   //public function get_lp_query($perpage, $return_noofrecords=false, $returnobjectlist=false){	
	    $courselist=$this->get_elearning_courselist($eleperpage,$startlimit);
		
		$facetofacelist=$this->get_facetofacelist($iltperpage, $startlimit);
		$lplist=$this->get_lplist( $lpperpage, $startlimit);
		 
		print_object($courselist['numberofrecords']);
		print_object($lplist['numberofrecords']);
		print_object($facetofacelist['numberofrecords']);
		
			
		$countofallrecords= $courselist['numberofrecords']+$lplist['numberofrecords']+$facetofacelist['numberofrecords'];
		
		$finalcourselist =$this->get_array_format($courselist);
	
		$finallplist =$this->get_array_format($lplist);

		$finalfacetofacelist =$this->get_array_format($facetofacelist);
	
		//print_object($finalcourselist);
		//print_object($finalfacetofacelist);
		//print_object($finallplist);
		
		$finallist = array_merge($finalcourselist,$finalfacetofacelist,$finallplist);
		//print_object($finallist);
		$checkingfloat = ($countofallrecords/$perpage);               
        if(is_float($checkingfloat)){
            $countofallrecords = $countofallrecords-1;
        }		
		$finallist['numberofrecords']=$countofallrecords;		
		return $finallist;
	} // end of to_get_alltypesof_courses 
	*/
	
	
	private function get_array_format($lists){
		$response=array();
		
		foreach($lists as $key=>$record){			
			if($record && ( is_numeric($key))){
				$response[] = $record;
				//print_object($response);				
			}			
		}

		return  $response;		
	} // end of get_array_format
	
	
	private function to_display_description($description){
		
		if(empty($description)){			
			$description= '<p>Description is not available</p>';			
		}		
		return $description;		
	} // end of to_display_description function
	
	
	
 } // end of class
