<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends MY_Controller{

    public function __construct(){
        parent::__construct();

        $this->load->model('Dashboard_model');
    }

    public function index(){
        $data = $this->data;
        $data['title'] = 'Dashboard';
        $this->load->view('templates/header', $data);
        $this->load->view('dashboard', $data);
        $this->load->view('templates/footer');
    }

    public function get_data(){
        $data = array(
            'tickets_total' => $this->Dashboard_model->count_tickets_total(),
            'tickets_open' => $this->Dashboard_model->count_tickets_by_status('OPEN')->total,
            'tickets_in_progress' => $this->Dashboard_model->count_tickets_by_status('IN PROGRESS')->total,
            'tickets_done' => $this->Dashboard_model->count_tickets_by_status('DONE')->total,
            'tickets_cancelled' => $this->Dashboard_model->count_tickets_by_status('CANCELLED')->total,
            'employees_total' => $this->Dashboard_model->count_employee_total(),
            'employees_active' => $this->Dashboard_model->count_employee_by_status(1)->total,
            'employees_inactive' => $this->Dashboard_model->count_employee_by_status(0)->total,
            'products_total' => $this->Dashboard_model->count_product_total(),
            'products_active' => $this->Dashboard_model->count_product_by_status(1)->total,
            'products_inactive' => $this->Dashboard_model->count_product_by_status(0)->total,
        );

        header('Content-Type: application/json');
        echo json_encode($data);
    }
}