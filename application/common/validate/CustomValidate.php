<?php


namespace app\common\validate;


class CustomValidate extends BaseValidate
{
    protected $rule = [
        'page' => 'require|integer|>:0',
    ];

    protected $message = [];

    protected $scene = [
        // 列表数据
        'list' => ['page'],
    ];
}