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
 * General plugin functions.
 *
 * @package    local
 * @subpackage Cost center
 * @copyright  2015 Naveen <naveen@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') or die;


require_once($CFG->dirroot . '/message/lib.php');

class costcenter {
    
    /*
     * @method get_costcenter_parent Get parent of the costcenter
     * @param object $costcenters costcenter data object
     * @param array $selected Costcenter position
     * @param boolean $inctop Include default value/not
     * @param boolean $all All option to select all values/not
     * @return array List of values
     */
    function get_costcenter_parent($costcenters, $selected = array(), $inctop = true, $all = false) {
        $out = array();

        //if an integer has been sent, convert to an array
        if (!is_array($selected)) {
            $selected = ($selected) ? array(intval($selected)) : array();
        }
        if ($inctop) {
            $out[null] = '---Select---';
        }
        if ($all) {
            $out[0] = get_string('all');
        }
        if (is_array($costcenters)) {
            foreach ($costcenters as $parent) {
                // An item cannot be its own parent and cannot be moved inside itself or one of its own children
                // what we have in $selected is an array of the ids of the parent nodes of selected branches
                // so we must exclude these parents and all their children
                //add using same spacing style as the bulkitems->move available & selected multiselects
                foreach ($selected as $key => $selectedid) {
                    if (preg_match("@/$selectedid(/|$)@", $parent->path)) {
                        continue 2;
                    }
                }
                if ($parent->id != null) {
                    $out[$parent->id] = format_string($parent->fullname);
                }
            }
        }

        return $out;
    }


  /*
     * @method get_costcenter_parent Get parent of the costcenter
     * @param object $costcenters costcenter data object
     * @param array $selected Costcenter position
     * @param boolean $inctop Include default value/not
     * @param boolean $all All option to select all values/not
     * @return array List of values
     */
    function get_costcenter_userdata($costcenters, $selected = array(), $inctop = true, $all = false) {
        $out = array();

        //if an integer has been sent, convert to an array
        if (!is_array($selected)) {
            $selected = ($selected) ? array(intval($selected)) : array();
        }
        if ($inctop) {
            $out[null] = '---Select---';
        }
        if ($all) {
            $out[0] = get_string('all');
        }
        if (is_array($costcenters)) {
            foreach ($costcenters as $parent) {
                // An item cannot be its own parent and cannot be moved inside itself or one of its own children
                // what we have in $selected is an array of the ids of the parent nodes of selected branches
                // so we must exclude these parents and all their children
                //add using same spacing style as the bulkitems->move available & selected multiselects
                foreach ($selected as $key => $selectedid) {
                    if (preg_match("@/$selectedid(/|$)@", $parent->path)) {
                        continue 2;
                    }
                }
                if ($parent->id != null) {
                    $out[$parent->id] = format_string($parent->fullname);
                }
            }
        }

        return $out;
    }


    /*
     * @method get_costcenter_items Get costcenter list
     * @param boolean $fromcostcenter used to indicate called from costcenter plugin,using while error handling
     * @return list of costcenters
     * */
    function get_costcenter_items($fromcostcenter = NULL) {

        global $DB, $USER;
        $activecostcenterlist = $DB->get_records('local_costcenter', array('visible' => 1), 'sortorder, fullname');

        if (empty($fromcostcenter)) {
            if (empty($activecostcenterlist))
                print_error('notassignedcostcenter', 'local_costcenter');
        }
        if (is_siteadmin()) {
                       $sql="SELECT * from {local_costcenter} where visible=1 ORDER by sortorder,fullname ";
            $assigned_costcenters = $DB->get_records_sql($sql);
        } else {
            $sql = " SELECT distinct(s.id),s.* FROM {local_costcenter} s  where s.visible=1 AND id in(select costcenterid from {local_costcenter_permissions} where userid={$USER->id})  ORDER BY s.sortorder";
            $assigned_costcenters = $DB->get_records_sql($sql);
        }
        if (empty($fromcostcenter)) {
            if (empty($assigned_costcenters)) {
                print_error('notassignedcostcenter', 'local_costcenter');
            } else
                return $assigned_costcenters;
        } else
            return $assigned_costcenters;
    }

    function get_next_child_sortthread($parentid, $table) {
        global $DB, $CFG;
        $maxthread = $DB->get_record_sql("SELECT MAX(sortorder) AS sortorder FROM {$CFG->prefix}{$table} WHERE parentid = ?", array($parentid));
        //  echo "the parentid".$parentid;
        if (!$maxthread || strlen($maxthread->sortorder) == 0) {
            if ($parentid == 0) {
                // first top level item
                return $this->inttovancode(1);
            } else {
                // parent has no children yet
                return $DB->get_field('local_costcenter', 'sortorder', array('id' => $parentid)) . '.' . $this->inttovancode(1);
            }
        }
        return $this->increment_sortorder($maxthread->sortorder);
    }

    // Returns the manager roleid
    
    public function get_manager_roleid() {
        global $DB;
        if ($DB->record_exists('role', array('archetype' => 'manager'))) {
            $role = $DB->get_records('role', array('archetype' => 'manager'));
            return $role;
        }
    }

    /**
     * @method get_managers List of managers
     * returns array List of all the users having manager capability
     */
    public function get_managers($costcenterid) {
        global $DB, $USER;
        $managers_list = array();
        $managers = array();
        $existing_manager = $DB->get_fieldset_sql('select userid from {local_costcenter_permissions}');
        $existing_manager = implode(',',$existing_manager);
        $managerid = $DB->get_field('role','id',array('archetype' => 'manager'));
        $sql = "SELECT u.id,u.firstname,u.lastname
                  FROM {role_assignments} ra,{user} u,{local_userdata} as ud
                 where ra.roleid=:roleid AND ra.userid=u.id AND ud.userid=u.id AND ud.costcenterid = :costcenterid";
        if(!empty($existing_manager))
        $sql .=" AND u.id not in($existing_manager)";
        $managers_list = $DB->get_records_sql($sql,array('roleid'=>$managerid,'costcenterid'=>$costcenterid));
        foreach ($managers_list as $manager) {
            $managers[$manager->id] = $manager->firstname . ' ' . $manager->lastname;
        }
       
        return $managers;
    }

     /* get_assignedcostcenters get the assigned costcenters for a user. For this version it is the unique identity..
     * One user is assigned to a One costcenter     *   
     * @method get_assignedcostcenters
     * @todo Get the list of assigned costcenters of registrar
     * @return List of costcenters in the format of array
     * 
     */

