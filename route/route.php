<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 需要登录
Route::group('/admin', function () {
    // 后台首页 页面
    Route::get('index', 'admin/index/index');
    // 后台首页 数据
    Route::post('index', 'admin/index/count');
    // 后台首页 数据
    Route::post('log', 'admin/index/log');
    // 数据导入
    Route::post('import', 'admin/Custom/import');
    // 短信配置
    Route::get('smsconfig', 'admin/Config/smsConfig');
    // 保存配置
    Route::post('config', 'admin/config/save');
    // 客户列表 页面
    Route::get('customlist', 'admin/Custom/list');
    // 客户列表 数据请求
    Route::post('customlist', 'admin/Custom/listData');
    // 添加任务
    Route::get('addtask', 'admin/Task/index');
    // 任务列表
    Route::get('tasklist', 'admin/Task/list');
    // 任务列表 数据请求
    Route::post('tasklist', 'admin/Task/getList');
    // 添加任务 表单提交
    Route::post('addtask', 'admin/Task/create');
    // 添加任务 文件上传
    Route::post('taskupload', 'admin/Task/upload');
    // 添加任务 批次列表
    Route::get('batchlist', 'admin/Task/batchList');
    // 添加任务 模板列表
    Route::get('templatelist', 'admin/Task/smsTemplateInfoList');
})->middleware(['CheckUserLogin']);

// 不需要登录
Route::group('/admin', function () {
    // 用户登录页面
    Route::get('login', 'admin/index/login');
});

// 登录授权
Route::get('auth', 'admin/user/auth');

// 测试专用
Route::get('cs', 'admin/index/cs');

// 用户验证
Route::get('checkuser/:token', 'admin/index/checkUser');

// 执行任务
Route::get('taskrun/:type/:run', 'task/Run/go')->middleware(['CheckTaskRunKey']);

Route::miss('admin/index/index')->middleware(['CheckUserLogin']);
