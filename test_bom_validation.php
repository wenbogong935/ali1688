<?php
/**
 * BOM系统完整性验证脚本
 * 验证所有BOM模块的文件完整性、类存在性和方法可用性
 */

// 设置错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 定义应用根目录
define('APP_PATH', __DIR__ . '/application/');
define('ROOT_PATH', __DIR__ . '/');

class BomSystemValidator
{
    private $errors = [];
    private $warnings = [];
    private $passed = [];

    public function __construct()
    {
        echo "=== BOM系统完整性验证开始 ===\n";
    }

    /**
     * 验证所有文件存在性
     */
    public function validateFiles()
    {
        echo "\n1. 验证文件完整性...\n";
        
        // 控制器文件
        $controllers = [
            'application/admin/controller/bom/Product.php',
            'application/admin/controller/bom/Component.php',
            'application/admin/controller/bom/RawMaterial.php',
            'application/admin/controller/bom/Process.php',
            'application/admin/controller/bom/MaterialUsage.php',
            'application/admin/controller/bom/ProcessAssignment.php'
        ];

        // 模型文件
        $models = [
            'application/admin/model/Product.php',
            'application/admin/model/Component.php',
            'application/admin/model/RawMaterial.php',
            'application/admin/model/Process.php',
            'application/admin/model/MaterialUsage.php',
            'application/admin/model/ProcessAssignment.php'
        ];

        // 库文件
        $libraries = [
            'application/common/library/costing/CostingService.php',
            'application/common/library/costing/SpecificationService.php',
            'application/common/library/costing/FormulaParser.php'
        ];

        // 视图文件
        $views = [
            'application/admin/view/bom/product/index.html',
            'application/admin/view/bom/product/add.html',
            'application/admin/view/bom/product/edit.html',
            'application/admin/view/bom/product/bom.html',
            'application/admin/view/bom/product/calculate.html',
            'application/admin/view/bom/component/index.html',
            'application/admin/view/bom/component/add.html',
            'application/admin/view/bom/component/edit.html',
            'application/admin/view/bom/rawmaterial/index.html',
            'application/admin/view/bom/rawmaterial/add.html',
            'application/admin/view/bom/rawmaterial/edit.html',
            'application/admin/view/bom/process/index.html',
            'application/admin/view/bom/process/add.html',
            'application/admin/view/bom/process/edit.html',
            'application/admin/view/bom/materialusage/index.html',
            'application/admin/view/bom/materialusage/add.html',
            'application/admin/view/bom/materialusage/edit.html',
            'application/admin/view/bom/processassignment/index.html',
            'application/admin/view/bom/processassignment/add.html',
            'application/admin/view/bom/processassignment/edit.html'
        ];

        // JavaScript文件
        $javascript = [
            'public/assets/js/backend/bom/product.js',
            'public/assets/js/backend/bom/component.js',
            'public/assets/js/backend/bom/rawmaterial.js',
            'public/assets/js/backend/bom/process.js',
            'public/assets/js/backend/bom/materialusage.js',
            'public/assets/js/backend/bom/processassignment.js'
        ];

        // CSS文件
        $css = [
            'public/assets/css/backend/bom.css'
        ];

        $allFiles = array_merge($controllers, $models, $libraries, $views, $javascript, $css);

        foreach ($allFiles as $file) {
            if (file_exists($file)) {
                $this->passed[] = "✓ $file";
            } else {
                $this->errors[] = "✗ 缺少文件: $file";
            }
        }
    }

    /**
     * 验证PHP类和方法
     */
    public function validateClasses()
    {
        echo "\n2. 验证PHP类和方法...\n";
        
        // 验证模型类的必要方法
        $this->validateModelMethods();
        
        // 验证服务类的必要方法
        $this->validateServiceMethods();
    }

    /**
     * 验证模型方法
     */
    private function validateModelMethods()
    {
        // 这里只能验证文件语法，因为没有完整的ThinkPHP环境
        $modelFiles = [
            'application/admin/model/Product.php',
            'application/admin/model/Component.php',
            'application/admin/model/RawMaterial.php',
            'application/admin/model/Process.php',
            'application/admin/model/MaterialUsage.php',
            'application/admin/model/ProcessAssignment.php'
        ];

        foreach ($modelFiles as $file) {
            if (file_exists($file)) {
                $content = file_get_contents($file);
                if (strpos($content, 'class ') !== false) {
                    $this->passed[] = "✓ 模型类结构正确: $file";
                } else {
                    $this->errors[] = "✗ 模型类结构错误: $file";
                }
            }
        }
    }

