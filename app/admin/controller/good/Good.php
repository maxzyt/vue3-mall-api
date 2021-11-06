<?php

namespace app\admin\controller\good;

use app\common\controller\Backend;
use think\exception\ValidateException;
use think\facade\Db;
use app\admin\model\good\Specs;
use app\admin\model\good\SpecsValue;

/**
 * 商品管理
 *
 * @icon fa fa-circle-o
 */
class Good extends Backend
{
    
    /**
     * Good模型对象
     * @var \app\admin\model\good\Good
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\good\Good;
        $this->view->assign("statusList", $this->model->getStatusList());
        $this->view->assign("butieList", $this->model->getButieList());
        $this->view->assign("isHomeList", $this->model->getIsHomeList());
        $this->view->assign("isBestList", $this->model->getIsBestList());
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
                    ->withJoin(['goodcategory'])
                    ->where($where)
                    ->order($sort, $order)
                    ->count();

            $list = $this->model
                    ->withJoin(['goodcategory'])
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
    /**
     * 添加
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if ($params) {
                $params = $this->preExcludeFields($params);

                if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                    $params[$this->dataLimitField] = $this->auth->id;
                }
                $result = false;
                Db::startTrans();

                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name     = str_replace('\\model\\', '\\validate\\', get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name.'.add' : $name) : $this->modelValidate;
                        validate($validate)->scene($this->modelSceneValidate ? 'edit' : $name)->check($params);
                    }
                    $result = $this->model->save($params);
                    Db::commit();
                } catch (ValidateException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (\PDOException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                if ($result !== false) {
                    $this->success();
                } else {
                    $this->error(__('No rows were inserted'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $data = Specs::withJoin('specsvalue')->where('status', 'normal')->select();
        $this->view->assign('_specs', $data);
        return $this->view->fetch();
    }
    /**
     * 编辑
     */
    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }
        if ($this->request->isPost()) {
            $params = $this->request->post('row/a');
            if ($params) {
                $params = $this->preExcludeFields($params);
                $result = false;
                Db::startTrans();

                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name     = str_replace('\\model\\', '\\validate\\', get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? $name : $this->modelValidate;
                        $pk       = $row->getPk();
                        if (!isset($params[$pk])) {
                            $params[$pk] = $row->$pk;
                        }
                        validate($validate)->scene($this->modelSceneValidate ? 'edit' : $name)->check($params);
                    }
                    $result = $row->save($params);
                    $allSpecs = SpecsValue::withJoin(['specs'])->where('good_id', $ids)->select();
                    foreach ($params['specs_value_id'] as $v) {

                    }
                    SpecsValue::withJoin('specs')->where('good_id', $ids)->where('specs_value.id', 'in', $params['specs_value_id'])->where('specs_value.is_use', 0)->update(['specs_value.is_use' => 1]);
                    SpecsValue::withJoin('specs')->where('good_id', $ids)->where('specs_value.id', 'not in', $params['specs_value_id'])->where('specs_value.is_use', 1)->update(['specs_value.is_use' => 0]);
                    Db::commit();
                } catch (ValidateException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (\PDOException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                if ($result !== false) {
                    $this->success();
                } else {
                    $this->error(__('No rows were updated'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->view->assign('row', $row);
        $data = Specs::withJoin('specsvalue')->where('good_id', $ids)->where('status', 'normal')->select();//dump($data);
        $this->view->assign('_specs', $data);
        return $this->view->fetch();
    }
}
