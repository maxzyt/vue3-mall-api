<?php

namespace app\admin\model\good;

use app\common\model\BaseModel;


class Good extends BaseModel
{

    

    

    // 表名
    protected $name = 'good';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'status_text',
        'is_home_text',
        'is_best_text',
        'butie_text'
    ];
    protected static function onBeforeWrite($row){
        $arr = [$row->is_home, $row->is_best, $row->butie];
        $i = 0;
        foreach ($arr as $v) {
            if($v) {
                $i++;
            }
        }
        if($i > 1) {
            //throw new \think\exception\ModelEventException('首页推荐，每日上新，百亿补贴只能一个是');
            return false;
        }
    }
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

    public function getIsHomeList()
    {
        return ['0' => __('Is_home 0'), '1' => __('Is_home 1')];
    }

    public function getIsBestList()
    {
        return ['0' => __('Is_best 0'), '1' => __('Is_best 1')];
    }
    public function getButieList()
    {
        return ['0' => __('Butie 0'), '1' => __('Butie 1')];
    }

    public function getButieTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['butie']) ? $data['butie'] : '');
        $list = $this->getButieList();
        return isset($list[$value]) ? $list[$value] : '';
    }
    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getIsHomeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['is_home']) ? $data['is_home'] : '');
        $list = $this->getIsHomeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getIsBestTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['is_best']) ? $data['is_best'] : '');
        $list = $this->getIsBestList();
        return isset($list[$value]) ? $list[$value] : '';
    }




    public function goodcategory()
    {
        return $this->belongsTo('app\admin\model\good\Goodcategory', 'good_category_id', 'id')->joinType('LEFT');
    }
}
