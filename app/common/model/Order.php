<?php
namespace app\common\model;
class Order extends BaseModel{
    protected $autoWriteTimestamp = true;
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $append=['createtime_text','status_text'];
    //状态:0=待付款,1=已付款,2=已发货,3=已收货,4=已评价
    public function getStatusTextAttr($value,$data)
    {
        $value=is_numeric($value)?$value:(isset($data['status'])?$data['status']:'');
        $arr=['待付款','已付款','已发货','已收货','已评价'];
        return is_numeric($value)?$arr[$value]:'';
    }
    public function getCreatetimeTextAttr($value,$data)
    {
        $value=is_numeric($value)?$value:(isset($data['createtime'])?$data['createtime']:'');
        return is_numeric($value)?date("Y-m-d H:i:s",$value):'';
    }
    public static function onBeforeInsert($row)
    {
        if(empty($row->ordersn)) {
            $row->ordersn = makesn('O');
            return $row;
        }
    }
    public static function orderDetail($id, $uid)
    {
        $row = self::with('ordergood')->find($id)->toArray();
        if($row['user_id'] != $uid) {
            //return __('Invalid parameters');
        }
        foreach ($row['ordergood'] as &$row2){
            $temp=OrderGoodSpecs::where('order_good_id', $row['id'])->column('good_specs_name');
            $row2['specs']=implode(' ',$temp);
        }
        //$data = $row;
        //$data['specs'] = $row->specs;
        return $row;
    }
    /**
     * 订单列表
     * @param $uid 用户ID
     * @return array|int
     */
    public static function orderList($uid,$status)
    {
        if($status){
            $data = self::with('ordergood')->where('user_id', $uid)->where('status',--$status)->select();
        }else{
            $data = self::with('ordergood')->where('user_id', $uid)->select();
        }

        if(!count($data)) {
            return 0;
        }
        foreach ($data as $k => $row) {
            foreach ($row['ordergood'] as &$row2){
                $temp=OrderGoodSpecs::where('order_good_id', $row['id'])->column('good_specs_name');
                $row2['specs']=implode(' ',$temp);
            }
        }
        return $data;
    }
    public function ordergood()
    {
        return $this->hasMany('OrderGood', 'order_id', 'id');
    }
    public function specs()
    {
        return $this->hasManyThrough('OrderGoodSpecs', 'OrderGood');
    }
}
