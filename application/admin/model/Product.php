<?php

namespace app\admin\model;

use think\Model;

class Product extends Model
{
    // 表名
    protected $name = 'products';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    
    // 追加属性
    protected $append = [
        'resolved_l',
        'resolved_w', 
        'resolved_h'
    ];

    /**
     * 关联顶层部件（没有父部件的部件）
     */
    public function components()
    {
        return $this->hasMany('Component', 'product_id')
                    ->where('parent_component_id', null)
                    ->order('sequence asc');
    }

    /**
     * 关联所有部件（包含子部件的完整BOM树）
     */
    public function allComponents()
    {
        return $this->hasMany('Component', 'product_id')
                    ->order('sequence asc');
    }

    /**
     * 获取完整的BOM树（预加载所有关联数据）
     */
    public function getFullBomTree()
    {
        return $this->with([
            'components' => function($query) {
                $query->with([
                    'children.materialUsages.rawMaterial',
                    'children.processAssignments.process',
                    'materialUsages.rawMaterial',
                    'materialUsages.processAssignments.process',
                    'processAssignments.process'
                ]);
            }
        ])->find($this->id);
    }

    /**
     * 获取产品状态列表
     */
    public function getStatusList()
    {
        return [
            'active' => '启用',
            'inactive' => '禁用'
        ];
    }

    /**
     * 长度公式访问器
     */
    public function getLengthFormulaAttr($value)
    {
        return $value ?: '0';
    }

    /**
     * 宽度公式访问器
     */
    public function getWidthFormulaAttr($value)
    {
        return $value ?: '0';
    }

    /**
     * 高度公式访问器
     */
    public function getHeightFormulaAttr($value)
    {
        return $value ?: '0';
    }

    /**
     * 解析后的长度（运行时属性）
     */
    public function getResolvedLAttr()
    {
        return $this->getData('resolved_l') ?: 0;
    }

    /**
     * 解析后的宽度（运行时属性）
     */
    public function getResolvedWAttr()
    {
        return $this->getData('resolved_w') ?: 0;
    }

    /**
     * 解析后的高度（运行时属性）
     */
    public function getResolvedHAttr()
    {
        return $this->getData('resolved_h') ?: 0;
    }
}