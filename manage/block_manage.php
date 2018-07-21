<?php

//
//
// This software is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This Moodle block is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @since 2.0
 * @package blocks
 * @copyright 2012 Georg Maißer und David Bogner http://www.edulabs.org
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 *
 * Used to produce  Manage block
 *
 * @package blocks
 * @copyright 2016 Anilkumar <anil@eabyas.in>
* @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/
class block_manage extends block_base {
	/**
	* block initializations
	*/
        
    public function init() {
	  $block_title = get_string('mydashboard', 'block_manage');
      $this->title = $block_title;
    }
	
    function get_required_javascript() {
        //$this->page->requires->jquery();
        $this->page->requires->js('/blocks/manage/js/jquery-ui.js',true);
		$this->page->requires->js('/blocks/manage/js/tabs_script.js',true);
		$this->page->requires->css('/blocks/manage/css/ui-style.css');
		
		$this->page->requires->js('/local/teammanager/js/select2.full.js');
		$this->page->requires->css('/local/teammanager/css/select2.min.css');
		$this->page->requires->css('/blocks/manage/css/jquery_ui_dialog.css');
		
		//Jpagination required js files
		$this->page->requires->css('/blocks/manage/css/jplist.core.min.css');
		$this->page->requires->css('/blocks/manage/css/jplist.filter-toggle-bundle.min.css');
		$this->page->requires->css('/blocks/manage/css/jplist.pagination-bundle.min.css');
		$this->page->requires->css('/blocks/manage/css/jplist.textbox-filter.min.css');
		$this->page->requires->css('/blocks/manage/css/jplist.views-control.min.css');
		
		$this->page->requires->js('/blocks/manage/js/jplist.core.min.js',true);
		$this->page->requires->js('/blocks/manage/js/jplist.filter-dropdown-bundle.min.js',true);
		$this->page->requires->js('/blocks/manage/js/jplist.filter-toggle-bundle.min.js',true);
		$this->page->requires->js('/blocks/manage/js/jplist.pagination-bundle.min.js',true);
		$this->page->requires->js('/blocks/manage/js/jplist.textbox-filter.min.js',true);
	}
	
    public function get_content() {
      global $DB;
      if ($this->content !== null) {
        return $this->content;
      }
        global $CFG, $USER, $PAGE;
        $this->content = new stdClass();
		require_once($CFG->dirroot.'/blocks/manage/renderer.php');
		$PAGE->requires->css('/blocks/manage/css/ui-style.css');
		$renderer = $PAGE->get_renderer('block_manage'); 
		//$blockdata = $renderer->display_tabs();
		$blockdata = $renderer->display_tabs_and_its_content();
        
        $this->content->text = $blockdata;
		
		// this query for search the courses
		//<script src="js/jp-list_custom_script.js"></script>
		//        $this->content->text .= html_writer::script("
		//            $('document').ready(function () {
		//          
		//                $('#demo').jplist({
		//                    itemsBox: '.list'
		//                    ,itemPath: '.list-item'
		//                    ,panelPath: '.jplist-panel'
		//                });
		//            });
		//		");

        $this->content->footer = '';
        return $this->content;
    }

}
