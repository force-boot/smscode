<?php

namespace app\http\middleware;

use app\common\model\User;

class CheckUserLogin
{
    public function handle($request, \Closure $next)
    {
        if (!$userInfo = User::checkLogin()) {
            exit( '<script>alert("你还没有登录！");location.href="/admin/login";</script>');
        }
        $request->userInfo = $userInfo;
        return $next($request);
    }
}
