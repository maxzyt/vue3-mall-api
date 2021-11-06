<?php

namespace app\admin\controller\good;

use app\common\controller\Backend;
use app\admin\model\good\SpecsValue;
use think\facade\Db;
/**
 * 商品规格管理
 *
 * @icon fa fa-circle-o
 */
class Specs extends Backend
{
    
    /**
     * Specs模型对象
     * @var \app\admin\model\good\Specs
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\good\Specs;
        $this->view->assign("statusList", $this->model->getStatusList());
    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */
    

    /**
     * 查看
     */
    public function index()
    {
        //当前是否为关联查询
        $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax())
        {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField'))
            {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                    ->withJoin(['good'])
                    ->where($where)
                    ->order($sort, $order)
                    ->count();

            $list = $this->model
                    ->withJoin(['good'])
                    ->where($where)
                    ->order($sort, $order)
                    ->limit($offset, $limit)
                    ->select();

            foreach ($list as $row) {
                
                
            }
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }
    public function getSpecs()
    {
        if($this->request->isAjax()) {
            $id = $this->request->param('id');
            $data = SpecsValue::withJoin(['specs'])->where('specs_id', $id)->select();
            if(empty($data)) {
                $this->error();
            }else {
                $this->success('', null, $data);
            }
        }
    }
    public function add()
    {
        if($this->request->isAjax()) {
            //$this->success('', null, 3);
            $goodId = $this->request->get('id');//var_dump($goodId);return;
            $param = $this->request->post('row/a');//var_dump($param);exit;
            Db::startTrans();
            $rs2 = 0;
            try{
                $param['good_id'] = $goodId;
                $rs = $this->model->save($param);
                if(!$rs) {
                    throw new \think\Exception('添加失败');
                }
                $i = 0;
                foreach ($param['name2'] as $k => $v) {
                    $insertData[$i]['name'] = $v;
                    $insertData[$i]['image'] = $param['image'][$k];
                    $insertData[$i]['specs_id'] = $this->model->id;
                    $i++;
                }
                $rs2 = (new \app\admin\model\good\SpecsValue())->saveAll($insertData);
            }catch (\Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }
            if($rs2) {
                Db::commit();
                $this->success('', null, $this->model->id);
            }else {
                Db::rollback();
                $this->error(__('No rows were inserted'));
            }
        }else {
            return $this->view->fetch();
        }

    }
}
