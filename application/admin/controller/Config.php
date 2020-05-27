<?php


namespace app\admin\controller;

use app\common\controller\BaseController;
use app\common\model\Config AS ConfigModel;

class Config extends BaseController
{
    /**
     * 保存配置
     * @return \think\response\Json
     * @throws \think\db\exception\BindParamException
     * @throws \think\exception\PDOException
     */
    public function save()
    {
        return ConfigModel::setSysConfig();
    }

    /**
     * 短信配置
     */
    public function smsConfig()
    {
        return $this->fetch();
    }
}