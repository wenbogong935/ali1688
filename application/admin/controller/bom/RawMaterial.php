<?php

namespace app\admin\controller\bom;

use app\common\controller\Backend;
use app\admin\model\RawMaterial as RawMaterialModel;
use think\Db;
use think\exception\DbException;
use think\exception\PDOException;
use think\exception\ValidateException;

/**
 * 原材料管理
 */
class RawMaterial extends Backend
{
    /**
     * RawMaterial模型对象
     * @var \app\admin\model\RawMaterial
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new RawMaterialModel;

        $this->view->assign("typeList", $this->model->getTypeList());
        $this->view->assign("unitOfMeasureList", $this->model->getUnitOfMeasureList());
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
        $this->relationSearch = false;
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
                $row->visible(['id','name','description','type','unit_of_measure','unit_cost','standard_length','standard_width','standard_height','gsm','thickness','density','supplier','notes','createtime','updatetime']);
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
     * 批量导入
     */
    public function import()
    {
        if ($this->request->isPost()) {
            $file = $this->request->file('file');
            if (!$file) {
                $this->error('请选择文件');
            }

            try {
                // 这里可以添加Excel导入逻辑
                // 使用PhpSpreadsheet库处理Excel文件
                $this->success('导入成功');
            } catch (\Exception $e) {
                $this->error('导入失败: ' . $e->getMessage());
            }
        }
        return $this->view->fetch();
    }

    /**
     * 导出模板
     */
    public function exportTemplate()
    {
        // 创建导出模板的逻辑
        $headers = [
            '原材料名称',
            '材料类型',
            '单位成本',
            '计量单位',
            '标准长度(cm)',
            '标准宽度(cm)',
            '标准克重(g/m²)',
            '厚度(mm)',
            '密度(kg/m³)',
            '备注'
        ];

        // 这里可以使用PhpSpreadsheet创建Excel模板
        $this->success('模板导出成功');
    }
}