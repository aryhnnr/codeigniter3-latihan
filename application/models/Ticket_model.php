<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ticket_model extends CI_Model
{
    protected $table = 'tickets';

    public function __construct()
    {
        parent::__construct();
    }


    public function get_data($filter = array()){
        $this->db->select('tickets.*, departments.department_name, teknisi.nama as teknisi_name, users.username as created_by_username');
        $this->db->from($this->table);
        $this->db->join('departments', 'departments.department_id  = tickets.departemen_id', 'left');
        $this->db->join('teknisi', 'teknisi.id = tickets.teknisi_id', 'left');
        $this->db->join('users', 'users.user_id = tickets.created_by', 'left');

        // Filter 
        if(!empty($filter['status'])){
            $this->db->where('tickets.status', $filter['status']);
        }
        if(!empty($filter['prioritas'])){
            $this->db->where('tickets.prioritas', $filter['prioritas']);
        }
        if(!empty($filter['teknisi_id'])){
            $this->db->where('tickets.teknisi_id', $filter['teknisi_id']);
        }
        $this->db->order_by('tickets.id', 'DESC');

        $query = $this->db->get();

        return $query->result();
    }


    public function get_departemen(){
        return $this->db->get('departments')->result();
    }

    public function get_teknisi(){
        return $this->db->get('teknisi')->result();
    }

    public function generate_ticket_number(){
        $year = date('Y');
        $this->db->like('ticket_number', 'IT-'.$year.'-', 'after');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get($this->table);
        $last = $query->row();


        if($last){
            $last_number = (int) substr($last->ticket_number, -4);
            $next_number = $last_number + 1;
        }else{
            $next_number = 1;
        }

        $formatted = str_pad($next_number, 4, '0', STR_PAD_LEFT);

        return 'IT-' . $year . '-' . $formatted;
    }


    public function insert($data){
        return $this->db->insert($this->table, $data);
    }


    public function get_by_id($id){
        $this->db->select('tickets.*, departments.department_name, teknisi.nama as teknisi_nama, users.username as created_by_username');
        $this->db->from($this->table);
        $this->db->join('departments', 'departments.department_id = tickets.departemen_id', 'left');
        $this->db->join('teknisi', 'teknisi.id = tickets.teknisi_id', 'left');
        $this->db->join('users', 'users.user_id = tickets.created_by', 'left');
        $this->db->where('tickets.id', $id);

        $query = $this->db->get();

        return $query->row();
    }
    public function get_teknisi_by_id($id)
    {
        return $this->db->get_where('teknisi', array('id' => $id))->row();
    }

    public function update($id, $data){
        $this->db->where('id', $id);
        return $this->db->update($this->table, $data);

    }

    public function is_valid_transition($status_lama, $status_baru){
        $allowed = array(
            'OPEN'        => array('IN PROGRESS', 'CANCELLED'),
            'IN PROGRESS' => array('DONE', 'CANCELLED'),
            'DONE'        => array(),
            'CANCELLED'   => array(),
        );

        if (!isset($allowed[$status_lama])){
            return FALSE;
        }

        return in_array($status_baru, $allowed[$status_lama]);
    }


    public function validate_status_change($ticket, $status_baru, $catatan_baru = null){
        $status_lama = $ticket->status;

        if ($status_lama == 'OPEN') {
            if ($status_baru != 'IN PROGRESS' && $status_baru != 'CANCELLED') {
                return array('valid' => false, 'message' => 'Ticket OPEN hanya boleh diubah ke IN PROGRESS atau CANCELLED.');
            }
        }elseif ($status_lama == 'IN PROGRESS') {
            if ($status_baru != 'DONE' && $status_baru != 'CANCELLED') {
                return array('valid' => false, 'message' => 'Ticket OPEN hanya boleh diubah ke IN PROGRESS atau CANCELLED.');
            }
        }elseif ($status_lama == 'DONE') {
            return array('valid' => false, 'message' => 'Ticket yang sudah DONE tidak dapat dirubah statusnya lagi.');
        }elseif ($status_lama == 'CANCELLED') {
            return array('valid' => false, 'message' => 'Ticket yang sudah CANCELLED tidak dapat dirubah statusnya lagi.');
        }


        if ($status_baru == 'IN PROGRESS' || $status_baru == 'DONE') {
            if (empty($ticket->teknisi_id)) {
                return array('valid' => false, 'message' => 'Teknisi wajib diassign sebelum status diubah ke ' . $status_baru . '.');
            }
        }

        if ($status_baru == 'DONE' || $status_baru == 'CANCELLED') {
            if (empty($catatan_baru)) {
                return array('valid' => false, 'message' => 'Catatan wajib diisi sebelum status diubah ke ' . $status_baru . '.');
            }
        }

        return array('valid' => true, 'message' => '');
    }
    

    
}