<?php
require_once('../../config.php');
global $CFG,$OUTPUT,$DB;
require_once($CFG->dirroot.'/course/edit_form.php');
require_once($CFG->dirroot.'/course/lib.php');
//echo $OUTPUT->header();
//class local_course extends course_edit_form{
//    
//    public function definition(){
//        global $DB;
//        $mform    = $this->_form;
        $category = $DB->get_record('course_categories', array('id'=>1), '*', MUST_EXIST);
        $obj = new course_edit_form(null,array('category'=>$category));
//        $mform->_form    =$obj;
//        $mform->addElement('text','fullname', get_string('fullnamecourse'),'maxlength="254" size="50"');
//        
//    }
//    
//}
 //$category = $DB->get_record('course_categories', array('id'=>1), '*', MUST_EXIST);
//$local_course = new local_course(null);
print_object($obj);

$obj->_form->addElement('text','fullname', get_string('fullnamecourse'),'maxlength="254" size="50"');
$obj->display();