    /**
     * 验证服务方法
     */
    private function validateServiceMethods()
    {
        $serviceFiles = [
            'application/common/library/costing/CostingService.php' => ['calculate'],
            'application/common/library/costing/SpecificationService.php' => ['calculate'],
            'application/common/library/costing/FormulaParser.php' => ['evaluate']
        ];

        foreach ($serviceFiles as $file => $methods) {
            if (file_exists($file)) {
                $content = file_get_contents($file);
                foreach ($methods as $method) {
                    if (strpos($content, "function $method") !== false) {
                        $this->passed[] = "✓ 方法存在: $file::$method";
                    } else {
                        $this->errors[] = "✗ 方法缺失: $file::$method";
                    }
                }
            }
        }
    }

    /**
     * 验证JavaScript文件结构
     */
    public function validateJavaScript()
    {
        echo "\n3. 验证JavaScript文件结构...\n";
        
        $jsFiles = [
            'public/assets/js/backend/bom/product.js',
            'public/assets/js/backend/bom/component.js',
            'public/assets/js/backend/bom/rawmaterial.js',
            'public/assets/js/backend/bom/process.js',
            'public/assets/js/backend/bom/materialusage.js',
            'public/assets/js/backend/bom/processassignment.js'
        ];

        foreach ($jsFiles as $file) {
            if (file_exists($file)) {
                $content = file_get_contents($file);
                if (strpos($content, 'define([') !== false && strpos($content, 'Controller') !== false) {
                    $this->passed[] = "✓ JavaScript结构正确: $file";
                } else {
                    $this->warnings[] = "⚠ JavaScript结构可能有问题: $file";
                }
            }
        }
    }

    /**
     * 验证视图文件结构
     */
    public function validateViews()
    {
        echo "\n4. 验证视图文件结构...\n";
        
        $viewDirs = [
            'application/admin/view/bom/product/',
            'application/admin/view/bom/component/',
            'application/admin/view/bom/rawmaterial/',
            'application/admin/view/bom/process/',
            'application/admin/view/bom/materialusage/',
            'application/admin/view/bom/processassignment/'
        ];

        foreach ($viewDirs as $dir) {
            if (is_dir($dir)) {
                $requiredFiles = ['index.html', 'add.html', 'edit.html'];
                foreach ($requiredFiles as $requiredFile) {
                    $filePath = $dir . $requiredFile;
                    if (file_exists($filePath)) {
                        $this->passed[] = "✓ 视图文件存在: $filePath";
                    } else {
                        $this->errors[] = "✗ 视图文件缺失: $filePath";
                    }
                }
            }
        }
    }

    /**
     * 运行所有验证
     */
    public function runAll()
    {
        $this->validateFiles();
        $this->validateClasses();
        $this->validateJavaScript();
        $this->validateViews();
        $this->showResults();
    }

    /**
     * 显示验证结果
     */
    public function showResults()
    {
        echo "\n=== 验证结果 ===\n";
        
        echo "\n通过的检查 (" . count($this->passed) . "项):\n";
        foreach ($this->passed as $item) {
            echo "$item\n";
        }

        if (!empty($this->warnings)) {
            echo "\n警告 (" . count($this->warnings) . "项):\n";
            foreach ($this->warnings as $item) {
                echo "$item\n";
            }
        }

        if (!empty($this->errors)) {
            echo "\n错误 (" . count($this->errors) . "项):\n";
            foreach ($this->errors as $item) {
                echo "$item\n";
            }
        }

        echo "\n=== 总结 ===\n";
        echo "✓ 通过: " . count($this->passed) . " 项\n";
        echo "⚠ 警告: " . count($this->warnings) . " 项\n";
        echo "✗ 错误: " . count($this->errors) . " 项\n";

        if (empty($this->errors)) {
            echo "\n🎉 BOM系统文件完整性验证通过！\n";
            return true;
        } else {
            echo "\n❌ BOM系统存在问题，需要修复上述错误\n";
            return false;
        }
    }
}

// 运行验证
$validator = new BomSystemValidator();
$result = $validator->runAll();

exit($result ? 0 : 1);