<?php namespace XoopsModules\Ams;

//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <http://www.xoops.org/>                             //
//  ------------------------------------------------------------------------ //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
//  ------------------------------------------------------------------------ //
// Author: Jan Keller Pedersen (AKA Mithrandir)                              //
// URL: http://www.idg.dk/ http://www.xoops.org/ http://www.web-udvikling.dk //
// Project: The XOOPS Project                                                //
// ------------------------------------------------------------------------- //

/**
* IDG Object Handler class.
* This class is responsible for providing data access mechanisms to the data source
* of derived class objects.
*
* @author  Jan Keller Pedersen <mithrandir@xoops.org>
* @copyright copyright (c) 2000-2004 XOOPS.org
* @package IDG
*/

class IdgObjectHandler extends \XoopsObjectHandler
{

    /**#@+
    * Information about the class, the handler is managing
    *
    * @var string
    */
    public $table;
    public $keyName;
    public $className;
    /**#@-*/

    /**
    * Constructor - called from child classes
    * @param \XoopsDatabase     $db         {@link XoopsDatabase} object
    * @param string     $tablename  Name of database table
    * @param string     $classname  Name of Class, this handler is managing
    * @param string     $keyname    Name of the property, holding the key
    *
    * @return void
    */
    public function __construct($db, $tablename, $classname, $keyname)
    {
        parent::__construct($db);
        $this->table = $this->db->prefix($tablename);
        $this->keyName = $keyname;
        $this->className = $classname;
    }

