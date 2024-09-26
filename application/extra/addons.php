<?php

return [
    'autoload' => false,
    'hooks' => [
        'upgrade' => [
            'drama',
        ],
        'app_init' => [
            'drama',
        ],
        'epay_config_init' => [
            'epay',
        ],
        'addon_action_begin' => [
            'epay',
        ],
        'action_begin' => [
            'epay',
        ],
        'config_init' => [
            'nkeditor',
        ],
    ],
    'route' => [],
    'priority' => [],
    'domain' => '',
];
