<?php

namespace app\common\model;

use think\Model;

class Custom extends Model
{
    // 自动写入时间戳
    public $autoWriteTimestamp = true;

    // 设置主键
    public $pk = 'id';

    /**
     * 关联用户 phone表
     * @return \think\model\relation\HasMany
     */
    public function customPhone()
    {
        return $this->hasMany('customPhone', 'custom_id', 'id');
    }

    /**
     * 关联数据批次
     * @return \think\model\relation\HasMany
     */
    public function batch()
    {
        return $this->hasMany('Batch', 'id', 'batch_id');
    }

    /**
     * 获取列表
     * @return array|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getList()
    {
        $param = input();
        $result = $this->with(['customPhone' => function ($query) {
            return $query->field('custom_id,phone');
        }, 'batch' => function ($query) {
            return $query->field('id,create_time');
        }])->order('id asc')
            ->page($param['page'], !isset($param['pageSize']) ? 10 : $param['pageSize'])
            ->select()
            ->toArray();
        return json([
            'code' => 0,
            'total' => $this->count('id'),
            'list' => $result
        ]);
    }
}