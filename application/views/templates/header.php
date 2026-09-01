<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>IT Support Ticket <?= isset($title) ? '| ' . $title : '' ?></title>

  <!-- Google Font -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="<?= base_url('assets/adminlte/plugins/fontawesome-free/css/all.min.css') ?>">
  <!-- DataTables -->
  <link rel="stylesheet" href="<?= base_url('assets/adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') ?>">
  <link rel="stylesheet" href="<?= base_url('assets/adminlte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') ?>">
  <link rel="stylesheet" href="<?= base_url('assets/adminlte/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') ?>">
  <!-- Theme style -->
  <link rel="stylesheet" href="<?= base_url('assets/adminlte/dist/css/adminlte.min.css') ?>">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="<?= base_url('assets/adminlte/plugins/overlayScrollbars/css/OverlayScrollbars.min.css') ?>">

  <!-- Daterangepicker CSS (boleh di head, tidak butuh jQuery) -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.1/daterangepicker.css">

  
  <!-- Bootstrap Datepicker -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/css/bootstrap-datepicker.min.css">
  
  <!-- iCheck for checkboxes and radio inputs -->
  <link rel="stylesheet" href="<?= base_url('assets/adminlte/plugins/icheck-bootstrap/icheck-bootstrap.min.css') ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/skins/square/blue.css">
  <!-- Select2 -->
  <link rel="stylesheet" href="<?= base_url('assets/adminlte/plugins/select2/css/select2.min.css') ?>">
  <link rel="stylesheet" href="<?= base_url('assets/adminlte/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') ?>">

  <!-- Sweet Alert -->
  <link rel="stylesheet" href="<?= base_url('assets/adminlte/plugins/sweetalert2/sweetalert2.min.css') ?>">
  <script src="<?= base_url('assets/adminlte/plugins/sweetalert2/sweetalert2.min.js') ?>"></script>

  <link href="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/css/bootstrap4-toggle.min.css" rel="stylesheet">

  <style>
    .select2-container--default .select2-selection--single {
        height: 38px !important;
        padding: 0 !important;
        border: 1px solid #ced4da !important;
        border-radius: 0.25rem !important;
        display: flex !important;
        align-items: center !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px !important;
        padding-left: 12px !important;
        padding-right: 30px !important;
        color: #495057 !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
        right: 6px !important;
    }
  </style>

</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
      <ul class="navbar-nav">
          <li class="nav-item">
              <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                  <i class="fas fa-bars"></i>
              </a>
          </li>
      </ul>

      <ul class="navbar-nav ml-auto">
          <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown">
                  <i class="fas fa-user-circle mr-1"></i>
                  <?= $this->session->userdata('role_name') ?: $this->session->userdata('role') ?>
              </a>
              <div class="dropdown-menu dropdown-menu-right">
                  <a class="dropdown-item" href="<?= base_url('profile') ?>">
                      <i class="fas fa-id-card mr-2"></i> Profile Saya
                  </a>
                  <div class="dropdown-divider"></div>
                  <a class="dropdown-item text-danger" href="javascript:void(0)" onclick="logout()">
                      <i class="fas fa-sign-out-alt mr-2"></i> Logout
                  </a>
              </div>
          </li>
      </ul>
  </nav>

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="<?= base_url('dashboard') ?>" class="brand-link">
      <img src="<?= base_url('assets/adminlte/dist/img/AdminLTELogo.png') ?>" alt="Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light">IT Support</span>
    </a>

    <div class="sidebar">
      <nav class="mt-2">
        <?php $current_section = strtolower($this->uri->segment(1)); ?>
        <?php $CI =& get_instance(); ?>
        <?php $CI->load->model('Menu_model'); ?>
        <?php $sidebar_menus = $CI->Menu_model->get_for_role($CI->session->userdata('role_id'), $CI->session->userdata('role')); ?>
        <?php
        $sidebar_children = [];
        $sidebar_roots = [];
        foreach ($sidebar_menus as $sidebar_menu) {
          if ((int) $sidebar_menu->parent_id === 0) {
            $sidebar_roots[] = $sidebar_menu;
          } else {
            $sidebar_children[(int) $sidebar_menu->parent_id][] = $sidebar_menu;
          }
        }
        ?>
        <ul id="sidebar-menu-list" class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <li class="nav-item">
            <a href="<?= base_url('dashboard') ?>" class="nav-link">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>Dashboard</p>
            </a>
          <!-- <li class="nav-header">MASTER DATA</li> -->
          <?php foreach ($sidebar_roots as $menu): ?>
            <?php $children = $sidebar_children[(int) $menu->id] ?? []; ?>
            <?php if (empty($children)): ?>
              <?php if ($menu->url === 'javascript:;'): ?>
                <li class="nav-header dynamic-sidebar-menu">
                  <?= htmlspecialchars($menu->name) ?>
                </li>
              <?php else: ?>
                <li class="nav-item dynamic-sidebar-menu">
                  <a href="<?= base_url($menu->url) ?>" class="nav-link <?= $current_section === strtolower(explode('/', $menu->url)[0]) ? 'active' : '' ?>">
                    <?php if (!empty($menu->icon)): ?><i class="nav-icon <?= htmlspecialchars($menu->icon) ?>"></i><?php endif; ?>
                    <p><?= htmlspecialchars($menu->name) ?></p>
                  </a>
                </li>
              <?php endif; ?>
            <?php else: ?>
              <?php $parent_active = false; 
                foreach ($children as $child) { 
                  if ($current_section === strtolower(explode('/', $child->url)[0])) { 
                    $parent_active = true; break; 
                  } 
                } ?>
              <li class="nav-item dynamic-sidebar-menu has-treeview <?= $parent_active ? 'menu-open' : '' ?>">
                <a href="javascript:;" class="nav-link <?= $parent_active ? 'active' : '' ?>">
                  <?php if (!empty($menu->icon)): ?><i class="nav-icon <?= htmlspecialchars($menu->icon) ?>"></i><?php endif; ?>
                  <p><?= htmlspecialchars($menu->name) ?><i class="right fas fa-angle-left"></i></p>
                </a>
                <ul class="nav nav-treeview">
                  <?php foreach ($children as $child): ?>
                    <li class="nav-item">
                      <a href="<?= base_url($child->url) ?>" class="nav-link <?= $current_section === strtolower(explode('/', $child->url)[0]) ? 'active' : '' ?>">
                        <?php if (!empty($child->icon)): ?><i class="nav-icon <?= htmlspecialchars($child->icon) ?>"></i><?php endif; ?>
                        <p><?= htmlspecialchars($child->name) ?></p>
                      </a>
                    </li>
                  <?php endforeach; ?>
                </ul>
              </li>
            <?php endif; ?>
          <?php endforeach; ?>

        </ul>
      </nav>
    </div>
  </aside>

  <!-- Content Wrapper -->
  <div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0"><?= isset($title) ? $title : 'Dashboard' ?></h1>
          </div>
        </div>
      </div>
    </div>

    <div class="content">
      <div class="container-fluid">
        <?php if($this->session->flashdata('success')): ?>
            <script>
                Swal.fire({
                    title: 'Sukses!',
                    text: '<?= $this->session->flashdata('success'); ?>',
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
            </script>
        <?php endif; ?>

        <?php if($this->session->flashdata('error')): ?>
            <script>
                Swal.fire({
                    title: 'Gagal!',
                    text: '<?= $this->session->flashdata('error'); ?>',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            </script>
        <?php endif; ?>