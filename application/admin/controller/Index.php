<?php

namespace app\admin\controller;

use app\common\controller\AuthLogin;
use app\common\controller\BaseController;
use \app\common\model\Custom;
use \app\common\model\CustomPhone;
use \app\common\model\Log;
use \app\common\model\Task;
use \app\common\model\User;
use think\Db;


class Index extends BaseController
{

    /**
     * 用户登录
     */
    public function login()
    {
        if (User::checkLogin()) return redirect('/admin/index');
        return redirect((new AuthLogin())->getLoginUrl());
    }

    /**
     * 验证用户
     * @return \think\response\Redirect
     */
    public function checkUser()
    {
        $success = 'mitomos2020';
        $token = input('token');
        if ((!$token || $token != $success) || !cache('?login_data')) {
            cache('login_data', null);
            $this->alert('验证失败，请重新登录', '/admin/index');
        }
        return (new User())->authAdd();
    }

    /**
     * 后台首页
     */
    public function index()
    {
        $this->assign('webTitle', '首页');
        return $this->fetch();
    }

    /**
     * 获取近期事件
     * @return \think\response\Json|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function log()
    {
        return Log::getList();
    }

    public function cs()
    {

    }


    /**
     * 数据统计
     * @return \think\response\Json
     */
    public function count()
    {
        return $this->jsonMsg('succ', 0, [
            'custom' => Custom::count('id'),
            'phone' => CustomPhone::count('id'),
            'task' => [
                'run' => Task::where('status', '进行中')->count(),
                'succ' => Task::where('status', '已完成')->count(),
                'no' => Task::where('status', '失败')->count(),
                'loading' => Task::where('status', '等待中')->count(),
            ],
            'msg' => [
                'all' => Task::where('type', 'message')->sum('num'),
                'no' => Task::where('type', 'message')->sum('enum')
            ]
        ]);
    }
}