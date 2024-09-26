<?php

$defaultHooks = [
    'share_wx_after' => [            //分享微信后
        'addons\\drama\\listener\\task\\Share'
    ],
    'share_wxf_after' => [            //分享微信朋友圈后
        'addons\\drama\\listener\\task\\Share'
    ],
    'share_success' => [            //邀请新用户
        'addons\\drama\\listener\\task\\Share'
    ],
    'user_register_after' => [            //用户注册后
        'addons\\drama\\listener\\task\\Register'
    ],
    'user_bind_name_after' => [            //用户绑定昵称后
        'addons\\drama\\listener\\task\\Register'
    ],
    'user_bind_avatar_after' => [            //用户绑定头像后
        'addons\\drama\\listener\\task\\Register'
    ],
    'register_after' => [            //用户注册后推广关系保存
        'addons\\drama\\listener\\Reseller'
    ],
    'finish_after' => [            //购买成功后分润
        'addons\\drama\\listener\\Reseller'
    ],
    'uniad_success' => [            //观看广告
        'addons\\drama\\listener\\task\\Uniad'
    ],
];


return $defaultHooks;
