<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

    <button id="sidebarToggleTop"
        class="btn btn-link d-md-none rounded-circle mr-3">

        <i class="fa fa-bars"></i>

    </button>

    <form class="d-none d-sm-inline-block form-inline mr-auto ml-md-3">

        <div class="input-group">

            <input type="text"
                class="form-control bg-light border-0 small"
                placeholder="Search...">

            <div class="input-group-append">

                <button class="btn btn-primary">

                    <i class="fas fa-search fa-sm"></i>

                </button>

            </div>

        </div>

    </form>

    <ul class="navbar-nav ml-auto">

        <li class="nav-item dropdown no-arrow mx-1">

            <a class="nav-link dropdown-toggle" href="#">

                <i class="fas fa-bell fa-fw"></i>

                <span class="badge badge-danger badge-counter">3</span>

            </a>

        </li>

        <div class="topbar-divider d-none d-sm-block"></div>

        <li class="nav-item dropdown no-arrow">

            <a class="nav-link dropdown-toggle" href="#">

                <span class="mr-2 d-none d-lg-inline text-gray-600 small">

                    <?= htmlspecialchars($_SESSION['fullname']) ?>

                </span>

                <img class="img-profile rounded-circle"
                    src="../img/undraw_profile.svg">

            </a>

        </li>

    </ul>

</nav>