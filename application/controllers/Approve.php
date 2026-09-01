<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Approve extends MY_Controller {

    public function __construct(){
        parent::__construct();

        $this->load->model('Approve_model');
    }

    public function index(){
        $data['title'] = 'Setting Approve';
        $this->load->view('templates/header', $data);
        $this->load->view('approve/index', $data);
        $this->load->view('templates/footer');
    }

    public function get_menu_data(){
        $menus = $this->Approve_model->get_menu();
        echo json_encode($menus);
    }

    public function get_employee_data(){
        $employees = $this->Approve_model->get_employee();
        echo json_encode($employees);
    }
}