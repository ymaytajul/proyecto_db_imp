<!-- Sidebar -->
<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="fas fa-home"></i> Inicio
                </a>
            </li>

            <!-- Módulo Impuesto Predial -->
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#submenuPredial" role="button" aria-expanded="true"
                    aria-controls="submenuPredial">
                    <i class="fas fa-building"></i> Impuesto Predial
                    <i class="fas fa-chevron-down float-end mt-1" style="font-size: 0.8em;"></i>
                </a>
                <div class="collapse show" id="submenuPredial">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a class="nav-link active" href="../../modules/predial/contribuyentes.php">
                                <i class="fas fa-users"></i> Maestro Contribuyentes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../../modules/predial/declaraciones.php">
                                <i class="fas fa-file-invoice-dollar"></i> Declaración Jurada
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="#">
                    <i class="fas fa-cogs"></i> Configuración
                </a>
            </li>
        </ul>
    </div>
</nav>

<!-- Main Content Start -->
<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">