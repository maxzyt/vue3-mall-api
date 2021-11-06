<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\model\GoodCategory;
use app\common\model\Good;
use app\common\model\Specs;
use app\common\model\Order;
use app\common\model\Live;

/**
 * 首页接口.
 */
class Index extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    public function test()
    {
        $data = Order::orderList(1);
        dump($data);
        exit;
        $data = Order::with('ordergood')->select();
        $rd   = [];
        foreach ($data as $k => $row) {
            $rd[$k]['id']        = $row->id;
            $rd[$k]['ordergood'] = $row->ordergood;
            $rd[$k]['specs']     = $row->specs;
            $rd[$k]['money']     = $row->money;
        }
//        dump($rd);
        $this->success('', $rd);
        //$this->success('', $data);
        //dump($data->orderGoodSpecs);
        exit;
        list($a, $b) = explode(' ', microtime());
        dump($a);
        dump(intval($a * 1000));
        dump($a);//dump($b);
        echo microtime();
        exit;
        $id   = $this->request->param('id');
        $data = Specs::goodSpecs($id);
        dump($data);
        //$this->success('', $data);
    }
    /**
     * 推荐商品
     */
    public function recommend()
    {
        $page=$this->request->param('page');
        $limit=$this->request->param('limit');
        $start=--$page*$limit;
        $recommendGood = Good::field('id, name, price, image, sale,policy')->where('status', 'normal')->where('is_home', 1)->order('weigh desc, id asc')->limit($start,$limit)->select();
        if($recommendGood->isEmpty()){
            $this->error('暂无更多数据');
        }
        $this->success('',$recommendGood);
    }
    /**
     * 首页接口
     */
    public function index()
    {
        $secondCategory=GoodCategory::secondData();
        //$category = GoodCategory::field('id, name')->where('pid', 0)->where('status',
        //    'normal')->order('weigh desc')->select();
        //每日上新
        $dayGood = Good::field('id, name, price, image')->where('status', 'normal')->where('is_best',
            1)->order('weigh desc, id asc')->limit(4)->select();
        //补贴商品
        $butieGood = Good::field('id, name, price, image')->where('status', 'normal')->where('butie',
            1)->order('weigh desc, id asc')->limit(4)->select();
        //推荐商品
        $recommendGood=[];
        if(!empty($secondCategory[0]['id'])){
            $ids=GoodCategory::allChildren([$secondCategory[0]['id']]);
            $recommendGood = Good::field('id, name, price, image, sale')->where('good_category_id','in',$ids)->where('status', 'normal')->where('is_home',
                1)->order('weigh desc, id asc')->select();
        }
        $this->success('', compact('dayGood', 'butieGood', 'recommendGood','secondCategory'));
    }
    /**
     * 根据ID获取首页推荐商品
     */
    public function recommendGoods()
    {
        $id=$this->request->param('id');
        $ids=GoodCategory::allChildren([$id]);
        $recommendGood = Good::field('id, name, price, image, sale')->where('good_category_id','in',$ids)->where('status', 'normal')->where('is_home',
            1)->order('weigh desc, id asc')->select();
        $this->success('',$recommendGood);
    }

    /**
     * 根据分类获取直播
     */
    public function live()
    {
        $id   = $this->request->param('id');
        $data = Live::where('good_category_id', $id)->where('status', 'normal')->order('weigh desc')->select();
        if ($data->isEmpty()) {
            $this->error('暂无更多数据');
        }
        $this->success('', $data);
    }

    /**
     * 直播分类
     */
    public function getLiveCategory()
    {
        $this->success('', GoodCategory::liveData(0));
        //$this->error('请求失败');
        $data = [
            ['id' => 1, 'label' => '推荐1'], ['id' => 2, 'label' => '流行穿搭2'], ['id' => 3, 'label' => '珠宝饰品'],
            ['id' => 4, 'label' => '歌舞娱乐'], ['id' => 5, 'label' => '宝妈优选'], ['id' => 6, 'label' => '鲜花萌宠'],
            ['id' => 7, 'label' => '生活美家'], ['id' => 8, 'label' => '美食生鲜']
        ];
        $this->success('ok', $data);
    }

    public function getNewsList()
    {
        $data = [['id' => 1, 'title' => '新闻标题1'], ['id' => 2, 'title' => '新闻标题2'], ['id' => 3, 'title' => '新闻标题3']];
        $this->success('ok', $data);
    }
    /**
     * 首页.
     */
//    public function index()
//    {
//        $this->success('请求成功');
//    }
}
