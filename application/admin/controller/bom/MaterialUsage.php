<?php

namespace app\admin\controller\bom;

use app\common\controller\Backend;
use app\admin\model\MaterialUsage as MaterialUsageModel;
use app\admin\model\Component as ComponentModel;
use app\admin\model\RawMaterial as RawMaterialModel;
use think\Db;  
use think\exception\DbException;
use think\exception\PDOException;
use think\exception\ValidateException;

/**
 * 材料使用管理
 */
class MaterialUsage extends Backend
{
    /**
     * MaterialUsage模型对象
     * @var \app\admin\model\MaterialUsage
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new MaterialUsageModel;
        
        // 获取部件列表
        $componentModel = new ComponentModel;
        $componentList = $componentModel->with('product')->select();
        $componentOptions = [];
        foreach ($componentList as $component) {
            $componentOptions[$component->id] = $component->product->name . ' > ' . $component->name;
        }
        $this->view->assign("componentList", $componentOptions);
        
        // 获取原材料列表
        $rawMaterialModel = new RawMaterialModel;
        $rawMaterialList = $rawMaterialModel->column('name', 'id');
        $this->view->assign("rawMaterialList", $rawMaterialList);
    }

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
                    ->with(['component.product', 'rawMaterial'])
                    ->where($where)
                    ->order($sort, $order)
                    ->paginate($limit);

            foreach ($list as $row) {
                $row->visible(['id','component_id','raw_material_id','length_formula','width_formula','height_formula','panel','gsm_override','thickness_override','density_override','sequence','createtime','updatetime']);
                $row->visible(['component', 'rawMaterial']);
                $row->getRelation('component')->visible(['name', 'product']);
                if ($row->component && $row->component->product) {
                    $row->getRelation('component')->getRelation('product')->visible(['name']);
                }
                $row->getRelation('rawMaterial')->visible(['name', 'type']);
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
     * 根据部件ID获取材料使用列表
     */
    public function getByComponent()
    {
        if (!$this->request->isAjax()) {
            $this->error('非法请求');
        }

        $componentId = $this->request->get('component_id');
        if (!$componentId) {
            $this->error('部件ID不能为空');
        }

        try {
            $materialUsages = $this->model
                ->with(['rawMaterial'])
                ->where('component_id', $componentId)
                ->order('sequence asc')
                ->select();

            $result = [];
            foreach ($materialUsages as $usage) {
                $result[] = [
                    'id' => $usage->id,
                    'alias' => $usage->alias,
                    'raw_material_name' => $usage->rawMaterial->name ?? 'Unknown',
                    'length_formula' => $usage->length_formula,
                    'width_formula' => $usage->width_formula,
                    'imposition_quantity' => $usage->imposition_quantity,
                    'grammage_override' => $usage->grammage_override,
                    'thickness_override_mm' => $usage->thickness_override_mm
                ];
            }

            $this->success('获取成功', '', $result);
        } catch (\Exception $e) {
            $this->error('获取失败: ' . $e->getMessage());
        }
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