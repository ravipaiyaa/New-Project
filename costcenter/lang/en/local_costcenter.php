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
 * Language strings
 *
 * @package    local
 * @subpackage costcenter
 * @copyright  2015 Naveen <naveen@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['missingtheme'] = 'Select Theme';
$string['theme'] = 'Theme Name';
$string['msg_del_reg_schl'] = 'Hi {$a->username}<br> You are un assigned from costcenter {$a->costcentername}.';
$string['msg_add_reg_schl'] = 'Hi {$a->username}<br> You are assigned to client {$a->costcentername}.';
$string['assignrole_help'] = 'Assign a role to the user in the selected client.';
$string['assignedcostcenter'] = 'Assigned Clients';
$string['assigncostcenter_help'] = 'Assign this user to a client.';
$string['anycostcenter'] = 'Any client';
$string['campus'] = 'Campus';
$string['university'] = 'University';
$string['location'] = 'Location';
$string['costcenterlevel'] = 'clientLevel';
$string['assignedtocostcenters'] = 'Assigned to client';
$string['assigncostcenter'] = 'Assign clients';
$string['notassignedcostcenter'] = 'Sorry you are not assigned to any costcenter.';
// $string['costcentername']='costcenter / costcenter Name';
$string['costcenterscolleges'] = 'Clients';
$string['costcenterid'] = 'Clients';
$string['costcenterrequired'] = 'client field is mandatory';
$string['missingcostcenter'] = 'Please select the client';
$string['select'] = 'Select client';
$string['selectsubcostcenter']='Sub Department';
$string['costcentername'] = 'Name';
$string['universitysettings'] = 'University Settings';
$string['cobaltLMSentitysettings'] = 'Entity Settings';
$string['costcentersettings'] = 'client Settings';
$string['GPA/CGPAsettings'] = 'GPA/CGPA Settings';
$string['PrefixandSuffix'] = 'Prefix and Suffix';
$string['assignmanager_title'] = 'client : Assign Managers';
$string['pluginname'] = 'Departments';
$string['managecostcenters'] = 'Manage Departments';
$string['allowframembedding'] = 'This page allows you to manage (delete/edit) the costcenters that are defined under this institution.';
$string['description'] = 'Description';
$string['deletecostcenter'] = 'Delete Department';
$string['delconfirm'] = 'Do you really want to delete this Course?';
$string['createcostcenter'] = 'Create New +';
$string['editcostcenter'] = 'Edit Client';
$string['missingcostcentername'] = 'Please Enter department Name';
$string['viewcostcenter'] = 'View Departments';
$string['top'] = 'Top';
$string['parent'] = 'Parent';
$string['parent_help'] = "To create a New client at Parent Level, please select 'Parent' ";
$string['costcenter'] = 'Departments';
$string['assignusers'] = 'Assign Managers';
$string['viewusers'] = 'View Users';
$string['unassign'] = 'Un assign';
$string['username'] = 'Managers';
$string['noprogram'] = 'No program is assigned';
$string['nocostcenter'] = 'No client is assigned';
$string['selectcostcenter'] = 'TOP Level';
$string['createsuccess'] = 'client with name "{$a->costcenter}" created successfully';
$string['updatesuccess'] = 'client with name "{$a->costcenter}" updated successfully';
$string['deletesuccess'] = 'Deleted Successfully';
$string['deletesuccesscostcenter'] = 'Client "<b>{$a}</b>" deleted Successfully';
$string['type'] = 'Type';
$string['type_help'] = 'Please select your client Type. If it is "University" please select University as Type. If it is "Campus"  select Campus as Type.';
$string['chilepermissions'] = 'Do we need to allow the manager to see child courses of this costcenter.';
$string['create'] = 'Create Departments';
$string['update_costcenter'] = 'Update Department';
$string['view'] = 'View Departments';
$string['assignmanager'] = 'Assign Managers';
$string['info'] = 'Help';
$string['reports'] = 'Reports';
$string['alreadyassigned'] = 'Already user is assigned to selected client "{$a->costcenter}"';
$string['assignedsuccess'] = 'Successfully assigned manager to Department.';
$string['permissions'] = 'Permissions';
$string['permissions_help'] = 'Do we need to allow the manager to see child courses of this client.';
$string['programname'] = 'Program Name';
$string['unassignmanager'] = "Are you sure, you want to unassign Manager?";
$string['unassingheading'] = 'Unassign Manager';
$string['unassignedsuccess'] = 'Successfully Unassigned Manager from client';
$string['problemunassignedsuccess'] = 'There is a problem in Unassigning manager from client';
$string['assignedfailed'] = 'Error in assigning a user';
$string['cannotdeletecostcenter'] = 'As the client "{$a->scname}" has sub client, you cannot delete it. Please delete the assigned Departments or programs first and come back here. ';
$string['nousersyet'] = 'No User is having Manager Role';
$string['costcentername'] = 'Name';
$string['saction'] = 'Action';
$string['assignmanagertxt'] = "Assign the manager to a Departments by selecting the respective manager, next selecting the respective organizations and then clicking on 'Assign Manager' ";
$string['costcenter:manage'] = 'costcenter:manage';
$string['costcenter:view'] = 'costcenter:view';
$string['nopermissions'] = 'Sorry, You dont have Permissions ';
$string['errormessage'] = 'Error Message';
$string['assign_costcenter'] = 'Assigned Departments ';
$string['programsandcostcenters'] = "<h3>Programs and Departments Assigned to this costcenter</h3>";
$string['success'] = 'clients "{$a->costcenter}" successfully {$a->visible}.';
$string['failure'] = 'You can not inactivate Departments.';
/* * **strings for bulk upload*** */
$string['allowdeletes'] = 'Allow deletes';
$string['csvdelimiter'] = 'CSV delimiter';
$string['defaultvalues'] = 'Default values';
$string['deleteerrors'] = 'Delete errors';
$string['encoding'] = 'Encoding';
$string['errors'] = 'Errors';
$string['nochanges'] = 'No changes';
$string['rowpreviewnum'] = 'Preview rows';
$string['uploadcostcenters'] = 'Upload Departments';
$string['uploadcostcenter_help'] = ' The format of the file should be as follows:
* Please download sample excelsheet through button provided .
* Enter the values based upon the information provided in Information/help tab';
$string['uploadcostcenterspreview'] = 'Upload Departments preview';
$string['uploadcostcentersresult'] = 'Upload Departments results';
$string['costcenteraccountupdated'] = 'Departments updated';
$string['costcenteraccountuptodate'] = 'Departments up-to-date';
$string['costcenterdeleted'] = 'Client deleted';
$string['costcenterscreated'] = 'Client created';
$string['costcentersdeleted'] = 'Client deleted';
$string['costcentersskipped'] = 'Client skipped';
$string['costcentersupdated'] = 'Client updated';
$string['uubulk'] = 'Select for bulk costcenter actions';
$string['uubulkall'] = 'All Departments';
$string['uubulknew'] = 'New Departments';
$string['uubulkupdated'] = 'Updated Departments';
$string['uucsvline'] = 'CSV line';
$string['uuoptype'] = 'Upload type';
$string['uuoptype_addnew'] = 'Add new only, skip existing Departments';
$string['uuoptype_addupdate'] = 'Add new and update existing Departments';
$string['uuoptype_update'] = 'Update existing Departments only';
$string['uuupdateall'] = 'Override with file and defaults';
$string['uuupdatefromfile'] = 'Override with file';
$string['uuupdatemissing'] = 'Fill in missing from file and defaults';
$string['uuupdatetype'] = 'Existing costcenter details';
$string['uploadcostcenters'] = 'Upload Departments';
$string['uploadcostcenter'] = 'Upload Departments';
$string['costcenternotaddedregistered'] = 'Departments not added, Already manager';
$string['newcostcenter'] = 'New program created';
$string['parentid'] = 'Parentid';
$string['uploadcostcenterspreview'] = 'Uploaded Departments preview';
$string['visible'] = 'Visible';
$string['duration'] = 'Duration';
$string['timecreated'] = 'Time Created';
$string['timemodified'] = 'Time modofied';
$string['costcentermodified'] = 'costcenter modified';
$string['description'] = 'Description';
$string['uploadcostcenterspreview'] = 'Uploaded Departments Preview';
$string['uploadcostcenters'] = 'Upload Departments';
$string['costcenters'] = 'Departments';
$string['no_user'] = "No user is assigned till now";
$string['information'] = 'A costcenter in Cobalt Learning Management System is defined as college/institution that offers program(s). The costcenter(s) is instructed/disciplined by Instructor(s). A costcenter has its own programs and clients. ';
$string['addcostcentertabdes'] = 'This page allows you to create/define a new costcenter.<br> 
Fill in the following details and click on  create college to create a new college.';
$string['editcostcentertabdes'] = 'This page allows you to edit costcenter.<br> 
Fill in the following details and click on  Update costcenter.';
$string['asignmanagertabdes'] = 'This page allows you to assign manager(s) to the respective costcenter(s). ';
$string['eventlevel_help'] = '<b style="color:red;">Note: </b>Global level is a default event level <br />
                                             We have four levels of events
                                            <ul><li><b>Global:</b> Site level events</li><li><b>costcenter:</b> Events for particular costcenter<li><b>program:</b>Events for particular program</li><li><b>Semester:</b> Events for particular semester</li></ul>';
