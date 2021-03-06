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

include __DIR__ . '/../../mainfile.php';
require_once XOOPS_ROOT_PATH . '/modules/ams/class/Story.php';
require_once XOOPS_ROOT_PATH . '/modules/ams/class/class.sfiles.php';
/** @var Ams\Helper $helper */
$helper = Ams\Helper::getInstance();
$helper->loadLanguage('main');
/*foreach ($_POST as $k => $v) {
    ${$k} = $v;
}
*/
$storyid = \Xmf\Request::getInt('storyid', 0, 'GET');
if (0 === $storyid) {
    redirect_header(XOOPS_URL . '/modules/ams/index.php', 2, _AMS_NW_NOSTORY);
    exit();
}

$myts = \MyTextSanitizer::getInstance();
// set comment mode if not set


$article = new Story($storyid);
if (0 == $article->published() || $article->published() > time()) {
    //redirect_header('index.php', 2, _AMS_NW_NOSTORY);
    require_once XOOPS_ROOT_PATH.'/header.php';
    include XOOPS_ROOT_PATH.'/footer.php';
    exit();
}
$admin = false;
$gpermHandler = xoops_getHandler('groupperm');
if (is_object($xoopsUser)) {
    $groups = $xoopsUser->getGroups();
} else {
    $groups = XOOPS_GROUP_ANONYMOUS;
}
if (!$gpermHandler->checkRight('ams_approve', $article->topicid(), $groups, $xoopsModule->getVar('mid'))) {
    if (!$gpermHandler->checkRight('ams_view', $article->topicid(), $groups, $xoopsModule->getVar('mid'))) {
        if (!$gpermHandler->checkRight('ams_submit', $article->topicid(), $groups, $xoopsModule->getVar('mid'))) {
            redirect_header(XOOPS_URL.'/modules/ams/index.php', 3, _NOPERM);
            exit();
        }
    }
    if (!$gpermHandler->checkRight('ams_audience', $article->audienceid, $groups, $xoopsModule->getVar('mid'))) {
        redirect_header(XOOPS_URL.'/modules/ams/index.php', 3, sprintf(_AMS_NW_NOTALLOWEDAUDIENCE, $article->audience));
        exit();
    }
} else {
    $admin = true;
}

$storypage = \Xmf\Request::getInt('page', 0, 'GET');
// update counter only when viewing top page
if (empty($_GET['com_id']) && 0 == $storypage) {
    $article->updateCounter();
}
if ($admin) {
    $xoopsConfig['module_cache'][$xoopsModule->getVar('mid')] = 0;
}
$GLOBALS['xoopsOption']['template_main'] = 'ams_article.tpl';
require_once XOOPS_ROOT_PATH.'/header.php';

$xoopsTpl->assign('story', $article->toArray($admin, true, $storypage));
$artbanner = $article->getBanner();
if ('' == $artbanner) {
    $artbanner = ' ';
}
$xoopsTpl->assign('articlebanner', $myts->displayTarea($artbanner, 1));
$showcomments = (XOOPS_COMMENT_APPROVENONE != $helper->getConfig('com_rule')) ? 1 : 0;
$allow_rating = $xoopsUser || $helper->getConfig('anonymous_vote') ? 1 :0;
$xoopsTpl->assign('showcomments', $showcomments);
$xoopsTpl->assign('allow_rating', $allow_rating);
$xoopsTpl->assign('lang_printerpage', _AMS_NW_PRINTERFRIENDLY);
$xoopsTpl->assign('lang_sendstory', _AMS_NW_SENDSTORY);
$xoopsTpl->assign('lang_on', _AMS_NW_PUBLISHED_DATE);
$xoopsTpl->assign('lang_postedby', _AMS_NW_POSTEDBY);
$xoopsTpl->assign('lang_reads', _AMS_NW_READS);
if (1 != $article->friendlyurl_enable) {
    $xoopsTpl->assign('mail_link', 'mailto:?subject='.sprintf(_AMS_NW_INTARTICLE, $xoopsConfig['sitename']).'&amp;body='.sprintf(_AMS_NW_INTARTFOUND, $xoopsConfig['sitename']).':  '.XOOPS_URL.'/modules/ams/article.php?storyid='.$article->storyid());
} else {
    $xoopsTpl->assign('mail_link', 'mailto:?subject='.sprintf(_AMS_NW_INTARTICLE, $xoopsConfig['sitename']).'&amp;body='.sprintf(_AMS_NW_INTARTFOUND, $xoopsConfig['sitename']).':  '.$article->friendlyurl);
}
$xoopsTpl->assign('related', $article->getLinks());
$xoopsTpl->assign('page', $storypage);
$xoopsTpl->assign('admin', $admin);
$xoopsTpl->assign('hasversions', $article->hasVersions());

$xoopsTpl->assign('lang_attached_files', _AMS_NW_ATTACHEDFILES);
$sfiles = new Ams\Files();
$filesarr= [];
$newsfiles= [];
$filesarr=$sfiles->getAllbyStory($storyid);
$filescount=count($filesarr);
$xoopsTpl->assign('attached_files_count', $filescount);
if ($filescount>0) {
    foreach ($filesarr as $onefile) {
        $newsfiles[] = [
            'file_id'           => $onefile->getFileid(),
            'visitlink'         => XOOPS_URL . '/modules/' . $xoopsModule->dirname() . '/visit.php?fileid=' . $onefile->getFileid(),
            'file_realname'     => $onefile->getFileRealName(),
            'file_attacheddate' => formatTimestamp($onefile->getDate()),
            'file_mimetype'     => $onefile->getMimetype(),
            'file_downloadname' => XOOPS_UPLOAD_URL . '/' . $onefile->getDownloadname()
        ];
    }
    $xoopsTpl->assign('attached_files', $newsfiles);
}
$xoopsTpl->assign('xoops_pagetitle', $myts->htmlSpecialChars($xoopsModule->name()) . ' - ' . $myts->htmlSpecialChars($article->topic_title()) . ' - ' . $myts->htmlSpecialChars($article->title()));

$xoopsTpl->assign('breadcrumb', $article->getPath());

include XOOPS_ROOT_PATH.'/include/comment_view.php';

include XOOPS_ROOT_PATH.'/footer.php';
