<?php

namespace app\admin\validate;

use think\Validate;

class MaterialUsage extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'component_id|部件ID' => 'require|integer|gt:0',
        'raw_material_id|原材料ID' => 'require|integer|gt:0',
        'length_formula|长度公式' => 'length:0,100|checkFormula',
        'width_formula|宽度公式' => 'length:0,100|checkFormula',
        'height_formula|高度公式' => 'length:0,100|checkFormula',
        'panel|拼版数' => 'integer|egt:1',
        'gsm_override|克重覆盖' => 'float|egt:0',
        'thickness_override|厚度覆盖' => 'float|egt:0',
        'density_override|密度覆盖' => 'float|egt:0',
        'sequence|排序' => 'require|integer|egt:0'
    ];

    /**
     * 提示消息
     */
    protected $message = [
        'component_id.require' => '必须选择部件',
        'component_id.integer' => '部件ID必须是整数',
        'component_id.gt' => '部件ID必须大于0',
        'raw_material_id.require' => '必须选择原材料',
        'raw_material_id.integer' => '原材料ID必须是整数',
        'raw_material_id.gt' => '原材料ID必须大于0',
        'length_formula.length' => '长度公式不能超过100个字符',
        'width_formula.length' => '宽度公式不能超过100个字符',
        'height_formula.length' => '高度公式不能超过100个字符',
        'panel.integer' => '拼版数必须是整数',
        'panel.egt' => '拼版数不能小于1',
        'gsm_override.float' => '克重覆盖必须是数字',
        'gsm_override.egt' => '克重覆盖不能小于0',
        'thickness_override.float' => '厚度覆盖必须是数字',
        'thickness_override.egt' => '厚度覆盖不能小于0',
        'density_override.float' => '密度覆盖必须是数字',
        'density_override.egt' => '密度覆盖不能小于0',
        'sequence.require' => '排序不能为空',
        'sequence.integer' => '排序必须是整数',
        'sequence.egt' => '排序不能小于0'
    ];

    /**
     * 验证场景
     */
    protected $scene = [
        'add' => ['component_id', 'raw_material_id', 'length_formula', 'width_formula', 'height_formula', 'panel', 'gsm_override', 'thickness_override', 'density_override', 'sequence'],
        'edit' => ['component_id', 'raw_material_id', 'length_formula', 'width_formula', 'height_formula', 'panel', 'gsm_override', 'thickness_override', 'density_override', 'sequence']
    ];

    /**
     * 自定义验证规则：验证公式格式
     */
    protected function checkFormula($value, $rule, $data)
    {
        if (empty($value)) {
            return true;
        }

        // 公式可以是纯数字或以=开头的表达式
        if (is_numeric($value)) {
            return true;
        }

        if (strpos($value, '=') === 0) {
            $expression = substr($value, 1);
            // 简单验证表达式只包含数字、字母、运算符和括号
            if (preg_match('/^[0-9a-zA-Z_+\-*\/\(\)\.\s]+$/', $expression)) {
                return true;
            }
        }

        return '公式格式不正确，应为数字或以=开头的表达式';
    }
}