<?php namespace XoopsModules\Ams;
// ------------------------------------------------------------------------ //
//               XOOPS - PHP Content Management System                      //
//                   Copyright (c) 2000 XOOPS.org                           //
//                      <http://www.xoops.org/>                             //
// ------------------------------------------------------------------------ //
// This program is free software; you can redistribute it and/or modify     //
// it under the terms of the GNU General Public License as published by     //
// the Free Software Foundation; either version 2 of the License, or        //
// (at your option) any later version.                                      //
// //
// You may not change or alter any portion of this comment or credits       //
// of supporting developers from this source code or any supporting         //
// source code which is considered copyrighted (c) material of the          //
// original comment or credit authors.                                      //
// //
// This program is distributed in the hope that it will be useful,          //
// but WITHOUT ANY WARRANTY; without even the implied warranty of           //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
// GNU General Public License for more details.                             //
// //
// You should have received a copy of the GNU General Public License        //
// along with this program; if not, write to the Free Software              //
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
// ------------------------------------------------------------------------ //

use XoopsModules\Ams;

class SpotlightHandler extends \XoopsPersistableObjectHandler //IdgObjectHandler
{
    public function __construct($db)
    {
        parent::__construct($db, 'ams_spotlight', 'Spotlight', 'spotlightid');
    }

    public function getSpotlightBlock($display_only = true)
    {
        $myts = \MyTextSanitizer::getInstance();
//        include_once XOOPS_ROOT_PATH . '/modules/ams/class/Story.php';
        $block = [];

        if ($display_only) {
            $criteria = new \Criteria('display', 1);
        } else {
            $criteria = new \Criteria('spotlightid', 0, '>');
        }
        $criteria->setSort('weight');
        $spots =& $this->getObjects($criteria);
        if (0 == count($spots)) {
            return $block;
        }
        $ids = [];
        foreach (array_keys($spots) as $i) {
            switch ($spots[$i]->getVar('mode')) {
                // Latest Article
                case 1:
                    $article_arr = Story::getAllPublished(1, 0, false, 0, 1, true, 'published', $ids);
                    $article = $article_arr[0];
                    if (!is_object($article)) {
                        continue;
                    }
                    $ids[$article->storyid()] = $article->storyid();
                    $uids[] = $article->uid();
                    break;

                // Latest Article in Topic
                case 2:
                    $article_arr = Story::getAllPublished(1, 0, false, $spots[$i]->getVar('topicid'), 1, true, 'published', $ids);
                    $article = $article_arr[0];
                    if (!is_object($article)) {
                        continue;
                    }
                    $ids[$article->storyid()] = $article->storyid();
                    $uids[] = $article->uid();
                    break;

                // Specific Article
                case 3:
                    $article = new Ams\Story($spots[$i]->getVar('storyid'));
                    if (!is_object($article)) {
                        continue;
                    }
                    $ids[$article->storyid()] = $article->storyid();
                    $uids[] = $article->uid();
                    break;

                case 4:
                    $article = '';
            }
            $spotarticles[] = $article;
        }
        $memberHandler = xoops_getHandler('member');
        $users = $memberHandler->getUsers(new \Criteria('uid', '(' . implode(',', array_unique($uids)) . ')', 'IN'), true);
        foreach (array_keys($spotarticles) as $i) {
            $article = $spotarticles[$i];
            $image = $spots[$i]->getImage($article);
            if (is_object($article)) {
                $article->uname($users);

                $teaser = 1 != $spots[$i]->getVar('autoteaser') ? $myts->displayTarea($spots[$i]->getVar('teaser', 'n'), 1) : ($spots[$i]->getVar('maxlength') > 0 ? xoops_substr(
                    $article->hometext(),
                    0,
                    $spots[$i]->getVar('maxlength'),
                                                                                                                                                                                  ''
                ) : $article->hometext());
                $id = $article->storyid();
                $title = $article->title();
                $hits = $article->counter();
                $poster = $article->uname;
                $posterid = $article->uid();
                $posttime = formatTimestamp($article->published());
                $custom = 0;
                $friendlyurl_enable = $article->friendlyurl_enable;
                $friendlyurl = $article->friendlyurl;
            } else {
                $id = 0;
                $title = '';
                $hits = 0;
                $custom = 1;
                $posterid = 0;
                $posttime = '';
                $poster = '';
                $teaser = $myts->displayTarea($spots[$i]->getVar('teaser', 'n'), 1);
            }
            $block['spotlights'][] = [
                'spotid'             => $spots[$i]->getVar('spotlightid'),
                'id'                 => $id,
                'title'              => $title,
                'hits'               => $hits,
                'image'              => $image,
                'text'               => $teaser,
                'weight'             => $spots[$i]->getVar('weight'),
                'display'            => $spots[$i]->getVar('display'),
                'posttime'           => $posttime,
                'poster'             => $poster,
                'posterid'           => $posterid,
                'autoteaser'         => $spots[$i]->getVar('autoteaser'),
                'custom'             => $custom,
                'friendlyurl_enable' => $friendlyurl_enable,
                'friendlyurl'        => $friendlyurl
            ];
        }
        $block['ids'] = $ids;
        return $block;
    }
}
