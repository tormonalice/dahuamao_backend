<?php

namespace addons\drama\library;

/**
 * 签到
 */
class UserSignProvider
{

    protected $rules = [
        "everyday" => "require",
        "is_inc" => "require|boolean",
        "inc_num" => "require",
        "until_day" => "require|egt:0",
        "discounts" => "array",
        "is_replenish" => "require|boolean",
        "replenish_days" => "require|gt:0",
        "replenish_limit" => "require|egt:0",
        "replenish_num" => "require|gt:0"
    ];


    protected $message  =   [
    ];


    protected $default = [
        "everyday" => 0,            // 每日签到固定积分
        "is_inc" => 0,              // 是否递增签到
        "inc_num" => 0,             // 递增奖励
        "until_day" => 0,                     // 递增持续天数
        "is_discounts" => 0,              // 是否连续签到奖励
        "discounts" => [],               // 连续签到奖励 {full:5, value:10} // 可以为空
        "is_replenish" => 0,            // 是否开启补签
        "replenish_days" => 1,            // 可补签天数，最小 1
        "replenish_limit" => 0,            // 补签时间限制，0 不限制
        "replenish_num" => 1,           // 补签所消耗积分           
    ];


    public function check($params)
    {
        $rules = $this->validate($params['rules']);

        // 从小到大排序
        if (isset($rules['discounts']) && $rules['discounts']) {
            // full 从小到大
            $discounts = $rules['discounts'];
            $discountsKeys = array_column($discounts, null, 'full');
            ksort($discountsKeys);
            $rules['discounts'] = array_values($discountsKeys);        // 按照 full 从小到大排序
        }

        $params['rules'] = $rules;

        return $params;
    }

    public function validate($data)
    {
        $data = array_merge($this->default, $data);

        $validate = (new \think\Validate)->message($this->message)->rule($this->rules);
        if (!$validate->check($data)) {
            $this->error_stop($validate->getError());
        }

        return $data;
    }

    public function error_stop($msg = '', $code = 0, $data = null, $status_code = 200, $header = [])
    {
        $result = [
            'code' => $code ?: 0,
            'msg' => $msg,
            'data' => $data
        ];

        $response = \think\Response::create($result, 'json', $status_code)->header($header);
        throw new \think\exception\HttpResponseException($response);
    }

}