    public function IdgObjectHandler($db, $tablename, $classname, $keyname)
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        trigger_error("Should call parent::__construct in {$trace[0]['file']} line {$trace[0]['line']},");
        self::__construct($db, $tablename, $classname, $keyname);
    }

    /**
     * create a new user
     *
     * @param bool $isNew Flag the new objects as "new"?
     *
     * @return object
     */
    public function create($isNew = true)
    {
        $obj = new $this->className();
        if (true === $isNew) {
            $obj->setNew();
        }
        return $obj;
    }

    /**
    * retrieve an object
    *
    * @param int $id ID of the object
    * @param bool $as_object whether to return an object or an array
    * @return mixed reference to the object, FALSE if failed
    */
    public function get($id, $as_object = true)
    {
        $criteria = new \Criteria($this->keyName, (int)$id);
        $criteria->setLimit(1);
        $obj_array =& $this->getObjects($criteria, false, $as_object);
        if (1 != count($obj_array)) {
            return false;
        }
        return $obj_array[0];
    }

    /**
    * retrieve objects from the database
    *
    * @param CriteriaElement $criteria {@link CriteriaElement} conditions to be met
    * @param bool $id_as_key use the ID as key for the array?
    * @param bool $as_object return an array of objects?
    *
    * @return array
    */
    public function &getObjects($criteria = null, $id_as_key = false, $as_object = true)
    {
        $ret = [];
        $limit = $start = 0;
        $sql = 'SELECT * FROM '.$this->table;
        if (isset($criteria) && is_subclass_of($criteria, 'CriteriaElement')) {
            $sql .= ' '.$criteria->renderWhere();
            if ('' != $criteria->getSort()) {
                $sql .= ' ORDER BY '.$criteria->getSort().' '.$criteria->getOrder();
            }
            $limit = $criteria->getLimit();
            $start = $criteria->getStart();
        }
        $result = $this->db->query($sql, $limit, $start);
        if (!$result) {
            return $ret;
        }
        $tmpObj= $this->convertResultSet($result, $id_as_key, $as_object);
        return $tmpObj;
    }

    /**
    * Convert a database resultset to a returnable array
    *
    * @param object $result database resultset
    * @param bool $id_as_key
    * @param bool $as_object
    *
    * @return array
    */
    public function convertResultSet($result, $id_as_key = false, $as_object = true)
    {
        $ret = [];
        while (false !== ($myrow = $this->db->fetchArray($result))) {
            $obj = $this->create(false);
            $obj->assignVars($myrow);
            if (!$id_as_key) {
                if ($as_object) {
                    $ret[] = $obj;
                } else {
                    $row = [];
                    $vars = $obj->getVars();
                    foreach (array_keys($vars) as $i) {
                        $row[$i] = $obj->getVar($i);
                    }
                    $ret[] = $row;
                }
            } else {
                if ($as_object) {
                    $ret[$myrow[$this->keyName]] = $obj;
                } else {
                    $row = [];
                    $vars = $obj->getVars();
                    foreach (array_keys($vars) as $i) {
                        $row[$i] = $obj->getVar($i);
                    }
                    $ret[$myrow[$this->keyName]] = $row;
                }
            }
            unset($obj);
        }

        return $ret;
    }

    /**
    * Retrieve a list of objects as arrays
    * @param CriteriaElement $criteria {@link CriteriaElement} conditions to be met
    * @param int   $limit      Max number of objects to fetch
    * @param int   $start      Which record to start at
    *
    * @return array
    */
    public function getList($criteria = null, $limit = 0, $start = 0)
    {
        if ($limit > 0 || $start > 0) {
            if (null === $criteria) {
                $criteria = new \Criteria($this->keyName, -1, '!=');
            }
            $criteria->setLimit($limit);
            $criteria->setStart($start);
        }
        return $this->getObjects($criteria, true, false);
    }

    /**

     * count objects matching a condition
     *
     * @param CriteriaElement $criteria {@link CriteriaElement} to match
     * @return int count of objects
     */
    public function getCount($criteria = null)
    {
        $field = '';
        $groupby = false;
        if (isset($criteria) && is_subclass_of($criteria, 'CriteriaElement')) {
            if ('' != $criteria->groupby) {
                $groupby = true;
                $field = $criteria->groupby . ', '; //Not entirely secure unless you KNOW that no criteria's groupby clause is going to be mis-used
            }
        }
        $sql = 'SELECT '.$field.'COUNT(*) FROM '.$this->table;
        if (isset($criteria) && is_subclass_of($criteria, 'CriteriaElement')) {
            $sql .= ' '.$criteria->renderWhere();
            if ('' != $criteria->groupby) {
                $sql .= $criteria->getGroupby();
            }
        }
        $result = $this->db->query($sql);
        if (!$result) {
            return 0;
        }
        if (false === $groupby) {
            list($count) = $this->db->fetchRow($result);
            return $count;
        } else {
            $ret = [];
            while (false !== (list($id, $count) = $this->db->fetchRow($result))) {
                $ret[$id] = $count;
            }
            return $ret;
        }
    }

    /**
    * delete an object from the database
    *
    * @param object $obj reference to the object to delete
    * @param bool $force
    * @return bool FALSE if failed.
    */
    public function delete(\XoopsObject $obj) // , $force = false)
    {
        $force = false;

        $sql = sprintf('DELETE FROM %s WHERE %s = %u', $this->table, $this->keyName, $obj->getVar($this->keyName));
        if (false !== $force) {
            $result = $this->db->queryF($sql);
        } else {
            $result = $this->db->query($sql);
        }
        if (!$result) {
            return false;
        }
        return true;
    }

    /**
    * insert a new object in the database
    *
    * @param object $obj reference to the object
    * @param bool $force whether to force the query execution despite security settings
    * @param bool $checkObject check if the object is dirty and clean the attributes
    * @return bool FALSE if failed, TRUE if already present and unchanged or successful
    */

    public function insert(\XoopsObject $obj) // , $force = false, $checkObject = true)
    {
        $force = false;
        $checkObject = true;

        if (false !== $checkObject) {
            /**
        * @TODO: Change to if (!(class_exists($this->className) && $obj instanceof $this->className)) when going fully PHP5
        */
            if (!is_a($obj, $this->className)) {
                $obj->setErrors(get_class($obj) . ' Differs from ' . $this->className);
                return false;
            }
            if (!$obj->isDirty()) {
                return true;
            }
        }
        if (!$obj->cleanVars()) {
            return false;
        }

        foreach ($obj->cleanVars as $k => $v) {
            if (XOBJ_DTYPE_INT == $obj->vars[$k]['data_type']) {
                $cleanvars[$k] = (int)$v;
            } else {
                $cleanvars[$k] = $this->db->quoteString($v);
            }
        }
        if ($obj->isNew()) {
            if ($cleanvars[$this->keyName] < 1) {
                $cleanvars[$this->keyName] = $this->db->genId($this->table.'_'.$this->keyName.'_seq');
            }
            $sql = 'INSERT INTO '
                    . $this->table . ' ('
                    . implode(',', array_keys($cleanvars)) . ') VALUES ('
                    . implode(',', array_values($cleanvars)) . ')';
        } else {
            $sql = 'UPDATE ' . $this->table . ' SET';
            foreach ($cleanvars as $key => $value) {
                if ($key == $this->keyName) {
                    continue;
                }
                if (isset($notfirst)) {
                    $sql .= ',';
                }
                $sql .= ' ' . $key . ' = ' . $value;
                $notfirst = true;
            }
            $sql .= ' WHERE ' . $this->keyName . ' = ' . $obj->getVar($this->keyName);
        }
        //echo "<script type=\"text/javascript\">alert(\"$sql\");</script>";
        if (false !== $force) {
            $result = $this->db->queryF($sql);
        } else {
            $result = $this->db->query($sql);
        }
        if (!$result) {
            return false;
        }
        if ($obj->isNew()) {
            $obj->assignVar($this->keyName, $this->db->getInsertId());
        }
        return true;
    }

    /**
    * Change a value for objects with a certain criteria
    *
    * @param   string  $fieldname  Name of the field
    * @param   string  $fieldvalue Value to write
    * @param   CriteriaElement  $criteria   {@link CriteriaElement}
    *
    * @return  bool
    **/
    public function updateAll($fieldname, $fieldvalue, $criteria = null, $force = false)
    {
        $set_clause = is_numeric($fieldvalue) ? $fieldname.' = '.$fieldvalue : $fieldname.' = '.$this->db->quoteString($fieldvalue);
        $sql = 'UPDATE '.$this->table.' SET '.$set_clause;
        if (isset($criteria) && is_subclass_of($criteria, 'CriteriaElement')) {
            $sql .= ' '.$criteria->renderWhere();
        }
        if (false !== $force) {
            $result = $this->db->queryF($sql);
        } else {
            $result = $this->db->query($sql);
        }
        if (!$result) {
            return false;
        }
        return true;
    }

    /**
    * delete all objects meeting the conditions
    *
    * @param CriteriaElement $criteria {@link CriteriaElement} with conditions to meet
    * @return bool
    */

    public function deleteAll($criteria = null)
    {
        if (isset($criteria) && is_subclass_of($criteria, 'CriteriaElement')) {
            $sql = 'DELETE FROM '.$this->table;
            $sql .= ' '.$criteria->renderWhere();
            if (!$this->db->query($sql)) {
                return false;
            }
            return true;
        }
        return false;
    }
}
