<?php


namespace app\common\validate;

use think\Validate;

class BaseValidate extends Validate
{
    /**
     * 通用验证数据 支持验证场景
     * @param string $scene 验证场景
     * @return bool|array
     */
    public function goCheck(string $scene = '')
    {
        //获取所有请求参数
        $params = input();
        //是否需要验证场景
        $check = $scene ? $this->scene($scene)->check($params) : $this->check($params);
        if (!$check) return json([
            'code' => -1,
            'msg' => $this->getError()
        ]);
        return true;
    }
}