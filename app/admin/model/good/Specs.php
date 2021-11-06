<?php

namespace app\admin\model\good;

use app\common\model\BaseModel;


class Specs extends BaseModel
{

    

    

    // 表名
    protected $name = 'specs';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'status_text'
    ];
    
    /**
    * @param Model $row
    */
    protected static function onAfterInsert($row){
        $pk = $row->getPk();
        $row->where($pk, $row[$pk])->update(['weigh' => $row[$pk]]);
    }

    
    public function getStatusList()
    {
        return ['normal' => __('Status normal'), 'hidden' => __('Status hidden')];
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }
    public static function getAllSpecs()
    {
        $data = self::where('status', 'normal')->select();
        //foreach ()
    }



    public function specsvalue()
    {
        return $this->hasMany('SpecsValue', 'specs_id', 'id');
    }
}
