<?php
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

use XoopsModules\Ams;

require_once XOOPS_ROOT_PATH.'/class/logger.php';
require_once XOOPS_ROOT_PATH.'/class/database/databasefactory.php';
require_once XOOPS_ROOT_PATH.'/class/database/'.XOOPS_DB_TYPE.'database.php';
require_once XOOPS_ROOT_PATH.'/class/database/sqlutility.php';

/**
* database manager for XOOPS installer
*
* @author Haruki Setoyama  <haruki@planewave.org>
* @access public
**/
class db_manager
{
    public $s_tables = [];
    public $f_tables = [];
    public $db;

    public function __construct()
    {
        $this->db = \XoopsDatabaseFactory::getDatabaseConnection();
        $this->db->setPrefix(XOOPS_DB_PREFIX);
        $this->db->setLogger(\XoopsLogger::getInstance());
    }

    public function isConnectable()
    {
        return (false !== $this->db->connect(false)) ? true : false;
    }

    public function dbExists()
    {
        return (false !== $this->db->connect()) ? true : false;
    }

    public function createDB()
    {
        $this->db->connect(false);

        $result = $this->db->query('CREATE DATABASE ' . XOOPS_DB_NAME);

        return (false !== $result) ? true : false;
    }

    public function queryFromFile($sql_file_path)
    {
        $tables = [];

        if (!file_exists($sql_file_path)) {
            return false;
        }
        $sql_query = trim(fread(fopen($sql_file_path, 'r'), filesize($sql_file_path)));
        SqlUtility::splitMySqlFile($pieces, $sql_query);
        $this->db->connect();
        foreach ($pieces as $piece) {
            $piece = trim($piece);
            // [0] contains the prefixed query
            // [4] contains unprefixed table name
            $prefixed_query = SqlUtility::prefixQuery($piece, $this->db->prefix());
            if (false !== $prefixed_query) {
                $table = $this->db->prefix($prefixed_query[4]);
                if ('CREATE TABLE' === $prefixed_query[1]) {
                    if (false !== $this->db->query($prefixed_query[0])) {
                        if (! isset($this->s_tables['create'][$table])) {
                            $this->s_tables['create'][$table] = 1;
                        }
                    } else {
                        if (! isset($this->f_tables['create'][$table])) {
                            $this->f_tables['create'][$table] = 1;
                        }
                    }
                } elseif ('INSERT INTO' === $prefixed_query[1]) {
                    if (false !== $this->db->query($prefixed_query[0])) {
                        if (! isset($this->s_tables['insert'][$table])) {
                            $this->s_tables['insert'][$table] = 1;
                        } else {
                            $this->s_tables['insert'][$table]++;
                        }
                    } else {
                        if (! isset($this->f_tables['insert'][$table])) {
                            $this->f_tables['insert'][$table] = 1;
                        } else {
                            $this->f_tables['insert'][$table]++;
                        }
                    }
                } elseif ('ALTER TABLE' === $prefixed_query[1]) {
                    if (false !== $this->db->query($prefixed_query[0])) {
                        if (! isset($this->s_tables['alter'][$table])) {
                            $this->s_tables['alter'][$table] = 1;
                        }
                    } else {
                        if (! isset($this->s_tables['alter'][$table])) {
                            $this->f_tables['alter'][$table] = 1;
                        }
                    }
                } elseif ('DROP TABLE' === $prefixed_query[1]) {
                    if (false !== $this->db->query('DROP TABLE ' . $table)) {
                        if (! isset($this->s_tables['drop'][$table])) {
                            $this->s_tables['drop'][$table] = 1;
                        }
                    } else {
                        if (! isset($this->s_tables['drop'][$table])) {
                            $this->f_tables['drop'][$table] = 1;
                        }
                    }
                }
            }
        }
        return true;
    }

    public function report()
    {
        $content = "<table align='center'><tr><td align='left'>\n";
        if (isset($this->s_tables['create'])) {
            foreach ($this->s_tables['create'] as $key => $val) {
                $content .= sprintf(_INSTALL_L45, "<b>$key</b>")."<br>\n";
            }
        }
        if (isset($this->s_tables['insert'])) {
            foreach ($this->s_tables['insert'] as $key => $val) {
                $content .= sprintf(_INSTALL_L119, $val, "<b>$key</b>")."<br>\n";
            }
        }
        if (isset($this->s_tables['alter'])) {
            foreach ($this->s_tables['alter'] as $key => $val) {
                $content .= sprintf(_INSTALL_L133, "<b>$key</b>")."<br>\n";
            }
        }
        if (isset($this->s_tables['drop'])) {
            foreach ($this->s_tables['drop'] as $key => $val) {
                $content .= sprintf(_INSTALL_L163, "<b>$key</b>")."<br>\n";
            }
        }
        $content .= "<br>\n";
        if (isset($this->f_tables['create'])) {
            foreach ($this->f_tables['create'] as $key => $val) {
                $content .= sprintf(_INSTALL_L118, "<b>$key</b>")."<br>\n";
            }
        }
        if (isset($this->f_tables['insert'])) {
            foreach ($this->f_tables['insert'] as $key => $val) {
                $content .= sprintf(_INSTALL_L120, $val, "<b>$key</b>")."<br>\n";
            }
        }
        if (isset($this->f_tables['alter'])) {
            foreach ($this->f_tables['alter'] as $key => $val) {
                $content .= sprintf(_INSTALL_L134, "<b>$key</b>")."<br>\n";
            }
        }
        if (isset($this->f_tables['drop'])) {
            foreach ($this->f_tables['drop'] as $key => $val) {
                $content .= sprintf(_INSTALL_L164, "<b>$key</b>")."<br>\n";
            }
        }
        $content .= "</td></tr></table>\n";
        return $content;
    }

    public function query($sql)
    {
        $this->db->connect();
        return $this->db->query($sql);
    }

    public function prefix($table)
    {
        $this->db->connect();
        return $this->db->prefix($table);
    }

    public function fetchArray($ret)
    {
        $this->db->connect();
        return $this->db->fetchArray($ret);
    }

    public function insert($table, $query)
    {
        $this->db->connect();
        $table = $this->db->prefix($table);
        $query = 'INSERT INTO '.$table.' '.$query;
        if (!$this->db->queryF($query)) {
            if (!isset($this->f_tables['insert'][$table])) {
                $this->f_tables['insert'][$table] = 1;
            } else {
                $this->f_tables['insert'][$table]++;
            }
            return false;
        } else {
            if (!isset($this->s_tables['insert'][$table])) {
                $this->s_tables['insert'][$table] = 1;
            } else {
                $this->s_tables['insert'][$table]++;
            }
            return $this->db->getInsertId();
        }
    }

    public function isError()
    {
        return isset($this->f_tables) ? true : false;
    }

    public function deleteTables($tables)
    {
        $deleted = [];
        $this->db->connect();
        foreach ($tables as $key => $val) {
            if (! $this->db->query('DROP TABLE ' . $this->db->prefix($key))) {
                $deleted[] = $ct;
            }
        }
        return $deleted;
    }

    public function tableExists($table)
    {
        $table = trim($table);
        $ret = false;
        if ('' != $table) {
            $this->db->connect();
            $sql = 'SELECT * FROM '.$this->db->prefix($table);
            $ret = (false !== $this->db->query($sql)) ? true : false;
        }
        return $ret;
    }
}
