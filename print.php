<?php
/*
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * @copyright      {@link https://xoops.org/ XOOPS Project}
 * @license        {@link http://www.gnu.org/licenses/gpl-2.0.html GNU GPL 2 or later}
 * @package
 * @since
 * @author         XOOPS Development Team
 */

use XoopsModules\Ams;

include __DIR__ . '/../../mainfile.php';

$storyid = \Xmf\Request::getInt('storyid', 0, 'GET');
if (0 === $storyid) {
    redirect_header(XOOPS_URL . '/modules/ams/index.php');
}
//require_once XOOPS_ROOT_PATH.'/modules/'.$xoopsModule->dirname().'/class/Story.php';

function PrintPage($storyid)
{
    global $xoopsConfig, $xoopsModule, $xoopsUser;
    $myts = \MyTextSanitizer::getInstance();
    $story = new Ams\Story($storyid);
    $datetime = formatTimestamp($story->published());
    $gpermHandler = xoops_getHandler('groupperm');
    if (is_object($xoopsUser)) {
        $groups = $xoopsUser->getGroups();
    } else {
        $groups = XOOPS_GROUP_ANONYMOUS;
    }
    if (!$gpermHandler->checkRight('ams_view', $story->topicid(), $groups, $xoopsModule->getVar('mid'))) {
        if (!$gpermHandler->checkRight('ams_submit', $story->topicid(), $groups, $xoopsModule->getVar('mid'))) {
            if (!$gpermHandler->checkRight('ams_approve', $story->topicid(), $groups, $xoopsModule->getVar('mid'))) {
                redirect_header(XOOPS_URL.'/modules/ams/index.php', 3, _NOPERM);
                exit();
            }
        }
    }
    echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">';
    echo '<html><head>';
    echo '<meta http-equiv="Content-Type" content="text/html; charset='._CHARSET.'" />';
    echo '<title>'.$xoopsConfig['sitename']. ' - ' . $myts->htmlSpecialChars($story->topic_title()) . ' - ' . $myts->htmlSpecialChars($story->title()).'</title>';
    echo '<meta name="AUTHOR" content="'.$xoopsConfig['sitename'].'" />';
    echo '<meta name="COPYRIGHT" content="Copyright (c) 2001 by '.$xoopsConfig['sitename'].'" />';
    echo '<meta name="DESCRIPTION" content="'.$xoopsConfig['slogan'].'" />';
    echo '<meta name="GENERATOR" content="'.XOOPS_VERSION.'" />';
    echo '<body bgcolor="#ffffff" text="#000000" onload="window.print()">
        <table border="0"><tr><td align="center">
        <table border="0" width="640" cellpadding="0" cellspacing="1" bgcolor="#000000"><tr><td>
        <table border="0" width="640" cellpadding="20" cellspacing="1" bgcolor="#ffffff"><tr><td align="center">
        <img src="'.XOOPS_URL.'/assets/images/logo.gif" border="0" alt="" /><br><br>
        <h3>'.$story->title().'</h3>
        <small><b>'._AMS_NW_DATE.'</b>&nbsp;'.$datetime.' | <b>'._AMS_NW_TOPICC.'</b>&nbsp;'.$story->topic_title().'</small><br><br></td></tr>';
    echo '<tr valign="top" style="font:12px;"><td>'.$story->hometext().'<br>';
    $bodytext = $story->bodytext();
    $bodytext = str_replace('[pagebreak]', '<br style="page-break-after:always;">', $bodytext);
    if ('' != $bodytext) {
        echo $bodytext.'<br><br>';
    }
    echo '</td></tr></table></td></tr></table>
    <br><br>';
    printf(_AMS_NW_THISCOMESFROM, $xoopsConfig['sitename']);
    echo '<br><a href="'.XOOPS_URL.'/">'.XOOPS_URL.'</a><br><br>
        '._AMS_NW_URLFORSTORY.' <!-- Tag below can be used to display Permalink image --><!--img src="'.XOOPS_URL.'/modules/'.$xoopsModule->dirname().'/assets/images/x.gif" /--><br>
        <a href="'.XOOPS_URL.'/modules/'.$xoopsModule->dirname().'/article.php?storyid='.$story->storyid().'">'.XOOPS_URL.'/modules/'.$xoopsModule->dirname().'/article.php?storyid='.$story->storyid().'</a>
        </td></tr></table>
        </body>
        </html>
        ';
}
PrintPage($storyid);
