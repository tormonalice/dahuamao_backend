<?php

namespace addons\drama\validate;

use think\Validate;

class UserSign extends Validate
{
    protected $rule = [
        'date' => 'require',
    ];

    protected $message  =   [
        'date.require'     => 'Please select a supplementary signing date',
    ];


    protected $scene = [
        'replenish'  =>  ['date'],
    ];
}