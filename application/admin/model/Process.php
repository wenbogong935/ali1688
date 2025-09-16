<?php

namespace app\admin\model;

use think\Model;

class Process extends Model
{
    // 表名
    protected $name = 'processes';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    /**
     * 关联工艺分配
     */
    public function processAssignments()
    {
        return $this->hasMany('ProcessAssignment', 'process_id');
    }

    /**
     * 获取成本类型列表
     */
    public function getCostTypeList()
    {
        return [
            'per_unit_variable' => '每单位可变',
            'per_area_variable' => '每面积可变',
            'per_time_variable' => '每时间可变',
            'fixed' => '固定'
        ];
    }

    /**
     * 获取成本单位列表
     */
    public function getUnitOfCostList()
    {
        return [
            'yuan_per_piece' => '元/件',
            'yuan_per_sqm' => '元/平方米',
            'yuan_per_hour' => '元/小时',
            'yuan_per_batch' => '元/批次',
            'yuan_per_kg' => '元/公斤',
            '元/米' => '元/米'
        ];
    }

    /**
     * 成本费率格式化
     */
    public function getCostRateAttr($value)
    {
        return number_format($value, 6);
    }

    /**
     * 设置成本格式化
     */
    public function getSetupCostAttr($value)
    {
        return number_format($value, 4);
    }

    /**
     * 吞吐率格式化
     */
    public function getThroughputRateAttr($value)
    {
        return $value ? number_format($value, 4) : null;
    }

    /**
     * 人工费率格式化
     */
    public function getLaborRatePerHourAttr($value)
    {
        return $value ? number_format($value, 4) : null;
    }

    /**
     * 损耗率格式化
     */
    public function getWastePercentageAttr($value)
    {
        return number_format($value, 4);
    }

    /**
     * 获取完整的工艺描述
     */
    public function getFullDescription()
    {
        $desc = $this->name;
        
        $details = [];
        $details[] = $this->cost_type;
        $details[] = $this->cost_rate . ' ' . $this->unit_of_cost;
        
        if ($this->setup_cost > 0) {
            $details[] = '设置费:' . $this->setup_cost . '元';
        }
        
        if ($this->waste_percentage > 0) {
            $details[] = '损耗:' . $this->waste_percentage . '%';
        }
        
        return $desc . ' [' . implode(', ', $details) . ']';
    }

    /**
     * 检查是否为按时间计费的工艺
     */
    public function isTimeBased()
    {
        return in_array($this->cost_type, ['每时间可变']);
    }

    /**
     * 检查是否为按面积计费的工艺
     */
    public function isAreaBased()
    {
        return in_array($this->cost_type, ['每面积可变']);
    }

    /**
     * 检查是否为固定成本工艺
     */
    public function isFixedCost()
    {
        return $this->cost_type === '固定';
    }
}