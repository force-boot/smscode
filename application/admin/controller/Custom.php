<?php

namespace app\admin\controller;

use app\common\controller\BaseController;
use app\common\model\Custom AS CustomModel;
use app\common\validate\CustomValidate;

class Custom extends BaseController
{
    /**
     * 客户列表
     * @return mixed
     */
    public function list()
    {
        return $this->fetch();
    }

    /**
     * 获取列表数据
     * @return array|bool|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function listData()
    {
        $check = (new CustomValidate())->goCheck('list');
        if (true !== $check) return $check;
        return (new CustomModel())->getList();
    }
}