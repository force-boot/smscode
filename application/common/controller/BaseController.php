<?php

namespace app\common\controller;

use app\common\model\Config;
use think\Controller;

/**
 * 基础控制器
 * @package app\common\controller
 * @author XieJiaWei<print_f@hotmail.com>
 * @version 1.0.0
 */
class BaseController extends Controller
{
    /**
     * BaseController constructor.
     */
    function __construct()
    {
        parent:: __construct();
        //加载配置
        $this->loadConfig();
        if (!empty(request()->header("X-PJAX"))) {
            config('default_ajax_return', 'html');
            if (!defined('PJAX')) define('PJAX', true);
        } else {
            if (!defined('PJAX')) define('PJAX', false);
        }
        $this->assign('webTitle', '管理后台');
        $this->assign('userInfo', request()->userInfo);
    }

    /**
     * 统一json格式输出
     * @param $msg
     * @param $code
     * @param $list
     * @return \think\response\Json
     */
    public function jsonMsg($msg, $code = -1, $list = [])
    {
        return json([
            'code' => $code,
            'msg' => $msg,
            'list' => $list
        ]);
    }

    /**
     * 提示
     * @param $msg
     * @param $url
     */
    public function alert($msg, $url)
    {
        exit('<script>alert("' . $msg . '");location.href="' . $url . '";</script>');
    }

    /**
     * 加载配置
     */
    private function loadConfig()
    {
        foreach (Config::getSysConfig() as $row) {
            config("{$row['k']}", $row['v']);
        }
    }
}