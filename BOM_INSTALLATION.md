# BOM系统安装说明

本文档说明如何在现有的FastAdmin系统中安装和配置BOM（Bill of Materials）成本计算系统。

## 安装步骤

### 1. 创建数据库表结构

首先执行SQL脚本创建BOM系统需要的数据库表：

```bash
mysql -u your_username -p your_database < bom_system_tables.sql
```

### 2. 安装菜单权限

执行菜单安装脚本将BOM系统的菜单项添加到FastAdmin后台：

```bash
mysql -u your_username -p your_database < install_bom_menu.sql
```

### 3. 导入演示数据（可选）

如果需要演示数据，可以执行：

```bash
mysql -u your_username -p your_database < demo_data.sql
```

### 4. 清理缓存

在FastAdmin后台执行缓存清理：
- 登录FastAdmin后台
- 进入"系统管理" -> "系统配置" -> "缓存管理"
- 点击"一键清除缓存"

## 功能模块

BOM系统包含以下6个核心模块：

1. **产品管理** (`bom/product`)
   - 产品基础信息管理
   - BOM树结构管理  
   - 成本计算功能

2. **原材料管理** (`bom/rawmaterial`)
   - 原材料基础数据
   - 支持批量导入/导出
   - 材料属性配置

3. **工艺管理** (`bom/process`)
   - 工艺基础数据
   - 成本类型配置
   - 工艺复制功能

4. **部件管理** (`bom/component`)
   - 产品部件层级管理
   - 支持多层嵌套结构
   - 尺寸公式配置

5. **材料使用** (`bom/materialusage`)
   - 部件与原材料的关联
   - 用量计算公式
   - 拼版配置

6. **工艺分配** (`bom/processassignment`)
   - 工艺与部件/材料的关联
   - 工艺顺序管理
   - 成本覆盖设置

## 系统特性

- **公式解析**: 支持动态公式计算（如：L*0.8+5）
- **多层BOM**: 支持复杂的产品结构层级
- **成本计算**: 自动计算材料成本和工艺成本
- **拼版处理**: 支持印刷拼版的成本分摊
- **权限控制**: 完整的FastAdmin权限管理集成

## 技术架构

### 后端结构
```
application/
├── admin/
│   ├── controller/bom/     # BOM控制器
│   ├── model/              # BOM模型
│   └── view/bom/           # BOM视图模板
└── common/
    └── library/costing/    # 成本计算库
```

### 前端结构
```
public/assets/js/backend/bom/  # BOM前端JS文件
```

### 数据库表
- `fa_products` - 产品表
- `fa_components` - 部件表  
- `fa_raw_materials` - 原材料表
- `fa_material_usages` - 材料使用表
- `fa_processes` - 工艺表
- `fa_process_assignments` - 工艺分配表

## 故障排除

### 1. 页面显示404错误
- 检查菜单权限是否正确安装
- 检查URL路由配置
- 清理缓存后重试

### 2. 页面显示500错误  
- 检查数据库表是否创建成功
- 检查模型文件是否存在
- 查看错误日志定位具体问题

### 3. 功能按钮无响应
- 检查JavaScript文件是否加载
- 检查浏览器控制台错误信息
- 确认权限配置是否正确

## 使用说明

1. **创建产品**: 在产品管理中添加新产品
2. **配置原材料**: 在原材料管理中添加所需材料
3. **设置工艺**: 在工艺管理中配置生产工艺
4. **构建BOM**: 通过部件管理创建产品结构
5. **关联材料**: 在材料使用中配置部件用料
6. **分配工艺**: 在工艺分配中设置生产流程
7. **成本计算**: 使用产品的成本计算功能获取报价

## 支持

如有问题，请检查：
1. FastAdmin版本兼容性
2. PHP版本要求 (>=5.6)
3. MySQL版本要求 (>=5.5)
4. 服务器权限配置