<?php

namespace app\admin\controller\bom;

use app\common\controller\Backend;
use app\admin\model\Product as ProductModel;
use app\common\library\costing\SpecificationService;
use app\common\library\costing\CostingService;
use think\Db;
use think\exception\DbException;
use think\exception\PDOException;
use think\exception\ValidateException;

/**
 * BOM产品管理
 */
class Product extends Backend
{
    /**
     * Product模型对象
     * @var \app\admin\model\Product
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new ProductModel;

        $this->view->assign("statusList", $this->model->getStatusList());
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
                    ->where($where)
                    ->order($sort, $order)
                    ->paginate($limit);

            foreach ($list as $row) {
                $row->visible(['id','name','description','image','length_formula','width_formula','height_formula','createtime','updatetime']);
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
     * BOM管理
     */
    public function bom($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }

        if ($this->request->isPost()) {
            // 处理BOM数据保存逻辑
            $bomData = $this->request->post('bom_data');
            // 这里可以添加BOM数据的保存逻辑
            $this->success('BOM数据保存成功');
        }

        // 加载完整的BOM树
        $productWithBom = $row->getFullBomTree();
        
        $this->view->assign("row", $productWithBom);
        return $this->view->fetch();
    }

    /**
     * 成本计算
     */
    public function calculate($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }

        try {
            // 1. 完整加载BOM树
            $productWithBom = ProductModel::with([
                'components' => function($query) {
                    $query->with([
                        'children' => function($subQuery) {
                            $subQuery->with([
                                'materialUsages.rawMaterial',
                                'processAssignments.process'
                            ]);
                        },
                        'materialUsages.rawMaterial',
                        'materialUsages.processAssignments.process', 
                        'processAssignments.process'
                    ])->order('sequence asc');
                }
            ])->find($ids);

            if (!$productWithBom) {
                $this->error('产品不存在');
            }

            // 2. 实例化服务
            $specService = new SpecificationService();
            $costService = new CostingService();

            // 3. 执行规格计算
            $annotatedProduct = $specService->calculate($productWithBom);

            // 4. 获取前端传入的动态参数
            $configParams = $this->request->get("configParams/a", []);
            
            // 默认参数
            $defaultParams = [
                'packaging_cost' => 0,      // 包装费用
                'labor_cost' => 0,          // 人工费用  
                'waste_rate' => 0,          // 总损耗率(%)
                'small_batch_cost' => 0     // 小批量生产摊销费用
            ];
            
            $configParams = array_merge($defaultParams, $configParams);

            // 5. 执行成本计算
            $costSummary = $costService->calculate($annotatedProduct, $configParams);

            // 6. 返回结果
            if ($this->request->isAjax()) {
                $this->success('计算成功', '', [
                    'summary' => $costSummary,
                    'product' => [
                        'id' => $annotatedProduct->id,
                        'name' => $annotatedProduct->name,
                        'resolved_l' => $annotatedProduct->resolved_l,
                        'resolved_w' => $annotatedProduct->resolved_w,
                        'resolved_h' => $annotatedProduct->resolved_h
                    ]
                ]);
            } else {
                // 非AJAX请求，返回页面视图
                $this->view->assign('product', $annotatedProduct);
                $this->view->assign('costSummary', $costSummary);
                $this->view->assign('configParams', $configParams);
                return $this->view->fetch();
            }

        } catch (\Exception $e) {
            \think\Log::error('成本计算错误: ' . $e->getMessage());
            if ($this->request->isAjax()) {
                $this->error('计算失败: ' . $e->getMessage());
            } else {
                $this->error('计算失败: ' . $e->getMessage());
            }
        }
    }

    /**
     * 获取产品的BOM树结构（AJAX接口）
     */
    public function getBomTree($ids = null)
    {
        if (!$this->request->isAjax()) {
            $this->error('非法请求');
        }

        $row = $this->model->get($ids);
        if (!$row) {
            $this->error('产品不存在');
        }

        try {
            // 加载完整BOM树
            $productWithBom = $row->getFullBomTree();
            
            // 构建树形结构数据
            $treeData = $this->buildBomTreeData($productWithBom);
            
            $this->success('获取成功', '', $treeData);
        } catch (\Exception $e) {
            $this->error('获取BOM树失败: ' . $e->getMessage());
        }
    }

    /**
     * 构建BOM树形数据（兼容jsTree格式）
     */
    private function buildBomTreeData($product)
    {
        $tree = [
            'id' => 'product_' . $product->id,
            'text' => $product->name . ' (产品)',
            'type' => 'product',
            'state' => ['opened' => true],
            'li_attr' => ['data-type' => 'product', 'data-id' => $product->id],
            'data' => [
                'id' => $product->id,
                'name' => $product->name,
                'type' => 'product',
                'length_formula' => $product->length_formula,
                'width_formula' => $product->width_formula,
                'height_formula' => $product->height_formula
            ],
            'children' => []
        ];

        if (isset($product->components)) {
            foreach ($product->components as $component) {
                $tree['children'][] = $this->buildComponentTreeData($component);
            }
        }

        return $tree;
    }

    /**
     * 构建部件树形数据（兼容jsTree格式）
     */
    private function buildComponentTreeData($component)
    {
        $node = [
            'id' => 'component_' . $component->id,
            'text' => $component->name . ' (部件) x' . $component->quantity_per_parent,
            'type' => 'component',
            'state' => ['opened' => true],
            'li_attr' => ['data-type' => 'component', 'data-id' => $component->id],
            'data' => [
                'id' => $component->id,
                'name' => $component->name,
                'type' => 'component',
                'quantity_per_parent' => $component->quantity_per_parent,
                'length_formula' => $component->length_formula,
                'width_formula' => $component->width_formula,
                'height_formula' => $component->height_formula
            ],
            'children' => []
        ];

        // 添加材料使用
        if (isset($component->materialUsages)) {
            foreach ($component->materialUsages as $materialUsage) {
                $node['children'][] = [
                    'id' => 'material_' . $materialUsage->id,
                    'text' => $materialUsage->getDisplayName() . ' (材料)',
                    'type' => 'material',
                    'li_attr' => ['data-type' => 'material', 'data-id' => $materialUsage->id],
                    'data' => [
                        'id' => $materialUsage->id,
                        'name' => $materialUsage->getDisplayName(),
                        'type' => 'material',
                        'raw_material_id' => $materialUsage->raw_material_id,
                        'length_formula' => $materialUsage->length_formula,
                        'width_formula' => $materialUsage->width_formula,
                        'imposition_quantity' => $materialUsage->imposition_quantity
                    ]
                ];
            }
        }

        // 添加工艺分配
        if (isset($component->processAssignments)) {
            foreach ($component->processAssignments as $processAssignment) {
                $node['children'][] = [
                    'id' => 'process_' . $processAssignment->id,
                    'text' => $processAssignment->getDisplayName() . ' (工艺)',
                    'type' => 'process',
                    'li_attr' => ['data-type' => 'process', 'data-id' => $processAssignment->id],
                    'data' => [
                        'id' => $processAssignment->id,
                        'name' => $processAssignment->getDisplayName(),
                        'type' => 'process',
                        'process_id' => $processAssignment->process_id,
                        'cost_override' => $processAssignment->cost_override
                    ]
                ];
            }
        }

        // 递归添加子部件
        if (isset($component->children)) {
            foreach ($component->children as $childComponent) {
                $node['children'][] = $this->buildComponentTreeData($childComponent);
            }
        }

        return $node;
    }

    /**
     * 移动节点位置
     */
    public function moveNode()
    {
        if (!$this->request->isAjax()) {
            $this->error('非法请求');
        }

        $nodeId = $this->request->post('node_id');
        $nodeType = $this->request->post('node_type');
        $parentId = $this->request->post('parent_id');
        $position = $this->request->post('position', 0);

        try {
            // 根据节点类型处理移动逻辑
            switch($nodeType) {
                case 'component':
                    $component = \app\admin\model\Component::get($nodeId);
                    if ($component) {
                        $component->parent_component_id = $parentId;
                        $component->sequence = $position;
                        $component->save();
                    }
                    break;
                case 'material':
                    // 材料节点移动逻辑
                    break;
                case 'process':
                    // 工艺节点移动逻辑
                    break;
            }

            $this->success('节点移动成功');
        } catch (\Exception $e) {
            $this->error('移动失败: ' . $e->getMessage());
        }
    }

    /**
     * 复制节点
     */
    public function duplicateNode()
    {
        if (!$this->request->isAjax()) {
            $this->error('非法请求');
        }

        $nodeType = $this->request->post('node_type');
        $nodeId = $this->request->post('node_id');

        try {
            switch($nodeType) {
                case 'component':
                    $original = \app\admin\model\Component::get($nodeId);
                    if ($original) {
                        $duplicate = $original->toArray();
                        unset($duplicate['id']);
                        $duplicate['name'] = $duplicate['name'] . ' (副本)';
                        $duplicate['createtime'] = time();
                        $duplicate['updatetime'] = time();
                        
                        \app\admin\model\Component::create($duplicate);
                        $this->success('部件复制成功');
                    }
                    break;
                // 其他类型的复制逻辑...
            }
        } catch (\Exception $e) {
            $this->error('复制失败: ' . $e->getMessage());
        }
    }

    /**
     * 导出BOM
     */
    public function exportBom($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }

        // 这里可以实现BOM导出逻辑
        // 比如导出Excel、PDF等格式
        $this->success('导出功能开发中...');
    }
}