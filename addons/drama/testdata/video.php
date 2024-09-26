<?php

/*
return array (
    'INSERT INTO `vs_drama_video` VALUES (null, __SITEID__, \'古装片\', \'内地\', \'2015\', \'我的修仙老公\', \'《我的修仙老公》：该网络短剧以玄幻为背景，讲述了一位普通女子与一位修仙者相爱并结为夫妻的故事。在与修仙世界的交集中，女主角面对着各种神秘和危险，同时也展现出自己的勇敢和智慧。通过两人之间的相互扶持和成长，他们共同面对了修仙世界的挑战，并找到了属于自己的幸福。\', \'http://222.186.43.171:99/p/94-我的修仙老公/logo.png\', \'hot,recommend\', \'《我的修仙老公》：该网络短剧以玄幻为背景，讲述了一位普通女子与一位修仙者相爱并结为夫妻的故事。在与修仙世界的交集中，女主角面对着各种神秘和危险，同时也展现出自己的勇敢和智慧。通过两人之间的相互扶持和成长，他们共同面对了修仙世界的挑战，并找到了属于自己的幸福。\', \'名著\', \'图文详情暂时无用\', 180, 150, 80, \'8.5\', 0, 0, 0, 0, 0, 0, 0, 0, 0, 176, \'up\', null,null,0,1, 1691477553, 1691477553, null);' =>
        array (
            0 => 'INSERT INTO `vs_drama_video_episodes` VALUES (null, __SITEID__, __LASTINSERTID__, \'第1集\', \'http://222.186.43.171:99/p/94-我的修仙老公/logo.png\', \'http://222.186.43.171:99/p/94-我的修仙老公/1.mp4\', 95, 0, 0, 0, 0, 0, 0, 0, 2343, 3493, 124, 659, 0, \'normal\', null,1691477620, 1691477620, null);',
            1 => 'INSERT INTO `vs_drama_video_episodes` VALUES (null, __SITEID__, __LASTINSERTID__, \'第2集\', \'http://222.186.43.171:99/p/94-我的修仙老公/logo.png\', \'http://222.186.43.171:99/p/94-我的修仙老公/2.mp4\', 95, 0, 0, 0, 0, 0, 0, 0, 2343, 3493, 124, 659, 0, \'normal\', null,1691477620, 1691477620, null);',
        ),
);
*/


return array (
    'INSERT INTO `vs_drama_video`(`id`,`site_id`,`category_ids`,`area_id`,`year_id`,`title`,`subtitle`,`image`,`flags`,`description`,`tags`,`content`,`price`,`vprice`,`episodes`,`score`,`sales`,`favorites`,`views`,`shares`,`likes`,`fake_views`,`fake_favorites`,`fake_shares`,`fake_likes`,`weigh`,`status`,`createtime`,`updatetime`,`deletetime`,`xcx_sync`,`platform`) VALUES (null, __SITEID__, \'古装片\', \'内地\', \'2015\', \'我的修仙老公\', \'《我的修仙老公》：该网络短剧以玄幻为背景，讲述了一位普通女子与一位修仙者相爱并结为夫妻的故事。在与修仙世界的交集中，女主角面对着各种神秘和危险，同时也展现出自己的勇敢和智慧。通过两人之间的相互扶持和成长，他们共同面对了修仙世界的挑战，并找到了属于自己的幸福。\', \'http://222.186.43.171:99/p/94-我的修仙老公/logo.png\', \'hot,recommend\', \'《我的修仙老公》：该网络短剧以玄幻为背景，讲述了一位普通女子与一位修仙者相爱并结为夫妻的故事。在与修仙世界的交集中，女主角面对着各种神秘和危险，同时也展现出自己的勇敢和智慧。通过两人之间的相互扶持和成长，他们共同面对了修仙世界的挑战，并找到了属于自己的幸福。\', \'名著\', \'图文详情暂时无用\', 180, 150, 80, \'8.5\', 0, 0, 0, 0, 0, 0, 0, 0, 0, 176, \'up\', 1691477553, 1691477553, null,0,\'1\');' =>
        array (
            0 => 'INSERT INTO `vs_drama_video_episodes`(`id`,`site_id`,`vid`,`name`,`image`,`video`,`duration`,`price`,`vprice`,`sales`,`likes`,`views`,`favorites`,`shares`,`fake_likes`,`fake_views`,`fake_favorites`,`fake_shares`,`weigh`,`status`,`updatetime`,`createtime`,`deletetime`) VALUES (null, __SITEID__, __LASTINSERTID__, \'第1集\', \'http://222.186.43.171:99/p/94-我的修仙老公/logo.png\', \'http://222.186.43.171:99/p/94-我的修仙老公/1.mp4\', 95, 0, 0, 0, 0, 0, 0, 0, 2343, 3493, 124, 659, 0, \'normal\',1691477620, 1691477620, null);',
            1 => 'INSERT INTO `vs_drama_video_episodes`(`id`,`site_id`,`vid`,`name`,`image`,`video`,`duration`,`price`,`vprice`,`sales`,`likes`,`views`,`favorites`,`shares`,`fake_likes`,`fake_views`,`fake_favorites`,`fake_shares`,`weigh`,`status`,`updatetime`,`createtime`,`deletetime`) VALUES (null, __SITEID__, __LASTINSERTID__, \'第2集\', \'http://222.186.43.171:99/p/94-我的修仙老公/logo.png\', \'http://222.186.43.171:99/p/94-我的修仙老公/2.mp4\', 95, 0, 0, 0, 0, 0, 0, 0, 2343, 3493, 124, 659, 0, \'normal\',1691477620, 1691477620, null);',
        ),
);