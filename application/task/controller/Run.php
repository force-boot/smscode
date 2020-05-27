<?php

namespace app\task\controller;

set_time_limit(0);
ini_set("memory_limit", "-1");

use app\common\controller\BaseController;
use app\common\model\Custom;
use app\common\model\Task;
use think\Exception;

class Run extends BaseController
{
    /**
     * @var string 当前任务类型
     */
    public $type;

    /**
     * 分发执行任务
     */
    public function go()
    {
        // 获取任务类型
        $this->type = input('type');
        // 获取任务运行对象
        $obj = $this->factory($this->type);
        // 执行方法
        return $obj->run();
    }

    /**
     * 获取实例
     * @param $type
     * @return mixed
     */
    public function factory($type)
    {
        $class = 'app\\task\\controller\\' . ucwords($type);
        return new $class;
    }

    /**
     * 修改任务状态
     * @param int $status
     * @param int $taskId
     * @return bool|int
     * @throws Exception
     * @throws \think\exception\PDOException
     */
    public function setTaskStatus(int $taskId, int $status)
    {
        return Task::where('id', $taskId)->update(['status' => $status]);
    }

    /**
     * 修改任务进度
     * @param int $taskId
     * @param int $num
     * @return bool|int|string
     * @throws Exception
     * @throws \think\exception\PDOException
     */
    public function setTaskSpeed(int $taskId, int $num)
    {
        return Task::where('id', $taskId)->update([
            'speed' => $num,
        ]);
    }

    /**
     * 修改任务运行日志
     * @param int $taskId
     * @param $msg
     * @return int|string
     * @throws Exception
     * @throws \think\exception\PDOException
     */
    public function setTaskLog(int $taskId, $msg)
    {
        return Task::where('id', $taskId)->update([
            'log' => $msg,
        ]);
    }

    /**
     * 修改任务成功次数
     * @param int $taskId
     * @param $num
     * @return int|string
     * @throws Exception
     */
    public function setTaskNum(int $taskId, $num)
    {
        return Task::where('id', $taskId)->setInc('num', $num);
    }

    /**
     * 修改任务失败次数
     * @param int $taskId
     * @param $num
     * @return int|string
     * @throws Exception
     */
    public function setTaskEnum(int $taskId, $num)
    {
        return Task::where('id', $taskId)->setInc('enum', $num);
    }


    /**
     * 获取客户列表
     * @param $bid
     * @param $page
     * @param int $limit
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getCustom($bid, $page, $limit = 200)
    {
        $custom = new Custom();
        $where = $bid != 0 ? ['batch_id' => $bid] : 1;
        return [
            'list' => $custom->where($where)
                ->with(['customPhone' => function ($query) {
                    return $query->field('custom_id,phone');
                }])->order('id asc')
                ->page($page, $limit)
                ->select()
                ->toArray(),
            'total' => $custom->where($where)->count('id')
        ];
    }

    /**
     * 统一转码 utf-8
     * @param string|array $data
     * @return array|string
     */
    public function enCodeToUtf8($data)
    {
        // 数组批量处理
        if (is_array($data)) {
            $res = [];
            foreach ($data as $value) {
                $res[] = characet($value);
            }
            return $res;
        }
        return characet($data);
    }
}
