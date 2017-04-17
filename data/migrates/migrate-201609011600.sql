-- ------------------------------------------------------------
-- date:20160901
-- description:新增微筹导航按钮
-- ------------------------------------------------------------

--
-- 转存表中的数据 `{pre}touch_nav`
--

INSERT INTO `{pre}touch_nav` (`id`, `ctype`, `cid`, `name`, `ifshow`, `vieworder`, `opennew`, `url`, `pic`, `type`) VALUES
(9, NULL, 0, '我的微筹', 1, 0, 0, 'index.php?c=crowdfunding', 'themes/default/images/nav/nav_8.png', 'middle');