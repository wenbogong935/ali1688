<?php

namespace app\admin\model;

use think\Model;

class ProcessAssignment extends Model
{
    // 表名
    protected $name = 'process_assignments';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    // 追加的属性
    protected $append = [
        'assignment_type'
    ];

    /**
     * 获取分配类型
     * @param mixed $value
     * @param array $data
     * @return string
     */
    public function getAssignmentTypeAttr($value, $data)
    {
        // 根据component_id或material_usage_id判断类型
        return !empty($data['component_id']) ? 'component' : 'material';
    }

    /**
     * 关联部件
     */
    public function component()
    {
        return $this->belongsTo('Component', 'component_id');
    }

    /**
     * 关联材料使用
     */
    public function materialUsage()
    {
        return $this->belongsTo('MaterialUsage', 'material_usage_id');
    }

    /**
     * 关联工艺
     */
    public function process()
    {
        return $this->belongsTo('Process', 'process_id');
    }

    /**
     * 关联父工艺分配（自引用）
     */
    public function parent()
    {
        return $this->belongsTo('ProcessAssignment', 'parent_assignment_id');
    }

    /**
     * 关联子工艺分配（自引用）
     */
    public function children()
    {
        return $this->hasMany('ProcessAssignment', 'parent_assignment_id')
                    ->order('sequence asc');
    }

    /**
     * 成本覆盖格式化
     */
    public function getCostOverrideAttr($value)
    {
        return $value ? number_format($value, 6) : null;
    }

    /**
     * 是否受拼版数影响格式化
     */
    public function getIsAffectedByImpositionAttr($value)
    {
        return $value ? 1 : 0;
    }

    /**
     * 是否转换性工艺格式化
     */
    public function getIsTransformativeAttr($value)
    {
        return $value ? 1 : 0;
    }

    /**
     * 输出长度公式访问器
     */
    public function getOutputLengthFormulaAttr($value)
    {
        return $value ?: null;
    }

    /**
     * 输出宽度公式访问器
     */
    public function getOutputWidthFormulaAttr($value)
    {
        return $value ?: null;
    }

    /**
     * 输出高度公式访问器
     */
    public function getOutputHeightFormulaAttr($value)
    {
        return $value ?: null;
    }

    /**
     * 输出数量公式访问器
     */
    public function getOutputQuantityFormulaAttr($value)
    {
        return $value ?: '1';
    }

    /**
     * 获取显示名称（别名或工艺名称）
     */
    public function getDisplayName()
    {
        return $this->alias ?: ($this->process ? $this->process->name : 'Unknown Process');
    }

    /**
     * 获取有效的成本费率（覆盖值优先）
     */
    public function getEffectiveCostRate()
    {
        return $this->cost_override ?: 
               ($this->process ? $this->process->cost_rate : 0);
    }

    /**
     * 获取关联对象的描述
     */
    public function getTargetDescription()
    {
        if ($this->component_id) {
            return '部件: ' . ($this->component ? $this->component->name : 'Unknown');
        } elseif ($this->material_usage_id) {
            return '材料: ' . ($this->materialUsage ? $this->materialUsage->getDisplayName() : 'Unknown');
        } else {
            return '未关联';
        }
    }

    /**
     * 获取完整的工艺分配描述
     */
    public function getFullDescription()
    {
        $desc = $this->getDisplayName();
        
        $details = [];
        $details[] = $this->getTargetDescription();
        
        if ($this->cost_override) {
            $details[] = '成本覆盖:' . $this->cost_override;
        }
        
        if ($this->is_transformative) {
            $details[] = '转换性工艺';
        }
        
        if (!$this->is_affected_by_imposition) {
            $details[] = '不受拼版影响';
        }
        
        return $desc . ' [' . implode(', ', $details) . ']';
    }

    /**
     * 检查是否有有效的输出公式
     */
    public function hasOutputFormulas()
    {
        return $this->output_length_formula || 
               $this->output_width_formula || 
               $this->output_height_formula || 
               $this->output_quantity_formula;
    }
}