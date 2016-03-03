<?php
namespace activity\Model;

use base\Model\BaseModel;

class ActivityModel extends BaseModel
{
	protected $tableName = 'favourable_activity';

	public function getList(){
		/* 取得用户等级 */
		$user_rank_list = array();
		$user_rank_list[0] = $_LANG['not_user'];
		$res = M('user_rank')->field('rank_id, rank_name')->select();
		foreach ($res as $row)
		{
			$user_rank_list[$row['rank_id']] = $row['rank_name'];
		}

		// 开始工作
		$res = M('favourable_activity')->order('`sort_order` ASC,`end_time` DESC')->select();

		$list = array();
		foreach ($res as $row)
		{
		    $row['start_time']  = local_date('Y-m-d H:i', $row['start_time']);
		    $row['end_time']    = local_date('Y-m-d H:i', $row['end_time']);

		    //享受优惠会员等级
		    $user_rank = explode(',', $row['user_rank']);
		    $row['user_rank'] = array();
		    foreach($user_rank as $val)
		    {
		        if (isset($user_rank_list[$val]))
		        {
		            $row['user_rank'][] = $user_rank_list[$val];
		        }
		    }

		    //优惠范围类型、内容
		    if ($row['act_range'] != FAR_ALL && !empty($row['act_range_ext']))
		    {
		        if ($row['act_range'] == FAR_CATEGORY)
		        {
		            $row['act_range'] = $_LANG['far_category'];
		            $row['program'] = 'category.php?id=';
		            $sql = "SELECT cat_id AS id, cat_name AS name FROM " . $ecs->table('category') .
		                " WHERE cat_id " . db_create_in($row['act_range_ext']);
		        }
		        elseif ($row['act_range'] == FAR_BRAND)
		        {
		            $row['act_range'] = $_LANG['far_brand'];
		            $row['program'] = 'brand.php?id=';
		            $sql = "SELECT brand_id AS id, brand_name AS name FROM " . $ecs->table('brand') .
		                " WHERE brand_id " . db_create_in($row['act_range_ext']);
		        }
		        else
		        {
		            $row['act_range'] = $_LANG['far_goods'];
		            $row['program'] = 'goods.php?id=';
		            $sql = "SELECT goods_id AS id, goods_name AS name FROM " . $ecs->table('goods') .
		                " WHERE goods_id " . db_create_in($row['act_range_ext']);
		        }
		        $act_range_ext = $db->getAll($sql);
		        $row['act_range_ext'] = $act_range_ext;
		    }
		    else
		    {
		        $row['act_range'] = $_LANG['far_all'];
		    }

		    //优惠方式
		    switch($row['act_type'])
		    {
		        case 0:
		            $row['act_type'] = $_LANG['fat_goods'];
		            $row['gift'] = unserialize($row['gift']);
		            if(is_array($row['gift']))
		            {
		                foreach($row['gift'] as $k=>$v)
		                {
		                    //$row['gift'][$k]['thumb'] = get_image_path($v['id'], $db->getOne("SELECT goods_thumb FROM " . $ecs->table('goods') . " WHERE goods_id = '" . $v['id'] . "'"), true);
		                }
		            }
		            break;
		        case 1:
		            $row['act_type'] = $_LANG['fat_price'];
		            $row['act_type_ext'] .= $_LANG['unit_yuan'];
		            $row['gift'] = array();
		            break;
		        case 2:
		            $row['act_type'] = $_LANG['fat_discount'];
		            $row['act_type_ext'] .= "%";
		            $row['gift'] = array();
		            break;
		    }

		    $list[] = $row;
		}

		return $list;
	}
}