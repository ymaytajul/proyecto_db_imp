<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Integrado de Administración Tributaria - SIADT</title>
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom Style -->
    <link href="/Sistema_Predial_Grupo8/assets/css/siadt_styles.css?v=<?php echo time(); ?>" rel="stylesheet">
</head>

<body>

    <!-- 1. Top Red Bar (Window Title) -->
    <div class="window-title-bar">
        <span>Sistema Integrado de Administración Tributaria - SIADT v3.00 Usuario:[HAROLD] [Municipalidad Distrital de
            Pacocha]</span>
        <div class="window-controls">
            <i class="fas fa-window-minimize"></i>
            <i class="fas fa-window-maximize mx-2"></i>
            <i class="fas fa-times"></i>
        </div>
    </div>

    <!-- 2. Main White Header -->
    <div class="main-header">
        <div class="header-left">
            <div class="logo-siadt">SIADT</div>
            <div class="logo-pixels"></div> <!-- Gradient bar -->
        </div>
        <div class="header-center">
            Sistema Integrado de Administración Tributaria
        </div>
        <div class="header-right">
            <span>Municipalidad Distrital de Pacocha</span>
            <i class="fas fa-shield-alt fa-2x ms-2 text-secondary"></i> <!-- Shield icon placeholder -->
        </div>
    </div>

    <!-- 3. Menu Bar -->
    <div class="menu-bar">
        MENU PRINCIPAL
    </div>

    <!-- 4. Main Workspace -->
    <div class="app-container">

        <!-- Center Grid Area -->
        <div class="modules-area">
            <div class="modules-header-strip">
                ACCESO POR MÓDULOS O ÁREAS
            </div>

            <div class="modules-grid">
                <!-- Row 1 -->
                <a href="modules/atencion/index.php" class="module-btn">
                    <i class="fas fa-user-pen"></i>
                    <span>Atención al Público</span>
                </a>

                <!-- PREDIAL MODULE -->
                <a href="modules/predial/contribuyentes.php" class="module-btn">
                    <i class="fas fa-city"></i>
                    <span>Impuesto Predial</span>
                </a>

                <a href="#" class="module-btn">
                    <i class="fas fa-home"></i>
                    <span>Arbitrios</span>
                </a>

                <a href="#" class="module-btn">
                    <i class="fas fa-file-contract"></i>
                    <span>Fraccionamiento Tributario</span>
                </a>

                <a href="#" class="module-btn">
                    <i class="fas fa-handshake"></i>
                    <span>Impuesto de Alcabala</span>
                </a>

                <!-- Row 2 -->
                <a href="#" class="module-btn">
                    <i class="fas fa-certificate"></i>
                    <span>Licencias de Funcionamiento</span>
                </a>

                <a href="#" class="module-btn">
                    <i class="fas fa-file-alt"></i>
                    <span>Notificaciones O/P y R/D</span>
                </a>

                <a href="#" class="module-btn">
                    <i class="fas fa-user-check"></i>
                    <span>Fiscalización</span>
                </a>

                <a href="#" class="module-btn">
                    <i class="fas fa-balance-scale"></i>
                    <span>Multas Tributarias</span>
                </a>

                <div class="module-btn" style="background: #aaa; border: none; box-shadow: none;">
                    <!-- Empty/Disabled placeholder -->
                </div>

                <!-- Row 3 -->
                <a href="#" class="module-btn">
                    <i class="fas fa-archive"></i>
                    <span>Cobranza Coactiva</span>
                </a>

                <a href="#" class="module-btn">
                    <i class="fas fa-mail-bulk"></i>
                    <span>Emisiones Masivas</span>
                </a>

                <a href="#" class="module-btn">
                    <i class="fas fa-credit-card"></i>
                    <span>Saldos de Cuentas Corrientes</span>
                </a>

                <a href="#" class="module-btn">
                    <i class="fas fa-chart-bar"></i>
                    <span>Reportes y Estadísticas</span>
                </a>

                <a href="#" class="module-btn">
                    <i class="fas fa-database"></i>
                    <span>Mantenimiento de Tablas</span>
                </a>

            </div>
        </div>

        <!-- Right Admin Area -->
        <div class="admin-area">
            <div class="admin-header-strip">
                ADMINISTRACIÓN DEL SISTEMA
            </div>

            <a href="#" class="admin-btn">
                <i class="fas fa-desktop"></i>
                <span>1. Soporte y Mantenimiento</span>
            </a>

            <a href="#" class="admin-btn">
                <i class="fas fa-link"></i>
                <span>2. Cambio de Clave</span>
            </a>

            <!-- Exit Button anchored to bottom -->
            <a href="login.php" class="exit-btn">
                Salir del Sistema
            </a>
        </div>

    </div>

    <!-- 5. Footer -->
    <div class="main-footer">
        <a href="#" class="footer-link">http://munipacocha.gob.pe/index</a>
        <span>Copyright HAROLD GERARDO INGA BRUZ</span>
    </div>

</body>

</html>