$string['list'] = '
<p style="text-align:justify;">We are accepting online application for the program <i>{$a->pfn}</i>
under the costcenter <i>{$a->sfn}</i> from <i>{$a->sd}</i>. Last date for online submission is <i>{$a->ed}</i>. Please click on below <i>Apply Now </i> button to submit online application.  <a href="program.php?id={$a->pid}">Readmore</a> for details.</p>';
$string['lists'] = '
<p style="text-align:justify;">We are accepting online application for the program <i>{$a->pfn}</i>
under the costcenter <i>{$a->sfn}</i> from <i>{$a->sd}</i>. Please click on below <i>Apply Now </i> button to submit online application. Click <a href="program.php?id={$a->pid}">here</a> for more details.</p>';
$string['graduatelist'] = '
<p style="text-align:justify;">Online applications will be accepted from <i>{$a->sd}</i> under the costcenter <i>{$a->sfn}</i>.
Last date for online submissions is <i>{$a->ed} </i>.  
<a href="program.php?id={$a->pid}">Readmore </a>for details.Click on <i>Apply Now</i> button to submit the online application.</p>';
$string['graduatelists'] = '
<p style="text-align:justify;">Online applications will be accepted from <i>{$a->sd}</i> under the costcenter <i>{$a->sfn}</i>. Click 
<a href="program.php?id={$a->pid}">here </a>for more details.Click on <i>Apply Now</i> button to submit the online application.</p>';
$string['offlist'] = '
<p style="text-align:justify;">We are accepting applications for the program <i>{$a->pfn}</i>
under the costcenter <i>{$a->sfn}</i> from <i>{$a->sd}</i>. Last date for online submission is <i>{$a->ed}</i>. Please click on below <i>Download </i> button to download application.  <a href="program.php?id={$a->pid}">Readmore</a> for details.</p>';
$string['offlists'] = '
<p style="text-align:justify;">We are accepting applications for the program <i>{$a->pfn}</i>
under the costcenter <i>{$a->sfn}</i> from <i>{$a->sd}</i>. Please click on below <i>Download </i> button to download application.  <a href="program.php?id={$a->pid}">Readmore</a> for details.</p>';
$string['offgraduatelist'] = '
<p style="text-align:justify;">Applications will be accepted from <i>{$a->sd}</i> under the costcenter <i>{$a->sfn}</i>.
Last date for application submissions is <i>{$a->ed} </i>.  
<a href="program.php?id={$a->pid}">Readmore </a>for details.Click on <i>Download </i> button to download the application.</p>';
$string['offgraduatelists'] = '
<p style="text-align:justify;">Applications will be accepted from <i>{$a->sd}</i> under the costcenter <i>{$a->sfn}</i>.  
<a href="program.php?id={$a->pid}">Readmore </a>for details.Click on <i>Download</i> button to download the application.</p>';
$string['applydesc'] = 'Thank you for your interest!<br>
To be a part of this costcenter, please fill in the following details and complete the admission process.<br>
You are applying to-<br>
costcenter Name :<b style="margin-left:5px;font-size:15px;margin-top:5px;">{$a->costcenter}</b><br>
Program Name :<b style="margin-left:5px;font-size:15px;">{$a->pgm}</b><br/>
Date of Application :<b style="margin-left:5px;font-size:15px;">{$a->today}</b>';
$string['pgmheading'] = 'costcenter & Program Details';
$string['reportdes'] = 'The list of accepted applicants is given below along with the registered costcenter name, program name, admission type, student type, and the status of the application.
<br>Apply filters to customize the view of applicants based on the application type, program type, costcenter, program, student type, and status.';
$string['viewapplicantsdes'] = 'The list of registered applicants is given below so as to view their applications and confirm their admission. Applicants whose details furnished do not meet the requirement can be rejected based on the rules and regulations. 
<br>Using the filters, customize the view of applicants based on the admission type, program type, costcenter, program and curriculum.
';
$string['help_des'] = '<h1>View Departments</h1>
<p>This page allows you to manage (delete/edit) the Departments that are defined under this institution.</b></p>

