<?php

namespace app\task\controller;

use app\common\controller\TencentSms;
use app\common\model\CustomPhone;
use app\common\model\Log;
use app\common\model\Task;
use think\facade\Cache;

class Message extends Run
{
    /**
     * @var int 任务id
     */
    public $taskId;

    /**
     * @var int 批次id
     */
    public $bid;

    /**
     * @var array 腾讯云错误码
     */
    public $code = [
        // 手机号格式有误
        'InvalidParameterValue.IncorrectPhoneNumber',
        // 手机号在运营商黑名单中
        'FailedOperation.PhoneNumberInBlacklist',
        // sig验证失败
        'InternalError.SigVerificationFail'
    ];

    /**
     * 执行任务
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function run()
    {
        if (!checkTimeSlot('08:00', '22:00')) return json(['code' => -1, 'msg' => 'Current period not allowed！']);
        if (!$data = cache('taskrun_msg')) {
            $data = Task::where([
                'type' => 'message',
                'status' => '等待中'
            ])->find();
            if (!$data) return json(['code' => -1, 'msg' => 'No tasks to run']);
            cache('taskrun_msg', $data);
        }
        $this->taskId = $data['id'];
        $this->bid = $data['batch_id'];
        $templateId = $data['data'];
        $limit = 200;
        // 页码
        if (!$page = cache('msg_page')) {
            $page = 1;
            $this->setTaskStatus($this->taskId, 2);
            Log::write('客户批次：' . $this->bid . '，短信发送任务已开始', 2);
            cache('msg_page', $page);
        }
        // 获取需要发送的客户
        $custom = $this->getCustom($this->bid, $page, $limit);
        $list = $custom['list'];
        // 如果没有数据了
        if (!$list) {
            $this->setTaskStatus($this->taskId, 3);
            $this->setTaskLog($this->taskId, '短信发送任务已完成');
            $this->setTaskSpeed($this->taskId, 100);
            Log::write('客户批次：' . $this->bid . '，短信发送任务已完成', 3);
            cache('msg_page', null);
            cache('taskrun_msg', null);
            return json(['code' => -1, 'msg' => 'No tasks to run']);
        }
        // 获取手机号码
        $phones = [];
        for ($i = 0; $i <= count($list) - 1; $i++) {
            foreach ($list[$i]['custom_phone'] as $phone) {
                $phones[] = $phone['phone'];
            }
        }
        // 判断数量 如果大于200 拆分数组
        if (count($phones) > 200) {
            $phones = array_chunk($phones, 200);
            // 分次提交
            $mtPhoneArrCount = count($phones);
            for ($i = 0; $i <= $mtPhoneArrCount - 1; $i++) {
                $this->sendMessage($phones[$i], $templateId);
            }
        } else {
            $this->sendMessage($phones, $templateId);
        }
        // 本批次执行完毕
        $speed = round($limit / $custom['total'] * 100, 2);
        $this->setTaskSpeed($this->taskId, $speed);
        Cache::inc('msg_page');
    }

    /**
     * 发送短信
     * @param $phone
     * @param bool $templateId
     * @return array
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function sendMessage($phone, $templateId = false)
    {
        $phoneCount = count($phone);
        $res = (new TencentSms())->sendSms($phone, $templateId);
        if (isset($res['Error'])) {
            // 记录失败手机号数据
            $error = [
                'taskId' => $this->taskId,
                'batchId' => $this->bid,
                'message' => $res['Error']['Message'],
                'errphone' => $phone
            ];
            Log::write("给$phone[0]等" . $phoneCount . '个手机号发送短信失败,原因：' . $res['Error']['Message'], 4, serialize($error));
            // 记录失败数量
            $this->setTaskEnum($this->taskId, $phoneCount);
            return json($res);
        }
        foreach ($res['SendStatusSet'] as $value) {
            if ($value['Code'] == 'Ok') { // 发送成功
                Log::write('给' . $value['PhoneNumber'] . '发送短信成功', 3);
                $this->setTaskLog($this->taskId, '给' . $value['PhoneNumber'] . '发送短信成功');
            }
            if ($value['Code'] == $this->code['0'] || $value['code'] == $this->code['1']) {
                CustomPhone::where('phone', $value['PhoneNumber'])->delete(); // 删除黑名单 和格式不正确的
                Log::write('给' . $value['PhoneNumber'] . '发送短信失败,原因：' . $value['Message'], 4, $value['PhoneNumber']);
                $this->setTaskLog($this->taskId, '给' . $value['PhoneNumber'] . '发送短信失败,原因：' . $value['Message']);
                $this->setTaskEnum($this->taskId, 1);
            }
            // sig 验证失败 重试
            if ($value['Code'] == $this->code['2']) $this->sendMessage($value['PhoneNumber'], $templateId);
        }
        // 记录已完成数量
        $this->setTaskNum($this->taskId, $phoneCount);
        return json($res);
    }

    /**
     * 修改任务进度
     * @param int $taskId
     * @param float|int $num
     * @return bool|int|string|void
     * @throws \think\Exception
     */
    public function setTaskSpeed(int $taskId, $num)
    {
        if ($num == 100) return Task::where('id', $taskId)->update('speed', 100);
        return Task::where('id', $taskId)->setInc('speed', $num);
    }
}
