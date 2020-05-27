<?php


namespace app\common\model;

use think\Model;

class Task extends Model
{
    // 自动写入时间戳
    public $autoWriteTimestamp = true;

    /**
     * 关联导入批次
     * @return \think\model\relation\HasOne
     */
    public function batch()
    {
        return $this->hasOne('batch', 'id', 'batch_id');
    }

    /**
     * 关联客户列表
     * @return \think\model\relation\HasMany
     */
    public function custom()
    {
        return $this->hasMany('Custom', 'batch_id', 'batch_id');
    }

    /**
     * 新建任务
     * @return \think\response\Json
     * @throws \think\exception\PDOException
     */
    public function addTask()
    {
        $param = input();
        if (!in_array($param['type'], ['import', 'email', 'message'])) return jsonMsg('未知任务类型');
        $bid = $param['type'] == 'import' ? Batch::create()->toArray()['id'] : $param['batch_id'];
        $create = [
            'type' => $param['type'],
            'desc' => $param['desc'],
            'data' => $param['data'],
            'batch_id' => $bid
        ];
        $this->startTrans();
        $res = self::create($create);
        if (!$res) {
            $this->rollback();
            return jsonMsg('新建任务失败，请重试');
        }
        $this->commit();
        return jsonMsg('新建任务成功', 0);
    }

    /**
     * 获取任务列表数据
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getList()
    {
        $param = input();
        $result = $this->with(['batch'])->order('id desc')
            ->page($param['page'], 10)
            ->select()
            ->toArray();
        return json([
            'code' => 0,
            'total' => $this->count('id'),
            'list' => $result
        ]);
    }
}