<h1>Add New</h1>
<p>This page allows you to create/define a new costcenter. </b></p>
<p>Fill in the following details and click on save changes to create a new costcenter.</p>
<ul>
<li style="display:block"><h4>Parent</h4>
<p>Parent denotes the main institution that can be categorized into different Departments, campus, universities etc. It can have one or multiple (child) sub-institutions.</b></p> 
<p>Select the top level or the parent costcenter under which the new costcenter has to be created. </p>
<p><b>Note*:</b> Select \'Top Level\', if the new costcenter will be the parent costcenter or the highest level under this institution.</p></li>
<li style="display:block"><h4>Type</h4> 
<p>Defines the type of institution or the naming convention you would like to apply for the above mentioned institution.</b></p>
<p><b>Campus -</b> A designation given to an educational institution that covers a large area including library, lecture halls, residence halls, student centers, parking etc.</p>
<p><b>University -</b> A designation given to an educational institution that grants graduation degrees, doctoral degrees or research certifications along with the undergraduate degrees. <Need to check/confirm></p>
<p><b>costcenter -</b> An educational institution or a part of collegiate university offering higher or vocational education. It may be interchangeable with University. It may also refer to a secondary or high costcenter or a constituent part of university.</p></li></ul>
<h1>Assign Manager</h1>
<p>This page allows you to assign manager(s) to the respective costcenter(s). </b></p>
<p>To assign manager(s), select the manager(s) by clicking on the checkbox, then select the costcenter from the given list and finally click on \'Assign Manager\'.</p>
';
$string['costcenter:create'] = 'costcenter:Create';
$string['costcenter:update'] = 'costcenter:Update';
$string['costcenter:visible'] = 'costcenter:Visible';
$string['costcenter:delete'] = 'costcenter:delete';
$string['costcenter:assignmanager'] = 'costcenter:Assign Manager to costcenter';
$string['permissions_error'] = 'Sorry! You dont have permission to access';
$string['notassignedcostcenter_ra'] = 'Sorry! You are not assigned to any costcenter/organization, Please click continue button to Assign.';
$string['notassignedcostcenter_otherrole'] = 'Sorry! You are not assigned to any costcenter/organization, Please inform authorized user(Admin or Manager) to Assign.';
$string['costcenternotfound_admin'] = 'Sorry! costcenter not created yet, Please click continue button to create.';
$string['costcenternotfound_otherrole'] = 'Sorry! costcenter not created yet, Please inform authorized user(Admin or Manager) to Crete costcenter';
$string['costcenternotcreated'] = 'Sorry! costcenter not created yet, Please click continue button to create or go to create costcenter/organization tab.';
$string['navigation_info'] = 'Presently no data is available, Click here to ';
$string['positions'] = 'Position';
$string['skillset'] = 'Skill set';
$string['subskillset'] = 'Sub skill set';
$string['batch'] = 'Batch';
$string['department'] = 'Department';
$string['shortname'] = 'Department Code';
$string['shortnametakenlp'] = 'Short name <b>"{$a}"</b> already taken ';
$string['assignemployee'] = 'Assign student';
$string['globalcourse']='Is this Global Course?';
/*added by Ravi*/
$string['addnewcourse']='Add New Course';
$string['subdepartment'] = 'Sub Department';
$string['subsubdepartment'] = 'Sub Sub Department';
$string['createnewcourse'] = 'Create New +';
$string['assignroles'] = 'Assign Roles';
$string['search'] = 'Search';
$string['moduleconfig']='Module Configuration';
$string['modulesettings']='Module COnfiguration';