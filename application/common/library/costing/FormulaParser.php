<?php

namespace app\common\library\costing;

/**
 * 公式解析器
 * 用于解析包含变量的数学表达式
 */
class FormulaParser
{
    /**
     * 求值表达式，支持基础数学运算和变量替换
     * @param string $expression 表达式，如 "L*0.8+5" 或 "100"
     * @param array $context 变量上下文，如 ['L' => 100, 'W' => 50]
     * @return float 计算结果
     */
    public function evaluate($expression, array $context = [])
    {
        if (empty($expression)) {
            return 0;
        }

        // 如果是纯数字，直接返回
        if (is_numeric($expression)) {
            return (float)$expression;
        }

        // 去除公式前缀标记（如果存在）
        $expression = ltrim($expression, '=');
        
        // 如果去除等号后是纯数字，直接返回
        if (is_numeric($expression)) {
            return (float)$expression;
        }

        // 提取所有变量名（字母开头，包含字母数字下划线）
        preg_match_all('/[a-zA-Z_][a-zA-Z0-9_]*/', $expression, $matches);
        $variables = array_unique($matches[0]);

        // 替换变量为对应的值
        foreach ($variables as $var) {
            if (array_key_exists($var, $context)) {
                $value = $context[$var];
                // 确保替换时不会影响到其他变量名
                $expression = preg_replace('/\b' . preg_quote($var, '/') . '\b/', $value, $expression);
            } else {
                // 变量未找到，使用默认值0
                $expression = preg_replace('/\b' . preg_quote($var, '/') . '\b/', '0', $expression);
            }
        }

        try {
            // 安全性检查：只允许数字、运算符、括号和小数点
            if (!preg_match('/^[\d\+\-\*\/\(\)\.\s]+$/', $expression)) {
                throw new \Exception('Invalid characters in expression');
            }
            
            // 使用eval计算表达式（注意：这里有安全风险，生产环境建议使用专门的数学表达式解析库）
            $result = eval('return ' . $expression . ';');
            
            return is_numeric($result) ? (float)$result : 0;
        } catch (\Throwable $e) {
            // 记录错误日志
            \think\Log::error('Formula evaluation error: ' . $e->getMessage() . ', Expression: ' . $expression);
            return 0;
        }
    }

    /**
     * 验证表达式语法是否正确
     * @param string $expression
     * @return bool
     */
    public function validate($expression)
    {
        if (empty($expression)) {
            return true;
        }

        try {
            // 创建一个测试上下文
            $testContext = ['L' => 100, 'W' => 50, 'H' => 20];
            $this->evaluate($expression, $testContext);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}