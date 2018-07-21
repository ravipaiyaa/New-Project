<?php
/**
 * Course category context class
 *
 * @package   core_access
 * @category  access
 * @copyright Petr Skoda {@link http://skodak.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since     Moodle 2.2
 */

define('CONTEXT_COSTCENTER','17');
 
class context_costcenter extends context {
    /**
     * Please use context_coursecat::instance($coursecatid) if you need the instance of context.
     * Alternatively if you know only the context id use context::instance_by_id($contextid)
     *
     * @param stdClass $record
     */
    protected function __construct(stdClass $record) {
        parent::__construct($record);
        if ($record->contextlevel != CONTEXT_COSTCENTER) {
            throw new coding_exception('Invalid $record->contextlevel in context_coursecat constructor.');
        }
    }

    /**
     * Returns human readable context level name.
     *
     * @static
     * @return string the human readable context level name.
     */
    public static function get_level_name() {
        return get_string('pluginname','local_costcenter');
    }

    /**
     * Returns human readable context identifier.
     *
     * @param boolean $withprefix whether to prefix the name of the context with Category
     * @param boolean $short does not apply to course categories
     * @return string the human readable context name.
     */
    public function get_context_name($withprefix = true, $short = false) {
        global $DB;

        $name = '';
        if ($costcenter = $DB->get_record('local_costcenter', array('id'=>$this->_instanceid))) {
            if ($withprefix){
                $name = get_string('local_pluginname','local_costcenter').': ';
            }
            $name .= format_string($costcenter->fullname, true, array('context' => $this));
        }
        return $name;
    }

    /**
     * Returns the most relevant URL for this context.
     *
     * @return moodle_url
     */
    public function get_url() {
        return new moodle_url('/local/costcenter/courses.php', array('costcenterid' => $this->_instanceid));
    }

    /**
     * Returns array of relevant context capability records.
     *
     * @return array
     */
    public function get_capabilities() {
        global $DB;

        $sort = 'ORDER BY contextlevel,component,name';   // To group them sensibly for display

        $params = array();
        $sql = "SELECT *
                  FROM {capabilities}
                 WHERE contextlevel IN (".CONTEXT_COSTCENTER.",".CONTEXT_COURSECAT.",".CONTEXT_COURSE.",".CONTEXT_MODULE.",".CONTEXT_BLOCK.")";

        return $DB->get_records_sql($sql.' '.$sort, $params);
    }

    /**
     * Returns course category context instance.
     *
     * @static
     * @param int $instanceid
     * @param int $strictness
     * @return context_coursecat context instance
     */
    public static function instance($instanceid, $strictness = MUST_EXIST) {
        global $DB;

        if ($context = context::cache_get(CONTEXT_COSTCENTER, $instanceid)) {
            return $context;
        }

        if (!$record = $DB->get_record('context', array('contextlevel'=>CONTEXT_COSTCENTER, 'instanceid'=>$instanceid))) {
            if ($costcenter = $DB->get_record('local_costcenter', array('id'=>$instanceid), 'id,parentid', $strictness)) {
                if ($costcenter->parentid) {
                    $parentcostcenter = context_costcenter::instance($costcenter->parent);
                    $record = context::insert_context_record(CONTEXT_COSTCENTER, $costcenter->id, $parentcostcenter->path);
                } else {
                    $record = context::insert_context_record(CONTEXT_COSTCENTER, $costcenter->id, '/'.SYSCONTEXTID, 0);
                }
            }
        }

        if ($record) {
            $context = new context_costcenter($record);
            context::cache_add($context);
            return $context;
        }

        return false;
    }

    /**
     * Returns immediate child contexts of category and all subcategories,
     * children of subcategories and courses are not returned.
     *
     * @return array
     */
    public function get_child_contexts() {
        global $DB;

        if (empty($this->_path) or empty($this->_depth)) {
            debugging('Can not find child contexts of context '.$this->_id.' try rebuilding of context paths');
            return array();
        }

        $sql = "SELECT ctx.*
                  FROM {context} ctx
                 WHERE ctx.path LIKE ? AND (ctx.depth = ? OR ctx.contextlevel = ?)";
        $params = array($this->_path.'/%', $this->depth+1, CONTEXT_COSTCENTER);
        $records = $DB->get_records_sql($sql, $params);

        $result = array();
        foreach ($records as $record) {
            $result[$record->id] = context::create_instance_from_record($record);
        }

        return $result;
    }

    /**
     * Create missing context instances at course category context level
     * @static
     */
    protected static function create_level_instances() {
        global $DB;

        $sql = "INSERT INTO {context} (contextlevel, instanceid)
                SELECT ".CONTEXT_COSTCENTER.", cc.id
                  FROM {local_costcenter} cc
                 WHERE NOT EXISTS (SELECT 'x'
                                     FROM {context} cx
                                    WHERE cc.id = cx.instanceid AND cx.contextlevel=".CONTEXT_COSTCENTER.")";
        $DB->execute($sql);
    }

    /**
     * Returns sql necessary for purging of stale context instances.
     *
     * @static
     * @return string cleanup SQL
     */
    protected static function get_cleanup_sql() {
        $sql = "
                  SELECT c.*
                    FROM {context} c
         LEFT OUTER JOIN {local_costcenter} cc ON c.instanceid = cc.id
                   WHERE cc.id IS NULL AND c.contextlevel = ".CONTEXT_COSTCENTER."
               ";

        return $sql;
    }

    /**
     * Rebuild context paths and depths at course category context level.
     *
     * @static
     * @param bool $force
     */
    protected static function build_paths($force) {
        global $DB;

        if ($force or $DB->record_exists_select('context', "contextlevel = ".CONTEXT_COSTCENTER." AND (depth = 0 OR path IS NULL)")) {
            if ($force) {
                $ctxemptyclause = $emptyclause = '';
            } else {
                $ctxemptyclause = "AND (ctx.path IS NULL OR ctx.depth = 0)";
                $emptyclause    = "AND ({context}.path IS NULL OR {context}.depth = 0)";
            }

            $base = '/'.SYSCONTEXTID;

            // Normal top level categories
            $sql = "UPDATE {context}
                       SET depth=2,
                           path=".$DB->sql_concat("'$base/'", 'id')."
                     WHERE contextlevel=".CONTEXT_COSTCENTER."
                           AND EXISTS (SELECT 'x'
                                         FROM {local_costcenter} cc
                                        WHERE cc.id = {context}.instanceid AND cc.depth=1)
                           $emptyclause";
            $DB->execute($sql);

            // Deeper categories - one query per depthlevel
            $maxdepth = $DB->get_field_sql("SELECT MAX(depth) FROM {local_costcenter}");
            for ($n=2; $n<=$maxdepth; $n++) {
                $sql = "INSERT INTO {context_temp} (id, path, depth)
                        SELECT ctx.id, ".$DB->sql_concat('pctx.path', "'/'", 'ctx.id').", pctx.depth+1
                          FROM {context} ctx
                          JOIN {local_costcenter} cc ON (cc.id = ctx.instanceid AND ctx.contextlevel = ".CONTEXT_COSTCENTER." AND cc.depth = $n)
                          JOIN {context} pctx ON (pctx.instanceid = cc.parent AND pctx.contextlevel = ".CONTEXT_COSTCENTER.")
                         WHERE pctx.path IS NOT NULL AND pctx.depth > 0
                               $ctxemptyclause";
                $trans = $DB->start_delegated_transaction();
                $DB->delete_records('context_temp');
                $DB->execute($sql);
                context::merge_context_temp_table();
                $DB->delete_records('context_temp');
                $trans->allow_commit();

            }
        }
    }
}