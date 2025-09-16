<?php

namespace app\admin\validate;

use think\Validate;

class Product extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'name|产品名称' => 'require|length:1,100',
        'description|产品描述' => 'length:0,500',
        'image|产品图片' => 'length:0,255',
        'length_formula|长度公式' => 'length:0,100',
        'width_formula|宽度公式' => 'length:0,100',
        'height_formula|高度公式' => 'length:0,100',
        'status|状态' => 'in:normal,hidden'
    ];

    /**
     * 提示消息
     */
    protected $message = [
        'name.require' => '产品名称不能为空',
        'name.length' => '产品名称长度必须在1-100个字符之间',
        'description.length' => '产品描述不能超过500个字符',
        'image.length' => '图片路径不能超过255个字符',
        'length_formula.length' => '长度公式不能超过100个字符',
        'width_formula.length' => '宽度公式不能超过100个字符',
        'height_formula.length' => '高度公式不能超过100个字符',
        'status.in' => '状态值不正确'
    ];

    /**
     * 验证场景
     */
    protected $scene = [
        'add' => ['name', 'description', 'image', 'length_formula', 'width_formula', 'height_formula', 'status'],
        'edit' => ['name', 'description', 'image', 'length_formula', 'width_formula', 'height_formula', 'status']
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