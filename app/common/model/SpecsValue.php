<?php
namespace app\common\model;
class SpecsValue extends BaseModel{
    public function specs()
    {
        return $this->belongsTo('Specs', 'specs_id', 'id');
    }
}
