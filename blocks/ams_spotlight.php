<?php
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <http://www.xoops.org/>                             //
// ------------------------------------------------------------------------- //
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

function b_ams_spotlight_show($options)
{
    require_once XOOPS_ROOT_PATH . '/modules/ams/class/Story.php';
    global $xoopsModule;
    if (!isset($xoopsModule) || 'AMS' !== $xoopsModule->getVar('dirname')) {
        $moduleHandler = xoops_getHandler('module');
        $amsModule = $moduleHandler->getByDirname('AMS');
    } else {
        $amsModule = $xoopsModule;
    }

    $spotlightHandler = Ams\Helper::getInstance()->getHandler('Spotlight');
    $block = $spotlightHandler->getSpotlightBlock();

    //load special block instruction if exist
    if (file_exists(XOOPS_ROOT_PATH.'/modules/ams/templates/'.$options[2].'.php')) {
        include XOOPS_ROOT_PATH.'/modules/ams/templates/'.$options[2].'.php';
    }

    $GLOBALS['xoopsTpl']->assign('spotlights', $block['spotlights']);
    $block['spotlightcontent'] = $GLOBALS['xoopsTpl']->fetch('db:'.$options[2]);
    $GLOBALS['xoopsTpl']->clear_assign('spotlights');

    if (count($options) > 0) {
        if ((int)$options[0] > 0) {
            $stories = Story::getAllPublished((int)$options[0], 0, false, 0, 1, true, 'published', $block['ids']);
            $count = 0;
            foreach (array_keys($stories) as $i) {
                $block['stories'][] = [
                    'id'                 => $stories[$i]->storyid(),
                    'title'              => $stories[$i]->title(),
                    'hits'               => $stories[$i]->counter(),
                    'friendlyurl_enable' => $stories[$i]->friendlyurl_enable,
                    'friendlyurl'        => $stories[$i]->friendlyurl
                ];
                $count ++;
            }
        }

        if (1 == $options[1]) {
            $block['total_art'] = Story::countPublishedByTopic();
            $block['total_read'] = Story::countReads();
            $commentHandler = xoops_getHandler('comment');
            $block['total_comments'] = $commentHandler->getCount(new \Criteria('com_modid', $amsModule->getVar('mid')));
        }
        $block['showministats'] = $options[1];
        $block['showother'] = (int)$options[0] > 0;
    }

    return $block;
}

function b_ams_spotlight_edit($options)
{
    require_once XOOPS_ROOT_PATH . '/class/xoopsformloader.php';
    require_once XOOPS_ROOT_PATH . '/modules/ams/include/functions.inc.php';

    global $xoopsModule;
    AMS_updateCache();
    if (!isset($xoopsModule) || 'AMS' !== $xoopsModule->getVar('dirname')) {
        $moduleHandler = xoops_getHandler('module');
        $module = $moduleHandler->getByDirname('AMS');
    } else {
        $module = $xoopsModule;
    }
    $configHandler = xoops_getHandler('config');
    $moduleConfig = $configHandler->getConfigsByCat(0, $module->getVar('mid'));
    $templates_list=array_flip($moduleConfig['spotlight_template']);

    //fix template list value lost after module update
    foreach ($templates_list as $k => $v) {
        $templates_list[$k] = substr($k, 20, strlen($k)-25);
    }


    $form = new \XoopsFormElementTray('', '<br/><br>');
    $numarticles_select = new \XoopsFormText(_AMS_MB_SPOT_NUMARTICLES, 'options[0]', 10, 10, $options[0]);
    $form->addElement($numarticles_select);

    $form->addElement(new \XoopsFormRadioYN(_AMS_MB_SPOT_SHOWMINISTATS, 'options[1]', $options[1]));

    //spotlight template selection
    $template_select = new \XoopsFormSelect(_AMS_MB_SPOTLIGHT_TEMPLATE, 'options[2]', $options[2]);
    $template_select->addOptionArray($templates_list);
    $template_select->setExtra("onchange='showImgSelected(\"template_preview\", \"options[2]\", \"" . '/modules/ams/assets/images/spotlight_preview' . '", ".jpg", "'
                               . XOOPS_URL . "\")'");
    $template_select->setDescription(_AMS_MB_SPOTLIGHT_TEMPLATE_DESC);
    $form->addElement($template_select);

    //spotlight preview image
    $imgpath=sprintf('', 'modules/ams/assets/images/spotlight_preview/');
    $form -> addElement(new \XoopsFormLabel('', "<br><img src='" . XOOPS_URL . '/modules/ams/assets/images/spotlight_preview/'
                                               . $options[2] . ".jpg' name='template_preview' id='template_preview' alt='' />"));


    return $form->render();
}
