<?php

use XoopsModules\Ams;

function xoops_module_update_ams(&$module, $old_version)
{
    $moduleDirName = basename(dirname(__DIR__));
    $capsDirName   = strtoupper($moduleDirName);

    /** @var Ams\Helper $helper */
    /** @var Ams\Utility $utility */
    /** @var Ams\Configurator $configurator */
    $helper  = Ams\Helper::getInstance();
    $utility = new Ams\Utility();
    $configurator = new Ams\Configurator();

    if ($old_version < 225) { //If upgrade from AMS older than 2.25
        $GLOBALS['xoopsDB']->queryFromFile(XOOPS_ROOT_PATH.'/modules/ams/sql/upgrade.sql');
    }
    if ($old_version <= 252) { //if upgrade from AMS 2.25 - AMS 2.50
        //There is template changes in AMS 2.50 Beta 2. Delete previous template in order not to confuse AMS
        if (file_exists(XOOPS_ROOT_PATH.'/modules/ams/templates/ams_block_spotlight_center.html')) {
            $module->setErrors('Old template detected !. Deleting ams_block_spotlight_center.html');
            unlink(XOOPS_ROOT_PATH.'/modules/ams/templates/ams_block_spotlight_center.html');
        }
        
        if (file_exists(XOOPS_ROOT_PATH.'/modules/ams/templates/ams_block_spotlight_left.html')) {
            $module->setErrors('Old template detected !. Deleting ams_block_spotlight_left.html');
            unlink(XOOPS_ROOT_PATH.'/modules/ams/templates/ams_block_spotlight_left.html');
        }

        if (file_exists(XOOPS_ROOT_PATH.'/modules/ams/templates/ams_block_spotlight_right.html')) {
            $module->setErrors('Old template detected !. Deleting ams_block_spotlight_right.html');
            unlink(XOOPS_ROOT_PATH.'/modules/ams/templates/ams_block_spotlight_right.html');
        }
    }
    if ($old_version < 300) { //If upgrade from AMS older than 3.00
        $GLOBALS['xoopsDB']->queryFromFile(XOOPS_ROOT_PATH.'/modules/ams/sql/upgradeto300.sql');
    }

    $module->setErrors('Database Tables Uptodate');
    return true;
}
