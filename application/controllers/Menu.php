<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Menu extends CI_Controller {

    public function __construct() {
        parent::__construct();
        // TODO: aktifkan kembali sebelum production!
        // if (!$this->session->userdata('logged_in')) {
        //     redirect('login');
        // }
        $this->load->model('Menu_model');
        $this->load->library('form_validation');
        $this->output->set_content_type('application/json'); // default, akan di-override oleh index()
    }

    public function index(){
        $this->output->set_content_type('text/html'); // index() render HTML, bukan JSON
        $data['title'] = 'Master Menu';
        $data['menu'] = $this->Menu_model->get_data();
        $data['parent_menus'] = $this->Menu_model->get_parent_menus();
        $data['status_list'] = $this->Menu_model->get_status();
        $this->load->view('templates/header', $data);
        $this->load->view('menu/index', $data);
        $this->load->view('templates/footer');
    }

    public function get_menu_data(){
        $menus = $this->Menu_model->get_data();
        $menus = $this->order_menus($menus);
        $has_children = [];
        foreach ($menus as $menu) {
            if ((int) $menu->parent_id !== 0) {
                $has_children[(int) $menu->parent_id] = true;
            }
        }

        $result = [];
        foreach ($menus as $menu){
            $icon_preview = $menu->icon
                ? '<i class="' . htmlspecialchars($menu->icon) . ' mr-2"></i> <small class="text-muted">' . htmlspecialchars($menu->icon) . '</small>'
                : '<span class="text-muted">-</span>';

            $status_name = $menu->product_status_name ?: '-';
            $badge_class = 'badge-light';
            if (stripos($status_name, 'nonaktif') !== false) {
                $badge_class = 'badge-secondary';
            } elseif (stripos($status_name, 'aktif') !== false) {
                $badge_class = 'badge-success';
            }
            $status_badge = '<span class="badge ' . $badge_class . '">' . htmlspecialchars($status_name) . '</span>';

            $id_safe = (int) $menu->id;

            $action = '
                <button class="btn btn-warning btn-sm btn-edit" data-id="' . $id_safe . '"><i class="fas fa-edit"></i> Edit</button>
                <button class="btn btn-danger btn-sm btn-delete" data-id="' . $id_safe . '"><i class="fas fa-trash"></i> Hapus</button>
            ';
            $result[] = [
                'name'     => $menu->parent_id === '0' || (int) $menu->parent_id === 0
                    ? '<strong>' . htmlspecialchars($menu->name) . '</strong>'
                    : '<span class="ml-4">' . htmlspecialchars($menu->name) . '</span>',
                'url'      => htmlspecialchars($menu->url),
                'icon'     => $icon_preview,
                'order_no' => (int) $menu->order_no,
                'parent'   => $menu->parent_id ? $this->get_parent_name($menu->parent_id, $menus) : '<span class="text-muted">Menu Utama</span>',
                'status'   => $status_badge,
                'action'   => $action,
            ];
        }

        echo json_encode($result);
    }

    private function order_menus($menus) {
        $roots = [];
        $children = [];
        foreach ($menus as $menu) {
            if ((int) $menu->parent_id === 0) {
                $roots[] = $menu;
            } else {
                $children[(int) $menu->parent_id][] = $menu;
            }
        }

        usort($roots, [$this, 'compare_menu_order']);
        foreach ($children as &$items) {
            usort($items, [$this, 'compare_menu_order']);
        }
        unset($items);

        $ordered = [];
        foreach ($roots as $root) {
            $ordered[] = $root;
            foreach ($children[(int) $root->id] ?? [] as $child) {
                $ordered[] = $child;
            }
        }

        foreach ($menus as $menu) {
            if (!in_array($menu, $ordered, true)) {
                $ordered[] = $menu;
            }
        }
        return $ordered;
    }

    private function compare_menu_order($first, $second) {
        return ((int) $first->order_no <=> (int) $second->order_no)
            ?: ((int) $first->id <=> (int) $second->id);
    }

    public function get_sidebar_data() {
        $menus = $this->Menu_model->get_for_role(
            $this->session->userdata('role_id'),
            $this->session->userdata('role')
        );

        $result = [];
        foreach ($menus as $menu) {
            $result[] = [
                'id'        => (int) $menu->id,
                'name'      => $menu->name,
                'url'       => $menu->url,
                'icon'      => $menu->icon,
                'parent_id' => (int) $menu->parent_id,
            ];
        }

        echo json_encode($result);
    }

    private function get_parent_name($parent_id, $menus) {
        foreach ($menus as $menu) {
            if ((int) $menu->id === (int) $parent_id) {
                return htmlspecialchars($menu->name);
            }
        }
        return '<span class="text-danger">Parent tidak ditemukan</span>';
    }

    public function save_menu(){
        $id = $this->input->post('id');
        $id = ($id && $id !== '0') ? (int) $id : null;

        $this->form_validation->set_rules('name', 'Nama Menu', 'required|trim|max_length[100]');
        $this->form_validation->set_rules('url', 'URL', 'required|trim|max_length[150]');
        $this->form_validation->set_rules('status', 'Status', 'required|numeric');

        if ($this->form_validation->run() == FALSE) {
            echo json_encode([
                'status' => 'error_validation',
                'errors' => $this->form_validation->error_array()
            ]);
            return;
        }

        $parent_id_raw = trim((string) $this->input->post('parent_id'));
        if ($parent_id_raw === '') {
            $parent_id_raw = '0';
        }
        if (!ctype_digit($parent_id_raw)) {
            echo json_encode([
                'status' => 'error_validation',
                'errors' => ['parent_id' => 'Parent menu tidak valid']
            ]);
            return;
        }

        $parent_id = (int) $parent_id_raw;
        if ($id && $parent_id === $id) {
            echo json_encode([
                'status' => 'error_validation',
                'errors' => ['parent_id' => 'Menu tidak boleh menjadi parent untuk dirinya sendiri']
            ]);
            return;
        }

        $parent = $parent_id > 0 ? $this->Menu_model->get_by_id($parent_id) : null;
        if ($parent_id > 0 && !$parent) {
            echo json_encode([
                'status' => 'error_validation',
                'errors' => ['parent_id' => 'Parent menu tidak ditemukan']
            ]);
            return;
        }

        if ($parent && (int) $parent->parent_id !== 0) {
            echo json_encode([
                'status' => 'error_validation',
                'errors' => ['parent_id' => 'Parent harus berupa menu utama']
            ]);
            return;
        }

        $url = strtolower(trim($this->input->post('url')));

        if ($url !== 'javascript:;' && !preg_match('/^[a-z0-9_-]+(?:\/[a-z0-9_-]+)*$/', $url)) {
            echo json_encode([
                'status' => 'error_validation',
                'errors' => ['url' => 'URL hanya boleh berisi path controller dengan huruf, angka, garis bawah, strip, atau javascript:;']
            ]);
            return;
        }

        if ($this->Menu_model->is_url_exists($url, $id)) {
            echo json_encode([
                'status' => 'error_validation',
                'errors' => ['url' => 'URL sudah digunakan menu lain']
            ]);
            return;
        }

        $current_menu = $id ? $this->Menu_model->get_by_id($id) : null;
        $order_no = $current_menu && (int) $current_menu->parent_id === $parent_id
            ? (int) $current_menu->order_no
            : $this->Menu_model->get_next_order($parent_id, $id);

        $data = [
            'name'     => $this->input->post('name'),
            'url'      => $url,
            'icon'     => $this->input->post('icon'),
            'parent_id' => $parent_id,
            'order_no' => $order_no,
            'status'   => (int) $this->input->post('status'),
        ];

        if ($id) {
            $this->Menu_model->update($id, $data);
            $message = 'Menu berhasil diupdate';
        } else {
            $this->Menu_model->insert($data);
            $message = 'Menu berhasil ditambahkan';
        }

        echo json_encode([
            'status'  => 'success',
            'message' => $message
        ]);
    }

    public function get($id) {
        if (!is_numeric($id)) {
            echo json_encode(['status' => 'failed', 'message' => 'ID tidak valid']);
            return;
        }

        $menu = $this->Menu_model->get_by_id($id);

        if (!$menu) {
            echo json_encode(['status' => 'failed', 'message' => 'Menu tidak ditemukan']);
            return;
        }

        echo json_encode($menu);
    }

    public function get_parent_menus() {
        $exclude_id = (int) $this->input->get('exclude_id');
        $parents = $this->Menu_model->get_parent_menus($exclude_id ?: null);
        $result = [];

        foreach ($parents as $parent) {
            $result[] = [
                'id'   => (int) $parent->id,
                'name' => $parent->name,
            ];
        }

        echo json_encode($result);
    }

    public function delete_menu($id) {
        if (!is_numeric($id)) {
            echo json_encode(['status' => 'failed', 'message' => 'ID tidak valid']);
            return;
        }

        if ($this->Menu_model->is_used_in_permission($id) > 0) {
            echo json_encode([
                'status'  => 'failed',
                'message' => 'Menu ini sudah dipakai di hak akses role, hapus permission-nya dulu'
            ]);
            return;
        }

        $this->Menu_model->delete($id);
        echo json_encode([
            'status'  => 'success',
            'message' => 'Menu berhasil dihapus'
        ]);
    }
}