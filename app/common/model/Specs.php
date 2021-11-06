<?php
namespace app\common\model;
class Specs extends BaseModel{
    public static function goodSpecs($id)
    {
        $data = self::with(['specsvalue' => function($query){
            $query->where('is_use', 1)->order('weigh', 'desc');
        }])->where('good_id', $id)->order('weigh desc')->select()->toArray();
        foreach ($data as $key => $row) {
            if(empty($row['specsvalue'])) {
                unset($data[$key]);
            }
        }
        return $data;
    }
    public function specsvalue()
    {
        return $this->hasMany('SpecsValue', 'specs_id', 'id');
    }
}
