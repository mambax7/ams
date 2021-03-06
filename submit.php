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

require_once __DIR__ . '/../../mainfile.php';
//require_once XOOPS_ROOT_PATH . '/modules/' . $xoopsModule->getVar('dirname') . '/class/Story.php';
//require_once XOOPS_ROOT_PATH . '/modules/' . $xoopsModule->getVar('dirname') . '/class/Files.php';
require_once XOOPS_ROOT_PATH . '/class/uploader.php';

$xoopsConfig['module_cache'][$xoopsModule->getVar('mid')] = 0;
require_once XOOPS_ROOT_PATH . '/header.php';
/** @var Ams\Helper $helper */
$helper = Ams\Helper::getInstance();
$helper->loadLanguage('admin');

require_once XOOPS_ROOT_PATH . '/modules/' . $xoopsModule->getVar('dirname') . '/include/functions.inc.php';
$module_id = $xoopsModule->getVar('mid');
$groups    = $xoopsUser ? $xoopsUser->getGroups() : XOOPS_GROUP_ANONYMOUS;

$gpermHandler = xoops_getHandler('groupperm');
$perm_itemid  = \Xmf\Request::getInt('topic_id', 0, 'POST');

//If no access
if (!$gpermHandler->checkRight('ams_submit', $perm_itemid, $groups, $module_id)) {
    redirect_header(XOOPS_URL . '/modules/ams/index.php', 3, _NOPERM);
}

$op   = 'form';
$myts = \MyTextSanitizer::getInstance();

//If approve privileges
$approveprivilege = 0;
if ($xoopsUser && $gpermHandler->checkRight('ams_approve', $perm_itemid, $groups, $module_id)) {
    $approveprivilege = 1;
}

if (isset($_REQUEST['hometext'])) {
    $hometext = $myts->stripSlashesGPC($_REQUEST['hometext']);
}

if (isset($_REQUEST['bodytext'])) {
    $bodytext = $myts->stripSlashesGPC($_REQUEST['bodytext']);
}

if (isset($_REQUEST['storyid'])) {
    $storyid = (int)$_REQUEST['storyid'];
}

if (isset($_REQUEST['preview'])) {
    $op = 'preview';
} elseif (isset($_REQUEST['post'])) {
    $op = 'post';
} elseif (isset($_REQUEST['op']) && isset($_REQUEST['storyid'])) {
    if ($approveprivilege && 'edit' === $_REQUEST['op']) {
        $op = 'edit';
    } elseif ($approveprivilege && 'delete' === $_REQUEST['op']) {
        $op = 'delete';
    } elseif ($approveprivilege && _AMS_NW_OVERRIDE == $_REQUEST['op']) {
        $op = _AMS_NW_OVERRIDE;
    } elseif ($approveprivilege && _AMS_NW_FINDVERSION == $_REQUEST['op']) {
        $op = _AMS_NW_FINDVERSION;
    } elseif ($approveprivilege && 'override_ok' === $_REQUEST['op']) {
        $op = 'override_ok';
    } else {
        redirect_header(XOOPS_URL . '/modules/ams/index.php', 0, _NOPERM);
        exit();
    }
    if (isset($_REQUEST['storyid'])) {
        $storyid = (int)$_REQUEST['storyid'];
    }
}

