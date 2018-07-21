<?php
require_once('../../config.php');
global $CFG, $DB, $USER, $PAGE;
$PAGE->requires->jquery();
$PAGE->requires->js('/local/costcenter/js/select2.full.js',true);
$PAGE->requires->js('/local/costcenter/js/jquery.dataTables.min.js',true);
$PAGE->requires->js('/local/costcenter/js/custom.js',true);
$start = optional_param('start',0, PARAM_INT);
$PAGE->set_context(context_system::instance());
$renderer = $PAGE->get_renderer('local_costcenter');

if($start >= 0){
    echo $renderer->courseallocation(false,$start);
}