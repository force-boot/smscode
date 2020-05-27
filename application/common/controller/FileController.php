<?php

namespace app\common\controller;

use app\common\controller\store\Store;
use think\File;

/**
 * 文件上传类
 * @package app\common\controller
 * @author XieJiaWei<print_f@hotmail.com>
 * @version 1.0.0
 */
class FileController
{
    /**
     * 默认配置参数
     * @var array
     */
    public $config = [
        'size' => 100067800,
        'ext' => 'csv',
        'path' => 'uploads'
    ];

    /**
     * 存储file对象
     * @var File
     */
    public $file;

    /**
     * FileController constructor.
     * @param array $option
     */
    public function __construct($option = [])
    {
        if (!empty($option)) $this->config = array_merge($this->config, $option);
    }

    /**
     * 上传文件处理
     * @param $files
     * @return array
     */
    public function uploadHandle($files): array
    {
        // 保存file对象
        $this->file = $files->validate($this->config);
        //保存到本地文件夹
        $res = $this->file->move($this->config['path']);
        return [
            'data' => $res ? $res->getPathname() : $this->file->getError(),
            'status' => $res ? true : false,
        ];
    }

    /**
     * 析构方法
     */
    public
    function __destruct()
    {
        $this->file = '';
    }
}