<?php
namespace app\api\controller;
use app\common\controller\Api;
use app\common\model\Good;
use app\common\model\SpecsValue;
use app\common\model\UserAddress;
use app\common\model\Order as OrderModel;
use app\common\logic\OrderLogic;
class Order extends Api{
    protected $noNeedLogin = ['initOrder'];
    protected $noNeedRight = ['*'];

    /**
     * 初始化确认订单
     */
    public function initOrder()
    {
        $goodId=$this->request->param('goodId');
        $attrIds=$this->request->param('attrIds');
        $amount=$this->request->param('amount');
        $row=Good::find($goodId);
        $attrIdArr=explode(',',$attrIds);
        $specs=SpecsValue::withJoin('specs')->where('specs_value.id','in',$attrIdArr)->select();
        foreach ($specs as $row2){
            $row2->getRelation('specs')->visible(['name']);
        }
        $address=UserAddress::where('user_id',$this->auth->id)->where('default',1)->find();
        $this->success('',compact('row','specs','address'));
    }
    /**
     * 订单详情
     */
    public function orderDetail()
    {
        $id = $this->request->param('id');//var_dump($id);
        $data = OrderModel::orderDetail($id, $this->auth->id);//var_dump($data);
        if(!is_string($data)) {
            $this->success('', $data);
        }else {
            $this->error($data);
        }
    }
    /**
     * 订单列表
     */
    public function orderList()
    {
        $status=$this->request->param('status');
        $data = OrderModel::orderList($this->auth->id,$status);
        if(empty($data)) {
            $this->error(__('No More'));
        }
        $this->success('', $data);
    }
    /**
     * 下单
     */
    public function createOrder()
    {
        //return json(['code'=>1,'data'=>123,'msg'=>'下单成功']);
        $goodId = $this->request->param('goodId');
        $specs = $this->request->param('specs');
        $amount = $this->request->param('amount');
        $payWay = $this->request->param('payWay');
        $address = UserAddress::where('user_id', $this->auth->id)->where('default', 1)->find();
        empty($address) && $this->error(__('请选择收货地址'));
        $amount <= 0 && $this->error(__('请设置购买数量'));
        return json(OrderLogic::createOrder($goodId, explode(',', $specs), $amount, $payWay, $this->auth->id, $address));
    }
}
