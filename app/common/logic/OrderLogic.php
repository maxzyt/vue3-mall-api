<?php
namespace app\common\logic;
use app\common\model\Order;
use app\common\model\OrderGood;
use app\common\model\OrderGoodSpecs;
use app\common\model\Good;
use think\Exception;
use think\facade\Db;
class OrderLogic{
    /**
     * 创建订单
     * @param $goodId
     * @param $specs
     * @param $amount
     * @param $payWay
     * @param $uid
     * @param $address
     * @return array
     */
    public static function createOrder($goodId, $specs, $amount, $payWay, $uid, $address)
    {
        $rs = true;
        try{
            $good = Good::find($goodId);
            if($good->status != 'normal') {
                throw new Exception(__('商品已下架'));
            }
            Db::startTrans();
            $now = time();
            $order = Order::create([
                'user_id' => $uid,
                'money' => bcmul($good->price, $amount, 2),
                'status' => 1,
                'pay_time' => $now,
                'pay_way' => $payWay,
                'consignee' => $address->name,
                'address' => $address->address,
                'mobile' => $address->mobile,
                'postal_code' => $address->postal_code
            ]);
            $orderGood = OrderGood::create([
                'order_id' => $order->id,
                'good_id' => $goodId,
                'price' => $good->price,
                'good_name' => $good->name,
                'good_image' => $good->image,
                'policy' => $good->policy,
                'amount' => $amount
            ]);
            if(!empty($specs)) {
                $specsData = \app\common\model\SpecsValue::with(['specs'])->where('id', 'in', $specs)->select();
                $insert = [];
                foreach ($specsData as $k => $row) {
                    $insert[$k]['order_good_id'] = $orderGood->id;
                    $insert[$k]['good_specs_id'] = $row->id;
                    $insert[$k]['specs_name'] = $row->specs->name;
                    $insert[$k]['good_specs_name'] = $row->name;
                }
                $orderGoodSpecsModel = new OrderGoodSpecs();
                $rs = $orderGoodSpecsModel->saveAll($insert);
            }

        }catch (\Exception $e) {
            Db::rollback();
            return ['code' => 0, 'msg' => $e->getMessage()];
        }
        if($rs) {
            Db::commit();
        }else {
            Db::rollback();
        }
        return $rs ? ['code' => 1, 'msg' => __('下单成功'), 'data'=>$order->id] : ['code' => 0, 'msg' => __('下单失败')];
    }
}
