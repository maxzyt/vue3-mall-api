<?php

namespace app\admin\model\user;

use app\common\model\BaseModel;


class Userhistory extends BaseModel
{

    

    

    // 表名
    protected $name = 'user_history';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];
    

    







    public function user()
    {
        return $this->belongsTo('app\admin\model\User', 'user_id', 'id')->joinType('LEFT');
    }


    public function good()
    {
        return $this->belongsTo('app\admin\model\good\Good', 'good_id', 'id')->joinType('LEFT');
    }
}