    public function get_assignedcostcenters() {
        global $DB, $CFG, $USER;
        $items = array();
        //$registrarrole = $this->get_registrar_roleid();
        //    if(is_siteadmin()){
        // $sql="SELECT distinct(s.id),s.* FROM {local_costcenter} s ORDER BY s.sortorder";
        $activecostcenterlist = $DB->get_records('local_costcenter', array('visible' => 1));
        if (empty($activecostcenterlist))
            throw new costcenternotfound_exception();

        $sql = "SELECT * FROM " . $CFG->prefix . "local_costcenter_permissions WHERE userid = {$USER->id}";
        // / }
        // /  else {
        ///   $sql="SELECT distinct(s.id),s.* FROM {local_costcenter} s  where s.usermodified={$USER->id} OR id in(selectcostcenterid costcenterid from {local_costcenter_permissions} sp/where sp.costcenterid=s.id AND sp.userid={$USER->id}) ORDER BY s.sortorder //";
        //  }
        //echo $sql;
        $costcenters = $DB->get_records_sql($sql);
        if (empty($costcenters) && (!is_siteadmin()))
            print_error('notassignedcostcenter', 'local_costcenter');

        foreach ($costcenters as $costcenter) {
            $items[] = $DB->get_record('local_costcenter', array('id' => $costcenter->costcenterid, 'visible' => 1));
        }
        if (!empty($items)) {
            foreach ($items as $item) {
                //check the costcenter is allowed to access the child costcenter
                $list = array();
                if ($item->childpermission) {
                    //get te child costcenter upto only one level
                    $childs = $DB->get_records('local_costcenter', array('parentid' => $item->id, 'visible' => 1));
                    foreach ($childs as $child) {
                        $list[] = $DB->get_record('local_costcenter', array('id' => $child->id, 'visible' => 1));
                    }
                }
            }
            $items = array_merge($items, $list);
        }
        return $items;
    }

/* get_subcostcenters get the assigned costcenters for a user. For this version it is the unique identity..
     * One user is assigned to a One costcenter     *   
     * @method get_subcostcenters
     * @todo Get the list of assigned costcenters of registrar at more than level -2
     * @return List of costcenters in the format of array
     * 
     */

    public function get_subcostcenters() {
        global $DB, $CFG, $USER;
        $items = array();
        //$registrarrole = $this->get_registrar_roleid();
           if(is_siteadmin()){
         $sql="SELECT s.* FROM {local_costcenter} s where s.visible=1 AND s.depth > 2 ORDER BY s.sortorder";
        $activecostcenterlist = $DB->get_records_sql($sql);
      /*  if (empty($activecostcenterlist))
            throw new costcenternotfound_exception();

        $sql = "SELECT * FROM {local_costcenter_permissions} WHERE userid = {$USER->id}";
*/
         }
          else {
           $sql="SELECT distinct(s.id),s.* FROM {local_costcenter} s  where s.usermodified={$USER->id} OR id in(select costcenterid from {local_costcenter_permissions} sp where sp.costcenterid=s.id AND sp.userid={$USER->id} AND s.depth > 2) ORDER BY s.sortorder";
          }
        $costcenters = $DB->get_records_sql($sql);
       // if (empty($costcenters) && (!is_siteadmin()))
         //   print_error('notassignedcostcenter', 'local_costcenter');

        foreach ($costcenters as $costcenter) {
            $items[] = $DB->get_record('local_costcenter', array('id' => $costcenter->id, 'visible' => 1));
        }
        if (!empty($items)) {
            foreach ($items as $item) {
                //check the costcenter is allowed to access the child costcenter
                $list = array();
                if ($item->childpermission) {
                    //get te child costcenter upto only one level
                    $childs = $DB->get_records('local_costcenter', array('parentid' => $item->id, 'visible' => 1));
                   
                }
            }
            $items = array_merge($items, $list);
        }
        return $items;
    }
    /*
     * @method set_confirmation To show the notification messages for user actions
     * @param string $message Notification message eg:costcenter is successfully created.
     * @param string $redirect Page URL where you need to redirect the page
     * @param array $options Options for the messages to display
     */

    function set_confirmation($message, $redirect = null, $options = array()) {

        // Check options is an array
        if (!is_array($options)) {
            print_error('error:confirmationparamtypewrong', 'local_costcenterstructure');
        }

        // Add message to options array
        $options['message'] = $message;

        // Add to confirmation queue
        $this->statement_concatinate('confirmation', $options);

        // Redirect if requested
        if ($redirect !== null) {
            redirect($redirect);
            exit();
        }
    }

