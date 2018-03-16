<?php namespace XoopsModules\Ams;

use XoopsModules\Ams;

class Seo extends \XoopsObject
{
    public function __construct()
    {
        $this->initVar('settingid', XOBJ_DTYPE_INT, null, true, 11);
        $this->initVar('settingvalue', XOBJ_DTYPE_TXTBOX, null, false, 100);
        $this->initVar('settingtype', XOBJ_DTYPE_TXTBOX, null, false, 30);
    }
}
