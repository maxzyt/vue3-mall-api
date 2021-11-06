<?php

namespace app\admin\model\good;

use app\common\model\BaseModel;


class SpecsValue extends BaseModel
{





    // 表名
    protected $name = 'specs_value';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    /**
     * @param Model $row
     */
    protected static function onAfterInsert($row){
        $pk = $row->getPk();
        $row->where($pk, $row[$pk])->update(['weigh' => $row[$pk]]);
    }
    public function specs()
    {
        return $this->belongsTo('app\admin\model\good\Specs', 'specs_id', 'id')->joinType('LEFT');
    }
}
