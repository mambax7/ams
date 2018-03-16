<?php namespace XoopsModules\Ams;

use XoopsModules\Ams;

class Audience extends XoopsObject
{
    public function __construct()
    {
        $this->initVar('audienceid', XOBJ_DTYPE_INT);
        $this->initVar('audience', XOBJ_DTYPE_TXTBOX);
    }
}
