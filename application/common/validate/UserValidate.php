<?php

namespace app\common\validate;

class UserValidate extends BaseValidate
{
	protected $rule = [
        'username|用户名' => 'require',
        'password|密码' => 'require|alphaDash',
        'captcha|验证码' => 'require|captcha'
    ];

    protected $message = [];

    protected $scene = [
        //帐号密码登录
        'login' => ['username', 'password','captcha'],
    ];

    public function __construct(array $rules = [], array $message = [], array $field = [])
    {
        $this->message = [
            'username.require' => lang('username require'),
            'password.require' => lang('password require'),
            'password.alphaDash' => lang('password alphaDash'),
        ];
        parent::__construct($rules, $message, $field);
    }
}
