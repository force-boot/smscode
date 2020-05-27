<?php

namespace app\admin\controller;

use app\common\controller\AuthLogin;
use app\common\controller\BaseController;
use app\common\model\User AS UserModel;

class User extends BaseController
{
    /**
     * 授权登录回调
     * @return \think\response\Json|\think\response\Redirect
     * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
     * @throws \Microsoft\Graph\Exception\GraphException
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function auth()
    {
        $state = input('get.state');
        $code = input('get.code');
        $auth = new AuthLogin();
        if (!$auth->checkState($state)) return json([
            'code' => -1,
            'msg' => 'bad request！'
        ]);
        session('state', null);
        $accessToken = $auth->getAccessToken($code);
        $userInfo = $auth->getUserInfo($accessToken);
        return (new UserModel())->login($userInfo);
    }
}
