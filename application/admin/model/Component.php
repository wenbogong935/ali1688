<?php

namespace app\admin\model;

use think\Model;

class Component extends Model
{
    // 表名
    protected $name = 'components';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    /**
     * 关联父产品
     */
    public function product()
    {
        return $this->belongsTo('Product', 'product_id');
    }

    /**
     * 关联父部件（自引用）
     */
    public function parent()
    {
        return $this->belongsTo('Component', 'parent_component_id');
    }

    /**
     * 关联子部件（自引用）
     */
    public function children()
    {
        return $this->hasMany('Component', 'parent_component_id')
                    ->order('sequence asc');
    }

    /**
     * 关联材料使用
     */
    public function materialUsages()
    {
        return $this->hasMany('MaterialUsage', 'component_id')
                    ->order('sequence asc');
    }

    /**
     * 关联工艺分配
     */
    public function processAssignments()
    {
        return $this->hasMany('ProcessAssignment', 'component_id')
                    ->order('sequence asc');
    }

    /**
     * 获取部件层级路径
     */
    public function getHierarchyPath()
    {
        $path = [$this->name];
        $parent = $this->parent;
        
        while ($parent) {
            array_unshift($path, $parent->name);
            $parent = $parent->parent;
        }
        
        return implode(' > ', $path);
    }

    /**
     * 获取部件层级深度
     */
    public function getDepth()
    {
        $depth = 0;
        $parent = $this->parent;
        
        while ($parent) {
            $depth++;
            $parent = $parent->parent;
        }
        
        return $depth;
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
     * 数量格式化
     */
    public function getQuantityPerParentAttr($value)
    {
        return $value ?: 1;
    }

    // Accessor for frontend compatibility
    public function getQuantityAttr($value, $data)
    {
        return $data['quantity_per_parent'] ?? 1;
    }

    // Mutator for frontend compatibility
    public function setQuantityAttr($value)
    {
        $this->set('quantity_per_parent', $value);
    }
}