<?php

class Admin_model extends Base_model
{

    public function get_admin_by_id($uid = 0)
    {
        $result = $this->db->select('user_id, user_name, password, email, last_login, ec_salt')
            ->from('admin')
            ->where('user_id', $uid)
            ->get();
        return $result->row_array();
    }

    public function get_admin_by_name($name = '')
    {
        $result = $this->db->select('user_id, user_name, password, email, last_login, ec_salt')
            ->from('admin')
            ->where('user_name', $name)
            ->get();
        return $result->row_array();
    }

    public function update_admin($uid, $data)
    {
        $this->db->where('user_id', $uid)->update('admin', $data);
        return ($this->db->affected_rows() > 0) ? TRUE : FALSE;
    }

    public function get_auth_rule(){
        $result = $this->db->select('id, pid, name, title')
            ->from('admin_rule')
            ->get();
        return $result->result_array();

    }

}