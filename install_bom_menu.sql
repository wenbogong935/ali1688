-- BOM系统菜单安装脚本
-- 为已有的FastAdmin系统添加BOM菜单项

-- 插入BOM主菜单
INSERT INTO `fa_auth_rule` (`type`, `pid`, `name`, `title`, `icon`, `condition`, `remark`, `ismenu`, `weigh`, `status`, `createtime`, `updatetime`) VALUES
('file', 0, 'bom', 'BOM管理', 'fa fa-sitemap', '', 'BOM成本计算系统', 1, 100, 'normal', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 获取BOM主菜单ID
SET @bom_menu_id = LAST_INSERT_ID();

-- 插入BOM子菜单
INSERT INTO `fa_auth_rule` (`type`, `pid`, `name`, `title`, `icon`, `condition`, `remark`, `ismenu`, `weigh`, `status`, `createtime`, `updatetime`) VALUES
('file', @bom_menu_id, 'bom/product', '产品管理', 'fa fa-cube', '', '产品和BOM管理', 1, 95, 'normal', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('file', @bom_menu_id, 'bom/rawmaterial', '原材料管理', 'fa fa-cubes', '', '原材料基础数据', 1, 90, 'normal', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('file', @bom_menu_id, 'bom/process', '工艺管理', 'fa fa-cogs', '', '工艺基础数据', 1, 85, 'normal', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('file', @bom_menu_id, 'bom/component', '部件管理', 'fa fa-puzzle-piece', '', '部件管理', 1, 80, 'normal', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('file', @bom_menu_id, 'bom/materialusage', '材料使用', 'fa fa-tags', '', '材料使用管理', 1, 75, 'normal', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('file', @bom_menu_id, 'bom/processassignment', '工艺分配', 'fa fa-tasks', '', '工艺分配管理', 1, 70, 'normal', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 为产品管理添加详细操作权限
INSERT INTO `fa_auth_rule` (`type`, `pid`, `name`, `title`, `icon`, `condition`, `remark`, `ismenu`, `weigh`, `status`, `createtime`, `updatetime`) VALUES
('file', (SELECT id FROM `fa_auth_rule` WHERE name = 'bom/product'), 'bom/product/index', '查看', 'fa fa-circle-o', '', '', 0, 0, 'normal', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('file', (SELECT id FROM `fa_auth_rule` WHERE name = 'bom/product'), 'bom/product/add', '添加', 'fa fa-circle-o', '', '', 0, 0, 'normal', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('file', (SELECT id FROM `fa_auth_rule` WHERE name = 'bom/product'), 'bom/product/edit', '编辑', 'fa fa-circle-o', '', '', 0, 0, 'normal', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('file', (SELECT id FROM `fa_auth_rule` WHERE name = 'bom/product'), 'bom/product/del', '删除', 'fa fa-circle-o', '', '', 0, 0, 'normal', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('file', (SELECT id FROM `fa_auth_rule` WHERE name = 'bom/product'), 'bom/product/multi', '批量更新', 'fa fa-circle-o', '', '', 0, 0, 'normal', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('file', (SELECT id FROM `fa_auth_rule` WHERE name = 'bom/product'), 'bom/product/bom', 'BOM管理', 'fa fa-circle-o', '', '', 0, 0, 'normal', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('file', (SELECT id FROM `fa_auth_rule` WHERE name = 'bom/product'), 'bom/product/calculate', '成本计算', 'fa fa-circle-o', '', '', 0, 0, 'normal', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 为原材料管理添加详细操作权限
INSERT INTO `fa_auth_rule` (`type`, `pid`, `name`, `title`, `icon`, `condition`, `remark`, `ismenu`, `weigh`, `status`, `createtime`, `updatetime`) VALUES
('file', (SELECT id FROM `fa_auth_rule` WHERE name = 'bom/rawmaterial'), 'bom/rawmaterial/index', '查看', 'fa fa-circle-o', '', '', 0, 0, 'normal', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('file', (SELECT id FROM `fa_auth_rule` WHERE name = 'bom/rawmaterial'), 'bom/rawmaterial/add', '添加', 'fa fa-circle-o', '', '', 0, 0, 'normal', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('file', (SELECT id FROM `fa_auth_rule` WHERE name = 'bom/rawmaterial'), 'bom/rawmaterial/edit', '编辑', 'fa fa-circle-o', '', '', 0, 0, 'normal', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('file', (SELECT id FROM `fa_auth_rule` WHERE name = 'bom/rawmaterial'), 'bom/rawmaterial/del', '删除', 'fa fa-circle-o', '', '', 0, 0, 'normal', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('file', (SELECT id FROM `fa_auth_rule` WHERE name = 'bom/rawmaterial'), 'bom/rawmaterial/multi', '批量更新', 'fa fa-circle-o', '', '', 0, 0, 'normal', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('file', (SELECT id FROM `fa_auth_rule` WHERE name = 'bom/rawmaterial'), 'bom/rawmaterial/import', '批量导入', 'fa fa-circle-o', '', '', 0, 0, 'normal', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('file', (SELECT id FROM `fa_auth_rule` WHERE name = 'bom/rawmaterial'), 'bom/rawmaterial/exportTemplate', '导出模板', 'fa fa-circle-o', '', '', 0, 0, 'normal', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 为工艺管理添加详细操作权限
INSERT INTO `fa_auth_rule` (`type`, `pid`, `name`, `title`, `icon`, `condition`, `remark`, `ismenu`, `weigh`, `status`, `createtime`, `updatetime`) VALUES
('file', (SELECT id FROM `fa_auth_rule` WHERE name = 'bom/process'), 'bom/process/index', '查看', 'fa fa-circle-o', '', '', 0, 0, 'normal', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('file', (SELECT id FROM `fa_auth_rule` WHERE name = 'bom/process'), 'bom/process/add', '添加', 'fa fa-circle-o', '', '', 0, 0, 'normal', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('file', (SELECT id FROM `fa_auth_rule` WHERE name = 'bom/process'), 'bom/process/edit', '编辑', 'fa fa-circle-o', '', '', 0, 0, 'normal', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('file', (SELECT id FROM `fa_auth_rule` WHERE name = 'bom/process'), 'bom/process/del', '删除', 'fa fa-circle-o', '', '', 0, 0, 'normal', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('file', (SELECT id FROM `fa_auth_rule` WHERE name = 'bom/process'), 'bom/process/multi', '批量更新', 'fa fa-circle-o', '', '', 0, 0, 'normal', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('file', (SELECT id FROM `fa_auth_rule` WHERE name = 'bom/process'), 'bom/process/copy', '复制工艺', 'fa fa-circle-o', '', '', 0, 0, 'normal', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 为部件管理添加详细操作权限
INSERT INTO `fa_auth_rule` (`type`, `pid`, `name`, `title`, `icon`, `condition`, `remark`, `ismenu`, `weigh`, `status`, `createtime`, `updatetime`) VALUES
('file', (SELECT id FROM `fa_auth_rule` WHERE name = 'bom/component'), 'bom/component/index', '查看', 'fa fa-circle-o', '', '', 0, 0, 'normal', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('file', (SELECT id FROM `fa_auth_rule` WHERE name = 'bom/component'), 'bom/component/add', '添加', 'fa fa-circle-o', '', '', 0, 0, 'normal', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('file', (SELECT id FROM `fa_auth_rule` WHERE name = 'bom/component'), 'bom/component/edit', '编辑', 'fa fa-circle-o', '', '', 0, 0, 'normal', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('file', (SELECT id FROM `fa_auth_rule` WHERE name = 'bom/component'), 'bom/component/del', '删除', 'fa fa-circle-o', '', '', 0, 0, 'normal', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('file', (SELECT id FROM `fa_auth_rule` WHERE name = 'bom/component'), 'bom/component/multi', '批量更新', 'fa fa-circle-o', '', '', 0, 0, 'normal', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('file', (SELECT id FROM `fa_auth_rule` WHERE name = 'bom/component'), 'bom/component/getComponentTree', '查看部件树', 'fa fa-circle-o', '', '', 0, 0, 'normal', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('file', (SELECT id FROM `fa_auth_rule` WHERE name = 'bom/component'), 'bom/component/updateSequence', '更新序号', 'fa fa-circle-o', '', '', 0, 0, 'normal', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 为材料使用添加详细操作权限
INSERT INTO `fa_auth_rule` (`type`, `pid`, `name`, `title`, `icon`, `condition`, `remark`, `ismenu`, `weigh`, `status`, `createtime`, `updatetime`) VALUES
('file', (SELECT id FROM `fa_auth_rule` WHERE name = 'bom/materialusage'), 'bom/materialusage/index', '查看', 'fa fa-circle-o', '', '', 0, 0, 'normal', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('file', (SELECT id FROM `fa_auth_rule` WHERE name = 'bom/materialusage'), 'bom/materialusage/add', '添加', 'fa fa-circle-o', '', '', 0, 0, 'normal', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('file', (SELECT id FROM `fa_auth_rule` WHERE name = 'bom/materialusage'), 'bom/materialusage/edit', '编辑', 'fa fa-circle-o', '', '', 0, 0, 'normal', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('file', (SELECT id FROM `fa_auth_rule` WHERE name = 'bom/materialusage'), 'bom/materialusage/del', '删除', 'fa fa-circle-o', '', '', 0, 0, 'normal', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('file', (SELECT id FROM `fa_auth_rule` WHERE name = 'bom/materialusage'), 'bom/materialusage/multi', '批量更新', 'fa fa-circle-o', '', '', 0, 0, 'normal', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('file', (SELECT id FROM `fa_auth_rule` WHERE name = 'bom/materialusage'), 'bom/materialusage/getByComponent', '按部件获取', 'fa fa-circle-o', '', '', 0, 0, 'normal', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('file', (SELECT id FROM `fa_auth_rule` WHERE name = 'bom/materialusage'), 'bom/materialusage/updateSequence', '更新序号', 'fa fa-circle-o', '', '', 0, 0, 'normal', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 为工艺分配添加详细操作权限
INSERT INTO `fa_auth_rule` (`type`, `pid`, `name`, `title`, `icon`, `condition`, `remark`, `ismenu`, `weigh`, `status`, `createtime`, `updatetime`) VALUES
('file', (SELECT id FROM `fa_auth_rule` WHERE name = 'bom/processassignment'), 'bom/processassignment/index', '查看', 'fa fa-circle-o', '', '', 0, 0, 'normal', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('file', (SELECT id FROM `fa_auth_rule` WHERE name = 'bom/processassignment'), 'bom/processassignment/add', '添加', 'fa fa-circle-o', '', '', 0, 0, 'normal', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('file', (SELECT id FROM `fa_auth_rule` WHERE name = 'bom/processassignment'), 'bom/processassignment/edit', '编辑', 'fa fa-circle-o', '', '', 0, 0, 'normal', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('file', (SELECT id FROM `fa_auth_rule` WHERE name = 'bom/processassignment'), 'bom/processassignment/del', '删除', 'fa fa-circle-o', '', '', 0, 0, 'normal', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('file', (SELECT id FROM `fa_auth_rule` WHERE name = 'bom/processassignment'), 'bom/processassignment/multi', '批量更新', 'fa fa-circle-o', '', '', 0, 0, 'normal', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('file', (SELECT id FROM `fa_auth_rule` WHERE name = 'bom/processassignment'), 'bom/processassignment/copy', '复制工艺分配', 'fa fa-circle-o', '', '', 0, 0, 'normal', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('file', (SELECT id FROM `fa_auth_rule` WHERE name = 'bom/processassignment'), 'bom/processassignment/getByTarget', '按目标获取', 'fa fa-circle-o', '', '', 0, 0, 'normal', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('file', (SELECT id FROM `fa_auth_rule` WHERE name = 'bom/processassignment'), 'bom/processassignment/updateSequence', '更新序号', 'fa fa-circle-o', '', '', 0, 0, 'normal', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());