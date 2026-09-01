<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Purchase_model extends CI_Model
{
    protected $table = 'purchase';

    public function __construct()
    {
        parent::__construct();
    }

    public function get_data($filter = []){
        $this->db->select('purchase.*, supplier.nama_supplier, product_status.product_status_name, users.username as created_by_username');   // pastikan ada ini
        $this->db->from($this->table);
        $this->db->join('supplier', 'supplier.id = purchase.supplier_id', 'left');
        $this->db->join('product_status', 'product_status.product_status_id = purchase.status', 'left');   // pastikan join ini ada
        $this->db->join('users', 'users.user_id = purchase.created_by', 'left');

        if (isset($filter['status']) && $filter['status'] !== '' && $filter['status'] !== null) {
            $this->db->where('purchase.status', $filter['status']);
        }

        if(!empty($filter['supplier_id'])){
            $this->db->where('purchase.supplier_id', $filter['supplier_id']);
        }

        if(!empty($filter['start_date']) && !empty($filter['end_date'])){
            $this->db->where('purchase.purchase_date >=', $filter['start_date']);
            $this->db->where('purchase.purchase_date <=', $filter['end_date']);
        }

        if (!empty($filter['payment_type'])) {
            $this->db->where('purchase.payment_type', $filter['payment_type']);
        }

        $this->db->order_by('purchase.purchase_id', 'DESC');

        return $this->db->get()->result();
    }

    public function get_by_id($id){
        $this->db->select('purchase.*, supplier.nama_supplier');
        $this->db->from($this->table);
        $this->db->join('supplier', 'supplier.id = purchase.supplier_id', 'left');
        $this->db->where('purchase.purchase_id', $id);

        $query = $this->db->get();

        return $query->row();
    }

    public function get_status(){
        $this->db->where('module', 'purchase');
        return $this->db->get('product_status')->result();
    }

    public function get_supplier(){
        $this->db->where('status', 1);
        return $this->db->get('supplier')->result();
    }
    
    public function get_product(){
        return $this->db->get('products')->result();
    }

    public function get_pembayaran(){
        $this->db->select('payment_type');
        $this->db->group_by('payment_type');
        return $this->db->get($this->table)->result();
    }

    public function generate_purchase_code(){
        $year = date('Y');
        $this->db->like('purchase_code', 'PO-'.$year.'-', 'after');
        $this->db->order_by('purchase_id', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get($this->table);
        $last = $query->row();


        if($last){
            $last_number = (int) substr($last->purchase_code, -6);
            $next_number = $last_number + 1;
        }else{
            $next_number = 1;
        }

        $formatted = str_pad($next_number, 6, '0', STR_PAD_LEFT);

        return 'PO-' . $year . '-' . $formatted;
    }

    public function proses_purchase($header, $items){
        $this->db->trans_begin();

        // Proses Header
        $this->db->insert($this->table, $header);
        $purchase_id = $this->db->insert_id();

        // Proses Detail
        foreach ($items as $item) {
            $subtotal = $item['qty'] * $item['price'];
            $this->db->insert('purchase_detail', [
                'purchase_id' => $purchase_id,
                'product_id' => $item['product_id'],
                'qty' => $item['qty'],
                'price' => $item['price'],
                'subtotal' => $subtotal
            ]);
        }

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return false;
        } else {
            $this->db->trans_commit();
            return $purchase_id;
        }
    }

    public function update_purchase($id, $header, $items){
        $this->db->trans_begin();

        // update header
        $this->db->where('purchase_id', $id);
        $this->db->update($this->table, $header);

        // hapus semua detail lama
        $this->db->where('purchase_id', $id);
        $this->db->delete('purchase_detail');

        foreach ($items as $item) {
            $subtotal = $item['qty'] * $item['price'];
            $this->db->insert('purchase_detail', [
                'purchase_id' => $id,
                'product_id'  => $item['product_id'],
                'qty'         => $item['qty'],
                'price'       => $item['price'],
                'subtotal'    => $subtotal
            ]);
        }

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return false;
        } else {
            $this->db->trans_commit();
            return true;
        }
    }

    public function get_purchase_items($purchase_id){
        $this->db->select('purchase_detail.*, products.product_name');
        $this->db->from('purchase_detail');
        $this->db->join('products', 'products.product_id = purchase_detail.product_id', 'left');
        $this->db->where('purchase_detail.purchase_id', $purchase_id);
        return $this->db->get()->result();
    }

    public function get_purchase_item($purchase_detail_id, $purchase_id){
        $this->db->select('purchase_detail.*, products.product_name');
        $this->db->from('purchase_detail');
        $this->db->join('products', 'products.product_id = purchase_detail.product_id', 'left');
        $this->db->where('purchase_detail.purchase_detail_id', $purchase_detail_id);
        $this->db->where('purchase_detail.purchase_id', $purchase_id);
        return $this->db->get()->row();
    }

    public function insert_purchase_item($purchase_id, $product_id, $qty, $price){
        $this->db->where('purchase_id', $purchase_id);
        $this->db->where('product_id', $product_id);
        if ($this->db->get('purchase_detail')->num_rows() > 0) {
            return false;
        }

        $this->db->trans_begin();
        $this->db->insert('purchase_detail', [
            'purchase_id' => $purchase_id,
            'product_id'  => $product_id,
            'qty'         => $qty,
            'price'       => $price,
            'subtotal'    => $qty * $price
        ]);
        $detail_id = $this->db->insert_id();
        $this->refresh_purchase_totals($purchase_id);

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return false;
        }
        $this->db->trans_commit();
        return $detail_id;
    }

    public function update_purchase_item($purchase_detail_id, $purchase_id, $product_id, $qty, $price){
        $item = $this->get_purchase_item($purchase_detail_id, $purchase_id);
        if (!$item) {
            return false;
        }

        $this->db->where('purchase_id', $purchase_id);
        $this->db->where('product_id', $product_id);
        $this->db->where('purchase_detail_id !=', $purchase_detail_id);
        if ($this->db->get('purchase_detail')->num_rows() > 0) {
            return false;
        }

        $this->db->trans_begin();
        $this->db->where('purchase_detail_id', $purchase_detail_id);
        $this->db->where('purchase_id', $purchase_id);
        $this->db->update('purchase_detail', [
            'product_id' => $product_id,
            'qty'        => $qty,
            'price'      => $price,
            'subtotal'   => $qty * $price
        ]);
        $this->refresh_purchase_totals($purchase_id);

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return false;
        }
        $this->db->trans_commit();
        return true;
    }

    public function delete_purchase_item($purchase_detail_id, $purchase_id){
        $this->db->trans_begin();
        $this->db->where('purchase_detail_id', $purchase_detail_id);
        $this->db->where('purchase_id', $purchase_id);
        $this->db->delete('purchase_detail');
        $deleted = $this->db->affected_rows() > 0;
        $this->refresh_purchase_totals($purchase_id);

        if (!$deleted || $this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return false;
        }
        $this->db->trans_commit();
        return true;
    }

    private function refresh_purchase_totals($purchase_id){
        $this->db->select_sum('subtotal');
        $this->db->where('purchase_id', $purchase_id);
        $subtotal = (float) ($this->db->get('purchase_detail')->row()->subtotal ?: 0);
        $purchase = $this->db->select('discount, tax')->where('purchase_id', $purchase_id)->get($this->table)->row();
        if (!$purchase) {
            return;
        }

        $this->db->where('purchase_id', $purchase_id);
        $this->db->update($this->table, [
            'subtotal'    => $subtotal,
            'grand_total' => max(0, $subtotal - (float) $purchase->discount + (float) $purchase->tax)
        ]);
    }

    // menampikan data detail dari purchase, dari data purchase dan list product yang dibeli
    public function get_purchase_detail($purchase_id){
        $this->db->select('purchase.*, supplier.nama_supplier, product_status.product_status_name, users.username as created_by_username');   // pastikan ada ini
        $this->db->from($this->table);
        $this->db->join('supplier', 'supplier.id = purchase.supplier_id', 'left');
        $this->db->join('product_status', 'product_status.product_status_id = purchase.status', 'left');
        $this->db->join('users', 'users.user_id = purchase.created_by', 'left');
        $this->db->where('purchase.purchase_id', $purchase_id);

        $header = $this->db->get()->row();

        if (!$header) {
            return null;
        }

        $this->db->select('purchase_detail.*, products.product_name');
        $this->db->from('purchase_detail');
        $this->db->join('products', 'products.product_id = purchase_detail.product_id', 'left');
        $this->db->where('purchase_detail.purchase_id', $purchase_id);

        $details = $this->db->get()->result();

        return [
            'header' => $header,
            'details' => $details
        ];
    }


    public function delete_purchase($purchase_id){
        $this->db->trans_begin();

        $this->db->where('purchase_id', $purchase_id);
        $this->db->delete($this->table);

        $this->db->where('purchase_id', $purchase_id);
        $this->db->delete('purchase_detail');

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return false;
        } else {
            $this->db->trans_commit();
            return true;
        }
    }


}