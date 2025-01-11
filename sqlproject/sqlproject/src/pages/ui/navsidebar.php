<link rel="stylesheet" href="../../assets/vendors/select2/select2.min.css">
<link rel="stylesheet" href="../../assets/vendors/select2-bootstrap-theme/select2-bootstrap.min.css">

<nav class="sidebar sidebar-offcanvas" id="sidebar">
<ul class="nav">
  <li class="nav-item">
    <a class="nav-link" href="/sqlproject/src/admin-dashboard.php">
      <i class="icon-grid menu-icon"></i>
      <span class="menu-title">Dashboard</span>
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link" data-bs-toggle="collapse" href="#ui-basic" aria-expanded="false" aria-controls="ui-basic">
      <i class="mdi mdi-account-multiple me-3"></i>
      <span class="menu-title">Utilizadores</span>
      <i class="menu-arrow ml-0"></i>
    </a>
    <div class="collapse" id="ui-basic">
      <ul class="nav flex-column sub-menu">
        <li class="nav-item"> <a class="nav-link" href="/sqlproject/src/pages/tables/clients-table.php">Clientes</a></li>
        <li class="nav-item"> <a class="nav-link" href="/sqlproject/src/pages/tables/agencies-table.php">Agências</a></li>
        <li class="nav-item"> <a class="nav-link" href="/sqlproject/src/pages/tables/organizations-table.php">Organizações</a></li>
      </ul>
    </div>
  </li>
  <li class="nav-item">
    <a class="nav-link" data-bs-toggle="collapse" href="#ui-basic" aria-expanded="false" aria-controls="ui-basic">
      <i class="mdi mdi-briefcase me-3"></i>
      <span class="menu-title">Contratos</span>
      <i class="menu-arrow ml-0"></i>
    </a>
    <div class="collapse" id="ui-basic">
      <ul class="nav flex-column sub-menu">
        <li class="nav-item"> <a class="nav-link" href="/sqlproject/src/pages/tables/contracts-table.php">Contratos</a></li>
        <li class="nav-item"> <a class="nav-link" href="/sqlproject/src/pages/tables/products-table.php">Produtos</a></li>
        <li class="nav-item"> <a class="nav-link" href="/sqlproject/src/pages/tables/licenses-table.php">Licenças</a></li>
        <li class="nav-item"> <a class="nav-link" href="/sqlproject/src/pages/tables/analysis-table.php">Análises</a></li>
      </ul>
    </div>
  </li>
</ul>
</nav>