<?php

namespace app\admin\controller\bom;

use app\common\controller\Backend;
use app\admin\model\Component as ComponentModel;
use app\admin\model\Product as ProductModel;
use think\Db;
use think\exception\DbException;
use think\exception\PDOException;
use think\exception\ValidateException;
use Exception;

/**
 * BOM部件管理
 */
class Component extends Backend
{
    /**
     * Component模型对象
     * @var \app\admin\model\Component
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new ComponentModel;
        
        // 获取产品列表
        $productModel = new ProductModel;
        $productList = $productModel->column('name', 'id');
        $this->view->assign("productList", $productList);
        
        // 获取部件列表（用于父部件选择）
        $componentList = $this->model->column('name', 'id');  
        $this->view->assign("componentList", $componentList);
    }

    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自定义
     * 如果需要自定义,可以覆盖对应的方法
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
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            $list = $this->model
                    ->with(['product', 'parent'])
                    ->where($where)
                    ->order($sort, $order)
                    ->paginate($limit);

            foreach ($list as $row) {
                $row->visible(['id','product_id','parent_component_id','name','quantity_per_parent','length_formula','width_formula','height_formula','sequence','createtime','updatetime']);
                $row->visible(['product']);
                $row->getRelation('product')->visible(['name']);
                if ($row->parent) {
                    $row->visible(['parent']);
                    $row->getRelation('parent')->visible(['name']);
                }
            }

            $result = array("total" => $list->total(), "rows" => $list->items());

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
            $params = $this->request->post("row/a");
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
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
                        $this->model->validateFailException(true)->validate($validate);
                    }
                    $result = $this->model->allowField(true)->save($params);
                    Db::commit();
                } catch (ValidateException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (PDOException $e) {
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
        if (is_array($adminIds) && !in_array($row[$this->dataLimitField], $adminIds)) {
            $this->error(__('You have no permission'));
        }
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                $params = $this->preExcludeFields($params);
                $result = false;
                Db::startTrans();
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                        $row->validateFailException(true)->validate($validate);
                    }
                    $result = $row->allowField(true)->save($params);
                    Db::commit();
                } catch (ValidateException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (PDOException $e) {
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
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

    /**
     * 根据产品ID获取部件树
     */
    public function getComponentTree()
    {
        if (!$this->request->isAjax()) {
            $this->error('非法请求');
        }

        $productId = $this->request->get('product_id');
        if (!$productId) {
            $this->error('产品ID不能为空');
        }

        try {
            $components = $this->model
                ->where('product_id', $productId)
                ->where('parent_component_id', null)
                ->with(['children'])
                ->order('sequence asc')
                ->select();

            $treeData = [];
            foreach ($components as $component) {
                $treeData[] = $this->buildComponentTree($component);
            }

            $this->success('获取成功', '', $treeData);
        } catch (\Exception $e) {
            $this->error('获取失败: ' . $e->getMessage());
        }
    }

    /**
     * 构建部件树形结构
     */
    private function buildComponentTree($component)
    {
        $node = [
            'id' => $component->id,
            'text' => $component->name,
            'type' => 'component',
            'data' => [
                'id' => $component->id,
                'name' => $component->name,
                'quantity_per_parent' => $component->quantity_per_parent,
                'length_formula' => $component->length_formula,
                'width_formula' => $component->width_formula,
                'height_formula' => $component->height_formula,
                'sequence' => $component->sequence
            ],
            'children' => []
        ];

        if ($component->children) {
            foreach ($component->children as $child) {
                $node['children'][] = $this->buildComponentTree($child);
            }
        }

        return $node;
    }

    /**
     * 批量更新序号
     */
    public function updateSequence()
    {
        if (!$this->request->isPost()) {
            $this->error('非法请求');
        }

        $sequences = $this->request->post('sequences/a');
        if (!$sequences) {
            $this->error('序号数据不能为空');
        }

        Db::startTrans();
        try {
            foreach ($sequences as $id => $sequence) {
                $this->model->where('id', $id)->update(['sequence' => $sequence]);
            }
            Db::commit();
            $this->success('更新成功');
        } catch (\Exception $e) {
            Db::rollback();
            $this->error('更新失败: ' . $e->getMessage());
        }
    }
}