<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIADT - Menú Principal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/siadt_styles.css" rel="stylesheet">
</head>

<body>

    <!-- Header Logos y Título -->
    <div class="bg-white border-bottom p-2 d-flex justify-content-between align-items-center">
        <!-- Logo Izquierda -->
        <div class="d-flex align-items-center">
            <div class="fw-bold fs-4 text-secondary me-3">SIADT</div>
            <div class="border-start ps-3 text-primary">Sistema Integrado de Administración Tributaria</div>
        </div>
        <!-- Logo Derecha -->
        <div class="d-flex align-items-center text-end">
            <div class="fw-bold text-success me-2">Municipalidad Distrital de Pacocha</div>
            <i class="fas fa-university fa-2x text-success"></i>
        </div>
    </div>

    <!-- Barra Azul Título Menú -->
    <div class="bg-primary text-white text-center py-1 fw-bold text-uppercase"
        style="background: linear-gradient(to bottom, #4a89dc, #2e6da4);">
        Menú Principal
    </div>

    <!-- Barra Info Usuario -->
    <div class="bg-light border-bottom py-1 px-2 small">
        <strong>Sistema Integrado de Administración Tributaria - SIADT v3.00</strong> |
        Usuario: [ADMIN] |
        [Municipalidad Distrital de Pacocha]
    </div>

    <!-- Contenedor Principal -->
    <div class="dashboard-container">

        <!-- Grid Central de Botones -->
        <div class="dashboard-grid-area">

            <a href="#" class="grid-btn">
                <i class="fas fa-users"></i>
                <span>Atención al Público</span>
            </a>

            <!-- ENLACE AL MODULO REAL -->
            <a href="modules/predial/contribuyentes.php" class="grid-btn">
                <i class="fas fa-building"></i>
                <span>Impuesto Predial</span>
            </a>

            <a href="#" class="grid-btn">
                <i class="fas fa-home"></i>
                <span>Arbitrios</span>
            </a>

            <a href="#" class="grid-btn">
                <i class="fas fa-file-contract"></i>
                <span>Fraccionamiento Tributario</span>
            </a>

            <a href="#" class="grid-btn">
                <i class="fas fa-handshake"></i>
                <span>Impuesto de Alcabala</span>
            </a>

            <a href="#" class="grid-btn">
                <i class="fas fa-certificate"></i>
                <span>Licencias de Funcionamiento</span>
            </a>

            <a href="#" class="grid-btn">
                <i class="fas fa-envelope-open-text"></i>
                <span>Notificaciones O/P y R/D</span>
            </a>

            <a href="#" class="grid-btn">
                <i class="fas fa-search"></i>
                <span>Fiscalización</span>
            </a>

            <a href="#" class="grid-btn">
                <i class="fas fa-gavel"></i>
                <span>Multas Tributarias</span>
            </a>

            <a href="#" class="grid-btn">
                <i class="fas fa-archive"></i>
                <span>Cobranza Coactiva</span>
            </a>

            <a href="#" class="grid-btn">
                <i class="fas fa-paper-plane"></i>
                <span>Emisiones Masivas</span>
            </a>

            <a href="#" class="grid-btn">
                <i class="fas fa-credit-card"></i>
                <span>Saldos de Cuentas Corrientes</span>
            </a>

            <a href="#" class="grid-btn">
                <i class="fas fa-chart-line"></i>
                <span>Reportes y Estadísticas</span>
            </a>

            <a href="#" class="grid-btn">
                <i class="fas fa-database"></i>
                <span>Mantenimiento de Tablas</span>
            </a>

        </div>

        <!-- Panel Derecho: Admin Sistema -->
        <div class="dashboard-sidebar-right position-relative">
            <div class="fw-bold small text-center mb-3">ADMINISTRACIÓN DEL SISTEMA</div>

            <a href="#" class="right-panel-btn">
                <i class="fas fa-desktop"></i>
                1. Soporte y Mantenimiento
            </a>

            <a href="#" class="right-panel-btn">
                <i class="fas fa-link"></i>
                2. Cambio de Clave
            </a>

            <a href="login.php" class="logout-btn fw-bold text-decoration-none d-block">
                Salir del Sistema
            </a>
        </div>

    </div>

    <!-- Footer -->
    <div class="siadt-footer" style="background-color: #004080; color: white; border-top: none;">
        <span class="me-3">http://munipacocha.gob.pe/index</span>
        <span>Copyright HAROLD GERARDO INGA BRUZ</span>
    </div>

</body>

</html>