<?php

namespace app\common\controller;

use TencentCloud\Common\Credential;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use TencentCloud\Sms\V20190711\Models\DescribeSmsTemplateListRequest;
use TencentCloud\Sms\V20190711\Models\SmsPackagesStatisticsRequest;
use TencentCloud\Sms\V20190711\SmsClient;
use TencentCloud\Sms\V20190711\Models\SendSmsRequest;

class TencentSms
{
    /**
     * 配置信息
     * @var array
     */
    public $config = [];

    /**
     * @var object
     */
    public $cred;

    /**
     * @var SmsClient
     */
    public $client;

    /**
     * 初始化
     * TencentSms constructor.
     */
    public function __construct()
    {
        // 初始化配置
        $this->initConfig();
        // 实例化SDK库
        $this->cred = new Credential($this->getConfig('secretId'), $this->getConfig('secretKey'));
        $this->client = new SmsClient($this->cred, $this->getConfig('region'));
    }

    /**
     * 初始化配置
     */
    public function initConfig()
    {
        $this->config = [
            'secretId' => config('sms_SecretId'),
            'secretKey' => config('sms_SecretKey'),
            'region' => config('sms_region'),
            'templateID' => config('sms_TemplateID'),
            'sdkAppid' => config('sms_SmsSdkAppid'),
            'sign' => config('sms_Sign')
        ];
    }

    /**
     * 获取配置信息
     * @param string $name
     * @return array|mixed
     */
    public function getConfig($name = '')
    {
        return !empty($name) ? $this->config[$name] : $this->config;
    }

    /**
     * 发送短信
     * @param array|string 发送手机号 支持数组多条和单条 单条
     * 腾讯云限制单次最多200条 并且类型统一 境内or境外
     * @param int|bool $templateId
     * @return array
     */
    public function sendSms($phone, $templateId = false)
    {
        // 如果是数组 先解析成json 数组形式
//        if (is_array($phone)) $phone = $this->parseArrayPhone($phone);
        // 判断 手机号是否+86
        if (!is_array($phone) && mb_substr($phone, 0, 3) != '+86') $phone = ['+86' . $phone];
        $req = new SendSmsRequest();
        $req->SmsSdkAppid = $this->getConfig('sdkAppid');
        $req->Sign = $this->getConfig('sign');
        $req->PhoneNumberSet = $phone;
        /* 模板 ID: 必须填写已审核通过的模板 ID。可登录 [短信控制台] 查看模板 ID */
        $req->TemplateID = $templateId ? $templateId : $this->getConfig('templateID');
        $resp = $this->client->SendSms($req);

        return json_decode($resp->toJsonString(), true);
    }

    /**
     * 获取套餐包信息
     * @param $limit int 获取套餐个数
     * @return array
     */
    public function getPackInfo(int $limit = 1)
    {
        $req = new SmsPackagesStatisticsRequest();
        $req->SmsSdkAppid = $this->getConfig('sdkAppid');
        $req->Limit = $limit;
        $req->Offset = 0;
        $resp = $this->client->SmsPackagesStatistics($req);

        return json_decode($resp->toJsonString(), true);
    }

    /**
     * 获取模板信息
     * @param array|string $templateId 模板id数组
     * @param int $international 0 国内 1港澳台 必填项 默认0
     * @return array
     */
    public function getTemplateInfo($templateId, int $international = 0)
    {
        $req = new DescribeSmsTemplateListRequest();
        // 格式兼容处理
        if (is_string($templateId)) preg_replace("/(\n)|(\s)|(\t)|(\')|(')|(，)|(\.)/", ',', $templateId);
        if (is_array($templateId)) $templateId = join(',', $templateId);

        $params = '{"TemplateIdSet":[' . $templateId . '],"International":' . $international . '}';
        $req->fromJsonString($params);
        $resp = $this->client->DescribeSmsTemplateList($req);
        return json_decode($resp->toJsonString(), true);
    }


    /**
     * 手机号解析处理
     * @param $phoneArr array
     * @return array|bool
     */
    public function parseArrayPhone(array $phoneArr)
    {
        $checkPhone86 = function ($value) {
            return mb_substr($value, 0, 3) == '+86' ? true : false;
        };
        $arr = [];
        foreach ($phoneArr as $value) {
            if (is_array($value)) return false; // 格式非法
            // 是否+86
            if (!$checkPhone86($value)) {
                $arr[] = '+86' . $value;
            } else {
                $str[] = $value;
            }
        }
        return $arr;
    }
}