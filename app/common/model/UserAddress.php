<?php
namespace app\common\model;
class UserAddress extends BaseModel{
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    public static function getAddress($uid)
    {
        return self::where('user_id',$uid)->order('default desc,id desc')->select();
    }
}
