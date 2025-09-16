<?php

namespace app\admin\model;

use think\Model;

class MaterialUsage extends Model
{
    // 表名
    protected $name = 'material_usages';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    /**
     * 关联部件
     */
    public function component()
    {
        return $this->belongsTo('Component', 'component_id');
    }

    /**
     * 关联原材料
     */
    public function rawMaterial()
    {
        return $this->belongsTo('RawMaterial', 'raw_material_id');
    }

    /**
     * 关联工艺分配
     */
    public function processAssignments()
    {
        return $this->hasMany('ProcessAssignment', 'material_usage_id')
                    ->order('sequence asc');
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
     * 拼版数格式化
     */
    public function getImpositionQuantityAttr($value)
    {
        return $value ?: 1;
    }

    /**
     * 克重覆盖格式化
     */
    public function getGrammageOverrideAttr($value)
    {
        return $value ? number_format($value, 2) : null;
    }

    /**
     * 厚度覆盖格式化
     */
    public function getThicknessOverrideMmAttr($value)
    {
        return $value ? number_format($value, 4) : null;
    }

    /**
     * 获取显示名称（别名或原材料名称）
     */
    public function getDisplayName()
    {
        return $this->alias ?: ($this->rawMaterial ? $this->rawMaterial->name : 'Unknown Material');
    }

    /**
     * 获取有效的克重值（覆盖值优先）
     */
    public function getEffectiveGrammage()
    {
        return $this->grammage_override ?: 
               ($this->rawMaterial ? $this->rawMaterial->std_grammage_gsm : 0);
    }

    /**
     * 获取有效的厚度值（覆盖值优先）
     */
    public function getEffectiveThickness()
    {
        return $this->thickness_override_mm ?: 
               ($this->rawMaterial ? $this->rawMaterial->thickness_mm : 0);
    }

    /**
     * 获取完整的材料使用描述
     */
    public function getFullDescription()
    {
        $desc = $this->getDisplayName();
        
        $specs = [];
        if ($this->length_formula && $this->length_formula != '0') {
            $specs[] = 'L:' . $this->length_formula;
        }
        if ($this->width_formula && $this->width_formula != '0') {
            $specs[] = 'W:' . $this->width_formula;
        }
        if ($this->imposition_quantity > 1) {
            $specs[] = '拼版:' . $this->imposition_quantity;
        }
        
        if (!empty($specs)) {
            $desc .= ' [' . implode(', ', $specs) . ']';
        }
        
        return $desc;
    }
}