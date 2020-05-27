<?php

namespace app\common\model;

use think\facade\Cache;
use think\Model;

class User extends Model
{
    // 自动写入时间戳
    public $autoWriteTimestamp = true;

    /**
     * 用户登录
     * @param object $userInfo
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function login($userInfo)
    {
        $display_name = $userInfo->getDisplayName();
        $mail = null !== $userInfo->getMail() ? $userInfo->getMail() : $userInfo->getUserPrincipalName();
        $name = $userInfo->getGivenName();
        $phone = $userInfo->getMobilePhone();
        $aid = $userInfo->getId();
        $ip = request()->ip();
        $city = getIpCity($ip);
        $login_time = time();
        $data = [
            'ip' => $ip,
            'city' => $city,
            'phone' => $phone,
            'name' => $name,
            'mail' => $mail,
            'login_time' => time(),
            'display_name' => $display_name,
            'auth_id' => $aid,
        ];
        if (!$user = $this->where('auth_id', $aid)->find()) {
            cache('login_data', $data);
            exit('<script>var token = prompt("该账户暂不可用，请输入通行证完成认证");location.href="/checkuser/"+token;</script>');
        } else {
            $this->where('id', $user['id'])->update($data);
        }
        cookie('userSid', md5($ip . $city . $login_time, 86400));
        cookie('user_id', $user['id'], 86400);
        exit('<script>alert("登录成功，欢迎您：' . $data['display_name'] . '");location.href="/admin/index";</script>');
    }

    /**
     * 新增用户
     */
    public function authAdd()
    {
        $data = Cache::pull('login_data');
        $user = self::create($data);
        cookie('userSid', md5(time(), 86400));
        cookie('user_id', $user['id'], 86400);
        exit('<script>alert("登录成功，欢迎您：' . $data['display_name'] . '");location.href="/admin/index";</script>');
    }

    /**
     * 检测用户是否登录
     * @return bool|mixed
     */
    public static function checkLogin()
    {
        if (!cookie('userSid') || !cookie('user_id')) return false;
        $userInfo = self::find(cookie('user_id'));
        if (!$userInfo) return false;
        return $userInfo;
    }
}
