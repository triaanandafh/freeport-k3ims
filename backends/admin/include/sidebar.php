<?php

function active($page, $current)
{
    return $page === $current ? 'active' : '';
}

?>

<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion"
    id="accordionSidebar">

    <!-- Brand -->
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

    <!-- Dashboard -->
    <li class="nav-item <?= active('dashboard', $current_page) ?>">
        <a class="nav-link" href="dashboard.php">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </li>

    <hr class="sidebar-divider">

    <!-- Master Data -->
    <div class="sidebar-heading">
        Master Data
    </div>

    <li class="nav-item">

        <a class="nav-link collapsed"
            href="#"
            data-toggle="collapse"
            data-target="#collapseMaster">

            <i class="fas fa-users"></i>
            <span>Master Data</span>

        </a>

        <div id="collapseMaster" class="collapse">

            <div class="bg-white py-2 collapse-inner rounded">

                <a class="collapse-item <?= active('departments', $current_page) ?>"
                    href="departments.php">
                    Departments
                </a>

                <a class="collapse-item <?= active('employees', $current_page) ?>"
                    href="employees.php">
                    Employees
                </a>

                <a class="collapse-item <?= active('users', $current_page) ?>"
                    href="users.php">
                    System Users
                </a>

            </div>

        </div>

    </li>

    <hr class="sidebar-divider">

    <!-- Operational Data -->
    <div class="sidebar-heading">
        Operational
    </div>

    <li class="nav-item">

        <a class="nav-link collapsed"
            href="#"
            data-toggle="collapse"
            data-target="#collapseOperational">

            <i class="fas fa-clipboard-list"></i>
            <span>Operations</span>

        </a>

        <div id="collapseOperational" class="collapse">

            <div class="bg-white py-2 collapse-inner rounded">

                <a class="collapse-item <?= active('reports', $current_page) ?>"
                    href="reports.php">
                    Reports
                </a>

                <a class="collapse-item <?= active('documents', $current_page) ?>"
                    href="documents.php">
                    Documents
                </a>

                <a class="collapse-item <?= active('audits', $current_page) ?>"
                    href="audits.php">
                    Audits
                </a>

            </div>

        </div>

    </li>

    <hr class="sidebar-divider">

    <!-- Logout -->
    <li class="nav-item">
        <a class="nav-link" href="../logout.php">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </li>

    <hr class="sidebar-divider d-none d-md-block">

    <!-- Sidebar Toggle -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>