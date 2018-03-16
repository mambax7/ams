<?php namespace XoopsModules\Ams;

use XoopsModules\Ams;

class AudienceHandler extends \XoopsPersistableObjectHandler //IdgObjectHandler
{
    public function __construct($db)
    {
        parent::__construct($db, 'ams_audience', Audience::class, 'audienceid');
    }

    public function deleteReplace($aud, $newaudid)
    {
        if (1 == $aud->getVar('audienceid')) {
            return false;
        }
        $sql = 'UPDATE '
               . $this->db->prefix('ams_article') . ' SET audienceid = '
               . (int)$newaudid . ' WHERE audienceid = '
               . (int)$aud->getVar('audienceid');
        if (!$this->db->query($sql)) {
            return false;
        }
        return parent::delete($aud);
    }

    public function getAllAudiences()
    {
        return $this->getObjects(null, true);
    }

    public function getStoryCountByAudience($audience)
    {
        $sql = 'SELECT COUNT(*) FROM ' . $this->db->prefix('ams_article') . ' WHERE audienceid=' . $audience->getVar('audienceid');
        if ($result = $this->db->query($sql)) {
            list($count) = $this->db->fetchRow($result);
            return $count;
        }
        return false;
    }
}
