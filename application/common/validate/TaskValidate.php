<?php

namespace app\common\validate;

class TaskValidate extends BaseValidate
{
    protected $rule = [
        'type' => 'require',
    ];

    protected $message = [
        'type.require' => '请选择任务类型',
    ];

    protected $scene = [
        //新建任务
        'add' => ['type'],
    ];

}