    public function statement_concatinate($key, $data) {
        global $SESSION;

        if (!isset($SESSION->eabyas_queue)) {
            $SESSION->eabyas_queue = array();
        }

        if (!isset($SESSION->eabyas_queue[$key])) {
            $SESSION->eabyas_queue[$key] = array();
        }

        $SESSION->eabyas_queue[$key][] = $data;
    }
    /*
     * @method get_costcenter_managers Get assigned managers for Coset center
     * @param int $costcenterid Cost center ID
     * @return string Managers with unassign icon
     */
    public function get_costcenter_managers($costcenterid) {
        global $DB, $CFG, $OUTPUT, $USER,$PAGE;
        $managerroles = $this->get_manager_roleid();
        $data = array();
        foreach ($managerroles as $manager) {
            $sql = "SELECT u.id,u.firstname,u.lastname
                      FROM {local_costcenter_permissions} sp,{user} u
                     WHERE sp.costcenterid = :costcenterid AND roleid= :roleid AND sp.userid=u.id AND u.deleted = 0";
            $managers = $DB->get_records_sql($sql,array('costcenterid'=>$costcenterid,'roleid'=>$manager->id));
            foreach ($managers as $costcentermanager) {
                $mid =html_writer::tag('a', $costcentermanager->firstname . '&nbsp' . $costcentermanager->lastname, array('href' => '' . $CFG->wwwroot . '/user/profile.php?id=' . $costcentermanager->id . '','id'=>'manager'.$costcenterid.$costcentermanager->id.''));
                if ($USER->id != $costcentermanager->id) {
                    $mid .= html_writer::link(new moodle_url('/local/costcenter/index.php'), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'alt' => get_string('unassign', 'local_costcenter'), 'title' => get_string('unassign', 'local_costcenter'), 'class' => 'iconsmall')),array('id'=>'unassign'.$costcenterid.$costcentermanager->id.''));
                    $PAGE->requires->event_handler('#unassign'.$costcenterid.$costcentermanager->id.'', 'click', 'M.util.tmahendra_show_confirm_dialog', array('message' =>  get_string('unassignmanager', 'local_costcenter'), 'callbackargs' => array('id' => $costcenterid,'extraparams'=>'userid='.$costcentermanager->id.'&unassign=1')));
                }
            $data[] = $mid;
            }
        }
        $result = implode(',', $data);
        return $result;
    }

    /**
     * Return an array containing any confirmation in $SESSION
     *
     * Should be called in the theme's header
     *
     * @return  array
     */
    public function get_confirmation() {
        return $this->statement_shift('confirmation', true);
    }

    /**
     * Return part or all of a eabyas session queue
     *
     * @param   string  $key    Queue key
     * @param   boolean $all    Flag to return entire session queue (optional)
     * @return  mixed
     */
    function statement_shift($key, $all = false) {
        global $SESSION;

        // Value to return if no items in queue
        $return = $all ? array() : null;

        // Check if an items in queue
        if (empty($SESSION->eabyas_queue) || empty($SESSION->eabyas_queue[$key])) {
            return $return;
        }

        // If returning all, grab all and reset queue
        if ($all) {
            $return = $SESSION->eabyas_queue[$key];
            $SESSION->eabyas_queue[$key] = array();
            return $return;
        }
        return array_shift($SESSION->eabyas_queue[$key]);
    }

    function inttovancode($int = 0) {
        $num = base_convert((int) $int, 10, 36);
        $length = strlen($num);
        return chr($length + ord('0') - 1) . $num;
    }

    /**
     * Convert a vancode to an integer
     * @param string $char Vancode to convert. Must be <= '9zzzzzzzzzz'
     * @return integer The integer representation of the specified vancode
     */
    function vancodetoint($char = '00') {
        return base_convert(substr($char, 1), 36, 10);
    }

    /**
     * Increment a vancode by N (or decrement if negative)
     *
     */
    function increment_vancode($char, $inc = 1) {
        return $this->inttovancode($this->vancodetoint($char) + (int) $inc);
    }

    function increment_sortorder($sortorder, $inc = 1) {
        if (!$lastdot = strrpos($sortorder, '.')) {
            // root level, just increment the whole thing
            return $this->increment_vancode($sortorder, $inc);
        }
        $start = substr($sortorder, 0, $lastdot + 1);
        $last = substr($sortorder, $lastdot + 1);
        // increment the last vancode in the sequence
        return $start . $this->increment_vancode($last, $inc);
    }

    /**
     * @method costcenter_add_intance Adds new costcenter
     * @param object $costcenter Cost center data object
     */
    public function costcenter_add_instance($costcenter) {
        global $DB, $CFG, $USER;

        if ($costcenter->parentid == 0) {
            $costcenter->depth = 1;
            $costcenter->path = '';
        } else {
            /* ---parent item must exist--- */
            $parent = $DB->get_record('local_costcenter', array('id' => $costcenter->parentid));
            $costcenter->depth = $parent->depth + 1;
            $costcenter->path = $parent->path;
        }
        /* ---get next child item that need to provide--- */
        if (!$sortorder = $this->get_next_child_sortthread($costcenter->parentid, 'local_costcenter')) {
            return false;
        }

        $costcenter->sortorder = $sortorder;
		
        $costcenters = $DB->insert_record('local_costcenter', $costcenter);
        /*
         * We dont need it anymore, Only admin can create cost centers
         *if (!is_siteadmin()) {
            $this->add_users(array($USER->id), $costcenters);
        }
        */
		//require_once($CFG->dirroot.'/course/lib.php');
        
        require_once($CFG->libdir.'/coursecatlib.php');
	$category=$DB->get_field('course_categories','id',array('idnumber'=>$costcenters->id));
	$data= new stdClass();
	$data->name=$costcenter->fullname;
	$data->idnumber=$costcenters;
        
        if(!empty($costcenter->parentid)){
            $parentids=$DB->get_field('local_costcenter','category',array('id'=>$costcenter->parentid));
            $data->parent=$parentids;
            
            
        }
        $category = coursecat::create($data, $data=NULL);
       
   
	//$category = coursecat::create($data, $data=NULL);
	   // }
       /*********added this below query and passing variable to update category in costcenter*******/
        $category_id=$DB->get_field('course_categories','id',array('idnumber'=>$costcenters));  
        $DB->set_field('local_costcenter', 'path', $costcenter->path . '/' . $costcenters, array('id' => $costcenters));
        $DB->set_field('local_costcenter', 'category', $category_id, array('id' => $costcenters));
        // exit;
        //$context->mark_dirty();
         
       // $currenturl = "{$CFG->wwwroot}/local/costcenter/index.php";
       // $conf = new object();
       // $conf->costcenter = $costcenter->fullname;
       //$message = get_string('createsuccess', 'local_costcenter', $conf);
       // $this->set_confirmation($message, $currenturl, array('style' => 'notifysuccess'));
        return $costcenters;
    }

    /**
     * @method costcenter_update_instance Updates new costcenter
     * @param int $costcenterid Cost centerid
     * @param object $newcostcenter costcenter data
     */
    public function costcenter_update_instance($costcenterid, $newcostcenter) {
        global $DB, $CFG;
        $oldcostcenter = $DB->get_record('local_costcenter', array('id' => $costcenterid));
        $currenturl = "{$CFG->wwwroot}/local/costcenter/index.php";
        /* ---check if the parentid is the same as that of new parentid--- */
        if ($newcostcenter->parentid != $oldcostcenter->parentid) {
            $newparentid = $newcostcenter->parentid;
            $newcostcenter->parentid = $oldcostcenter->parentid;
        }
        $today = time();
        $today = make_timestamp(date('Y', $today), date('m', $today), date('d', $today), 0, 0, 0);
        $newcostcenter->timemodified = $today;

        $DB->update_record('local_costcenter', $newcostcenter);
        if (isset($newparentid)) {
            $updatedcostcenter = $DB->get_record('local_costcenter', array('id' => $costcenterid));
            $newparentid = isset($newparentid) ? $newparentid : 0;
            /* ---if the new parentid is different then update--- */
            $this->update_costcenter($updatedcostcenter, $newparentid, 'local_costcenter');
        }
        $updatedcostcenter = $DB->get_record('local_costcenter', array('id' => $costcenterid));
   /* updated category */
         if (isset($coursecat)) {
             
            if ((int)$data->parent !== (int)$coursecat->parent && !$coursecat->can_change_parent($data->parent)) {
                  print_error('cannotmovecategory');
            }
        $coursecat->update($data, $mform->get_description_editor_options());
         }
        
      /*End of the list */  
        
        
        $conf = new object();
        $conf->costcenter = $newcostcenter->fullname;
        $message = get_string('updatesuccess', 'local_costcenter', $conf);
        $this->set_confirmation($message, $currenturl, array('style' => 'notifysuccess'));
    }

    /**
     * @method update_costcenter
     * @param object $costcenter 
     * @param object $newparentid costcenter data
     * @retun Updates the costcenter
     * 
     */
    public function update_costcenter($costcenter, $newparentid, $plugin) {
        global $DB, $CFG;

        //$hierarche = new department ();
        if (!is_object($costcenter)) {
            return false;
        }

        if ($costcenter->parentid == 0) {
            /* ---create a 'fake' old parent item for items at the top level--- */
            $oldparent = new stdClass();
            $oldparent->id = 0;
            $oldparent->path = '';
            $oldparent->depth = 0;
        } else {
            $oldparent = $DB->get_record($plugin, array('id' => $costcenter->parentid));
        }

        if ($newparentid == 0) {
            $newparent = new stdClass();
            $newparent->id = 0;
            $newparent->path = '';
            $newparent->depth = 0;
        } else {
            $newparent = $DB->get_record($plugin, array('id' => $newparentid));

            if ($this->subcostcenter_of($newparent, $costcenter->id) || empty($newparent)) {
                return false;
            }
        }

        if (!$newsortorder = $this->get_next_child_sortthread($newparentid, $plugin)) {
            return false;
        }
        $oldsortorder = $costcenter->sortorder;

        /* ---update the sortorder for the all items--- */
        $this->update_sortorder($oldsortorder, $newsortorder, $plugin);
        /* ---update the depth of the item and its descendants--- */
        $depthdiff = ($newparent->depth + 1) - $costcenter->depth;
        /* ---update the depth--- */
        $params = array('depthdiff' => $depthdiff,
                        'path' => $costcenter->path,
                        'pathb' => "$costcenter->path/%");

        $sql = "UPDATE $CFG->prefix$plugin
                   SET depth = depth + :depthdiff
                 WHERE (path = :path OR " . $DB->sql_like('path', ':pathb') . ")";
        $DB->execute($sql, $params);
        $length_sql = $DB->sql_length("'$oldparent->path'");
        $substr_sql = $DB->sql_substr('path', "{$length_sql} + 1");
        $updatepath = $DB->sql_concat("'{$newparent->path}'", $substr_sql);

        $params = array('path' => $costcenter->path,
                        'pathb' => "$costcenter->path/%");
        $sql = "UPDATE $CFG->prefix$plugin
                   SET path = $updatepath
                 WHERE (path = :path OR " . $DB->sql_like('path', ':pathb') . ")";
        $DB->execute($sql, $params);
        $todb = new stdClass();
        $todb->id = $costcenter->id;
        $todb->parentid = $newparentid;
        $DB->update_record($plugin, $todb);
        return true;
    }
    
    /*
     * @method update_sortorder Updates the sortorder of the record
     * @param INT oldsortorder
     * @param INT $newsortorder
     * @param string pluginname
     */
    public function update_sortorder($oldsortorder, $newsortorder, $plugin) {
        global $DB, $CFG;

        $length_sql = $DB->sql_length("'$oldsortorder'");
        $substr_sql = $DB->sql_substr('sortorder', "$length_sql + 1");
        $sortorder = $DB->sql_concat(":newsortorder", $substr_sql);
        $params = array('newsortorder' => $newsortorder,
                        'oldsortorder' => $oldsortorder,
                        'oldsortordermatch' => "{$oldsortorder}%");
        $sql = "UPDATE $CFG->prefix$plugin
                   SET sortorder = $sortorder
                 WHERE (sortorder = :oldsortorder OR " . $DB->sql_like('sortorder', ':oldsortordermatch') . ')';
        return $DB->execute($sql, $params);
    }

    public function subcostcenter_of($costcenter, $ids) {
        if (!isset($costcenter->path))
            return false;
        
        $ids = (is_array($ids)) ? $ids : array($ids);
        $parents = explode('/', substr($costcenter->path, 1));
        foreach ($parents as $parent) {
            if (in_array($parent, $ids)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @method get_childitems
     * @todo get the child items of the costcenter 
     * @param int $id costcenter id     
     * @retun object childcostcenter list
     */
    public function get_childitems($id) {
        global $CFG, $DB;
        $sql = "SELECT path,id from {local_costcenter} where id={$id}";
        $path = $DB->get_field_sql($sql);
        if ($path) {
            /* ---the WHERE clause must be like this to avoid /1% matching /10--- */
            $sql = "SELECT id, fullname, parentid, path
                      FROM {local_costcenter}
                     WHERE path = '{$path}' OR path LIKE '{$path}/%'
                     ORDER BY path";
            $records =  $DB->get_records_sql($sql);
            return $records;
        }
        return false;
    }

    /**
     * @method costcenter_delete_instance Delete the costcenter
     * @param int $id costcenter id     
     */
    public function costcenter_delete_instance($id) {
        global $DB, $CFG;
        //$department = new department ();
         $costcenter = $DB->get_field('local_costcenter','fullname',array('id'=>$id));
        $delete_costcenters = $this->get_childitems($id);
        $DB->delete_records('local_costcenter', array('id' => $id));
        $DB->delete_records('local_costcenter_permissions ', array('costcenterid' => $id));
        // --- deleting instance in costcenter context level---------------------- 
     }
 
    /**
     * @method add_users Assigns the manager to a costcenter
     * @param array $userids
     * @param int $costcenterid  Cost centerid
     */
    public function add_users($userids, $costcenterid) {
        global $CFG, $DB, $OUTPUT, $USER;

        //$department = new department ();
        $currenturl = "{$CFG->wwwroot}/local/costcenter/index.php";
        if (empty($userids)) {
            /* ---nothing to do--- */
            return;
        }
        $userids = array_reverse($userids);
        foreach ($userids as $userid) {
            $manager = new stdClass();
            $manager->userid = $userid;
            $manager->costcenterid = $costcenterid;
            $manager->roleid = $DB->get_field('role','id',array('archetype' => 'manager'));
            $today = time();
            $today = make_timestamp(date('Y', $today), date('m', $today), date('d', $today), 0, 0, 0);
            $manager->timecreated = $today ;
            $manager->usermodified = $USER->id;
            
            $userdata = new stdClass();
            $userdata->costcenterid = $costcenterid;
            $userdata->userid = $userid;
            $userdata->timecreated = $today;
            $userdata->timemodified = $today;
            $userdata->usermodified = $USER->id;
            $userdata->supervisorid = 0;
            $userdata->reportingmanagerid = 0;
            
            $costcenter = $DB->get_record('local_costcenter', array('id' => $costcenterid));
            $checkexist = $DB->get_record('local_costcenter_permissions', array('userid' => $userid, 'costcenterid' => $costcenterid));
          
            if ($checkexist) {
                $this->set_confirmation(get_string('alreadyassigned', 'local_costcenter', array('costcenter' => $costcenter->fullname)), $currenturl);
            } else {
             //     $theme=$DB->get_field('local_costcenter','theme',array('id'=>$costcenterid));
             //  $DB->set_field('user', 'theme',$theme, array('id' => $userid));
            //   echo $theme;
            //   exit;
                $permission_id = $DB->insert_record('local_costcenter_permissions', $manager);
               
              
                //if(!$DB->record_exists('local_userdata',array('userid'=>$userid)))
                //$DB->insert_record('local_userdata', $userdata);
            }
            if ($permission_id) {
                $conf = new object();
                $conf->username = $DB->get_field('user', 'username', array('id' => $userid));
                $conf->costcentername = $DB->get_field('local_costcenter', 'fullname', array('id' => $costcenterid));
                $message = get_string('msg_add_reg_schl', 'local_costcenter', $conf);
                $userfrom = $DB->get_record('user', array('id' => $USER->id));
                $userto = $DB->get_record('user', array('id' => $userid));
                $message_post_message = message_post_message($userfrom, $userto, $message, FORMAT_HTML);
            }
        }
        if ($permission_id) {
            $this->set_confirmation(get_string('assignedsuccess', 'local_costcenter'), $currenturl, array('style' => 'notifysuccess'));
        } else {
            $this->set_confirmation(get_string('assignedfailed', 'local_costcenter'), $currenturl, array('style' => 'notifyproblem'));
        }
    }

    /**
     * @method unassign_users_instance Unassigns the manager to a costcenter
     * @param int $id Cost center id
     * @param int $userid User ID
     */
    public function unassign_users_instance($id, $userid) {
        global $DB, $CFG, $USER;

        $currenturl = "{$CFG->wwwroot}/local/costcenter/index.php";
        $conf = new object();
        $conf->username = $DB->get_field('user', 'username', array('id' => $userid));
        $conf->costcentername = $DB->get_field('local_costcenter', 'fullname', array('id' => $id));

        $delete = $DB->delete_records('local_costcenter_permissions', array('costcenterid' => $id, 'userid' => $userid));
        if ($delete) {
            $message = get_string('msg_del_reg_schl', 'local_costcenter', $conf);
            $userfrom = $DB->get_record('user', array('id' => $USER->id));
            $userto = $DB->get_record('user', array('id' => $userid));
            $message_post_message = message_post_message($userfrom, $userto, $message, FORMAT_HTML);
            $this->set_confirmation(get_string('unassignedsuccess', 'local_costcenter'), $currenturl, array('style' => 'notifysuccess'));
        } else {
            $this->set_confirmation(get_string('problemunassignedsuccess', 'local_costcenter'), $currenturl, array('style' => 'notifyproblem'));
        }
    }
    
    /**
     * @method print_costcentertabs Prints tabs for costcenter
     * @param string $currenttab, current tab name
     * @param int $id ,used to change the tab name
     */
    //public function print_costcentertabs($currenttab, $id) {
    //    $systemcontext = context_system::instance();
    //    global $OUTPUT;
    //    $toprow = array();
    //    if ($id < 0 || empty($id)) {
    //        if (has_capability('local/costcenter:manage', $systemcontext))
    //            $toprow[] = new tabobject('create', new moodle_url('/local/costcenter/costcenter.php'), get_string('create', 'local_costcenter'));
    //    } else {
    //        if (has_capability('local/costcenter:manage', $systemcontext))
    //            $toprow[] = new tabobject('edit', new moodle_url('/local/costcenter/costcenter.php'), get_string('editcostcenter', 'local_costcenter'));
    //    }
    //    $toprow[] = new tabobject('view', new moodle_url('/local/costcenter/index.php'), get_string('view', 'local_costcenter'));
    //
    //    $assignmanager_cap = array('local/costcenter:manage', 'local/costcenter:assignmanager');
    //    if (has_any_capability($assignmanager_cap, $systemcontext)) {
    //        $toprow[] = new tabobject('assignmanager', new moodle_url('/local/costcenter/assignusers.php'), get_string('assignmanager', 'local_costcenter'));
    //    }
    //    if (has_capability('local/costcenter:view', $systemcontext)) {
    //        $toprow[] = new tabobject('info', new moodle_url('/local/costcenter/info.php'), get_string('info', 'local_costcenter'));
    //    }
    //    echo $OUTPUT->tabtree($toprow, $currenttab);
    //}
    
    public function get_theme_list() {
        global $CFG, $DB;
        $themes = array();
        $themelist[null] = '---Select---';
        $themelist['colms'] = 'MonoGraphic';
        $themelist['slp'] = 'slp';
        return $themelist;
    }
    /*************!Function To List The academic and Manager Belonging Costcenter By Ravi_369!*************/
    public function get_course_list_for_manager(){
        global $CFG, $DB,$USER;
		$systemcontext = context_system::instance();
		 $field_act=$DB->get_field('local_costcenter','id',array('shortname'=>'ACD'));
		 
        $userdata = $DB->get_field('local_userdata','costcenterid',array('userid'=>$USER->id));
		
			if (!is_siteadmin() && has_capability('local/assign_multiple_departments:manage', $systemcontext)){
				  $sql = "select c.*, cd.costcenterid,cd.courseid from {course} c
						join {local_coursedetails} cd ON c.id = cd.courseid
						where c.id > 1 AND cd.costcenterid = ".$field_act." ORDER BY id DESC";
				
			}
			else {
       
		        $sql = "select c.*, cd.costcenterid,cd.courseid from {course} c
						join {local_coursedetails} cd ON c.id = cd.courseid
						where c.id > 1 AND cd.costcenterid = ".$userdata." ORDER BY id DESC";
			}
			
		
        $courses = $DB->get_records_sql($sql);
      
        return $courses;
    }
   /*************End of Function***********/
   
   public function add_questioncategory($newparent, $newcategory, $newinfo, $return = false, $newinfoformat = FORMAT_HTML){
    global $DB;
        if (empty($newcategory)) {
            print_error('categorynamecantbeblank', 'question');
        }
        list($parentid, $contextid) = explode(',', $newparent);
        //moodle_form makes sure select element output is legal no need for further cleaning
        require_capability('moodle/question:managecategory', context::instance_by_id($contextid));

        if ($parentid) {
            if(!($DB->get_field('question_categories', 'contextid', array('id' => $parentid)) == $contextid)) {
                print_error('cannotinsertquestioncatecontext', 'question', '', array('cat'=>$newcategory, 'ctx'=>$contextid));
            }
        }

        $cat = new stdClass();
        $cat->parent = $parentid;
        $cat->contextid = $contextid;
        $cat->name = $newcategory;
        $cat->info = $newinfo;
        $cat->infoformat = $newinfoformat;
        $cat->sortorder = 999;
        $cat->stamp = make_unique_id_code();
        $categoryid = $DB->insert_record("question_categories", $cat);

        // Log the creation of this category.
        $params = array(
            'objectid' => $categoryid,
            'contextid' => $contextid
        );
        $event = \core\event\question_category_created::create($params);
        $event->trigger();

        return $categoryid;
   }
    
    function get_targetaudience($id,$type){
        global $DB;
        $sql = "SELECT u.id,u.id as uid FROM {user} u JOIN {local_userdata} ud ON u.id =ud.userid WHERE u.id >1 AND u.deleted=0 AND u.suspended=0 ";
		
        if($type==1){
            $learningplan = $DB->get_record('local_learningplan',array('id'=>$id));
            $us = $learningplan->band;
            $array=explode(',',$us);
            
            $bandlist=implode("','",$array);
            $costcenter = $learningplan->costcenter;
            $department = $learningplan->department;
            $subdepartment = $learningplan->subdepartment;
            $subsubdepartment = $learningplan->subsubdepartment;
            //print_object($learningplan);exit;
        }elseif($type==2){
            $facetoface = $DB->get_record('facetoface',array('id'=>$id));
            $us = $facetoface->bands;
            $array = explode(',',$us);
            $bandlist = implode("','",$array);
            
            $batch_locations = explode(',',$facetoface->training_at);
            $ilt_locations = implode("','", $batch_locations);
            if($facetoface->organizationid==NULL){
                 $costcenter = $facetoface->costcenter;
            }else{
                 $costcenter = $facetoface->organizationid;
            }
           
            $department = $facetoface->departmentid;
            $subdepartment = $facetoface->subdepartmentid;
            $subsubdepartment = $facetoface->sub_subdepartmentid;
            //print_object($facetoface);exit;
             if($facetoface->training_at != -1 && $facetoface->training_at!=''){
                $sql .=" AND ud.location IN ('$ilt_locations') ";
            }
        }
		
		if($costcenter){
			$sql .=' AND ud.costcenterid IN ('.$costcenter.') ';
		}else{
			$sql .=' AND ud.costcenterid!="" ';
		}
		
		if($department!=''){
			$sql .=' AND ud.department IN ('.$department.') ';
		}else{
			//$sql.=' AND ud.department!="" ' ;
		}
		
		if($subdepartment!=''){
			$sql .=' AND ud.subdepartment IN ('.$subdepartment.') ';
		}else{
			//$sql.=' AND ud.subdepartment!="" ';
		}
		
		if($subsubdepartment!=''){
			$sql .=' AND ud.sub_sub_department IN('.$subsubdepartment.') ';
		}else{
			//$sql.=' AND ud.sub_sub_department!="" ';
		}
		
		if($bandlist!=''){
			$sql .=" AND ud.band IN('$bandlist')";
		}else{
			//$sql .=' AND ud.band!=""  ';
		}
		//echo $sql;
		$users=$DB->get_records_sql_menu($sql);
		return $users;
    }
    
    function get_enrolledcoursefilter_users_employeeids($costcenter, $like = false,$page = false, $filterid = false, $filterpage = false){
        global $DB;
        
        if($filterid && $filterpage){
            switch ($filterpage){
                case 'lp':
                    $targetaudience = $this->get_targetaudience($filterid,1);
                    $targetaudience = implode(',',$targetaudience);
                break;
                case 'ilt':
                    $targetaudience = $this->get_targetaudience($filterid,2);
                    $targetaudience = implode(',',$targetaudience);
                break;
            }
        }
        
        $sql = "select u.idnumber as idnumber_key, u.idnumber as idnumber_value,concat(u.firstname,' ',u.lastname) as 	username
            from {user} as u
            join {local_userdata} as lu on u.id=lu.userid
            where u.deleted = 0 and u.suspended = 0";
        
        $systemcontext = context_system::instance();
        if($costcenter && !is_siteadmin() && !has_capability('local/assign_multiple_departments:manage',$systemcontext)) {
            $sql .= " AND lu.costcenterid = $costcenter";
        }
        if($like){
            $sql .= " AND u.idnumber LIKE '%%$like%%'";
        }
        if(!empty($targetaudience)){
            $sql .= " AND u.id IN ($targetaudience) ";
        }
        
        $sql .= " GROUP BY u.idnumber";
        $total_ids = $DB->get_records_sql($sql);
        if($page > 1){
            $page = $page-1;
            $length = $page*50;
            $sql .= " LIMIT $length, 50";
        }else{
            $sql .= " LIMIT 0,50";
        }
        $employeeids_list = $DB->get_records_sql($sql);
        $allusers_employees = array();
        
        if($employeeids_list){
            foreach($employeeids_list as $employeeids){
                $data_id=preg_replace("/[^0-9,.]/", "", $employeeids->idnumber_value);
               $name="($employeeids->username)";
               $allusers_employees[] = ['id'=>$employeeids->idnumber_key,'filtername'=>$data_id.' '.($name)];
            }
        }
        
        $dataobject = new stdClass();
        $dataobject->total_count = count($total_ids);
        $dataobject->incomplete_results = false;
        $dataobject->items = $allusers_employees;
        
        return $dataobject;
    }
    
    public function get_enrolledcoursefilter_users_emails($costcenter, $like = false,$page = 0, $filterid = false, $filterpage = false){
        global $DB;
        
        if($filterid && $filterpage){
            switch ($filterpage){
                case 'lp':
                    $targetaudience = $this->get_targetaudience($filterid,1);
                    $targetaudience = implode(',',$targetaudience);
                break;
                case 'ilt':
                    $targetaudience = $this->get_targetaudience($filterid,2);
                    $targetaudience = implode(',',$targetaudience);
                break;
            }
        }
       // print_object($targetaudience);
        $sql = "SELECT u.id as uid,email
                    FROM {user} as u
                    join {local_userdata} as lu on u.id=lu.userid
                    WHERE u.deleted = 0 and u.suspended = 0 and u.id>2";
        
        $systemcontext = context_system::instance();
        if($costcenter && !is_siteadmin() && !has_capability('local/assign_multiple_departments:manage',$systemcontext)) {
            $sql .= " AND lu.costcenterid = $costcenter";
        }
        if($like){
            $sql .= " AND u.email LIKE '%%$like%%'";
        }
        
        if(!empty($targetaudience)){
            $sql .= " AND u.id IN ($targetaudience) ";
        }
        
        $total_emails = $DB->get_records_sql($sql);
        if($page){
            $page = $page-1;
            $length = $page*50;
            $sql .= " LIMIT $length, 50";
        }else{
            $sql .= " LIMIT 0,50";
        }
        
        $users_emails = $DB->get_records_sql($sql);
        $allusers_emails = array();
        
        if($users_emails){
            foreach($users_emails as $users_email){
                $allusers_emails[] = ['id'=>$users_email->uid,'filtername'=>$users_email->email];
            }
        }
        $dataobject = new stdClass();
        $dataobject->total_count = count($total_emails);
        $dataobject->incomplete_results = false;
        $dataobject->items = $allusers_emails;
        
        return $dataobject;
    }
  
    public function get_enrolledcoursefilter_users_bands($costcenter, $like = false,$page = 0, $filterid = false, $filterpage = false){
        global $DB;
        if($filterid && $filterpage){
            switch ($filterpage){
                case 'lp':
                    $users = $DB->get_record('local_learningplan',array('id'=>$filterid));
                    $lepcostcenter=$users->costcenter;
                    $us = $users->band;
                    $array=explode(',',$us);
                    $targetaudience = implode("','",$array);
                break;
                case 'ilt':
                    $facetoface = $DB->get_record('facetoface',array('id'=>$filterid));
                    $lepcostcenter=$facetoface->organizationid;
                    $us = $facetoface->bands;
                    $array = explode(',',$us);
                    $targetaudience = implode("','",$array);
                break;
            }
        }
        //print_object($users);exit;
		$sql = "SELECT  band as bkey, band 
                FROM {user} as u
                JOIN {local_userdata} as lu on u.id=lu.userid
                WHERE u.deleted = 0 and u.suspended = 0 and u.id>2 ";
        $systemcontext = context_system::instance();
        if($costcenter && !is_siteadmin() && !has_capability('local/assign_multiple_departments:manage',$systemcontext)) {
            $sql .= " AND lu.costcenterid = $costcenter";
        }
        if(!empty($targetaudience)){
           
            $sql .= " AND lu.band IN ('$targetaudience') ";
        }
        if($like){
            if($lepcostcenter){
                $sql .= " AND lu.costcenterid IN ($lepcostcenter)";
            }
            
            $sql .= " AND lu.band LIKE '%%$like%%'";
        }
        $sql .= " GROUP BY lu.band";
        $total_bands = $DB->get_records_sql($sql);
        
        if($page){
            $page = $page-1;
            $length = $page*50;
            $sql .= " LIMIT $length, 50";
        }else{
            $sql .= " LIMIT 0,50";
        }
        //echo $sql;
        $users_bands = $DB->get_records_sql($sql);
        
        $allusers_bands = array();
        if(!empty($users_bands)){
            foreach($users_bands as $users_band){
                $allusers_bands[] = ['id'=>$users_band->bkey,'filtername'=>$users_band->band];
            }
        }
        $dataobject = new stdClass();
        $dataobject->total_count = count($total_bands);
        $dataobject->incomplete_results = false;
        $dataobject->items = $allusers_bands;
        
        return $dataobject;
    }
    
    public function get_enrolledcoursefilter_users_departments($costcenter, $like = false,$page = 0, $filterid = false, $filterpage = false){
        global $DB;
        if($filterid && $filterpage){
            switch ($filterpage){
                case 'lp':
                    $users = $DB->get_record('local_learningplan',array('id'=>$filterid));
                    $targetaudience = $users->department;
                    $lepcostcenter=$users->costcenter;
                break;
                case 'ilt':
                    $facetoface = $DB->get_record('facetoface',array('id'=>$filterid));
                    $lepcostcenter=$facetoface->organizationid;
                    $targetaudience = $facetoface->departmentid;
                break;
            }
        }
        $sql = "select ud.id as idnumber_value, ud.department, c.fullname AS departmentname
                    FROM {local_userdata} AS ud
                    JOIN {local_costcenter} AS c ON c.id = ud.department";
        
        $systemcontext = context_system::instance();
        if($costcenter && !is_siteadmin() && !has_capability('local/assign_multiple_departments:manage',$systemcontext)) {
            $sql .= " AND ud.costcenterid = $costcenter";
        }
        if(!empty($targetaudience)){
            $sql .= " AND ud.department IN ($targetaudience) ";
        }
        if($like){
            if($lepcostcenter){
             $sql .= " AND ud.costcenterid IN ($lepcostcenter)";
            }
            $sql .= " AND c.fullname LIKE '%%$like%%' GROUP by ud.department";
        }else{
            $sql .= " GROUP by ud.department ";
        }
        $total_departments = $DB->get_records_sql($sql);
        
        if($page){
            $page = $page-1;
            $length = $page*50;
            $sql .= " LIMIT $length, 50";
        }else{
            $sql .= " LIMIT 0,50";
        }
        $users_department = $DB->get_records_sql($sql);
        $allusers_departments = array();
    
        if($users_department){
            foreach($users_department as $users_departments){
                //$department = $DB->get_field('local_costcenter','fullname',array('id'=>$users_departments->department));
                $allusers_departments[] = ['id'=>$users_departments->department,'filtername'=>$users_departments->departmentname];
            }
        }
        $dataobject = new stdClass();
        $dataobject->total_count = count($total_departments);
        $dataobject->incomplete_results = false;
        $dataobject->items = $allusers_departments;
        
        return $dataobject;
    }
    
    public function get_enrolledcoursefilter_users_subdepartments($costcenter, $like = false,$page = 0, $filterid = false, $filterpage = false){
        global $DB;
        if($filterid && $filterpage){
            switch ($filterpage){
                case 'lp':
                    $users = $DB->get_record('local_learningplan',array('id'=>$filterid));
                    $deptargetaudience = $users->department;
                    $targetaudience = $users->subdepartment;
                    $lepcostcenter=$users->costcenter;
                break;
                case 'ilt':
                    $facetoface = $DB->get_record('facetoface',array('id'=>$filterid));
                    $deptargetaudience = $facetoface->department;
                    $lepcostcenter=$facetoface->organizationid;
                    $targetaudience = $facetoface->subdepartmentid;
                break;
            }
        }
        $sql = "select ud.id as idnumber_value, ud.subdepartment, c.fullname AS departmentname
                    FROM {local_userdata} AS ud
                    JOIN {local_costcenter} AS c ON c.id = ud.subdepartment";  
        
        $systemcontext = context_system::instance();
        if($costcenter && !is_siteadmin() && !has_capability('local/assign_multiple_departments:manage',$systemcontext)) {
            $sql .= " AND ud.costcenterid = $costcenter";
        }
        if(!empty($deptargetaudience)){
            $sql .= " AND ud.department IN ($deptargetaudience) ";
        }
        if(!empty($targetaudience)){
            $sql .= " AND ud.subdepartment IN ($targetaudience) ";
        }
        if($like){
            if($lepcostcenter){
             $sql .= " AND ud.costcenterid IN ($lepcostcenter)";
            }
            $sql .= " AND c.fullname LIKE '%%$like%%' ";
        }
        $sql .= " GROUP by ud.subdepartment ";
        
        $total_subdepartments = $DB->get_records_sql($sql);
        
        if($page){
            $page = $page-1;
            $length = $page*50;
            $sql .= " LIMIT $length, 50";
        }else{
            $sql .= " LIMIT 0,50";
        }
        $users_subdepartments = $DB->get_records_sql($sql);
        $allusers_subdepartments = array();
         
        if($users_subdepartments){
            foreach($users_subdepartments as $users_subdepartment){
                //$subdepartment = $DB->get_field('local_costcenter','fullname',array('id'=>$users_subdepartment->subdepartment));
                $allusers_subdepartments[] = ['id'=>$users_subdepartment->subdepartment,'filtername'=>$users_subdepartment->departmentname];
            }
        }
        $dataobject = new stdClass();
        $dataobject->total_count = count($total_subdepartments);
        $dataobject->incomplete_results = false;
        $dataobject->items = $allusers_subdepartments;
        
        return $dataobject;
    }
    
    public function get_enrolledcoursefilter_users_subsub_departments($costcenter, $like = false,$page = 0, $filterid = false, $filterpage = false){
        global $DB;
        if($filterid && $filterpage){
            switch ($filterpage){
                case 'lp':
                    $users = $DB->get_record('local_learningplan',array('id'=>$filterid));
                    $deptargetaudience = $users->department;
                    $subdeptargetaudience = $users->subdepartment;
                    $targetaudience = $users->subsubdepartment;
                    $lepcostcenter=$users->costcenter;
                break;
                case 'ilt':
                    $facetoface = $DB->get_record('facetoface',array('id'=>$filterid));
                    $lepcostcenter=$facetoface->organizationid;
                    $deptargetaudience = $users->department;
                    $subdeptargetaudience = $users->subdepartment;
                    $targetaudience = $facetoface->sub_subdepartmentid;
                break;
            }
        }
        $sql = "SELECT ud.id as idnumber_value, ud.sub_sub_department, c.fullname AS departmentname
                FROM {local_userdata} AS ud
                JOIN {local_costcenter} AS c ON c.id = ud.sub_sub_department";
        
        $systemcontext = context_system::instance();
        if($costcenter && !is_siteadmin() && !has_capability('local/assign_multiple_departments:manage',$systemcontext)) {
            $sql .= " AND ud.costcenterid = $costcenter";
        }
        if(!empty($deptargetaudience)){
            $sql .= " AND ud.department IN ($deptargetaudience) ";
        }
        if(!empty($subdeptargetaudience)){
            $sql .= " AND ud.subdepartment IN ($targetaudience) ";
        }
        
        
        
        if(!empty($targetaudience)){
            $sql .= " AND ud.sub_sub_department IN ($targetaudience) ";
        }
        if($like){
            if($lepcostcenter){
             $sql .= " AND ud.costcenterid IN ($lepcostcenter)";
            }
            $sql .= " AND c.fullname LIKE '%%$like%%' GROUP by ud.sub_sub_department";
        }else{
            $sql .= " GROUP by ud.sub_sub_department ";
        }
        
        $total_subsubdepartments = $DB->get_records_sql($sql);
        
        if($page){
            $page = $page-1;
            $length = $page*50;
            $sql .= " LIMIT $length, 50";
        }else{
            $sql .= " LIMIT 0,50";
        }
        $users_ssdepartments = $DB->get_records_sql($sql);
        $allusers_ssdepts = array();
        
        if($users_ssdepartments){
            foreach($users_ssdepartments as $users_email){
                //$ssdept = $DB->get_field('local_costcenter','fullname',array('id'=>$users_email->sub_sub_department));
                $allusers_ssdepts[] = ['id'=>$users_email->sub_sub_department,'filtername'=>$users_email->departmentname];
            }
        }
        
        $dataobject = new stdClass();
        $dataobject->total_count = count($total_subsubdepartments);
        $dataobject->incomplete_results = false;
        $dataobject->items = $allusers_ssdepts;
        
        return $dataobject;
    }
    
    function get_enrolledcoursefilter_users_designation($costcenter, $like = false,$page = 0, $filterid = false, $filterpage = false){
        global $DB;
        //print_object($filterid);
        if($filterid){
            $users = $DB->get_record('local_learningplan',array('id'=>$filterid));
            $facetoface = $DB->get_record('facetoface',array('id'=>$filterid));
              //print_object($users);
             // print_object($facetoface);
        }
        $sql = "select id as idnumber_value, designation as designation_key from {local_userdata} WHERE 1=1 "; 
        
        $systemcontext = context_system::instance();
        if($costcenter && !is_siteadmin() && !has_capability('local/assign_multiple_departments:manage',$systemcontext)) {
            $sql .= " AND costcenterid = $costcenter";
        }
        if($like){
            
            if($users){
                $lepcostcenter=$users->costcenter;
                if($lepcostcenter){
                 $sql .= " AND costcenterid IN ($lepcostcenter)";
                 }
            }else{
                $lepcostcenter=$facetoface->organizationid;
                 if($lepcostcenter){
                 $sql .= " AND costcenterid IN ($lepcostcenter)";
            }
        }
            $sql .= " AND designation LIKE '%%$like%%'";
        }
        $total_designations = $DB->get_records_sql($sql);
        
        if($page){
            $page = $page-1;
            $length = $page*50;
            $sql .= " LIMIT $length, 50";
        }else{
            $sql .= " LIMIT 0,50";
        }
        
        $employeeids_list = $DB->get_records_sql($sql);
        $allusers_employees = array();
        
        if($employeeids_list){
            foreach($employeeids_list as $employeeids){
                $allusers_employees[] = ['id'=>$employeeids->idnumber_value,'filtername'=>$employeeids->designation_key];
            }
        }
        $dataobject = new stdClass();
        $dataobject->total_count = count($total_designations);
        $dataobject->incomplete_results = false;
        $dataobject->items = $allusers_employees;
        
        return $dataobject;
    }
    
    public function get_enrolledcoursefilter_users_costcenters($like = false,$page = 0, $filterid = false, $filterpage = false){
        global $DB;
        if($filterid && $filterpage){
            switch ($filterpage){
                case 'lp':
                    $users = $DB->get_record('local_learningplan',array('id'=>$filterid));
                    $targetaudience = $users->costcenter;
            }
        }
        $sql = "select id,fullname from {local_costcenter} where visible =1 and parentid IN(0,1)";
        if(!empty($targetaudience)){
            $sql .= " AND id IN ($targetaudience) ";
        }
        
        if($like){
            $sql .= " AND fullname LIKE '%%$like%%'";
        }
        $total_costcenters = $DB->get_records_sql($sql);
        
        if($page){
            $page = $page-1;
            $length = $page*50;
            $sql .= " LIMIT $length, 50";
        }else{
            $sql .= " LIMIT 0,50";
        }
        $depts = $DB->get_records_sql($sql);
        
        $departments = array();
        if($depts){
            foreach($depts as $dept){
                $departments[] = ['id'=>$dept->id,'filtername'=>$dept->fullname];
            }
        }
        
        $dataobject = new stdClass();
        $dataobject->total_count = count($total_costcenters);
        $dataobject->incomplete_results = false;
        $dataobject->items = $departments;
        
        return $dataobject;
    }
}
 
 