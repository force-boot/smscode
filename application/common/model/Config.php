<?php

namespace app\common\model;

use think\Model;

class Config extends Model
{
    protected $pk = 'k';

    /**
     * 获取系统配置 留空获取全部
     * @param string $name
     * @return mixed
     */
    public static function getSysConfig($name = '')
    {
        if (!empty($name)) return self::find($name)['v'];

        return self::select();
    }

    /**
     *  设置系统配置 支持动态导入
     * @param array $config
     * @return \think\response\Json
     * @throws \think\db\exception\BindParamException
     * @throws \think\exception\PDOException
     */
    public static function setSysConfig($config = [])
    {
        $param = !empty($config) ? $config : input('post.');
        foreach ($param as $k => $value) {
            self::execute("INSERT INTO config SET k='" . $k . "',v='" . $value . "' ON DUPLICATE KEY UPDATE v='" . $value . "'");
        }
        return jsonLangMsg('save_succ', 0);
    }
}