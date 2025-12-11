<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIADT - Módulo Predial</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Estilos SIADT -->
    <link href="/Sistema_Predial_Grupo8/assets/css/siadt_styles.css?v=<?php echo time(); ?>" rel="stylesheet">
    <style>
        /* Ajustes específicos para vistas internas */
        body {
            background-color: white;
        }

        .module-menu-bar {
            background-color: #f0f0f0;
            border-bottom: 1px solid #ccc;
            padding: 5px 10px;
            font-size: 0.9rem;
        }

        .module-menu-link {
            color: #333;
            text-decoration: none;
            margin-right: 15px;
            padding: 2px 5px;
        }

        .module-menu-link:hover {
            background-color: #ddd;
            border: 1px solid #999;
        }
    </style>
</head>

<body>

    <!-- Header Rojo (Banner) -->
    <div class="siadt-header d-flex justify-content-between align-items-center py-1"
        style="font-size: 1rem; background: linear-gradient(to right, #8B0000, #b30000);">
        <div>
            Sistema Integrado de Administración Tributaria - SIADT v3.00 Usuario:[HAROLD] [Municipalidad Distrital de
            Pacocha]
        </div>
        <div>
            <a href="../../dashboard.php" class="text-white text-decoration-none border px-2">X</a>
        </div>
    </div>

    <!-- Barra de Menú Tipo Windows -->
    <div class="module-menu-bar d-flex">
        <?php if (isset($menu_movimiento) && is_array($menu_movimiento)): ?>
            <div class="dropdown">
                <a class="module-menu-link dropdown-toggle" href="#" role="button" id="dropdownMovimiento"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    Movimiento
                </a>
                <ul class="dropdown-menu" aria-labelledby="dropdownMovimiento"
                    style="border-radius: 0; margin-top: 0; font-size: 0.9rem;">
                    <?php foreach ($menu_movimiento as $item): ?>
                        <li><a class="dropdown-item" href="<?php echo $item['url'] ?? '#'; ?>"><?php echo $item['label']; ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php else: ?>
            <a href="#" class="module-menu-link">Movimiento</a>
        <?php endif; ?>

        <a href="#" class="module-menu-link">Ventana</a>
        <a href="../../dashboard.php" class="module-menu-link">Salir</a>
    </div>

    <!-- Contenido Principal -->
    <div class="container-fluid mt-4">
        <?php if (!isset($hide_default_title) || !$hide_default_title): ?>
            <h4 class="mb-4 text-primary border-bottom pb-2">Módulo:
                <?php echo isset($module_title) ? $module_title : 'IMPUESTO PREDIAL'; ?>
            </h4>
        <?php endif; ?>

        <!-- Aquí comienza el contenido de cada página -->
        <div class="row">