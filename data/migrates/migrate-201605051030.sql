-- ------------------------------------------------------------
-- author:gaojian
-- date:20160505
-- description:增加商品多用户评论表
-- ------------------------------------------------------------


CREATE TABLE IF NOT EXISTS `{pre}order_rec_comment` (
  `rec_id` mediuint(8)  NOT NULL ,
  `comment_id` int(10)  NOT NULL  
) ENGINE=MyISAM DEFAULT CHARSET=utf8  ;
