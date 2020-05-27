<?php


namespace app\common\model;

use think\Model;

class Batch extends Model
{
    // 自动写入时间戳
    public $autoWriteTimestamp = true;

    /**
     * 关联导入批次
     * @return \think\model\relation\HasOne
     */
    public function task()
    {
        return $this->hasOne('Task', 'batch_id');
    }

    /**
     * 获取列表
     * @return array|\PDOStatement|string|\think\Collection|\think\model\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getList()
    {
        $res = $this->with('task')
            ->order('id desc')
            ->select()
            ->toArray();
        return json([
            'code' => 0,
            'msg'=> 'success',
            'list' => $res
        ]);
    }
}