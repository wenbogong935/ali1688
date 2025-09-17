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
            'paper' => '纸类',
            'plastic' => '塑料类',
            'metal' => '金属类',
            'wood' => '木材类',
            'glass' => '玻璃类',
            'ceramic' => '陶瓷类',
            'textile' => '纺织品',
            'chemical' => '化学品',
            'other' => '其他'
        ];
    }

    /**
     * 获取计量单位列表
     */
    public function getUnitOfMeasureList()
    {
        return [
            'per_kg' => '每公斤',
            'per_sqm' => '每平方米',
            'per_cbm' => '每立方米',
            'per_piece' => '每件',
            'per_meter' => '每米',
            'per_liter' => '每升',
            'per_roll' => '每卷',
            'per_sheet' => '每张'
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

    // Accessors for frontend compatibility
    public function getStandardLengthAttr($value, $data)
    {
        return $data['std_length_cm'] ?? null;
    }
    public function getStandardWidthAttr($value, $data)
    {
        return $data['std_width_cm'] ?? null;
    }
    public function getStandardHeightAttr($value, $data)
    {
        return $data['std_height_cm'] ?? null;
    }
    public function getGsmAttr($value, $data)
    {
        return $data['std_grammage_gsm'] ?? null;
    }
    public function getThicknessAttr($value, $data)
    {
        return $data['thickness_mm'] ?? null;
    }
    public function getDensityAttr($value, $data)
    {
        return $data['density_kgm3'] ?? null;
    }

    // Mutators for frontend compatibility
    public function setStandardLengthAttr($value)
    {
        $this->set('std_length_cm', $value);
    }
    public function setStandardWidthAttr($value)
    {
        $this->set('std_width_cm', $value);
    }
    public function setStandardHeightAttr($value)
    {
        $this->set('std_height_cm', $value);
    }
    public function setGsmAttr($value)
    {
        $this->set('std_grammage_gsm', $value);
    }
    public function setThicknessAttr($value)
    {
        $this->set('thickness_mm', $value);
    }
    public function setDensityAttr($value)
    {
        $this->set('density_kgm3', $value);
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