<?php

namespace app\task\controller;

use app\common\controller\Csv;
use app\common\model\Custom;
use app\common\model\CustomPhone;
use app\common\model\Log;
use app\common\model\Task;
use think\Exception;

class Import extends Run
{

    /**
     * 运行任务
     * @return bool|int|\think\response\Json
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function run()
    {
        // 有任务在执行
        if (cache('taskrun_import')) return json(['code' => -1, 'msg' => 'import task is already running']);
        $data = Task::where([
            'type' => 'import',
            'status' => '等待中'
        ])->find();
        if (!$data) return json(['code' => -1, 'msg' => 'No tasks to run']);
        cache('taskrun_import', 1);
        $taskId = $data['id'];
        $this->setTaskStatus($taskId, 2);
        Log::write('导入客户数据任务已开始', 2);
        $file = $data['data'];
        $handle = fopen($file, 'r');
        $batch_id = $data['batch_id'];
        $result = (new Csv())->import($handle); // 解析csv
        $len_result = count($result);
        if ($len_result == 0) {
            $this->setTaskSpeed($taskId, 100);
            $this->setTaskLog($taskId, '无数据可导入');
            return $this->setTaskStatus($taskId, 3);
        }
        $data_values = [];
        $phoneArr = [];
        $count = 0;
        for ($i = 1; $i < $len_result + 1; $i++) { // 循环获取各字段值
            $arr = array_values($result[$i]);
            if (empty(formatPhone($arr['12']))) continue;
            $phoneArr[] = formatPhone($arr['12']);
            $data_values[] = [
                'name' => $arr[0] . $arr[1],
                'email' => $arr[2],
                'company' => $arr[3],
                'city' => $arr[6],
                'province' => $arr[7],
                'batch_id' => $batch_id,
            ];
            $count++;
        }
        fclose($handle); // 关闭指针
        $custom_phone = [];
        // 开启事务
        $custom = new Custom();
        try {
            $custom->startTrans();
            $save = $custom->saveAll($data_values);
        } catch (\Exception $e) {
            $custom->rollback();
            $this->setTaskSpeed($taskId, 100);
            $this->setTaskLog($taskId, $e->getMessage());
            return $this->setTaskStatus($taskId, 4);
        }
        foreach ($save as $key => $value) {
            foreach ($phoneArr[$key] as $phone) {
                $custom_phone[] = [
                    'custom_id' => $value['id'],
                    'phone' => $phone
                ];
            }
        }
        // 开启事务
        $customPhoneModel = new CustomPhone();
        try {
            $customPhoneModel->startTrans();
            // 批量写入客户手机号
            $customPhoneModel->saveAll($custom_phone);
        } catch (\Exception $e) {
            $customPhoneModel->rollback();
            $this->setTaskSpeed($taskId, 100);
            $this->setTaskLog($taskId, $e->getMessage());
            return $this->setTaskStatus($taskId, 4);
        }
        // 提交事务
        $custom->commit();
        $customPhoneModel->commit();
        $this->setTaskSpeed($taskId, 100);
        $this->setTaskStatus($taskId, 3);
        $this->setTaskLog($taskId, '导入客户数据成功，导入数据：' . $count . '条，已排除座机/无手机客户');
        Log::write('导入客户数据成功，导入数据：' . $count . '条，已排除座机/无手机客户', 3);
        $this->setTaskNum($taskId, $count);
        return json([
            'code' => 1,
            'message' => 'import success!'
        ]);
    }

    /**
     * 修改任务状态
     * @param int $taskId
     * @param int $status
     * @return bool|int
     * @throws Exception
     * @throws \think\exception\PDOException
     */
    public function setTaskStatus(int $taskId, int $status)
    {
        if ($status == 3 || $status == 4) cache('taskrun_import', null);
        return parent::setTaskStatus($taskId, $status); // TODO: Change the autogenerated stub
    }
}
