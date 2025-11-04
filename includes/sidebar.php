<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="dashboard.php">
        <div class="sidebar-brand-icon">
            <img src="assets/img/logo.png" alt="Logo Gestor de Mensalidades" style="height: 40px;">
        </div>
        <div class="sidebar-brand-text mx-3">Gestor Mensalidades</div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Dashboard -->
    <li class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
        <a class="nav-link" href="dashboard.php">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span></a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        Operações
    </div>

    <!-- Nav Item - Mensalidades -->
    <li class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'mensalidades.php' ? 'active' : '' ?>">
        <a class="nav-link" href="mensalidades.php">
            <i class="fas fa-fw fa-calendar"></i>
            <span>Mensalidades</span></a>
    </li>

    <!-- Nav Item - Clientes -->
    <li class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'clientes.php' ? 'active' : '' ?>">
        <a class="nav-link" href="clientes.php">
            <i class="fas fa-fw fa-users"></i>
            <span>Clientes</span></a>
    </li>

    <!-- Nav Item - Planos -->
    <li class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'planos.php' ? 'active' : '' ?>">
        <a class="nav-link" href="planos.php">
            <i class="fas fa-fw fa-list"></i>
            <span>Planos</span></a>
    </li>

    <!-- Nav Item - Fornecedores -->
    <li class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'fornecedores.php' ? 'active' : '' ?>">
        <a class="nav-link" href="fornecedores.php">
            <i class="fas fa-fw fa-truck"></i>
            <span>Fornecedores</span></a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        Configurações
    </div>

    <!-- Nav Item - Templates -->
    <li class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'templates.php' ? 'active' : '' ?>">
        <a class="nav-link" href="templates.php">
            <i class="fas fa-fw fa-envelope"></i>
            <span>Templates de Mensagem</span></a>
    </li>

    <!-- Nav Item - Configurações -->
    <li class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'configuracoes.php' ? 'active' : '' ?>">
        <a class="nav-link" href="configuracoes.php">
            <i class="fas fa-fw fa-cog"></i>
            <span>Configurações</span></a>
    </li>

    <!-- Nav Item - Perfil -->
    <li class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'perfil.php' ? 'active' : '' ?>">
        <a class="nav-link" href="perfil.php">
            <i class="fas fa-fw fa-user"></i>
            <span>Perfil</span></a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider d-none d-md-block">

    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>
<!-- End of Sidebar -->