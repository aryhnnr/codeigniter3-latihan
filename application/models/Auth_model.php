<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth_model extends CI_Model {

    protected $table = 'users';

    public function get_user($identifier){
        $this->db->select('users.*, roles.name as role_name, roles.slug as role_slug');
        $this->db->from($this->table);
        $this->db->join('roles', 'roles.id = users.role_id', 'left');
        $this->db->group_start();
        $this->db->where('users.username', $identifier);
        $this->db->or_where('users.email', $identifier);
        $this->db->or_where('users.nomor_hp', $identifier);
        $this->db->group_end();
        $query = $this->db->get();

        return $query->row();
    }

    public function create_token($data){
        return $this->db->insert('tokens', $data);
    }
    
    public function get_token($hashed_token){
        return $this->db
            ->select('tokens.*, users.email, users.username')
            ->from('tokens')
            ->join('users', 'users.user_id = tokens.user_id')
            ->where('tokens.token', $hashed_token)
            ->order_by('tokens.id', 'DESC')
            ->limit(1)
            ->get()
            ->row();
    }
    public function mark_token_used($token_id){
        return $this->db->where('id', $token_id)->update('tokens', ['used' => 1]);
    }
    public function update_password($user_id, $hashed_password){
        return $this->db->where('user_id', $user_id)->update('users', ['password' => $hashed_password]);
    }

    public function get_by_id($user_id){
        $this->db->select('users.*, roles.name as role_name, roles.slug as role_slug');
        $this->db->from($this->table);
        $this->db->join('roles', 'roles.id = users.role_id', 'left');
        $this->db->where('users.user_id', $user_id);
        return $this->db->get()->row();
    }

    public function update_profile($user_id, $data){
        return $this->db->where('user_id', $user_id)->update($this->table, $data);
    }
}
