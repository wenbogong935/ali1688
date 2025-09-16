<?php

namespace app\admin\model;

use think\Model;

class RawMaterial extends Model
{
    // 表名
    protected $name = 'raw_materials';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    /**
     * 关联材料使用记录
     */
    public function materialUsages()
    {
        return $this->hasMany('MaterialUsage', 'raw_material_id');
    }

    /**
     * 获取材料类型列表
     */
    public function getTypeList()
    {
        return [
            '纸类' => '纸类',
            '塑料类' => '塑料类',
            '金属类' => '金属类',
            '木材类' => '木材类',
            '玻璃类' => '玻璃类',
            '陶瓷类' => '陶瓷类',
            '纺织品' => '纺织品',
            '化学品' => '化学品',
            '其他' => '其他'
        ];
    }

    /**
     * 获取计量单位列表
     */
    public function getUnitOfMeasureList()
    {
        return [
            '每公斤' => '每公斤',
            '每平方米' => '每平方米',
            '每立方米' => '每立方米',
            '每件' => '每件',
            '每米' => '每米',
            '每升' => '每升'
        ];
    }

    /**
     * 单位成本格式化
     */
    public function getUnitCostAttr($value)
    {
        return number_format($value, 6);
    }

    /**
     * 标准长度格式化
     */
    public function getStdLengthCmAttr($value)
    {
        return $value ? number_format($value, 2) : null;
    }

    /**
     * 标准宽度格式化
     */
    public function getStdWidthCmAttr($value)
    {
        return $value ? number_format($value, 2) : null;
    }

    /**
     * 标准克重格式化
     */
    public function getStdGrammageGsmAttr($value)
    {
        return $value ? number_format($value, 2) : null;
    }

    /**
     * 厚度格式化
     */
    public function getThicknessMmAttr($value)
    {
        return $value ? number_format($value, 4) : null;
    }

    /**
     * 密度格式化
     */
    public function getDensityKgm3Attr($value)
    {
        return $value ? number_format($value, 2) : null;
    }

    /**
     * 获取完整的材料描述
     */
    public function getFullDescription()
    {
        $desc = $this->name;
        
        if ($this->type) {
            $desc .= ' (' . $this->type . ')';
        }
        
        $specs = [];
        if ($this->std_length_cm || $this->std_width_cm) {
            $specs[] = $this->std_length_cm . 'x' . $this->std_width_cm . 'cm';
        }
        if ($this->std_grammage_gsm) {
            $specs[] = $this->std_grammage_gsm . 'g/m²';
        }
        if ($this->thickness_mm) {
            $specs[] = $this->thickness_mm . 'mm';
        }
        
        if (!empty($specs)) {
            $desc .= ' [' . implode(', ', $specs) . ']';
        }
        
        return $desc;
    }
}