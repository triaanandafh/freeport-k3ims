<?php

function active($page, $current)
{
    return $page === $current ? 'active' : '';
}

?>

<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion"
    id="accordionSidebar">

    <a class="sidebar-brand d-flex align-items-center justify-content-center"
        href="dashboard.php">

        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-hard-hat"></i>
        </div>

        <div class="sidebar-brand-text mx-3">
            K3 IMS
        </div>

    </a>

    <hr class="sidebar-divider my-0">

    <li class="nav-item <?= active('dashboard', $current_page) ?>">

        <a class="nav-link" href="dashboard.php">

            <i class="fas fa-fw fa-tachometer-alt"></i>

            <span>Dashboard</span>

        </a>

    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">
        Master Data
    </div>

    <li class="nav-item">

        <a class="nav-link collapsed"
            href="#"
            data-toggle="collapse"
            data-target="#collapseMaster">

            <i class="fas fa-database"></i>

            <span>Master Data</span>

        </a>

        <div id="collapseMaster" class="collapse">

            <div class="bg-white py-2 collapse-inner rounded">

                <a class="collapse-item" href="departments.php">Departments</a>

                <a class="collapse-item" href="employees.php">Employees</a>

                <a class="collapse-item" href="users.php">Users</a>

            </div>

        </div>

    </li>

    <div class="sidebar-heading">
        K3 Management
    </div>

    <li class="nav-item">

        <a class="nav-link collapsed"
            href="#"
            data-toggle="collapse"
            data-target="#collapseK3">

            <i class="fas fa-shield-alt"></i>

            <span>K3 Data</span>

        </a>

        <div id="collapseK3" class="collapse">

            <div class="bg-white py-2 collapse-inner rounded">

                <a class="collapse-item" href="reports.php">Reports</a>

                <a class="collapse-item" href="documents.php">Documents</a>

            </div>

        </div>

    </li>

    <div class="sidebar-heading">
        Audit
    </div>

    <li class="nav-item">

        <a class="nav-link" href="audits.php">

            <i class="fas fa-clipboard-check"></i>

            <span>Audit Management</span>

        </a>

    </li>

    <hr class="sidebar-divider">

    <li class="nav-item">

        <a class="nav-link" href="logout.php">

            <i class="fas fa-sign-out-alt"></i>

            <span>Logout</span>

        </a>

    </li>

</ul>