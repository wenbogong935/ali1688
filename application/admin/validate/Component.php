<?php

namespace app\admin\validate;

use think\Validate;

class Component extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'name|部件名称' => 'require|length:1,100',
        'description|部件描述' => 'length:0,500',
        'product_id|产品ID' => 'require|integer|gt:0',
        'parent_component_id|父部件ID' => 'integer|checkParent',
        'quantity|数量' => 'require|float|gt:0',
        'length_formula|长度公式' => 'length:0,100|checkFormula',
        'width_formula|宽度公式' => 'length:0,100|checkFormula',
        'height_formula|高度公式' => 'length:0,100|checkFormula',
        'sequence|排序' => 'require|integer|egt:0'
    ];

    /**
     * 提示消息
     */
    protected $message = [
        'name.require' => '部件名称不能为空',
        'name.length' => '部件名称长度必须在1-100个字符之间',
        'description.length' => '部件描述不能超过500个字符',
        'product_id.require' => '必须选择所属产品',
        'product_id.integer' => '产品ID必须是整数',
        'product_id.gt' => '产品ID必须大于0',
        'parent_component_id.integer' => '父部件ID必须是整数',
        'quantity.require' => '数量不能为空',
        'quantity.float' => '数量必须是数字',
        'quantity.gt' => '数量必须大于0',
        'sequence.require' => '排序不能为空',
        'sequence.integer' => '排序必须是整数',
        'sequence.egt' => '排序不能小于0'
    ];

    /**
     * 验证场景
     */
    protected $scene = [
        'add' => ['name', 'description', 'product_id', 'parent_component_id', 'quantity', 'length_formula', 'width_formula', 'height_formula', 'sequence'],
        'edit' => ['name', 'description', 'product_id', 'parent_component_id', 'quantity', 'length_formula', 'width_formula', 'height_formula', 'sequence']
    ];

    /**
     * 自定义验证规则：验证父部件不能是自己
     */
    protected function checkParent($value, $rule, $data)
    {
        if (empty($value)) {
            return true;
        }

        if (isset($data['id']) && $value == $data['id']) {
            return '父部件不能是自己';
        }

        return true;
    }

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