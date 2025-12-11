<?php
// 1. INICIO DE PHP: Esto es lo que faltaba.
$module_title = "ATENCION AL PUBLICO";
$hide_default_title = true; // Oculta el título azul por defecto del header

// 2. DEFINICIÓN DEL MENÚ
// El header leerá esta variable para generar las opciones "Movimiento"
$menu_movimiento = [
    ['label' => 'Maestro de Contribuyentes', 'url' => '../predial/contribuyentes.php'], // Ajusté la ruta según tu estructura
    ['label' => 'Impuesto de Alcabala', 'url' => '#'],
    ['label' => 'Facturación Conjunta', 'url' => '#']
];

// 3. CARGA DE CABECERA Y ESTILOS
include '../../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12 ps-4 d-flex flex-column" style="min-height: 80vh;">

            <div class="mt-4">
                <div class="mb-3" style="font-family: Arial, sans-serif; color: #666;">
                    <h2 class="fw-bold mb-0" style="font-size: 1.8rem; letter-spacing: -0.5px; color: #555;">
                        Sistema Integrado de Administración Tributaria - SIADT v3.00
                    </h2>
                    <div class="fw-bold" style="font-size: 1rem; color: #777;">Municipalidad Distrital de Pacocha</div>
                    <div class="fw-bold mt-1" style="font-size: 1rem; color: #777;">Módulo: <?php echo $module_title; ?></div>
                </div>

                <div class="d-flex align-items-start mb-4 mt-4">
                    <i class="fas fa-user-circle fa-4x text-secondary me-3" style="opacity: 0.3;"></i>
                    
                    <div style="font-family: Arial, sans-serif; font-size: 0.9rem; color: #777; line-height: 1.6;">
                        <div>Usuario: <strong><?php echo $_SESSION['username'] ?? 'HAROLD'; ?></strong></div>
                        <div>Nombres y Apellidos: <?php echo $_SESSION['nombre_completo'] ?? 'HAROLD INGA'; ?></div>
                        <div>Fecha y Hora de Acceso: <?php echo date('d/m/Y - H:i:s'); ?></div>
                    </div>
                </div>

                <div class="mb-5">
                    <a href="http://munipacocha.gob.pe/index" target="_blank"
                       class="fw-bold text-decoration-underline"
                       style="font-size: 0.9rem; color: #0056b3;">
                        http://munipacocha.gob.pe/index
                    </a>
                </div>
            </div>

            <div class="mt-auto border-top pt-2 w-100">
                 <div class="d-flex justify-content-between align-items-end">
                    <div class="small text-secondary fw-bold" style="font-size: 0.75rem;">
                        Copyright by @ Harold Gerardo Inga Bruz
                    </div>
                    <div>
                        <i class="fas fa-server fa-2x text-secondary opacity-25"></i>
                        <i class="fas fa-network-wired fa-2x text-secondary opacity-25 ms-3"></i>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php 
// 5. CARGA DEL FOOTER GLOBAL
include '../../includes/footer.php'; 
?>