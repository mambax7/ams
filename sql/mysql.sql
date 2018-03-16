#
# Table structure for table `ams_article`
#

CREATE TABLE `ams_setting` (
  settingid    INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  settingvalue VARCHAR(100)     NOT NULL,
  settingtype  VARCHAR(30)      NOT NULL,
  PRIMARY KEY (`settingid`)
)
  ENGINE = MyISAM;

INSERT INTO `ams_setting` (settingid, settingvalue, settingtype) VALUES (1, "0", "friendlyurl_enable");
INSERT INTO `ams_setting` (settingid, settingvalue, settingtype) VALUES (2, "[XOOPS_URL]/[AMS_DIR]/[AUDIENCE]/[TOPIC]", "friendlyurl_template");


CREATE TABLE ams_article (
  storyid      INT(8) UNSIGNED      NOT NULL AUTO_INCREMENT,
  title        VARCHAR(255)         NOT NULL DEFAULT '',
  created      INT(10) UNSIGNED     NOT NULL DEFAULT '0',
  published    INT(10) UNSIGNED     NOT NULL DEFAULT '0',
  expired      INT(10) UNSIGNED     NOT NULL DEFAULT '0',
  hostname     VARCHAR(20)          NOT NULL DEFAULT '',
  nohtml       TINYINT(1)           NOT NULL DEFAULT '0',
  nosmiley     TINYINT(1)           NOT NULL DEFAULT '0',
  counter      INT(8) UNSIGNED      NOT NULL DEFAULT '0',
  topicid      SMALLINT(4) UNSIGNED NOT NULL DEFAULT '1',
  ihome        TINYINT(1)           NOT NULL DEFAULT '0',
  notifypub    TINYINT(1)           NOT NULL DEFAULT '0',
  story_type   VARCHAR(5)           NOT NULL DEFAULT '',
  topicdisplay TINYINT(1)           NOT NULL DEFAULT '0',
  topicalign   CHAR(1)              NOT NULL DEFAULT 'R',
  comments     SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
  rating       INT(3)               NOT NULL DEFAULT '0',
  banner       TEXT,
  audienceid   INT(11)              NOT NULL DEFAULT 1,
  PRIMARY KEY (storyid),
  KEY idxstoriestopic (topicid),
  KEY ihome (ihome),
  KEY published_ihome (published, ihome),
  KEY title (title(40)),
  KEY created (created),
  FULLTEXT KEY search (title)
)
  ENGINE = MyISAM;

#
# Table structure for table `ams_text`
#

CREATE TABLE ams_text (
  storyid       INT(8) UNSIGNED  NOT NULL,
  version       INT(8) UNSIGNED  NOT NULL DEFAULT '1',
  revision      INT(8) UNSIGNED  NOT NULL DEFAULT '0',
  revisionminor INT(8) UNSIGNED  NOT NULL DEFAULT '0',
  uid           INT(5) UNSIGNED  NOT NULL DEFAULT '0',
  hometext      TEXT             NOT NULL,
  bodytext      TEXT             NOT NULL,
  current       TINYINT(2)       NOT NULL DEFAULT '0',
  updated       INT(10) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`storyid`, `version`, `revision`, `revisionminor`),
  KEY uid (uid),
  FULLTEXT KEY search (hometext, bodytext)
)
  ENGINE = MyISAM;

#
# Table structure for table `ams_files`
#

CREATE TABLE ams_files (
  fileid       INT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
  filerealname VARCHAR(255)    NOT NULL DEFAULT '',
  storyid      INT(8) UNSIGNED NOT NULL DEFAULT '0',
  date         INT(10)         NOT NULL DEFAULT '0',
  mimetype     VARCHAR(64)     NOT NULL DEFAULT '',
  downloadname VARCHAR(255)    NOT NULL DEFAULT '',
  counter      INT(8) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (fileid),
  KEY storyid (storyid)
)
  ENGINE = MyISAM;

#
# Table structure for table `ams_topics`
#

CREATE TABLE ams_topics (
  topic_id       SMALLINT(4) UNSIGNED NOT NULL AUTO_INCREMENT,
  topic_pid      SMALLINT(4) UNSIGNED NOT NULL DEFAULT '0',
  topic_imgurl   VARCHAR(50)          NOT NULL DEFAULT '',
  topic_title    VARCHAR(50)          NOT NULL DEFAULT '',
  banner         TEXT,
  banner_inherit TINYINT(2)           NOT NULL DEFAULT 0,
  forum_id       INT(12)              NOT NULL DEFAULT 0,
  weight         INT(5)               NOT NULL DEFAULT 1,
  PRIMARY KEY (topic_id),
  KEY pid (topic_pid)
)
  ENGINE = MyISAM;

#
# Table structure for table `ams_link`
#

CREATE TABLE `ams_link` (
  `linkid`        INT(12) UNSIGNED NOT NULL AUTO_INCREMENT,
  `storyid`       INT(12)          NOT NULL,
  `link_module`   INT(12)          NOT NULL,
  `link_link`     VARCHAR(120)     NOT NULL DEFAULT '',
  `link_title`    VARCHAR(70)      NOT NULL DEFAULT '',
  `link_counter`  INT(12) UNSIGNED NOT NULL DEFAULT 0,
  `link_position` VARCHAR(12)      NOT NULL DEFAULT 'bottom',
  PRIMARY KEY (`linkid`)
)
  ENGINE = MyISAM;

#
# Table structure for table `ams_rating`
#

CREATE TABLE `ams_rating` (
  ratingid        INT(11) UNSIGNED     NOT NULL AUTO_INCREMENT,
  storyid         INT(11) UNSIGNED     NOT NULL DEFAULT '0',
  ratinguser      INT(11)              NOT NULL DEFAULT '0',
  rating          SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0',
  ratinghostname  VARCHAR(60)          NOT NULL DEFAULT '',
  ratingtimestamp INT(10)              NOT NULL DEFAULT '0',
  PRIMARY KEY (`ratingid`),
  KEY `ratinguser` (`ratinguser`),
  KEY `ratinghostname` (`ratinghostname`),
  KEY `storyid` (`storyid`)
)
  ENGINE = MyISAM;

#
# Table structure for table `ams_audience`
#

CREATE TABLE `ams_audience` (
  audienceid INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  audience   VARCHAR(30)      NOT NULL,
  PRIMARY KEY (`audienceid`)
)
  ENGINE = MyISAM;

INSERT INTO `ams_audience` (audienceid, audience) VALUES (1, "Default");

CREATE TABLE `ams_spotlight` (
  `spotlightid` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `showimage`   TINYINT(1)                DEFAULT 1,
  `image`       VARCHAR(255)              DEFAULT '',
  `teaser`      TEXT,
  `autoteaser`  TINYINT(1)                DEFAULT 1,
  `maxlength`   INT(5)                    DEFAULT 100,
  `display`     TINYINT(1)                DEFAULT 1,
  `mode`        TINYINT(1)                DEFAULT 1,
  `storyid`     INT(12)                   DEFAULT 0,
  `topicid`     INT(12)                   DEFAULT 0,
  `weight`      INT(5)                    DEFAULT 1,
  PRIMARY KEY (`spotlightid`)
)
  ENGINE = MyISAM;
