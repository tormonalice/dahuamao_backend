-- ----------------------------
-- Table structure for vs_drama_block
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__drama_block`  (
    `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT,
    `site_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `type` enum('focus','side') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'focus' COMMENT '类型:focus=焦点图,side=广告图',
    `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '名称',
    `title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '标题',
    `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '图片',
    `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '链接',
    `parsetpl` tinyint(3) UNSIGNED NULL DEFAULT 0 COMMENT '链接类型:0=外部,1=内部',
    `weigh` int(11) NULL DEFAULT 0 COMMENT '权重',
    `createtime` bigint(20) NULL DEFAULT NULL COMMENT '添加时间',
    `updatetime` bigint(20) NULL DEFAULT NULL COMMENT '更新时间',
    `status` enum('normal','hidden') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'normal' COMMENT '状态:normal=显示,hidden=隐藏',
    PRIMARY KEY (`id`) USING BTREE,
    INDEX `name`(`name`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '区块表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for vs_drama_category
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__drama_category`  (
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `site_id` int(11) NOT NULL DEFAULT 0 COMMENT '站点',
    `name` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
    `style` tinyint(1) NOT NULL DEFAULT 0 COMMENT '样式:1=一级分类,2=二级分类,3=三级分类',
    `type` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '类型:video=视频,year=年份,area=地区',
    `image` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '图片',
    `pid` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '父ID',
    `weigh` int(11) NOT NULL DEFAULT 0 COMMENT '权重',
    `description` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '描述',
    `status` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '状态',
    `createtime` int(11) NULL DEFAULT NULL COMMENT '创建时间',
    `updatetime` int(11) NULL DEFAULT NULL COMMENT '更新时间',
    PRIMARY KEY (`id`) USING BTREE,
    INDEX `pid`(`pid`) USING BTREE,
    INDEX `weigh_id`(`weigh`, `id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '商城分类表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for vs_drama_config
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__drama_config`  (
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `site_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `name` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '变量名',
    `group` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '分组',
    `title` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '变量标题',
    `tip` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '变量描述',
    `type` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '类型:string,text,int,bool,array,datetime,date,file',
    `value` longtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '变量值',
    `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '变量字典数据',
    `rule` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '验证规则',
    `extend` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '扩展属性',
    PRIMARY KEY (`id`) USING BTREE,
    INDEX `name`(`name`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '配置' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for vs_drama_cryptocard
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__drama_cryptocard`  (
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `site_id` int(11) NOT NULL DEFAULT 0 COMMENT '站点',
    `type` enum('vip','reseller','usable') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'usable' COMMENT '类型:vip=VIP套餐,reseller=分销商套餐,usable=剧场积分套餐',
    `item_id` int(11) NULL DEFAULT NULL COMMENT '套餐',
    `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '名称',
    `pwd` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '兑换码',
    `usetime` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '有效期',
    `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态:0=待使用,1=已使用',
    `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '备注',
    `usetimestart` bigint(20) NULL DEFAULT NULL COMMENT '使用时间',
    `usetimeend` bigint(20) NULL DEFAULT NULL COMMENT '使用时间',
    `createtime` bigint(20) NULL DEFAULT NULL COMMENT '创建时间',
    `deletetime` bigint(20) NULL DEFAULT NULL COMMENT '删除时间',
    PRIMARY KEY (`id`) USING BTREE,
    UNIQUE INDEX `site_id`(`site_id`, `pwd`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '卡密' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for vs_drama_feedback
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__drama_feedback`  (
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `site_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `user_id` int(11) NOT NULL COMMENT '反馈用户',
    `type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '反馈类型',
    `content` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '反馈内容',
    `images` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '图片',
    `phone` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '联系电话',
    `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '是否处理:0=未处理,1=已处理',
    `remark` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '处理备注',
    `createtime` int(11) NULL DEFAULT NULL COMMENT '创建时间',
    `updatetime` int(11) NULL DEFAULT NULL COMMENT '更新时间',
    `deletetime` int(11) NULL DEFAULT NULL COMMENT '删除时间',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '意见反馈' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for vs_drama_reseller
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__drama_reseller`  (
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `site_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `name` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '分销商',
    `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '图片',
    `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '介绍',
    `price` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '价格',
    `original_price` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '原价',
    `level` tinyint(4) NOT NULL COMMENT '等级',
    `direct` decimal(10, 2) NOT NULL COMMENT '直接分润',
    `indirect` decimal(10, 2) NOT NULL COMMENT '间接分润',
    `expire` int(11) NOT NULL COMMENT '有效期',
    `weigh` int(11) NOT NULL DEFAULT 0 COMMENT '排序',
    `status` enum('normal','hidden') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'normal' COMMENT '状态:normal=显示,hidden=隐藏',
    `updatetime` int(11) NOT NULL COMMENT '更新时间',
    `createtime` int(11) NOT NULL COMMENT '创建时间',
    `deletetime` int(11) NULL DEFAULT NULL COMMENT '删除时间',
    PRIMARY KEY (`id`) USING BTREE,
    UNIQUE INDEX `level`(`site_id`, `status`, `level`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '分销商' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for vs_drama_reseller_bind
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__drama_reseller_bind`  (
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `site_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `user_id` int(11) NOT NULL COMMENT '用户ID',
    `reseller_id` int(11) NOT NULL COMMENT '分销等级ID',
    `level` int(11) NOT NULL COMMENT '分销等级',
    `reseller_json` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '分销参数',
    `expiretime` int(11) NOT NULL DEFAULT 0 COMMENT '过期时间',
    `createtime` int(11) NOT NULL COMMENT '创建时间',
    `updatetime` int(11) NOT NULL COMMENT '更新时间',
    PRIMARY KEY (`id`) USING BTREE,
    UNIQUE INDEX `user_id`(`user_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '用户分销信息' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for vs_drama_reseller_log
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__drama_reseller_log`  (
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `site_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `type` enum('direct','indirect') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '类型:direct=直接佣金,indirect=间接佣金',
    `reseller_user_id` int(11) NOT NULL COMMENT '分销商ID',
    `user_id` int(11) NOT NULL COMMENT '用户ID',
    `pay_money` decimal(10, 2) NOT NULL COMMENT '支付金额',
    `ratio` decimal(10, 2) NOT NULL COMMENT '分润比例',
    `money` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '佣金',
    `memo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '备注',
    `order_type` enum('vip','reseller','usable') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '订单类型:vip=VIP订单,reseller=分销商订单,usable=剧场积分订单',
    `order_id` int(11) NOT NULL COMMENT '订单ID',
    `createtime` int(11) NOT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '分佣记录' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for vs_drama_reseller_order
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__drama_reseller_order`  (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `site_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `reseller_id` int(11) NOT NULL DEFAULT 0 COMMENT '分销商ID',
    `order_sn` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '订单号',
    `user_id` int(11) NULL DEFAULT 0 COMMENT '用户',
    `times` int(11) NULL DEFAULT 0 COMMENT '有效期',
    `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '订单状态:-2=交易关闭,-1=已取消,0=未支付,1=已支付,2=已完成',
    `total_fee` decimal(10, 2) NOT NULL COMMENT '支付金额',
    `pay_fee` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '实际支付金额',
    `transaction_id` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '交易单号',
    `payment_json` varchar(2500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '交易原始数据',
    `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '订单备注',
    `pay_type` enum('wechat','alipay','wallet','score','cryptocard','system') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '支付方式:wechat=微信支付,alipay=支付宝,wallet=钱包支付,score=积分支付,cryptocard=卡密兑换,system=管理员设置',
    `paytime` int(11) NULL DEFAULT NULL COMMENT '支付时间',
    `ext` varchar(2048) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '附加字段',
    `platform` enum('H5','Web','wxOfficialAccount','wxMiniProgram','App') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '平台:H5=H5,wxOfficialAccount=微信公众号,wxMiniProgram=微信小程序,Web=Web,App=APP',
    `createtime` int(11) NULL DEFAULT NULL COMMENT '创建时间',
    `updatetime` int(11) NULL DEFAULT NULL COMMENT '更新时间',
    `deletetime` int(11) NULL DEFAULT NULL COMMENT '删除时间',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for vs_drama_reseller_user
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__drama_reseller_user`  (
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `site_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `user_id` int(10) UNSIGNED NOT NULL COMMENT '用户ID',
    `parent_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '上级用户ID',
    `reseller_user_id` int(10) UNSIGNED NOT NULL COMMENT '分销商ID',
    `type` enum('1','2') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '用户类型:1=直接用户,2=间接用户',
    `createtime` int(11) NOT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '分销用户' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for vs_drama_richtext
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__drama_richtext`  (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `site_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '标题',
    `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '内容',
    `createtime` int(11) NULL DEFAULT NULL COMMENT '创建时间',
    `updatetime` int(11) NULL DEFAULT NULL COMMENT '更新时间',
    `deletetime` int(11) NULL DEFAULT NULL COMMENT '删除时间',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '富文本' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for vs_drama_share
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__drama_share`  (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `site_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `user_id` int(11) NOT NULL COMMENT '用户',
    `share_id` int(11) NOT NULL COMMENT '分享人',
    `type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '识别类型:index=默认分享,add=手动添加',
    `type_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '识别标识',
    `platform` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '识别平台',
    `share_platform` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '分享来源',
    `from` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '分享方式',
    `createtime` int(11) NULL DEFAULT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '用户分享' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for vs_drama_task
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__drama_task`  (
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `site_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '任务标题',
    `desc` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '任务描述',
    `hook` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '事件',
    `type` enum('day','first') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '任务类型:first=首次,day=每天',
    `limit` int(11) NOT NULL DEFAULT 1 COMMENT '限制次数',
    `usable` int(11) NOT NULL COMMENT '奖励次数',
    `status` enum('normal','hidden') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '状态:normal=启用,hidden=隐藏',
    `createtime` int(11) NOT NULL,
    `updatetime` int(11) NOT NULL,
    `deletetime` int(11) NULL DEFAULT NULL,
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '任务' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for vs_drama_usable
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__drama_usable`  (
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `site_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `title` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '标题',
    `image` varchar(512) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '图片',
    `flag` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '标识',
    `desc` varchar(512) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '描述',
    `content` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '权益',
    `usable` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '总积分',
    `original_usable` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '原始积分',
    `give_usable` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '赠送积分',
    `price` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '总价格',
    `give_price` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '赠送金额',
    `first_price` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '首冲价格',
    `original_price` decimal(10, 2) NOT NULL COMMENT '划线价格',
    `status` enum('0','1') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '1' COMMENT '是否启用:0=不启用,1=启用',
    `weigh` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序',
    `createtime` int(11) NOT NULL COMMENT '创建时间',
    `updatetime` int(11) NOT NULL COMMENT '更新时间',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = 'AI次数套餐' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for vs_drama_usable_order
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__drama_usable_order`  (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `site_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `usable_id` int(11) NOT NULL DEFAULT 0 COMMENT '充值套餐',
    `order_sn` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '订单号',
    `user_id` int(11) NULL DEFAULT 0 COMMENT '用户',
    `usable` int(11) NULL DEFAULT 0 COMMENT '充值次数',
    `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '订单状态:-2=交易关闭,-1=已取消,0=未支付,1=已支付,2=已完成',
    `total_fee` decimal(10, 2) NOT NULL COMMENT '支付金额',
    `pay_fee` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '实际支付金额',
    `transaction_id` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '交易单号',
    `payment_json` varchar(2500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '交易原始数据',
    `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '订单备注',
    `pay_type` enum('wechat','alipay','wallet','score','cryptocard','system') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '支付方式:wechat=微信支付,alipay=支付宝,wallet=钱包支付,score=积分支付,cryptocard=卡密兑换,system=管理员设置',
    `paytime` int(11) NULL DEFAULT NULL COMMENT '支付时间',
    `ext` varchar(2048) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '附加字段',
    `platform` enum('H5','Web','wxOfficialAccount','wxMiniProgram','App') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '平台:H5=H5,wxOfficialAccount=微信公众号,wxMiniProgram=微信小程序,Web=Web,App=APP',
    `createtime` int(11) NULL DEFAULT NULL COMMENT '创建时间',
    `updatetime` int(11) NULL DEFAULT NULL COMMENT '更新时间',
    `deletetime` int(11) NULL DEFAULT NULL COMMENT '删除时间',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = 'AI次数充值订单' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for vs_drama_user_bank
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__drama_user_bank`  (
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `site_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `user_id` int(11) NOT NULL COMMENT '用户id',
    `type` enum('bank','alipay','wechat') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '账户类型:bank=银行卡,alipay=支付宝,wechat=微信',
    `real_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '真实姓名',
    `bank_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '银行名',
    `card_no` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '卡号',
    `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '收款码',
    `createtime` int(11) NULL DEFAULT NULL COMMENT '创建时间',
    `updatetime` int(11) NULL DEFAULT NULL COMMENT '更新时间',
    `deletetime` int(11) NULL DEFAULT NULL COMMENT '删除时间',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '提现银行卡' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for vs_drama_user_cryptocard
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__drama_user_cryptocard`  (
     `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
     `user_id` int(11) NULL DEFAULT NULL COMMENT '用户',
     `cryptocard_id` int(11) NULL DEFAULT NULL COMMENT '卡密',
     `type` enum('vip','reseller','usable') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'usable' COMMENT '类型:vip=VIP套餐,reseller=分销商套餐,usable=剧场积分套餐',
     `order_id` int(11) NOT NULL DEFAULT 0 COMMENT '订单 id',
     `createtime` bigint(20) NULL DEFAULT NULL COMMENT '使用时间',
     PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '用户卡密记录' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for vs_drama_user_oauth
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__drama_user_oauth`  (
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `site_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `user_id` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '用户',
    `provider` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '厂商',
    `platform` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '平台',
    `unionid` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '厂商ID',
    `openid` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '平台ID',
    `nickname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '昵称',
    `sex` tinyint(1) NULL DEFAULT 0 COMMENT '性别',
    `country` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '国家',
    `province` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '省',
    `city` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '市',
    `headimgurl` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '头像',
    `logintime` int(11) NULL DEFAULT NULL COMMENT '登录时间',
    `logincount` int(11) NULL DEFAULT 0 COMMENT '累计登陆',
    `expire_in` int(11) NULL DEFAULT NULL COMMENT '过期周期(s)',
    `expiretime` int(11) NULL DEFAULT NULL COMMENT '过期时间',
    `session_key` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT 'session_key',
    `refresh_token` varchar(110) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'refresh_token',
    `access_token` varchar(110) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'access_token',
    `createtime` int(11) NULL DEFAULT 0 COMMENT '创建时间',
    `updatetime` int(11) NULL DEFAULT 0 COMMENT '更新时间',
    PRIMARY KEY (`id`) USING BTREE,
    INDEX `openid`(`openid`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '第三方授权' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for vs_drama_user_wallet_apply
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__drama_user_wallet_apply`  (
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `site_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `user_id` int(11) NOT NULL COMMENT '提现用户',
    `apply_sn` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '提现单号',
    `apply_type` enum('bank','wechat','alipay') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '收款类型:bank=银行卡,wechat=微信零钱,alipay=支付宝',
    `money` decimal(10, 2) NOT NULL COMMENT '提现金额',
    `actual_money` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '实际到账',
    `charge_money` decimal(10, 2) NOT NULL COMMENT '手续费',
    `service_fee` decimal(10, 3) NULL DEFAULT NULL COMMENT '手续费率',
    `apply_info` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '打款信息',
    `status` tinyint(1) NULL DEFAULT 0 COMMENT '提现状态:-1=已拒绝,0=待审核,1=处理中,2=已处理',
    `platform` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '平台',
    `payment_json` varchar(2500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '交易原始数据',
    `createtime` int(11) NULL DEFAULT NULL COMMENT '申请时间',
    `updatetime` int(11) NULL DEFAULT NULL COMMENT '操作时间',
    `log` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '操作日志',
    PRIMARY KEY (`id`) USING BTREE,
    UNIQUE INDEX `apply_sn`(`apply_sn`) USING BTREE COMMENT '提现单号'
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '用户提现' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for vs_drama_user_wallet_log
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__drama_user_wallet_log`  (
    `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '日志 id',
    `site_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `user_id` int(11) NOT NULL DEFAULT 0 COMMENT '用户',
    `wallet` decimal(10, 2) NOT NULL COMMENT '变动金额',
    `wallet_type` enum('money','score','usable') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '日志类型:money=余额,score=积分,usable=AI次数',
    `type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '变动类型',
    `before` decimal(10, 2) NOT NULL COMMENT '变动前',
    `after` decimal(10, 2) NOT NULL COMMENT '变动后',
    `item_id` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '项目 id',
    `memo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '备注',
    `ext` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '附加字段',
    `oper_type` enum('user','admin','system') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'user' COMMENT '操作人类型',
    `oper_id` int(11) NOT NULL DEFAULT 0 COMMENT '操作人',
    `createtime` int(11) NULL DEFAULT NULL COMMENT '创建时间',
    `updatetime` int(11) NULL DEFAULT NULL COMMENT '更新时间',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '钱包日志' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for vs_drama_video
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__drama_video`  (
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `site_id` int(11) NOT NULL DEFAULT 0,
    `category_ids` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0' COMMENT '分类',
    `area_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '地区',
    `year_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '年份',
    `title` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '标题',
    `subtitle` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '副标题',
    `image` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '封面',
    `flags` set('hot','recommend') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '标志:hot=热门,recommend=推荐',
    `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '简介',
    `tags` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '标签',
    `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '图文详情',
    `price` int(11) NOT NULL COMMENT '价格',
    `vprice` int(11) NOT NULL COMMENT 'VIP价格',
    `episodes` int(11) NOT NULL COMMENT '总集数',
    `score` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '评分',
    `sales` int(11) NOT NULL DEFAULT 0 COMMENT '销量',
    `favorites` int(11) NOT NULL DEFAULT 0 COMMENT '收藏量',
    `views` int(11) NOT NULL DEFAULT 0 COMMENT '播放量',
    `shares` int(11) NOT NULL DEFAULT 0 COMMENT '转发量',
    `likes` int(11) NOT NULL DEFAULT 0 COMMENT '点赞量',
    `fake_views` int(11) NOT NULL DEFAULT 0 COMMENT '虚拟播放量',
    `fake_favorites` int(11) NOT NULL DEFAULT 0 COMMENT '虚拟收藏量',
    `fake_shares` int(11) NOT NULL DEFAULT 0 COMMENT '虚拟转发量',
    `fake_likes` int(11) NOT NULL DEFAULT 0 COMMENT '虚拟点赞量',
    `weigh` int(11) NOT NULL DEFAULT 0 COMMENT '排序',
    `status` enum('up','down') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'up' COMMENT '商品状态:up=上架,down=下架',
    `createtime` int(11) NOT NULL COMMENT '添加时间',
    `updatetime` int(11) NOT NULL COMMENT '更新时间',
    `deletetime` int(11) NULL DEFAULT NULL COMMENT '删除时间',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '短剧' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for vs_drama_video_episodes
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__drama_video_episodes`  (
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `site_id` int(11) NOT NULL DEFAULT 0,
    `vid` int(11) NOT NULL DEFAULT 0 COMMENT '短剧',
    `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '名称',
    `image` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '封面',
    `video` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '视频',
    `duration` int(11) NOT NULL COMMENT '时长',
    `price` int(11) NOT NULL COMMENT '价格',
    `vprice` int(11) NOT NULL COMMENT 'VIP价格',
    `sales` int(11) NOT NULL DEFAULT 0 COMMENT '销量',
    `likes` int(11) NOT NULL DEFAULT 0 COMMENT '点赞量',
    `views` int(11) NOT NULL DEFAULT 0 COMMENT '播放量',
    `favorites` int(11) NOT NULL DEFAULT 0 COMMENT '收藏量',
    `shares` int(11) NOT NULL DEFAULT 0 COMMENT '转发量',
    `fake_likes` int(11) NOT NULL DEFAULT 0 COMMENT '虚拟点赞量',
    `fake_views` int(11) NOT NULL DEFAULT 0 COMMENT '虚拟播放量',
    `fake_favorites` int(11) NOT NULL DEFAULT 0 COMMENT '虚拟收藏量',
    `fake_shares` int(11) NOT NULL DEFAULT 0 COMMENT '虚拟转发量',
    `weigh` int(11) NOT NULL DEFAULT 0 COMMENT '排序',
    `status` enum('normal','hidden') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'normal' COMMENT '商品状态:normal=显示,hidden=隐藏',
    `updatetime` int(11) NOT NULL COMMENT '更新时间',
    `createtime` int(11) NOT NULL COMMENT '添加时间',
    `deletetime` int(11) NULL DEFAULT NULL COMMENT '删除时间',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '剧集' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for vs_drama_video_favorite
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__drama_video_favorite`  (
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `site_id` int(11) NOT NULL DEFAULT 0,
    `type` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '类型:like=点赞,favorite=收藏',
    `user_id` int(11) NOT NULL DEFAULT 0 COMMENT '用户',
    `vid` int(11) NOT NULL DEFAULT 0 COMMENT '短剧',
    `episode_id` int(11) NOT NULL DEFAULT 0 COMMENT '剧集',
    `createtime` int(11) NOT NULL COMMENT '添加时间',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '收藏点赞' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for vs_drama_video_images
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__drama_video_images`  (
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `site_id` int(11) NOT NULL DEFAULT 0,
    `vid` int(11) NOT NULL COMMENT '短剧',
    `name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '壁纸名称',
    `image` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '壁纸图片',
    `views` int(11) NOT NULL DEFAULT 0 COMMENT '浏览量',
    `downloads` int(11) NOT NULL DEFAULT 0 COMMENT '下载量',
    `createtime` int(11) NOT NULL COMMENT '添加时间',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '剧情壁纸' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for vs_drama_video_log
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__drama_video_log`  (
        `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `site_id` int(11) NOT NULL DEFAULT 0,
        `type` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '类型:log=记录,favorite=追剧',
        `user_id` int(11) NOT NULL DEFAULT 0 COMMENT '用户',
        `vid` int(11) NOT NULL DEFAULT 0 COMMENT '短剧',
        `episode_id` int(11) NOT NULL DEFAULT 0 COMMENT '剧集',
        `view_time` int(11) NOT NULL DEFAULT 0 COMMENT '观看时间',
        `createtime` int(11) NOT NULL COMMENT '添加时间',
        `updatetime` int(11) NOT NULL COMMENT '更新时间',
        PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '追剧记录' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for vs_drama_video_order
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__drama_video_order`  (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `site_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `vid` int(11) NOT NULL DEFAULT 0 COMMENT '短剧',
    `episode_id` int(11) NOT NULL DEFAULT 0 COMMENT '剧集',
    `order_sn` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '订单号',
    `user_id` int(11) NULL DEFAULT 0 COMMENT '用户',
    `total_fee` int(10) NOT NULL DEFAULT 0 COMMENT '支付积分',
    `platform` enum('H5','Web','wxOfficialAccount','wxMiniProgram','App') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '平台:H5=H5,wxOfficialAccount=微信公众号,wxMiniProgram=微信小程序,Web=Web,App=APP',
    `createtime` int(11) NOT NULL DEFAULT 0 COMMENT '创建时间',
    `updatetime` int(11) NOT NULL DEFAULT 0 COMMENT '更新时间',
    `deletetime` int(11) NULL DEFAULT NULL COMMENT '删除时间',
    PRIMARY KEY (`id`) USING BTREE,
    INDEX `vid`(`user_id`, `vid`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for vs_drama_video_performer
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__drama_video_performer`  (
    `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `site_id` int(11) NOT NULL DEFAULT 0,
    `vid` int(11) NOT NULL DEFAULT 0 COMMENT '短剧',
    `type` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '类型:director=导演,performer=演员',
    `name` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '姓名',
    `en_name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '英文名',
    `avatar` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '头像',
    `tags` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '标签',
    `play` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '饰演',
    `profile` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '简介',
    `weigh` int(11) NOT NULL DEFAULT 0 COMMENT '排序',
    `createtime` bigint(20) NOT NULL COMMENT '创建时间',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '演员表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for vs_drama_vip
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__drama_vip`  (
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `site_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `type` enum('d','m','q','y') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '充值类型:d=天,m=月,q=季,y=年',
    `title` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '标题',
    `image` varchar(512) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '标识',
    `desc` varchar(512) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '描述',
    `content` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '权益',
    `price` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '价格',
    `first_price` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '首冲价格',
    `original_price` decimal(10, 2) NOT NULL COMMENT '划线价格',
    `num` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '充值数量',
    `status` enum('0','1') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '1' COMMENT '是否启用:0=不启用,1=启用',
    `weigh` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序',
    `createtime` int(11) NOT NULL COMMENT '创建时间',
    `updatetime` int(11) NOT NULL COMMENT '更新时间',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '用户充值会员价格' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for vs_drama_vip_order
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__drama_vip_order`  (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `site_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
    `vip_id` int(11) NOT NULL DEFAULT 0 COMMENT 'VIP ID',
    `order_sn` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '订单号',
    `user_id` int(11) NULL DEFAULT 0 COMMENT '用户',
    `times` int(11) NULL DEFAULT 0 COMMENT 'VIP时长',
    `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '订单状态:-2=交易关闭,-1=已取消,0=未支付,1=已支付,2=已完成',
    `total_fee` decimal(10, 2) NOT NULL COMMENT '支付金额',
    `pay_fee` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '实际支付金额',
    `transaction_id` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '交易单号',
    `payment_json` varchar(2500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '交易原始数据',
    `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '订单备注',
    `pay_type` enum('wechat','alipay','wallet','score','cryptocard','system') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '支付方式:wechat=微信支付,alipay=支付宝,wallet=钱包支付,score=积分支付,cryptocard=卡密兑换,system=管理员设置',
    `paytime` int(11) NULL DEFAULT NULL COMMENT '支付时间',
    `ext` varchar(2048) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '附加字段',
    `platform` enum('H5','Web','wxOfficialAccount','wxMiniProgram','App') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '平台:H5=H5,wxOfficialAccount=微信公众号,wxMiniProgram=微信小程序,Web=Web,App=APP',
    `createtime` int(11) NULL DEFAULT NULL COMMENT '创建时间',
    `updatetime` int(11) NULL DEFAULT NULL COMMENT '更新时间',
    `deletetime` int(11) NULL DEFAULT NULL COMMENT '删除时间',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for vs_drama_wechat
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__drama_wechat`  (
    `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `site_id` int(11) NOT NULL DEFAULT 0 COMMENT '站点',
    `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '配置类型',
    `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '名称',
    `rules` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '规则',
    `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '内容',
    `createtime` int(11) NOT NULL COMMENT '创建时间',
    `updatetime` int(11) NOT NULL COMMENT '更新时间',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '微信管理' ROW_FORMAT = Dynamic;

UPDATE `__PREFIX__auth_rule` SET `remark` = '您现在是在超管后台，您需要退出当前用户，使用站点的账号密码进行登录则可管理您的网站信息。\r\n<br />默认站点是指您的主站，域名URL里不会带后置参数。' WHERE `name` = 'sites';

UPDATE `__PREFIX__auth_rule` SET `remark` = '编辑富文本信息用于前端页面显示。\r\n<br />请在：剧场管理》系统配置》剧场配置》系统信息 里面绑定富文本信息。' WHERE `name` = 'drama/richtext';


-- 1.1.7 标准版更新 ↓

CREATE TABLE IF NOT EXISTS `__PREFIX__drama_version`  (
    `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `site_id` int(11) NOT NULL DEFAULT 0,
    `oldversion` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '旧版本号',
    `newversion` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '新版本号',
    `packagesize` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '包大小',
    `content` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '升级内容',
    `downloadurl` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '下载地址',
    `enforce` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '强制更新',
    `createtime` bigint(20) NULL DEFAULT NULL COMMENT '创建时间',
    `updatetime` bigint(20) NULL DEFAULT NULL COMMENT '更新时间',
    `weigh` int(11) NOT NULL DEFAULT 0 COMMENT '权重',
    `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '状态:normal=正常,hidden=隐藏',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '版本表' ROW_FORMAT = Dynamic;

ALTER TABLE `__PREFIX__drama_vip_order` MODIFY COLUMN `platform` enum('H5','Web','wxOfficialAccount','wxMiniProgram','App') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '平台:H5=H5,wxOfficialAccount=微信公众号,wxMiniProgram=微信小程序,Web=Web,App=APP' AFTER `ext`;
ALTER TABLE `__PREFIX__drama_usable_order` MODIFY COLUMN `platform` enum('H5','Web','wxOfficialAccount','wxMiniProgram','App') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '平台:H5=H5,wxOfficialAccount=微信公众号,wxMiniProgram=微信小程序,Web=Web,App=APP' AFTER `ext`;
ALTER TABLE `__PREFIX__drama_reseller_order` MODIFY COLUMN `platform` enum('H5','Web','wxOfficialAccount','wxMiniProgram','App') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '平台:H5=H5,wxOfficialAccount=微信公众号,wxMiniProgram=微信小程序,Web=Web,App=APP' AFTER `ext`;
ALTER TABLE `__PREFIX__drama_video_order` MODIFY COLUMN `platform` enum('H5','Web','wxOfficialAccount','wxMiniProgram','App') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '平台:H5=H5,wxOfficialAccount=微信公众号,wxMiniProgram=微信小程序,Web=Web,App=APP' AFTER `total_fee`;

-- 1.1.7 标准版更新 ↑


-- 1.2.8

ALTER TABLE `__PREFIX__drama_vip` ADD COLUMN product_id varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT '虚拟支付道具ID';
ALTER TABLE `__PREFIX__drama_vip_order` ADD COLUMN product_id varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT '虚拟支付道具ID';
ALTER TABLE `__PREFIX__drama_usable` ADD COLUMN product_id varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT '虚拟支付道具ID';
ALTER TABLE `__PREFIX__drama_usable_order` ADD COLUMN product_id varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT '虚拟支付道具ID';
ALTER TABLE `__PREFIX__drama_reseller` ADD COLUMN product_id varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT '虚拟支付道具ID';
ALTER TABLE `__PREFIX__drama_reseller_order` ADD COLUMN product_id varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT '虚拟支付道具ID';

-- 1.3.0

CREATE TABLE IF NOT EXISTS `__PREFIX__jobs`  (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `queue` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
                            `payload` longtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
                            `attempts` tinyint(3) UNSIGNED NOT NULL,
                            `reserved` tinyint(3) UNSIGNED NOT NULL,
                            `reserved_at` int(10) UNSIGNED NULL DEFAULT NULL,
                            `available_at` int(10) UNSIGNED NOT NULL,
                            `created_at` int(10) UNSIGNED NOT NULL,
                            PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 7 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;


ALTER TABLE `__PREFIX__drama_video` ADD COLUMN platform tinyint(1) NOT NULL DEFAULT 1 COMMENT '平台:1=普通,2=微信小程序,3=抖音小程序' AFTER `status`;
ALTER TABLE `__PREFIX__drama_video` ADD COLUMN xcx_sync tinyint(1) NOT NULL DEFAULT 0 COMMENT '小程序媒资同步状态:0=未同步,1=已同步' AFTER `status`;
ALTER TABLE `__PREFIX__drama_video` ADD COLUMN xcx_update_time int(11) NULL DEFAULT NULL COMMENT '小程序媒资更新时间' AFTER `status`;
ALTER TABLE `__PREFIX__drama_video` ADD COLUMN xcx_drama_id int(11) NULL DEFAULT NULL COMMENT '小程序媒资剧目ID' AFTER `status`;

ALTER TABLE `__PREFIX__drama_video_episodes` ADD COLUMN xcx_media_id int(11) NULL DEFAULT NULL COMMENT '小程序媒资视频ID' AFTER `status`;

INSERT INTO `__PREFIX__auth_rule` VALUES (1660, 'file', 289, 'drama/xcx_meizi', '微信媒资管理', 'fa fa-shopping-bag', '', '', '', 1, 'addtabs', '', 'xcxmzgl', 'xiaochengxumeiziguanli', 1698463999, 1698463999, 0, 'normal');
INSERT INTO `__PREFIX__auth_rule` VALUES (1661, 'file', 1660, 'drama/xcx_meizi/index', '查看', 'fa fa-circle-o', '', '', '', 0, 'addtabs', '', 'zk', 'zhakan', 1698464074, 1698464074, 0, 'normal');
INSERT INTO `__PREFIX__auth_rule` VALUES (1662, 'file', 1660, 'drama/xcx_meizi/recyclebin', '回收站', 'fa fa-circle-o', '', '', '', 0, 'addtabs', '', 'hsz', 'huishouzhan', 1698464139, 1698464139, 0, 'normal');
INSERT INTO `__PREFIX__auth_rule` VALUES (1663, 'file', 1660, 'drama/xcx_meizi/add', '添加', 'fa fa-circle-o', '', '', '', 0, 'addtabs', '', 'tj', 'tianjia', 1698464169, 1698464169, 0, 'normal');
INSERT INTO `__PREFIX__auth_rule` VALUES (1664, 'file', 1660, 'drama/xcx_meizi/edit', '编辑', 'fa fa-circle-o', '', '', '', 0, 'addtabs', '', 'bj', 'bianji', 1698464186, 1698464186, 0, 'normal');
INSERT INTO `__PREFIX__auth_rule` VALUES (1665, 'file', 1660, 'drama/xcx_meizi/del', '删除', 'fa fa-circle-o', '', '', '', 0, 'addtabs', '', 'sc', 'shanchu', 1698464202, 1698464202, 0, 'normal');
INSERT INTO `__PREFIX__auth_rule` VALUES (1666, 'file', 1660, 'drama/xcx_meizi/destroy', '真实删除', 'fa fa-circle-o', '', '', '', 0, 'addtabs', '', 'zssc', 'zhenshishanchu', 1698464231, 1698464231, 0, 'normal');
INSERT INTO `__PREFIX__auth_rule` VALUES (1667, 'file', 1660, 'drama/xcx_meizi/restore', '还原', 'fa fa-circle-o', '', '', '', 0, 'addtabs', '', 'hy', 'huanyuan', 1698464249, 1698464249, 0, 'normal');
INSERT INTO `__PREFIX__auth_rule` VALUES (1668, 'file', 1660, 'drama/xcx_meizi/multi', '批量更新', 'fa fa-circle-o', '', '', '', 0, 'addtabs', '', 'plgx', 'pilianggengxin', 1698464265, 1698464265, 0, 'normal');
INSERT INTO `__PREFIX__auth_rule` VALUES (1669, 'file', 1660, 'drama/xcx_meizi/detail', '查看详情', 'fa fa-circle-o', '', '', '', 0, 'addtabs', '', 'zkxq', 'zhakanxiangqing', 1698464281, 1698464281, 0, 'normal');
INSERT INTO `__PREFIX__auth_rule` VALUES (1670, 'file', 1660, 'drama/xcx_meizi/setstatus', '商品状态', 'fa fa-circle-o', '', '', '', 0, 'addtabs', '', 'spzt', 'shangpinzhuangtai', 1698464301, 1698464301, 0, 'normal');

-- 1.5.0


INSERT INTO `__PREFIX__auth_rule` VALUES (1677, 'file', 289, 'drama/dy_meizi', '抖音媒资管理', 'fa fa-shopping-bag', '', '', '', 1, 'addtabs', '', 'dymzgl', 'douyinmeiziguanli', 1698463999, 1698463999, 0, 'normal');
INSERT INTO `__PREFIX__auth_rule` VALUES (1678, 'file', 1677, 'drama/dy_meizi/index', '查看', 'fa fa-circle-o', '', '', '', 0, 'addtabs', '', 'zk', 'zhakan', 1698464074, 1698464074, 0, 'normal');
INSERT INTO `__PREFIX__auth_rule` VALUES (1679, 'file', 1677, 'drama/dy_meizi/recyclebin', '回收站', 'fa fa-circle-o', '', '', '', 0, 'addtabs', '', 'hsz', 'huishouzhan', 1698464139, 1698464139, 0, 'normal');
INSERT INTO `__PREFIX__auth_rule` VALUES (1680, 'file', 1677, 'drama/dy_meizi/add', '添加', 'fa fa-circle-o', '', '', '', 0, 'addtabs', '', 'tj', 'tianjia', 1698464169, 1698464169, 0, 'normal');
INSERT INTO `__PREFIX__auth_rule` VALUES (1681, 'file', 1677, 'drama/dy_meizi/edit', '编辑', 'fa fa-circle-o', '', '', '', 0, 'addtabs', '', 'bj', 'bianji', 1698464186, 1698464186, 0, 'normal');
INSERT INTO `__PREFIX__auth_rule` VALUES (1682, 'file', 1677, 'drama/dy_meizi/del', '删除', 'fa fa-circle-o', '', '', '', 0, 'addtabs', '', 'sc', 'shanchu', 1698464202, 1698464202, 0, 'normal');
INSERT INTO `__PREFIX__auth_rule` VALUES (1683, 'file', 1677, 'drama/dy_meizi/destroy', '真实删除', 'fa fa-circle-o', '', '', '', 0, 'addtabs', '', 'zssc', 'zhenshishanchu', 1698464231, 1698464231, 0, 'normal');
INSERT INTO `__PREFIX__auth_rule` VALUES (1684, 'file', 1677, 'drama/dy_meizi/restore', '还原', 'fa fa-circle-o', '', '', '', 0, 'addtabs', '', 'hy', 'huanyuan', 1698464249, 1698464249, 0, 'normal');
INSERT INTO `__PREFIX__auth_rule` VALUES (1685, 'file', 1677, 'drama/dy_meizi/multi', '批量更新', 'fa fa-circle-o', '', '', '', 0, 'addtabs', '', 'plgx', 'pilianggengxin', 1698464265, 1698464265, 0, 'normal');
INSERT INTO `__PREFIX__auth_rule` VALUES (1686, 'file', 1677, 'drama/dy_meizi/detail', '查看详情', 'fa fa-circle-o', '', '', '', 0, 'addtabs', '', 'zkxq', 'zhakanxiangqing', 1698464281, 1698464281, 0, 'normal');
INSERT INTO `__PREFIX__auth_rule` VALUES (1687, 'file', 1677, 'drama/dy_meizi/setstatus', '商品状态', 'fa fa-circle-o', '', '', '', 0, 'addtabs', '', 'spzt', 'shangpinzhuangtai', 1698464301, 1698464301, 0, 'normal');

INSERT INTO `__PREFIX__auth_rule` VALUES (1688, 'file', 184, 'drama/dashboard', '数据中心', 'fa fa-dashboard', '', '', '', 1, 'addtabs', '', 'sjzx', 'shujuzhongxin', 1698464301, 1698464301, 0, 'normal');
INSERT INTO `__PREFIX__auth_rule` VALUES (1689, 'file', 1688, 'drama/dashboard/index', '查看', 'fa fa-circle-o', '', '', '', 0, 'addtabs', '', 'zk', 'zhakan', 1698464074, 1698464074, 0, 'normal');


UPDATE `__PREFIX__auth_group` SET `rules` = '7,23,24,25,26,27,28,29,30,32,103,104,105,106,107,108,109,110,111,112,113,114,115,116,117,118,119,120,121,122,123,124,125,126,127,128,129,130,131,132,133,134,135,136,137,138,139,140,141,142,143,144,145,146,147,148,149,150,151,152,153,154,155,156,157,158,159,160,161,162,163,164,165,166,167,168,169,170,171,172,173,174,175,176,177,178,179,180,181,182,183,184,185,186,187,188,189,190,191,192,193,194,195,196,197,198,199,200,201,202,203,204,205,206,207,208,209,210,211,212,213,214,215,216,217,218,219,220,221,222,223,224,225,226,227,228,229,230,231,232,233,234,235,236,237,238,239,240,241,242,243,244,245,246,247,248,249,250,251,252,253,254,255,256,257,258,259,260,261,262,264,265,266,267,268,269,270,271,272,273,274,275,276,277,278,279,280,281,282,283,284,285,287,288,289,290,291,292,293,294,295,1660,1661,1662,1663,1664,1665,1666,1667,1668,1669,1670,2,8,1677,1678,1679,1680,1681,1682,1683,1684,1685,1686,1687,1688,1689,1690,1691,1692,1693,1694,1695,1696,1697,1698,1699,1700,1701,1702,1703,1704,1705,1706,1707,1708,1709,1710,1711,1712,1713,1714,1715,1716,1717,1718,1719,1720,1721,1722,1723,1724,1725,1726,1727,1734,1735,1736,1737,1738,1739,1740,1741,1742,1743,1744,1745,1746,1747,1748,1749,1750,1751,1752,1753,1754' WHERE `id` = 2;


ALTER TABLE `__PREFIX__drama_vip_order` MODIFY COLUMN `platform` enum('H5','Web','wxOfficialAccount','wxMiniProgram','App','douyinxcx') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '平台:H5=H5,wxOfficialAccount=微信公众号,wxMiniProgram=微信小程序,Web=Web,App=APP,douyinxcx=抖音小程序' AFTER `ext`;
ALTER TABLE `__PREFIX__drama_usable_order` MODIFY COLUMN `platform` enum('H5','Web','wxOfficialAccount','wxMiniProgram','App','douyinxcx') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '平台:H5=H5,wxOfficialAccount=微信公众号,wxMiniProgram=微信小程序,Web=Web,App=APP,douyinxcx=抖音小程序' AFTER `ext`;
ALTER TABLE `__PREFIX__drama_reseller_order` MODIFY COLUMN `platform` enum('H5','Web','wxOfficialAccount','wxMiniProgram','App','douyinxcx') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '平台:H5=H5,wxOfficialAccount=微信公众号,wxMiniProgram=微信小程序,Web=Web,App=APP,douyinxcx=抖音小程序' AFTER `ext`;
ALTER TABLE `__PREFIX__drama_video_order` MODIFY COLUMN `platform` enum('H5','Web','wxOfficialAccount','wxMiniProgram','App','douyinxcx') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '平台:H5=H5,wxOfficialAccount=微信公众号,wxMiniProgram=微信小程序,Web=Web,App=APP,douyinxcx=抖音小程序' AFTER `total_fee`;

ALTER TABLE `__PREFIX__drama_vip_order` MODIFY COLUMN `pay_type` enum('wechat','alipay','wallet','score','cryptocard','system','douyinpay') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '支付方式:wechat=微信支付,alipay=支付宝,wallet=钱包支付,score=积分支付,cryptocard=卡密兑换,system=管理员设置,douyinpay=抖音支付' AFTER `ext`;
ALTER TABLE `__PREFIX__drama_usable_order` MODIFY COLUMN `pay_type` enum('wechat','alipay','wallet','score','cryptocard','system','douyinpay') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '支付方式:wechat=微信支付,alipay=支付宝,wallet=钱包支付,score=积分支付,cryptocard=卡密兑换,system=管理员设置,douyinpay=抖音支付' AFTER `ext`;
ALTER TABLE `__PREFIX__drama_reseller_order` MODIFY COLUMN `pay_type` enum('wechat','alipay','wallet','score','cryptocard','system','douyinpay') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '支付方式:wechat=微信支付,alipay=支付宝,wallet=钱包支付,score=积分支付,cryptocard=卡密兑换,system=管理员设置,douyinpay=抖音支付' AFTER `ext`;

-- 1.6.0

CREATE TABLE IF NOT EXISTS `__PREFIX__drama_view_log`  (
                                      `id` int(11) NOT NULL AUTO_INCREMENT,
                                      `site_id` int(11) NULL DEFAULT NULL COMMENT '站点',
                                      `video_id` int(11) NULL DEFAULT NULL COMMENT '剧目',
                                      `episodes_id` int(11) NULL DEFAULT NULL COMMENT '剧集',
                                      `user_id` int(11) NULL DEFAULT NULL COMMENT '用户',
                                      `ip` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'ip',
                                      `type` tinyint(1) NULL DEFAULT NULL COMMENT '类型:1=游客,2=用户',
                                      `platform` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '平台:H5=H5,wxOfficialAccount=微信公众号,wxMiniProgram=微信小程序,App=APP,douyinxcx=抖音小程序',
                                      `updatetime` int(11) NOT NULL COMMENT '更新时间',
                                      `createtime` int(11) NOT NULL COMMENT '添加时间',
                                      PRIMARY KEY (`id`) USING BTREE,
                                      INDEX `video_id`(`video_id`) USING BTREE,
                                      INDEX `episodes_id`(`episodes_id`) USING BTREE,
                                      INDEX `user_id`(`user_id`) USING BTREE,
                                      INDEX `type`(`type`) USING BTREE,
                                      INDEX `platform`(`platform`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 73 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '观看日志' ROW_FORMAT = Dynamic;

CREATE TABLE IF NOT EXISTS `__PREFIX__drama_episodes_view`  (
                                           `id` int(11) NOT NULL AUTO_INCREMENT,
                                           `site_id` int(11) NULL DEFAULT NULL COMMENT '站点',
                                           `video_id` int(11) NULL DEFAULT NULL COMMENT '剧目',
                                           `episodes_id` int(11) NULL DEFAULT NULL COMMENT '剧集',
                                           `H5_user` int(11) NULL DEFAULT 0 COMMENT 'H5观看用户数',
                                           `H5_user_view` int(11) NULL DEFAULT 0 COMMENT 'H5用户观看次数',
                                           `H5_visitor_view` int(11) NULL DEFAULT 0 COMMENT 'H5游客观看次数',
                                           `H5_total_view` int(11) NULL DEFAULT 0 COMMENT 'H5总观看次数',
                                           `wxOfficialAccount_user` int(11) NULL DEFAULT 0 COMMENT '微信公众号观看用户数',
                                           `wxOfficialAccount_user_view` int(11) NULL DEFAULT 0 COMMENT '微信公众号用户观看次数',
                                           `wxOfficialAccount_visitor_view` int(11) NULL DEFAULT 0 COMMENT '微信公众号游客观看次数',
                                           `wxOfficialAccount_total_view` int(11) NULL DEFAULT 0 COMMENT '微信公众号总观看次数',
                                           `wxMiniProgram_user` int(11) NULL DEFAULT 0 COMMENT '微信小程序观看用户数',
                                           `wxMiniProgram_user_view` int(11) NULL DEFAULT 0 COMMENT '微信小程序用户观看次数',
                                           `wxMiniProgram_visitor_view` int(11) NULL DEFAULT 0 COMMENT '微信小程序游客观看次数',
                                           `wxMiniProgram_total_view` int(11) NULL DEFAULT 0 COMMENT '微信小程序总观看次数',
                                           `App_user` int(11) NULL DEFAULT 0 COMMENT 'APP观看用户数',
                                           `App_user_view` int(11) NULL DEFAULT 0 COMMENT 'APP用户观看次数',
                                           `App_visitor_view` int(11) NULL DEFAULT 0 COMMENT 'APP游客观看次数',
                                           `App_total_view` int(11) NULL DEFAULT 0 COMMENT 'APP总观看次数',
                                           `douyinxcx_user` int(11) NULL DEFAULT 0 COMMENT '抖音小程序观看用户数',
                                           `douyinxcx_user_view` int(11) NULL DEFAULT 0 COMMENT '抖音小程序用户观看次数',
                                           `douyinxcx_visitor_view` int(11) NULL DEFAULT 0 COMMENT '抖音小程序游客观看次数',
                                           `douyinxcx_total_view` int(11) NULL DEFAULT 0 COMMENT '抖音小程序总观看次数',
                                           `total_user` int(11) NULL DEFAULT 0 COMMENT '总观看用户数',
                                           `total_user_view` int(11) NULL DEFAULT 0 COMMENT '总用户观看次数',
                                           `total_visitor_view` int(11) NULL DEFAULT 0 COMMENT '总游客观看次数',
                                           `total_view` int(11) NULL DEFAULT 0 COMMENT '总观看次数',
                                           `createtime` int(11) NOT NULL COMMENT '添加时间',
                                           `updatetime` int(11) NOT NULL COMMENT '更新时间',
                                           PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 37 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '剧集观看统计' ROW_FORMAT = Dynamic;

CREATE TABLE IF NOT EXISTS `__PREFIX__drama_video_view`  (
                                        `id` int(11) NOT NULL AUTO_INCREMENT,
                                        `site_id` int(11) NULL DEFAULT NULL COMMENT '站点',
                                        `video_id` int(11) NULL DEFAULT NULL COMMENT '剧目',
                                        `H5_user` int(11) NULL DEFAULT 0 COMMENT 'H5观看用户数',
                                        `H5_user_view` int(11) NULL DEFAULT 0 COMMENT 'H5用户观看次数',
                                        `H5_visitor_view` int(11) NULL DEFAULT 0 COMMENT 'H5游客观看次数',
                                        `H5_total_view` int(11) NULL DEFAULT 0 COMMENT 'H5总观看次数',
                                        `wxOfficialAccount_user` int(11) NULL DEFAULT 0 COMMENT '微信公众号观看用户数',
                                        `wxOfficialAccount_user_view` int(11) NULL DEFAULT 0 COMMENT '微信公众号用户观看次数',
                                        `wxOfficialAccount_visitor_view` int(11) NULL DEFAULT 0 COMMENT '微信公众号游客观看次数',
                                        `wxOfficialAccount_total_view` int(11) NULL DEFAULT 0 COMMENT '微信公众号总观看次数',
                                        `wxMiniProgram_user` int(11) NULL DEFAULT 0 COMMENT '微信小程序观看用户数',
                                        `wxMiniProgram_user_view` int(11) NULL DEFAULT 0 COMMENT '微信小程序用户观看次数',
                                        `wxMiniProgram_visitor_view` int(11) NULL DEFAULT 0 COMMENT '微信小程序游客观看次数',
                                        `wxMiniProgram_total_view` int(11) NULL DEFAULT 0 COMMENT '微信小程序总观看次数',
                                        `App_user` int(11) NULL DEFAULT 0 COMMENT 'APP观看用户数',
                                        `App_user_view` int(11) NULL DEFAULT 0 COMMENT 'APP用户观看次数',
                                        `App_visitor_view` int(11) NULL DEFAULT 0 COMMENT 'APP游客观看次数',
                                        `App_total_view` int(11) NULL DEFAULT 0 COMMENT 'APP总观看次数',
                                        `douyinxcx_user` int(11) NULL DEFAULT 0 COMMENT '抖音小程序观看用户数',
                                        `douyinxcx_user_view` int(11) NULL DEFAULT 0 COMMENT '抖音小程序用户观看次数',
                                        `douyinxcx_visitor_view` int(11) NULL DEFAULT 0 COMMENT '抖音小程序游客观看次数',
                                        `douyinxcx_total_view` int(11) NULL DEFAULT 0 COMMENT '抖音小程序总观看次数',
                                        `total_user` int(11) NULL DEFAULT 0 COMMENT '总观看用户数',
                                        `total_user_view` int(11) NULL DEFAULT 0 COMMENT '总用户观看次数',
                                        `total_visitor_view` int(11) NULL DEFAULT 0 COMMENT '总游客观看次数',
                                        `total_view` int(11) NULL DEFAULT 0 COMMENT '总观看次数',
                                        `createtime` int(11) NOT NULL COMMENT '添加时间',
                                        `updatetime` int(11) NOT NULL COMMENT '更新时间',
                                        PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 10 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '剧目观看统计' ROW_FORMAT = Dynamic;



INSERT INTO `__PREFIX__auth_rule` VALUES (1690, 'file', 0, 'drama/tongji', '数据统计', 'fa fa-bar-chart', '', '', '', 1, 'addtabs', '', 'sjtj', 'shujutongji', 1698463999, 1698463999, 0, 'normal');

ALTER TABLE `__PREFIX__user` ADD COLUMN mgg tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否免广告:1=是,0=否' AFTER `verification`;

INSERT INTO `__PREFIX__auth_rule` VALUES (1709, 'file', 184, 'drama/mgg_log_order', '免广告管理', 'fa fa-vimeo', '', '', '', 1, NULL, '', 'mgggl', 'mianguanggaoguanli', 1689831282, 1707034970, 8, 'normal');
INSERT INTO `__PREFIX__auth_rule` VALUES (1710, 'file', 1709, 'drama/mgg', '免广告配置', 'fa fa-vimeo-square', '', '', '', 1, NULL, '', 'mggpz', 'mianguanggaopeizhi', 1689831282, 1707034970, 0, 'normal');
INSERT INTO `__PREFIX__auth_rule` VALUES (1711, 'file', 1710, 'drama/mgg/index', '查看', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'zk', 'zhakan', 1689831282, 1707034970, 0, 'normal');
INSERT INTO `__PREFIX__auth_rule` VALUES (1712, 'file', 1710, 'drama/mgg/recyclebin', '回收站', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'hsz', 'huishouzhan', 1689831282, 1707034970, 0, 'normal');
INSERT INTO `__PREFIX__auth_rule` VALUES (1713, 'file', 1710, 'drama/mgg/add', '添加', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'tj', 'tianjia', 1689831282, 1707034970, 0, 'normal');
INSERT INTO `__PREFIX__auth_rule` VALUES (1714, 'file', 1710, 'drama/mgg/edit', '编辑', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'bj', 'bianji', 1689831282, 1707034970, 0, 'normal');
INSERT INTO `__PREFIX__auth_rule` VALUES (1715, 'file', 1710, 'drama/mgg/del', '删除', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'sc', 'shanchu', 1689831282, 1707034970, 0, 'normal');
INSERT INTO `__PREFIX__auth_rule` VALUES (1716, 'file', 1710, 'drama/mgg/destroy', '真实删除', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'zssc', 'zhenshishanchu', 1689831282, 1707034970, 0, 'normal');
INSERT INTO `__PREFIX__auth_rule` VALUES (1717, 'file', 1710, 'drama/mgg/restore', '还原', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'hy', 'huanyuan', 1689831282, 1707034970, 0, 'normal');
INSERT INTO `__PREFIX__auth_rule` VALUES (1718, 'file', 1710, 'drama/mgg/multi', '批量更新', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'plgx', 'pilianggengxin', 1689831282, 1707034970, 0, 'normal');
INSERT INTO `__PREFIX__auth_rule` VALUES (1719, 'file', 1709, 'drama/mgg_order', '免广告订单', 'fa fa-file-text', '', '', '', 1, NULL, '', 'mggdd', 'mianguanggaodingdan', 1689831282, 1707034970, 0, 'normal');
INSERT INTO `__PREFIX__auth_rule` VALUES (1720, 'file', 1719, 'drama/mgg_order/index', '查看', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'zk', 'zhakan', 1689831282, 1707034970, 0, 'normal');
INSERT INTO `__PREFIX__auth_rule` VALUES (1721, 'file', 1719, 'drama/mgg_order/recyclebin', '回收站', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'hsz', 'huishouzhan', 1689831282, 1707034970, 0, 'normal');
INSERT INTO `__PREFIX__auth_rule` VALUES (1722, 'file', 1719, 'drama/mgg_order/add', '添加', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'tj', 'tianjia', 1689831282, 1707034970, 0, 'normal');
INSERT INTO `__PREFIX__auth_rule` VALUES (1723, 'file', 1719, 'drama/mgg_order/edit', '编辑', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'bj', 'bianji', 1689831282, 1707034970, 0, 'normal');
INSERT INTO `__PREFIX__auth_rule` VALUES (1724, 'file', 1719, 'drama/mgg_order/del', '删除', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'sc', 'shanchu', 1689831282, 1707034970, 0, 'normal');
INSERT INTO `__PREFIX__auth_rule` VALUES (1725, 'file', 1719, 'drama/mgg_order/destroy', '真实删除', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'zssc', 'zhenshishanchu', 1689831282, 1707034970, 0, 'normal');
INSERT INTO `__PREFIX__auth_rule` VALUES (1726, 'file', 1719, 'drama/mgg_order/restore', '还原', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'hy', 'huanyuan', 1689831282, 1707034970, 0, 'normal');
INSERT INTO `__PREFIX__auth_rule` VALUES (1727, 'file', 1719, 'drama/mgg_order/multi', '批量更新', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'plgx', 'pilianggengxin', 1689831282, 1707034970, 0, 'normal');

CREATE TABLE IF NOT EXISTS `__PREFIX__drama_mgg`  (
                                 `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                                 `site_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
                                 `name` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '名称',
                                 `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '图片',
                                 `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '介绍',
                                 `price` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '价格',
                                 `original_price` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '原价',
                                 `level` tinyint(4) NOT NULL COMMENT '等级',
                                 `expire` int(11) NOT NULL COMMENT '有效期',
                                 `weigh` int(11) NOT NULL DEFAULT 0 COMMENT '排序',
                                 `status` enum('normal','hidden') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'normal' COMMENT '状态:normal=显示,hidden=隐藏',
                                 `updatetime` int(11) NOT NULL COMMENT '更新时间',
                                 `createtime` int(11) NOT NULL COMMENT '创建时间',
                                 `deletetime` int(11) NULL DEFAULT NULL COMMENT '删除时间',
                                 `product_id` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '虚拟支付道具ID',
                                 PRIMARY KEY (`id`) USING BTREE,
                                 UNIQUE INDEX `level`(`site_id`, `status`, `level`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 53 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '分销商' ROW_FORMAT = DYNAMIC;

CREATE TABLE IF NOT EXISTS `__PREFIX__drama_mgg_order`  (
                                       `id` int(11) NOT NULL AUTO_INCREMENT,
                                       `site_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
                                       `mgg_id` int(11) NOT NULL DEFAULT 0 COMMENT '免广告ID',
                                       `order_sn` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '订单号',
                                       `user_id` int(11) NULL DEFAULT 0 COMMENT '用户',
                                       `times` int(11) NULL DEFAULT 0 COMMENT '有效期',
                                       `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '订单状态:-2=交易关闭,-1=已取消,0=未支付,1=已支付,2=已完成',
                                       `total_fee` decimal(10, 2) NOT NULL COMMENT '支付金额',
                                       `pay_fee` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '实际支付金额',
                                       `transaction_id` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '交易单号',
                                       `payment_json` varchar(2500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '交易原始数据',
                                       `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '订单备注',
                                       `paytime` int(11) NULL DEFAULT NULL COMMENT '支付时间',
                                       `ext` varchar(2048) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '附加字段',
                                       `pay_type` enum('wechat','alipay','wallet','score','cryptocard','system','douyinpay') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '支付方式:wechat=微信支付,alipay=支付宝,wallet=钱包支付,score=积分支付,cryptocard=卡密兑换,system=管理员设置,douyinpay=抖音支付',
                                       `platform` enum('H5','Web','wxOfficialAccount','wxMiniProgram','App','douyinxcx') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '平台:H5=H5,wxOfficialAccount=微信公众号,wxMiniProgram=微信小程序,Web=Web,App=APP,douyinxcx=抖音小程序',
                                       `createtime` int(11) NULL DEFAULT NULL COMMENT '创建时间',
                                       `updatetime` int(11) NULL DEFAULT NULL COMMENT '更新时间',
                                       `deletetime` int(11) NULL DEFAULT NULL COMMENT '删除时间',
                                       `product_id` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '虚拟支付道具ID',
                                       PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 752 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

CREATE TABLE IF NOT EXISTS `__PREFIX__drama_point`  (
                                   `id` int(11) NOT NULL AUTO_INCREMENT,
                                   `site_id` int(11) NULL DEFAULT NULL COMMENT '站点',
                                   `item_id` int(11) NULL DEFAULT NULL COMMENT '关联id',
                                   `user_id` int(11) NULL DEFAULT NULL COMMENT '用户',
                                   `ip` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT 'ip',
                                   `content` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '内容',
                                   `point_type` int(5) NULL DEFAULT NULL COMMENT '埋点类型:1=首页点击,2=追剧页点击,3=搜索展示,4=搜索点击,5=底部tab,6=开通会员,7=充值',
                                   `user_type` tinyint(1) NULL DEFAULT NULL COMMENT '用户类型:1=游客,2=用户',
                                   `platform` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '平台:H5=H5,wxOfficialAccount=微信公众号,wxMiniProgram=微信小程序,App=APP,douyinxcx=抖音小程序',
                                   `updatetime` int(11) NOT NULL COMMENT '更新时间',
                                   `createtime` int(11) NOT NULL COMMENT '添加时间',
                                   PRIMARY KEY (`id`) USING BTREE,
                                   INDEX `user_id`(`user_id`) USING BTREE,
                                   INDEX `platform`(`platform`) USING BTREE,
                                   INDEX `item_id`(`item_id`) USING BTREE,
                                   INDEX `point_type`(`point_type`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 528 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '观看日志' ROW_FORMAT = Dynamic;

ALTER TABLE `__PREFIX__drama_video` ADD COLUMN open_pic_id int(11) NULL DEFAULT NULL COMMENT '抖音封面图id' AFTER `platform`;
ALTER TABLE `__PREFIX__drama_video` ADD COLUMN album_status tinyint(1) NULL DEFAULT NULL COMMENT '短剧更新状态:1=未上映,2=更新中,3=已完结' AFTER `platform`;
ALTER TABLE `__PREFIX__drama_video` ADD COLUMN recommendation varchar(12) NULL DEFAULT NULL COMMENT '短剧推荐语' AFTER `platform`;
ALTER TABLE `__PREFIX__drama_video` ADD COLUMN tag_list varchar(500) NULL DEFAULT NULL COMMENT '短剧类目标签' AFTER `platform`;
ALTER TABLE `__PREFIX__drama_video` ADD COLUMN qualification tinyint(1) NULL DEFAULT NULL COMMENT '资质状态:1=未报审,2=报审通过,3=报审不通过,4=不建议报审' AFTER `platform`;
ALTER TABLE `__PREFIX__drama_video` ADD COLUMN license_num varchar(50) NULL DEFAULT NULL COMMENT '许可证号' AFTER `platform`;
ALTER TABLE `__PREFIX__drama_video` ADD COLUMN registration_num varchar(50) NULL DEFAULT NULL COMMENT '登记号' AFTER `platform`;
ALTER TABLE `__PREFIX__drama_video` ADD COLUMN ordinary_record_num varchar(50) NULL DEFAULT NULL COMMENT '普通备案号' AFTER `platform`;
ALTER TABLE `__PREFIX__drama_video` ADD COLUMN key_record_num varchar(50) NULL DEFAULT NULL COMMENT '重点备案号' AFTER `platform`;
ALTER TABLE `__PREFIX__drama_video` ADD COLUMN examine_status tinyint(1) NULL DEFAULT 0 COMMENT '提审状态:0=未提审,1=等待审核,2=审核中,3=审核通过,4=审核未通过' AFTER `platform`;
ALTER TABLE `__PREFIX__drama_video` ADD COLUMN examine_version int(11) NULL DEFAULT 1 COMMENT '提审版本号' AFTER `platform`;
ALTER TABLE `__PREFIX__drama_video` ADD COLUMN audit_msg text NULL DEFAULT NULL COMMENT '审核备注' AFTER `platform`;

CREATE TABLE IF NOT EXISTS `__PREFIX__drama_dy_log`  (
                                    `id` int(11) NOT NULL AUTO_INCREMENT,
                                    `site_id` int(11) NULL DEFAULT NULL COMMENT '站点ID',
                                    `appid` int(11) NULL DEFAULT NULL COMMENT '抖音appid',
                                    `video_id` int(11) NULL DEFAULT NULL COMMENT '短剧ID',
                                    `album_id` int(11) NULL DEFAULT NULL COMMENT '内容库ID',
                                    `version` int(11) NULL DEFAULT NULL COMMENT '版本',
                                    `scope_list` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '能力',
                                    `audit_status` tinyint(1) NULL DEFAULT NULL COMMENT '审核状态:1=未通过,2=通过',
                                    `audit_msg` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '审核备注',
                                    `operate_content` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '操作内容',
                                    `extend_json` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '扩展json',
                                    `createtime` int(11) NOT NULL COMMENT '添加时间',
                                    `updatetime` int(11) NOT NULL COMMENT '更新时间',
                                    `deletetime` int(11) NULL DEFAULT NULL COMMENT '删除时间',
                                    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '抖音操作日志' ROW_FORMAT = Dynamic;

INSERT INTO `__PREFIX__auth_rule` VALUES (1746, 'file', 229, 'drama/video_order', '积分支付', 'fa fa-file-text', '', '', '', 1, NULL, '', 'jfdd', 'jifendingdan', 1689831282, 1690364942, 0, 'normal');
INSERT INTO `__PREFIX__auth_rule` VALUES (1747, 'file', 1746, 'drama/video_order/index', '查看', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'zk', 'zhakan', 1689831282, 1690364942, 0, 'normal');
INSERT INTO `__PREFIX__auth_rule` VALUES (1748, 'file', 1746, 'drama/video_order/recyclebin', '回收站', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'hsz', 'huishouzhan', 1689831282, 1690364942, 0, 'normal');
INSERT INTO `__PREFIX__auth_rule` VALUES (1749, 'file', 1746, 'drama/video_order/add', '添加', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'tj', 'tianjia', 1689831282, 1690364942, 0, 'normal');
INSERT INTO `__PREFIX__auth_rule` VALUES (1750, 'file', 1746, 'drama/video_order/edit', '编辑', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'bj', 'bianji', 1689831282, 1690364942, 0, 'normal');
INSERT INTO `__PREFIX__auth_rule` VALUES (1751, 'file', 1746, 'drama/video_order/del', '删除', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'sc', 'shanchu', 1689831282, 1690364942, 0, 'normal');
INSERT INTO `__PREFIX__auth_rule` VALUES (1752, 'file', 1746, 'drama/video_order/destroy', '真实删除', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'zssc', 'zhenshishanchu', 1689831282, 1690364942, 0, 'normal');
INSERT INTO `__PREFIX__auth_rule` VALUES (1753, 'file', 1746, 'drama/video_order/restore', '还原', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'hy', 'huanyuan', 1689831282, 1690364942, 0, 'normal');
INSERT INTO `__PREFIX__auth_rule` VALUES (1754, 'file', 1746, 'drama/video_order/multi', '批量更新', 'fa fa-circle-o', '', '', '', 0, NULL, '', 'plgx', 'pilianggengxin', 1689831282, 1690364942, 0, 'normal');

UPDATE `__PREFIX__auth_rule` SET `pid` = 1690 WHERE `id` = '1688';

ALTER TABLE `__PREFIX__drama_video_episodes` ADD COLUMN dy_yun_id varchar(100) NULL DEFAULT NULL COMMENT '抖音云ID' AFTER `xcx_media_id`;
ALTER TABLE `__PREFIX__drama_video_episodes` ADD COLUMN dy_nrk_id varchar(100) NULL DEFAULT NULL COMMENT '内容库ID' AFTER `xcx_media_id`;
ALTER TABLE `__PREFIX__drama_video_episodes` ADD COLUMN open_pic_id int(11) NULL DEFAULT NULL COMMENT '抖音封面图id' AFTER `xcx_media_id`;
ALTER TABLE `__PREFIX__drama_video_episodes` ADD COLUMN dy_sync tinyint(0) NULL DEFAULT NULL COMMENT '同步内容库状态:0=未同步,1=同步中,2=已同步' AFTER `xcx_media_id`;

ALTER TABLE `__PREFIX__drama_dy_log` ADD COLUMN ve_id int(11) NULL DEFAULT NULL COMMENT '剧集ID' AFTER `video_id`;

ALTER TABLE `__PREFIX__drama_vip` ADD COLUMN yingxiao varchar(100) NULL DEFAULT NULL COMMENT '营销语' AFTER `product_id`;
ALTER TABLE `__PREFIX__drama_reseller` ADD COLUMN yingxiao varchar(100) NULL DEFAULT NULL COMMENT '营销语' AFTER `product_id`;

COMMIT;