switch ($op) {
    case 'delete':

        if (!empty($_POST['ok'])) {
            if (empty($_POST['storyid'])) {
                redirect_header(XOOPS_URL . '/modules/ams/index.php?op=newarticle', 2, _AMS_AM_EMPTYNODELETE);
                exit();
            }
            $storyid = (int)$_POST['storyid'];
            $story   = new Ams\Story($storyid);
            $story->delete();
            $sfiles   = new Ams\Files();
            $filesarr = [];
            $filesarr = $sfiles->getAllbyStory($storyid);
            if (count($filesarr) > 0) {
                foreach ($filesarr as $onefile) {
                    $onefile->delete();
                }
            }
            xoops_comment_delete($xoopsModule->getVar('mid'), $storyid);
            xoops_notification_deletebyitem($xoopsModule->getVar('mid'), 'story', $storyid);
            redirect_header(XOOPS_URL . '/modules/ams/index.php?op=newarticle', 1, _AMS_AM_DBUPDATED);
            exit();
        } else {

            //require_once __DIR__ . '/../../include/cp_header.php';
            //xoops_cp_header();
            echo '<h4>' . _AMS_AM_CONFIG . '</h4>';
            xoops_confirm(['op' => 'delete', 'storyid' => $_REQUEST['storyid'], 'ok' => 1], 'submit.php', _AMS_AM_RUSUREDEL);
        }
        break;

    case 'edit':
        if (!$approveprivilege) {
            redirect_header(XOOPS_URL . '/modules/ams/index.php', 0, _NOPERM);
            break;
        }
        $storyid = $_REQUEST['storyid'];
        $story   = new Ams\Story($storyid);

        echo $story->getPath(true) . ' > ' . _EDIT . ' ' . $story->title();

        echo "<table width='100%' border='0' cellspacing='1' class='outer'><tr><td class=\"odd\">";
        echo '<h4>' . _AMS_AM_EDITARTICLE . '</h4>';

        if ($story->published() > 0) {
            $story->setApproved(1);
        } else {
            $story->setApproved(0);
        }
        $edit = true;
        $type = $story->type();
        $story->uname();
        $author = $story->uname;
        include __DIR__ . '/include/storyform.inc.php';
        echo '</td></tr></table>';
        break;

    case 'preview':
        $xt = new Ams\Topic($xoopsDB->prefix('ams_topics'), $_POST['topic_id']);
        //$hometext = $_POST['hometext'];
        //$bodytext = $_POST['bodytext'];

        if (isset($_POST['storyid'])) {
            $story = new Ams\Story($storyid);
            $edit  = true;
        } else {
            $story = new Ams\Story();
            $story->setPublished(0);
            $story->setExpired(0);
            $edit = false;
        }
        $story->setTopicId($_POST['topic_id']);
        $story->setTitle($_POST['title']);
        $story->setHometext($hometext);
        $story->setBodytext($bodytext);
        $story->banner = isset($_POST['banner']) ? $_POST['banner'] : 0;
        if ($approveprivilege) {
            $story->setTopicdisplay($_POST['topicdisplay']);
            $story->setTopicalign($_POST['topicalign']);
            $story->audienceid = $_POST['audience'];
        } else {
            $noname = \Xmf\Request::getInt('noname', 0, 'POST');
        }
        $notifypub = \Xmf\Request::getInt('notifypub', 0, 'POST');
        $story->setNotifyPub($notifypub);

        if (isset($_POST['nosmiley']) && (0 == $_POST['nosmiley'] || 1 == $_POST['nosmiley'])) {
            $story->setNosmiley($_POST['nosmiley']);
        } else {
            $story->setNosmiley(0);
        }
        if ($approveprivilege) {
            $change = isset($_POST['change']) ? $_POST['change'] : 0;
            $story->setChange($change);
            $nohtml = \Xmf\Request::getInt('nohtml', 0, 'POST');
            $story->setNohtml($nohtml);
            if (!isset($_POST['approve'])) {
                $story->setApproved(0);
            } else {
                $story->setApproved($_POST['approve']);
            }
        } else {
            $story->setNohtml = 1;
        }
        //Display breadcrumbs
        if ($edit) {
            echo $story->getPath(true) . ' > ' . _EDIT . ' ' . $story->title();
        }

        //Display post preview
        $p_title    = $story->title('Preview');
        $p_hometext = $story->hometext('Preview');
        $p_hometext .= '<br>' . $story->bodytext('Preview');
        $topversion = (0 == $story->revision && 0 == $story->revisionminor) ? 1 : 0;
        $topicalign = isset($story->topicalign) ? 'align="' . $story->topicalign() . '"' : '';
    $p_hometext = (('' != $xt->topic_imgurl()) && $story->topicdisplay()) ? '<img src="assets/images/topics/' . $xt->topic_imgurl() . '" ' . $story->topicalign() . ' alt="" />' . $p_hometext : $p_hometext;

        //Added  in AMS 2.50 Final. replace deprecated themecenterposts function
        if (file_exists(XOOPS_ROOT_PATH . '/Frameworks/xoops22/class/xoopsformloader.php')) {
            if (!@require_once XOOPS_ROOT_PATH . '/Frameworks/xoops22/class/xoopsformloader.php') {
                require_once XOOPS_ROOT_PATH . '/class/xoopsformloader.php';
            }
        } else {
            require_once XOOPS_ROOT_PATH . '/class/xoopsformloader.php';
        }
        $pform = new \XoopsThemeForm($p_title, 'previewform', XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/submit.php');
        $pform->display();
        print $p_hometext;

        $story->uname();
        $author = $story->uname;

        //Display post edit form
        include __DIR__ . '/include/storyform.inc.php';
        break;

    case 'post':
        //$hometext = $_POST['hometext'];
        //$bodytext = $_POST['bodytext'];
        $nohtml_db = 1;
        if (is_object($xoopsUser)) {
            $uid = $xoopsUser->getVar('uid');
            if ($approveprivilege) {
                $nohtml_db = empty($_POST['nohtml']) ? 0 : 1;
            }
        } else {
            $uid = 0;
        }
        if (empty($_POST['storyid'])) {
            $story      = new Ams\Story();
            $oldapprove = 0;
            $story->setUid($uid);
        } else {
            $story      = new Ams\Story($_POST['storyid']);
            $oldapprove = $story->published() > 0 ? 1 : 0;

            $change = isset($_POST['change']) ? $_POST['change'] : 0;
            //If change = auto
            if (4 == $change) {
                if (($hometext != $story->hometext)
                    || ($bodytext != $story->bodytext)
                    || ($_POST['newauthor'] && $approveprivilege)) {
                    $change = 3;
                } else {
                    $change = 0;
                }
            }
            $story->setChange($change);
            if ($_POST['newauthor'] && $approveprivilege) {
                $story->setUid($uid);
            }
        }
        $story->banner = isset($_POST['banner']) ? $_POST['banner'] : 0;
        $story->setTitle($_POST['title']);

        $story->setHometext($hometext);
        if ($bodytext) {
            $story->setBodytext($bodytext);
        } else {
            $story->setBodytext(' ');
        }
        $story->setTopicId($_POST['topic_id']);
        $story->setHostname(xoops_getenv('REMOTE_ADDR'));
        $story->setNohtml($nohtml_db);
        $nosmiley  = \Xmf\Request::getInt('nosmiley', 0, 'POST');
        $notifypub = \Xmf\Request::getInt('notifypub', 0, 'POST');
        $story->setNosmiley($nosmiley);
        $story->setNotifyPub($notifypub);
        $story->setType($_POST['type']);
        // Set audience id to default
        $story->audienceid = 1;
        if ($approveprivilege) {
            $approve = isset($_POST['approve']) ? $_POST['approve'] : 0;
            if (!empty($_POST['autodate'])) {
                $pubdate = strtotime($_POST['publish_date']['date']) + $_POST['publish_date']['time'];
                $offset  = $xoopsUser->timezone() - $xoopsConfig['server_TZ'];
                $pubdate -= ($offset * 3600);
                if ($pubdate - time() > 0 && $pubdate - time() < 600) { //fix bug article missing for 10 minute after republish
                    $pubdate -= 601; //set publish date backward 10 minute
                }

                $story->setPublished($pubdate);
            } else {
                $story->setPublished(time());
            }
            if (!empty($_POST['autoexpdate'])) {
                $expiry_date = strtotime($_POST['expiry_date']['date']) + $_POST['expiry_date']['time'];
                $offset      = $xoopsUser->timezone() - $xoopsConfig['server_TZ'];
                $expiry_date -= ($offset * 3600);
                $story->setExpired($expiry_date);
            } else {
                $story->setExpired(0);
            }

            $story->setTopicdisplay($_POST['topicdisplay']);
            $story->setTopicalign($_POST['topicalign']);
            $story->setIhome($_POST['ihome']);

            if (!$approve) {
                $story->setPublished(0);
            }

            if ($story->published() >= $story->expired()) {
                $story->setExpired(0);
            }

            $story->audienceid = (int)$_POST['audience'];
        } elseif (1 == $helper->getConfig('autoapprove') && !$approveprivilege) {
            $approve = 1;
            $story->setPublished(time());
            $story->setExpired(0);
            $story->setTopicalign('R');
        } else {
            $story->setPublished(0);
            $approve = 0;
            $story->setExpired(0);
        }
        $story->setApproved($approve);
        $result = $story->store();

        if ($result) {
            // Notification
            $notificationHandler = xoops_getHandler('notification');
            $tags                = [];
            $tags['STORY_NAME']  = $story->title();
            $tags['STORY_URL']   = XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/article.php?storyid=' . $story->storyid();
            if (1 == $approve && 0 == $oldapprove && $story->published <= time()) {
                $notificationHandler->triggerEvent('global', 0, 'new_story', $tags);
            } elseif (1 != $approve) {
                $tags['WAITINGSTORIES_URL'] = XOOPS_URL . '/modules/' . $xoopsModule->getVar('dirname') . '/submit.php?op=edit&amp;storyid='.$story->storyid();
                $notificationHandler->triggerEvent('global', 0, 'story_submit', $tags);

                // If notify checkbox is set, add subscription for approve
                if ($notifypub) {
                    require_once XOOPS_ROOT_PATH . '/include/notification_constants.php';
                    $notificationHandler->subscribe('story', $story->storyid(), 'approve', XOOPS_NOTIFICATION_MODE_SENDONCETHENDELETE);
                }
            }

            // Manage upload(s)
            if (isset($_POST['delupload']) && count($_POST['delupload']) > 0) {
                foreach ($_POST['delupload'] as $onefile) {
                    $sfiles = new Ams\Files($onefile);
                    $sfiles->delete();
                }
            }

            if (isset($_POST['xoops_upload_file']) && isset($_FILES[$_POST['xoops_upload_file'][0]])) {
                $fldname = $_FILES[$_POST['xoops_upload_file'][0]];
                $fldname = get_magic_quotes_gpc() ? stripslashes($fldname['name']) : $fldname['name'];
                if (trim('' != $fldname)) {
                    $sfiles   = new Ams\Files();
                    $destname = $sfiles->createUploadName(XOOPS_UPLOAD_PATH, $fldname);
                    // Actually : Web pictures (png, gif, jpeg), zip, doc, xls, pdf, gtar, tar, txt, tiff, htm, xml, ico,swf flv, mp3, bmp, ra, mov, swc. swf not allow by xoops, not AMS
                    $permittedtypes = explode(';', ams_getmoduleoption('mimetypes'));
                    $uploader       = new \XoopsMediaUploader(XOOPS_UPLOAD_PATH, $permittedtypes, $helper->getConfig('maxuploadsize'));
                    $uploader->setTargetFileName($destname);
                    if ($uploader->fetchMedia($_POST['xoops_upload_file'][0])) {
                        if ($uploader->upload()) {
                            $sfiles->setFileRealName($uploader->getMediaName());
                            $sfiles->setStoryid($story->storyid());
                            $sfiles->setMimetype($sfiles->giveMimetype(XOOPS_UPLOAD_PATH . '/' . $uploader->getMediaName()));
                            $sfiles->setDownloadname($destname);
                            if (!$sfiles->store()) {
                                //echo _AMS_AM_UPLOAD_DBERROR_SAVE;
                                echo $uploader->getErrors(); //Better error message
                            }
                        } else {
                            //echo _AMS_AM_UPLOAD_ERROR;
                            echo $uploader->getErrors(); //Better error message
                        }
                    } else {
                        if (!$_FILES[$fldname]['tmp_name']) {
                            echo $uploader->getErrors();
                            echo 'Or file size too big'; //Add additional comment since the original error message not so accurate. TODO : add this into language
                        } else {
                            echo $uploader->getErrors();
                        }
                    }
                }
            }
        } else {
            if (true === $story->versionConflict) {
                include __DIR__ . '/include/versionconflict.inc.php';
                break;
            } else {
                $message = $story->renderErrors();
            }
        }
        if (!isset($message)) {
            $message = _AMS_NW_THANKS;
        }
        redirect_header(XOOPS_URL . '/modules/ams/index.php', 2, $message);
        break;

    case _AMS_NW_OVERRIDE:
        if (!$approveprivilege || !$xoopsUser) {
            redirect_header(XOOPS_URL . '/modules/ams/index.php', 3, _NOPERM);
        }
        $change  = isset($_POST['change']) ? $_POST['change'] : 0;
        $hiddens = [
            'bodytext' => $bodytext,
            'hometext' => $hometext,
            'storyid'  => $storyid,
            'change'   => $change,
            'op'       => 'override_ok'
        ];
        $story   = new Ams\Story($storyid);
        $story->setChange($change);

        $message = '';
        $story->calculateVersion();
        $message         .= _AMS_NW_TRYINGTOSAVE . ' ' . $story->version . '.' . $story->revision . '.' . $story->revisionminor . ' <br>';
        $higher_versions = $story->getVersions(true);
        if (count($higher_versions) > 0) {
            $message .= sprintf(_AMS_NW_VERSIONSEXIST, count($higher_versions));
            $message .= '<br>';
            foreach ($higher_versions as $key => $version) {
                $message .= $version['version'] . '.' . $version['revision'] . '.' . $version['revisionminor'] . '<br>';
            }
        }
        $message .= _AMS_NW_AREYOUSUREOVERRIDE;
        xoops_confirm($hiddens, 'submit.php', $message, _YES);
        break;

    case 'override_ok':
        if (!$approveprivilege || !$xoopsUser) {
            redirect_header(XOOPS_URL . '/modules/ams/index.php', 3, _NOPERM);
        }
        $story  = new Ams\Story($_POST['storyid']);
        $change = isset($_POST['change']) ? $_POST['change'] : 0;
        $story->setChange($change);
        $story->setUid($xoopsUser->getVar('uid'));
        $story->setHometext($hometext);
        $story->setBodytext($bodytext);
        $story->calculateVersion();
        if ($story->overrideVersion()) {
            $message = sprintf(_AMS_NW_VERSIONUPDATED, $story->version . '.' . $story->revision . '.' . $story->revisionminor);
        } else {
            $message = $story->renderErrors();
        }
        redirect_header(XOOPS_URL . '/modules/ams/article.php?storyid=' . $story->storyid, 3, $message);
        break;

    case _AMS_NW_FINDVERSION:
        if (!$approveprivilege || !$xoopsUser) {
            redirect_header(XOOPS_URL . '/modules/ams/index.php', 3, _NOPERM);
            exit();
        }
        $story = new Ams\Story($_POST['storyid']);
        $story->setUid($xoopsUser->getVar('uid'));
        $story->setHometext($hometext);
        $story->setBodytext($bodytext);

        $change = isset($_POST['change']) ? $_POST['change'] : 0;
        $story->setChange($change);
        if ($story->calculateVersion(true)) {
            if ($story->updateVersion()) {
                $message = sprintf(_AMS_NW_VERSIONUPDATED, $story->version . '.' . $story->revision . '.' . $story->revisionminor);
            //redirect_header('article.php?storyid='.$story->storyid(), 3, $message);
                //exit();
            } else {
                $message = $story->renderErrors();
            }
        } else {
            $message = $story->renderErrors();
        }
        redirect_header(XOOPS_URL . '/modules/ams/article.php?storyid=' . $story->storyid(), 3, $message);
        break;

    case 'form':
    default:
        $story = new Ams\Story();
        $story->setTitle('');
        $story->setHometext('');
        $noname = 0;
        $story->setNohtml(0);
        $story->setNosmiley(0);
        $story->setNotifyPub(1);
        $story->setTopicId(0);
        if ($approveprivilege) {
            $story->setTopicdisplay(0);
            $story->setTopicalign('R');
            $story->setIhome(0);
            $story->setBodytext('');
            $story->setApproved(1);
            $story->set = '';
            $expired    = 0;
            $published  = 0;
            $audience   = 0;
        }
        $banner = '';
        $edit   = false;
        include __DIR__ . '/include/storyform.inc.php';
        break;
}
include XOOPS_ROOT_PATH . '/footer.php';
