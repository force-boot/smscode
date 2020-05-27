<?php


namespace app\admin\controller;

use app\common\controller\BaseController;
use app\common\controller\FileController;
use app\common\controller\TencentSms;
use app\common\model\Batch;
use app\common\validate\CustomValidate;
use app\common\validate\TaskValidate;
use app\common\model\Task as TaskModel;

class Task extends BaseController
{
    /**
     * 任务首页
     * @return mixed
     */
    public function index()
    {
        return $this->fetch();
    }

    /**
     * 任务列表
     * @return array|bool|\think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function list()
    {
        return $this->fetch();
    }

    /**
     * 获取列表数据
     * @return array|bool|\think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getList()
    {
        $check = (new CustomValidate())->goCheck('list');
        if (true !== $check) return $check;
        return (new TaskModel())->getList();
    }

    /**
     * 创建任务
     * @return array|bool|\think\response\Json
     * @throws \think\exception\PDOException
     */
    public function create()
    {
        $check = (new TaskValidate())->goCheck('add');
        if (true !== $check) return $check;
        return (new TaskModel())->addTask();
    }

    /**
     * 获取导入批次
     * @return array|\PDOStatement|string|\think\Collection|\think\model\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function batchList()
    {
        return (new Batch())->getList();
    }

    /**
     * 获取短信模板信息列表
     * @return mixed
     */
    public function smsTemplateInfoList()
    {
        // 获取当前模板ID
        $template = config('sms_TemplateID');
        $res = (new TencentSms())->getTemplateInfo($template);
        return json([
            'code' => 0,
            'msg' => 'success',
            'list' => $res['DescribeTemplateStatusSet']
        ]);
    }

    /**
     * 上传文件
     * @return \think\response\Json
     */
    public function upload()
    {
        // 获取表单上传文件
        $file = request()->file('csvfile');
        if (empty($file)) return jsonMsg('请选择要上传的文件');
        $fileInfo = (new FileController())->uploadHandle($file);
        if (!$fileInfo['status']) return jsonMsg($fileInfo['data']);
        return jsonMsg('上传成功!', 0, $fileInfo['data']);
    }
}