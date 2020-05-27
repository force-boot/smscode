<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/5/23 0023
 * Time: 15:52
 */

namespace app\common\model;

use think\Model;

class Log extends Model
{
    // 自动写入时间戳
    public $autoWriteTimestamp = true;

    /**
     * 写入日志
     * @param $msg
     * @param int $status
     * @param mixed $data
     * @return static
     */
    public static function write($msg, $status = 2, $data = '')
    {
        return self::create([
            'desc' => $msg,
            'status' => $status,
            'data' => $data
        ]);
    }

    /**
     * 获取列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getList()
    {
        $result = self::order('id desc')->limit(10)
            ->select()->toArray();
        return json([
            'code' => 0,
            'list' => $result
        ]);
    }
}