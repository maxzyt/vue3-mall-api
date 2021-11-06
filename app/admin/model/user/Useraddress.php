<?php

namespace app\admin\model\user;

use app\common\model\BaseModel;


class Useraddress extends BaseModel
{

    

    

    // 表名
    protected $name = 'user_address';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'default_text'
    ];
    

    
    public function getDefaultList()
    {
        return ['0' => __('Default 0'), '1' => __('Default 1')];
    }


    public function getDefaultTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['default']) ? $data['default'] : '');
        $list = $this->getDefaultList();
        return isset($list[$value]) ? $list[$value] : '';
    }




    public function user()
    {
        return $this->belongsTo('app\admin\model\User', 'user_id', 'id')->joinType('LEFT');
    }
}
