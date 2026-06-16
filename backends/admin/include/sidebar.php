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


    <!-- MASTER DATA -->
    <div class="sidebar-heading">
        Master Data
    </div>

    <li class="nav-item">

        <a class="nav-link collapsed"
            href="#"
            data-toggle="collapse"
            data-target="#collapseMaster"
            aria-expanded="true">

            <i class="fas fa-fw fa-database"></i>
            <span>Master Data</span>

        </a>

        <div id="collapseMaster"
            class="collapse"
            data-parent="#accordionSidebar">

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


    <!-- SAFETY OPERATIONS -->
    <div class="sidebar-heading">
        Safety Operations
    </div>

    <li class="nav-item">

        <a class="nav-link collapsed"
            href="#"
            data-toggle="collapse"
            data-target="#collapseSafety"
            aria-expanded="true">

            <i class="fas fa-fw fa-shield-alt"></i>
            <span>Safety Operations</span>

        </a>

        <div id="collapseSafety"
            class="collapse"
            data-parent="#accordionSidebar">

            <div class="bg-white py-2 collapse-inner rounded">

                <a class="collapse-item <?= active('safety_checks', $current_page) ?>"
                    href="safety_checks.php">

                    Safety Checks

                </a>

                <a class="collapse-item <?= active('reports', $current_page) ?>"
                    href="reports.php">

                    Incident Reports

                </a>

                <a class="collapse-item <?= active('documents', $current_page) ?>"
                    href="documents.php">

                    K3 Documents

                </a>

            </div>

        </div>

    </li>


    <hr class="sidebar-divider">


    <!-- AUDIT & COMPLIANCE -->
    <div class="sidebar-heading">
        Compliance
    </div>

    <li class="nav-item">

        <a class="nav-link collapsed"
            href="#"
            data-toggle="collapse"
            data-target="#collapseAudit"
            aria-expanded="true">

            <i class="fas fa-fw fa-clipboard-check"></i>
            <span>Audit & Compliance</span>

        </a>

        <div id="collapseAudit"
            class="collapse"
            data-parent="#accordionSidebar">

            <div class="bg-white py-2 collapse-inner rounded">

                <a class="collapse-item <?= active('audits', $current_page) ?>"
                    href="audits.php">

                    Audit Management

                </a>

                <a class="collapse-item <?= active('audit_checklists', $current_page) ?>"
                    href="audit_checklists.php">

                    Audit Checklists

                </a>

            </div>

        </div>

    </li>


    <hr class="sidebar-divider">


    <!-- LOGOUT -->
    <li class="nav-item">

        <a class="nav-link" href="../logout.php">

            <i class="fas fa-fw fa-sign-out-alt"></i>
            <span>Logout</span>

        </a>

    </li>


    <hr class="sidebar-divider d-none d-md-block">


    <!-- TOGGLE -->
    <div class="text-center d-none d-md-inline">

        <button class="rounded-circle border-0"
            id="sidebarToggle"></button>

    </div>

</ul>