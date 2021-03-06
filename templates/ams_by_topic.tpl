<{if $breadcrumb|default:false}><div class="breadcrumb"><{$breadcrumb}></div><{/if}>

<{if isset($topicbanner)}><div><{$topicbanner}></div><{/if}>

<{if $displaynav == true}>
  <div style="text-align: right;"><b><a href="<{$xoops_url}>/modules/ams/submit.php"><img src="<{$xoops_url}>/modules/ams/assets/images/new.png"><{$lang_postnewarticle}></a></b></div>
  <div style="text-align: center;">
    <{$topic_form.javascript}>
        <form name="<{$topic_form.name}>" id="<{$topic_form.name}>" action="<{$topic_form.action}>" method="<{$topic_form.method}>">
    <table id="topicform" cellspacing="0">
    <!-- start of form elements loop -->
    <tr valign="top">
    <td>
    <{foreach item=element from=$topic_form.elements}>
      <{if $element.hidden != true}>
            <{$element.body}>&nbsp;
      <{else}>
        <{$element.body}>
      <{/if}>
    <{/foreach}>
    </td>
    </tr>
    <!-- end of form elements loop -->
    </table>
  </form>
  <hr />
  </div>
<{/if}>

<div class="item">
<table width="100%" border="0">
    <tr>
        <{counter start=0 print=false assign=topicnum}>
        <{foreach item=topic from=$topics}>
            <td width="<{$columnwidth}>%" valign="top">
                <{if $topic.subtopiccount > 0}>
                    <div style="float:left; width: 70%;">
                <{else}>
                    <div style="float:left; width: 99%;">
                <{/if}>
                    <div>
                        <div>
                            <table cellpadding="0" cellspacing="0" width="100%">
                                <tr class="itemHead" style="line-height: 200%;">
                                    <td class="itemTitle">
                                        &nbsp;&nbsp;<a href="<{$xoops_url}>/modules/ams/index.php?storytopic=<{$topic.id}>"><{$topic.title}></a>
                                    </td>
                                    <td class="itemTitle" style="text-align: right;">
                                        <{$smarty.const._AMS_NW_TOTALARTICLES}> : <{$topic.articlecount}>&nbsp;&nbsp;
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <{assign var="storycount" value=0}>
                        <{section name=i loop=$topic.stories}>
                          <{if $storycount == 0}>
                            <ul>
				<{if $topic.stories[i].friendlyurl_enable != 1}>
                                <li><a href="<{$xoops_url}>/modules/ams/article.php?storyid=<{$topic.stories[i].id}>"><{$topic.stories[i].title}></a> (<{$topic.stories[i].posttime}>)</li>
				<{else}>
                                <li><a href="<{$topic.stories[i].friendlyurl}>"><{$topic.stories[i].title}></a> (<{$topic.stories[i].posttime}>)</li>
				<{/if}>
                            <{assign var="storycount" value=1}>
                          <{else}>
				<{if $topic.stories[i].friendlyurl_enable != 1}>
                            	<li><a href="<{$xoops_url}>/modules/ams/article.php?storyid=<{$topic.stories[i].id}>"><{$topic.stories[i].title}></a> (<{$topic.stories[i].posttime}>)</li>
				<{else}>
                            	<li><a href="<{$topic.stories[i].friendlyurl}>"><{$topic.stories[i].title}></a> (<{$topic.stories[i].posttime}>)</li>
				<{/if}>

                          <{/if}>
                          <{counter}>
                        <{/section}>
                        <{if $storycount > 0}>
                            </ul>
                            <a href="<{$xoops_url}>/modules/ams/index.php?storytopic=<{$topic.id}>"><{$lang_morereleases}><{$topic.title}></a>
                        <{/if}>
                    </div>
                </div>
                <{if $topic.subtopiccount > 0}>
                    <div class="outer" style="float:left; width: 29%; margin-left: 5px;">
                        <div class="itemHead">
                            <span class="itemTitle">
                                <{$smarty.const._AMS_MA_SUBTOPICS}><{$topic.title}>
                            </span>
                        </div>
                        <ul>
                            <{foreach item=subtopic from=$topic.subtopics}>
                                <{if $subtopic.imageurl != "" }>
                                    <li class="<{cycle values="even,odd"}>" style="list-style: url(<{$xoops_url}>/modules/ams/assets/images/topics/<{$subtopic.imageurl}>) circle; list-style-position: inside; text-align: left;">
                                <{else}>
                                    <li>
           <div class="breadcrumb"><{$breadcrumb}></div>
<div><{if isset($topicbanner)}><{$topicbanner}><{/if}></div>

<{if $displaynav == true}>
  <div style="text-align: center;">
    <{$topic_form.javascript}>
        <form name="<{$topic_form.name}>" id="<{$topic_form.name}>" action="<{$topic_form.action}>" method="<{$topic_form.method}>">
    <table id="topicform" cellspacing="0">
    <!-- start of form elements loop -->
    <tr valign="top">
    <td>
    <{foreach item=element from=$topic_form.elements}>
      <{if $element.hidden != true}>
            <{$element.body}>&nbsp;
      <{else}>
        <{$element.body}>
      <{/if}>
    <{/foreach}>
    </td>
    </tr>
    <!-- end of form elements loop -->
    </table>
  </form>
  <hr />
  </div>
<{/if}>

<div style="margin: 10px;"><{$pagenav}></div>
<table width="100%" border="0">
    <tr><td width="<{$columnwidth}>%"><ul>
        <!-- start news item loop -->
        <{counter assign=story_count start=0 print=false}>
        <{section name=i loop=$stories}>
            <li><a href="<{$xoops_url}>/modules/ams/article.php?storyid=<{$stories[i].id}>"><{$stories[i].title}></a> (<{$stories[i].posttime}>)</li>
        <{counter}>
        <{/section}>
    </ul></td></tr>
</table>

<div style="text-align: right; margin: 10px;"><{$pagenav}></div>
<{include file='db:system_notification_select.tpl'}>
<br>                     <{/if}>
                                    <a valign="middle" href="<{$xoops_url}>/modules/ams/index.php?storytopic=<{$subtopic.id}>"><{$subtopic.title}></a>

                                </li>
                            <{/foreach}>
                        </ul>
                    </div>
                <{/if}>
            </td>
            <{if $topicnum mod $columns == 0}>
                </tr>
                <tr>
            <{/if}>
        <{/foreach}>
    </tr>
</table>
<!-- end topic loop -->
</div>
<{include file='db:system_notification_select.tpl'}>
