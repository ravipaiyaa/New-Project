<?php
global $USER, $DB,$PAGE,$CFG, $OUTPUT;
require_once('../../config.php');
$systemcontext = context_system::instance();
$PAGE->set_pagelayout('admin');
/* ---check the context level of the user and check whether the user is login to the system or not--- */
$PAGE->set_context($systemcontext);
require_login();

/* ---second level of checking--- */
$PAGE->set_url('/local/costcenter/module_settings.php');
/* ---Header and the navigation bar--- */
$PAGE->set_heading($SITE->fullname);
$PAGE->set_title(get_string('moduleconfig', 'local_costcenter'));
$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('modulesettings', 'local_costcenter'),new moodle_url('/local/costcenter/module_settings.php'));
echo $OUTPUT->header();
 $page_header =  "<h2 class='tmhead2' id='local_costcenter_heading'>".get_string('modulesettings', 'local_costcenter')."</h2>";

echo $page_header;
$data_submitted=data_submitted();

if(!empty($data_submitted)){
    $submitted_datas=$data_submitted->module;
    foreach($submitted_datas as $moduleid=>$submitted_data){
        $insert_record=new stdClass();
        $insert_record->moduleid=$moduleid;
        $insert_record->costcenters=implode(',',$submitted_data);
        $DB->insert_record('local_moduleconfig', $insert_record); 
    }
}
    $modules="select id,name from {modules}";
    $costcenters="select id,fullname from {local_costcenter} where parentid=0";
    $modules_list=$DB->get_records_sql($modules);
    $costcenters_list=$DB->get_records_sql($costcenters);
    $count=count($costcenters_list);

    $table = new html_table();
    //-------loop for dynamic headings----------
    $view=array();
    for($first=0;$first<=$count;$first++){
        $view[]="CostCenter".$first;
    }
        $data=array();
     foreach($modules_list as $module_lists){
            $list=array();
            $i = 2;
            $costcenters_list_check=array();
            foreach($costcenters_list as $costcenters_lists){
                 $costcenters_list_check[$i]='<input type="checkbox" name="module['.$module_lists->id.'][]" value="'.$costcenters_lists->id.'">'.$costcenters_lists->fullname;
                 $i++;
            }
            $list[]= $module_lists->name;
            $data[]=$list+$costcenters_list_check;
       }
        $table->head = array('Module Name')+$view;  
        $table->data=$data;
        echo '<form method="post">'.html_writer::table($table).'<input type="submit" value="Submit"></form>';
        echo $OUTPUT->footer();
?>