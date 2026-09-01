<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Role extends CI_Controller {

    public function __construct() {
        parent::__construct();
        // if (!$this->session->userdata('logged_in')) {
        //     redirect('login');
        // }
        $this->load->model('Role_model');
        $this->load->library('form_validation');
        $this->output->set_content_type('application/json');
    }

    public function index() {
        $this->output->set_content_type('text/html');
        $data['title'] = 'Master Role & Hak Akses';
        $this->load->view('templates/header', $data);
        $this->load->view('role/index', $data);
        $this->load->view('templates/footer');
    }

    public function get_role_data() {
        $roles = $this->Role_model->get_data();

        $result = [];
        foreach ($roles as $role) {
            $status_badge = $role->status
                ? '<span class="badge badge-success">Aktif</span>'
                : '<span class="badge badge-secondary">Nonaktif</span>';

            $id_safe = (int) $role->id;

            $action = '
                <a href="' . base_url('role/detail/' . $id_safe) . '" class="btn btn-info btn-sm" title="Detail"><i class="fas fa-eye"></i></a>
                <a href="' . base_url('role/edit/' . $id_safe) . '" class="btn btn-warning btn-sm" title="Edit"><i class="fas fa-edit"></i></a>
                <button class="btn btn-danger btn-sm btn-delete" data-id="' . $id_safe . '" title="Hapus"><i class="fas fa-trash"></i></button>
            ';

            $result[] = [
                'name'   => htmlspecialchars($role->name),
                'slug'   => htmlspecialchars($role->slug),
                'status' => $status_badge,
                'action' => $action,
            ];
        }

        echo json_encode($result);
    }

    public function create() {
        $this->output->set_content_type('text/html');
        $data['title']       = 'Tambah Role & Hak Akses';
        $data['mode']        = 'create';
        $data['role']        = null;
        $data['menus']       = $this->Role_model->get_all_menus();
        $data['permissions'] = [];
        $data['is_unrestricted_role'] = false;
        $data['permission_menus'] = $this->prepare_permission_menus(
            $data['menus'],
            $data['permissions'],
            $data['is_unrestricted_role']
        );
        $this->load->view('templates/header', $data);
        $this->load->view('role/form', $data);
        $this->load->view('templates/footer');
    }

    public function edit($id) {
        $role = $this->Role_model->get_by_id($id);
        if (!$role) {
            show_404();
        }

        $this->output->set_content_type('text/html');
        $data['title']       = 'Edit Role & Hak Akses';
        $data['mode']        = 'edit';
        $data['role']        = $role;
        $data['menus']       = $this->Role_model->get_all_menus();
        $data['permissions'] = $this->Role_model->get_permissions_by_role($id);
        $data['is_unrestricted_role'] = $this->Role_model->is_unrestricted_role($role);
        $data['permission_menus'] = $this->prepare_permission_menus(
            $data['menus'],
            $data['permissions'],
            $data['is_unrestricted_role']
        );
        $this->load->view('templates/header', $data);
        $this->load->view('role/form', $data);
        $this->load->view('templates/footer');
    }

    public function detail($id) {
        $role = $this->Role_model->get_by_id($id);
        if (!$role) {
            show_404();
        }

        $this->output->set_content_type('text/html');
        $data['title']       = 'Detail Role & Hak Akses';
        $data['mode']        = 'detail';
        $data['role']        = $role;
        $data['menus']       = $this->Role_model->get_all_menus();
        $data['permissions'] = $this->Role_model->get_permissions_by_role($id);
        $data['is_unrestricted_role'] = $this->Role_model->is_unrestricted_role($role);
        $data['permission_menus'] = $this->prepare_permission_menus(
            $data['menus'],
            $data['permissions'],
            $data['is_unrestricted_role']
        );
        $this->load->view('templates/header', $data);
        $this->load->view('role/form', $data);
        $this->load->view('templates/footer');
    }

    private function prepare_permission_menus($menus, array $permissions, $is_unrestricted_role) {
        $menu_children = [];
        $menu_roots = [];
        $menu_ids = [];

        foreach ($menus as $menu) {
            $menu_id = (int) $menu->id;
            $menu_ids[$menu_id] = true;

            if ((int) $menu->parent_id === 0) {
                $menu_roots[] = $menu;
            } else {
                $menu_children[(int) $menu->parent_id][] = $menu;
            }
        }

        foreach ($menus as $menu) {
            $parent_id = (int) $menu->parent_id;
            if ($parent_id !== 0 && !isset($menu_ids[$parent_id])) {
                $menu_roots[] = $menu;
                unset($menu_children[$parent_id]);
            }
        }

        $permission_menus = [];
        foreach ($menu_roots as $menu) {
            $menu_id = (int) $menu->id;
            $children = $menu_children[$menu_id] ?? [];
            $menu_perm = $permissions[$menu_id] ?? null;
            $child_checked = 0;
            $prepared_children = [];

            foreach ($children as $child) {
                $child_id = (int) $child->id;
                $child_perm = $permissions[$child_id] ?? null;
                $child_is_checked = $is_unrestricted_role || ($child_perm && $child_perm->can_view);
                $child_checked += $child_is_checked ? 1 : 0;
                $prepared_children[] = [
                    'menu' => $child,
                    'permission' => $child_perm,
                    'checked' => $child_is_checked,
                ];
            }

            $parent_checked = $is_unrestricted_role
                ? true
                : (!empty($children)
                    ? ($child_checked === count($children))
                    : ($menu_perm && $menu_perm->can_view));

            $permission_menus[] = [
                'menu' => $menu,
                'permission' => $menu_perm,
                'children' => $prepared_children,
                'checked' => (bool) $parent_checked,
            ];
        }

        return $permission_menus;
    }

    // Satu endpoint untuk create & update, role + permission disimpan sekali jalan
    public function save() {
        $id = $this->input->post('id');
        $id = ($id && $id !== '0') ? (int) $id : null;

        $this->form_validation->set_rules('name', 'Nama Role', 'required|trim|max_length[50]');
        $this->form_validation->set_rules('slug', 'Slug', 'required|trim|max_length[50]|alpha_dash');
        $this->form_validation->set_rules('status', 'Status', 'required|in_list[0,1]');

        if ($this->form_validation->run() == FALSE) {
            echo json_encode([
                'status'  => 'error_validation',
                'errors'  => $this->form_validation->error_array()
            ]);
            return;
        }

        $slug = strtolower(trim($this->input->post('slug')));

        if ($this->Role_model->is_slug_exists($slug, $id)) {
            echo json_encode([
                'status'  => 'failed',
                'message' => 'Slug sudah digunakan role lain'
            ]);
            return;
        }

        $data = [
            'name'   => $this->input->post('name'),
            'slug'   => $slug,
            'status' => (int) $this->input->post('status'),
        ];

        if ($id) {
            $this->Role_model->update($id, $data);
            $role_id = $id;
            $message = 'Role berhasil diupdate';
        } else {
            $role_id = $this->Role_model->insert($data);
            $message = 'Role berhasil ditambahkan';
        }

        // permissions dikirim sebagai array: permissions[menu_id][view|add|edit|delete]
        $permissions = $this->input->post('permissions');
        $this->Role_model->save_permissions($role_id, is_array($permissions) ? $permissions : []);

        $this->session->set_flashdata('success', $message);
        echo json_encode([
            'status'  => 'success',
            'message' => $message
        ]);
    }

    public function delete_role($id) {
        if (!is_numeric($id)) {
            echo json_encode(['status' => 'failed', 'message' => 'ID tidak valid']);
            return;
        }

        if ($this->Role_model->is_used_in_users($id) > 0) {
            echo json_encode([
                'status'  => 'failed',
                'message' => 'Role ini masih dipakai oleh user, pindahkan usernya dulu ke role lain'
            ]);
            return;
        }

        $this->Role_model->delete($id);

        $this->session->set_flashdata('success', 'Role berhasil dihapus');
        echo json_encode([
            'status'  => 'success',
            'message' => 'Role berhasil dihapus'
        ]);
    }
}