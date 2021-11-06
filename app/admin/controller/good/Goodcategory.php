<?php

namespace app\admin\controller\good;
use fast\Tree;
use app\common\controller\Backend;

/**
 * 分类管理
 *
 * @icon fa fa-circle-o
 */
class Goodcategory extends Backend
{
    
    /**
     * Goodcategory模型对象
     * @var \app\admin\model\good\Goodcategory
     */
    protected $model = null;
    protected $rulelist = [];
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\good\Goodcategory;
        $this->view->assign("typeList", $this->model->getTypeList());
        $this->view->assign("statusList", $this->model->getStatusList());
        parent::_initialize();
        if (!$this->auth->isSuperAdmin()) {
            $this->error(__('Access is allowed only to the super management group'));
        }
        // 必须将结果集转换为数组
        $ruleList = $this->model->order('weigh', 'desc')->order('id', 'asc')->where('type', 0)->select()->toArray();
        foreach ($ruleList as $k => &$v) {
            $v['name']  = __($v['name']);
        }
        unset($v);
        Tree::instance()->init($ruleList, null, ' ');
        $this->rulelist = Tree::instance()->getTreeList(Tree::instance()->getTreeArray(0), 'name');
        $ruledata       = [0 => __('None')];
        foreach ($this->rulelist as $k => &$v) {
            $ruledata[$v['id']] = $v['name'];
        }
        unset($v);
        $this->view->assign('ruledata', $ruledata);
    }
    
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    /**
     * 查看.
     */
    public function index()
    {
        if ($this->request->isAjax()) {
            if($keyValue=$this->request->param('keyValue')){
                $total=1;
                $list=$this->model->where('id',$keyValue)->select();
            }else{
                $list  = $this->rulelist;
                $total = count($this->rulelist);
            }
            $result = ['total' => $total, 'rows' => $list];

            return json($result);
        }

        return $this->view->fetch();
    }
}
