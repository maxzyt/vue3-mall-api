<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\model\GoodCategory;
use app\common\model\Good as GoodModel;
use app\common\model\UserCollect;
use app\common\model\Specs;
use app\common\model\UserHistory;

/**
 * 首页接口
 */
class Good extends Api
{
    protected $noNeedLogin = ['goodList', 'category', 'goodDetail','categoryById'];
    protected $noNeedRight = ['*'];

    /**
     * 商品列表
     */
    public function goodList()
    {
        $cid       = $this->request->param('cid');
        $orderby       = $this->request->param('orderby');//0综合1销量2价格
        $priceSort = $this->request->param('priceSort');//0升序1降序
        $page      = $this->request->param('page');
        $limit     = $this->request->param('limit');
        if($orderby==1){
            $order = 'sale';
        }elseif ($orderby==2){
            $order='price';
        }else{
            $order='weigh';
        }
        $order=$priceSort==1?$order.' desc':$order.' asc';
        $data = GoodModel::goodList($cid, $page, $limit, $order);
        $this->success('', $data);
    }
    /**
     * 商品分类获取子分类
     */
    public function categoryById()
    {
        $id=$this->request->param('id');
        $data1 = GoodCategory::goodData($id);
        foreach ($data1 as $k => $row) {
            $data1[$k]['data2'] = GoodCategory::goodData($row->id);
        }
        $this->success('',$data1);
    }
    /**
     * 商品分类
     */
    public function category()
    {
        $data = GoodCategory::goodData(0);
        if (!count($data)) {
            $this->error(__('No More'));
        }
        $id    = $data[0]['id'];
        $data1 = GoodCategory::goodData($id);
        foreach ($data1 as $k => $row) {
            $data1[$k]['data2'] = GoodCategory::goodData($row->id);
        }
        $this->success('', compact('data', 'data1'));
    }

    /**
     * 商品详情
     */
    public function goodDetail()
    {
        $id  = $this->request->param('id');
        $row = GoodModel::find($id);
        if ($row->status != 'normal') {
            $this->error(__('商品已下架'));
        }
        $gallery      = explode(',', $row->images);
        $specs        = Specs::goodSpecs($id);
        $domain       = getDomain();
        $row->content = str_replace('/uploads', $domain.'/uploads', $row->content);
        $uid=$this->auth->id;
        $isCollect=false;
        if($uid){
            $row2=UserCollect::where('user_id',$uid)->where('good_id',$id)->find();
            $isCollect=!empty($row2)?true:false;
            UserHistory::addHistory($uid,$id);
        }
        $this->success('', compact('row', 'gallery', 'specs','isCollect'));
    }

    /**
     * 收藏商品
     */
    public function collect()
    {
        $id  = $this->request->param('id');
        $row = GoodModel::find($id);
        if ($row->status != 'normal') {
            $this->error('商品已下架');
        }
        if (UserCollect::where('good_id', $id)->find()) {
            $this->error(__('商品已收藏'));
        }
        UserCollect::create([
            'user_id' => $this->auth->id,
            'good_id' => $id
        ]);
        $this->success(__('收藏成功'));
    }
    /**
     * 添加浏览历史
     */
    public function addHistory()
    {
        $id=$this->request->param('id');
    }
}
