<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Menu_model extends CI_Model{
    protected $table = 'menus';

    public function __construct()
    {
        parent::__construct();
    }

    public function get_data() {
        $this->db->select('menus.*, product_status.product_status_name');
        $this->db->from($this->table);
        $this->db->join('product_status', 'product_status.product_status_id = menus.status', 'left');
        $this->db->order_by('menus.id', 'ASC');
        return $this->db->get()->result();
    }

    public function get_parent_menus($exclude_id = null) {
        $this->db->where('parent_id', 0);
        if ($exclude_id) {
            $this->db->where('id !=', (int) $exclude_id);
        }
        return $this->db->order_by('order_no', 'ASC')->order_by('id', 'ASC')->get($this->table)->result();
    }

    public function get_next_order($parent_id, $exclude_id = null) {
        $this->db->select_max('order_no');
        $this->db->where('parent_id', (int) $parent_id);
        if ($exclude_id) {
            $this->db->where('id !=', (int) $exclude_id);
        }
        $row = $this->db->get($this->table)->row();
        return ((int) ($row->order_no ?? 0)) + 1;
    }

    public function get_for_role($role_id, $role_slug = '') {
        $this->db->select('menus.*');
        $this->db->from($this->table);
        $this->db->where('menus.status', 1);

        if (!in_array(strtolower((string) $role_slug), ['superadmin', 'admin'], true)
            && (int) $role_id !== 1 && (int) $role_id !== 2) {
            $this->db->join(
                'role_menu_permissions',
                'role_menu_permissions.menu_id = menus.id AND role_menu_permissions.role_id = ' . (int) $role_id,
                'left'
            );
            $this->db->group_start();
            $this->db->where('role_menu_permissions.can_view', 1);
            $this->db->or_where(
                'EXISTS (SELECT 1 FROM role_menu_permissions child_permissions WHERE child_permissions.role_id = ' . (int) $role_id . ' AND child_permissions.menu_id IN (SELECT child_menus.id FROM menus child_menus WHERE child_menus.parent_id = menus.id) AND child_permissions.can_view = 1)',
                null,
                false
            );
            $this->db->group_end();
        }

        return $this->db->order_by('menus.order_no', 'ASC')->order_by('menus.id', 'ASC')->get()->result();
    }

    public function get_by_id($id) {
        $this->db->select('menus.*, product_status.product_status_name');
        $this->db->from($this->table);
        $this->db->join('product_status', 'product_status.product_status_id = menus.status', 'left');
        $this->db->where('menus.id', $id);
        return $this->db->get()->row();
    }

    public function insert($data){
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert($this->table, $data);
    }

    public function update($id, $data){
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->where('id', $id);
        $this->db->update($this->table, $data);
    }

    public function delete($id){
        $this->db->where('id', $id);
        $this->db->delete($this->table);
    }

    public function is_used_in_permission($id){
        return $this->db->where('menu_id', $id)->count_all_results('role_menu_permissions');
    }

    public function is_url_exists($url, $exclude_id = null){
        if (strtolower(trim($url)) === 'javascript:;') {
            return false;
        }

        $this->db->where('url', $url);
        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }
        return $this->db->get($this->table)->num_rows() > 0;
    }

    // Daftar status Aktif/Nonaktif dipakai bareng dari module 'product'
    public function get_status(){
        $this->db->where('module', 'product');
        $this->db->order_by('product_status_id', 'ASC');
        return $this->db->get('product_status')->result();
    }
}