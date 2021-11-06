<?php
namespace app\common\model;
class UserCollect extends BaseModel{
    protected $autoWriteTimestamp = true;
    protected $createTime = 'createtime';
    protected $updateTime = '';
    public static function getCollect($uid,$page,$limit)
    {
        $start = --$page*$limit;
        return self::withJoin(['user', 'good'])->where('user_id', $uid)->order('id desc')->limit($start, $limit)->select();
    }
    public function user()
    {
        return $this->belongsTo('User', 'user_id', 'id')->joinType('LEFT');
    }


    public function good()
    {
        return $this->belongsTo('Good', 'good_id', 'id')->joinType('LEFT');
    }
}
