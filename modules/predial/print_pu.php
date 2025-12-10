<?php
// modules/predial/print_pu.php
require_once '../../config/db.php';

$id_predio = $_GET['id'] ?? '';
if (empty($id_predio))
    die("ID inválido");

$database = new Database();
$db = $database->getConnection();

// 1. Datos del Predio + Cabecera DJ
$stmt = $db->prepare("SELECT dp.*, d.anio, d.codigo_contribuyente, c.nombres_razon_social, up.descripcion_uso, ct.denominacion as clasificacion
                      FROM imp_dj_predio dp
                      JOIN imp_declaracion_jurada d ON dp.id_declaracion_jurada = d.id_declaracion_jurada
                      JOIN imp_contribuyente c ON d.codigo_contribuyente = c.codigo
                      LEFT JOIN imp_uso_predio up ON dp.id_uso_predio = up.id_uso
                      LEFT JOIN imp_predio_terreno pt ON dp.id_dj_predio = pt.id_dj_predio -- asumimos 1 a 1 simple
                      LEFT JOIN imp_clasificacion_terreno ct ON pt.id_clasificacion_terreno = ct.id_clasificacion_terreno
                      WHERE dp.id_dj_predio = ?");
$stmt->execute([$id_predio]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row)
    die("Predio no encontrado");

// 2. Pisos
$stmt_pisos = $db->prepare("SELECT pc.*, n.denominacion as nivel, me.denominacion as material, ec.denominacion as estado_conservacion
                            FROM imp_predio_construccion pc
                            LEFT JOIN imp_nivel n ON pc.id_nivel = n.id_nivel
                            LEFT JOIN imp_material_estructural_predio me ON pc.id_material_estructural_predio = me.id_material_estructural_predio
                            LEFT JOIN imp_estado_conservacion ec ON pc.id_depreciacion = ec.id_estado_conservacion -- simplificado
                            WHERE pc.id_dj_predio = ? ORDER BY pc.id_nivel");
$stmt_pisos->execute([$id_predio]);
$pisos = $stmt_pisos->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>PU - <?php echo $row['id_dj_predio']; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }

        .section {
            margin-bottom: 15px;
            border: 1px solid #000;
        }

        .section-title {
            background: #eee;
            padding: 4px;
            font-weight: bold;
            border-bottom: 1px solid #000;
        }

        .row {
            display: flex;
            flex-wrap: wrap;
        }

        .col {
            flex: 1;
            padding: 4px;
            border-right: 1px solid #ccc;
        }

        .col:last-child {
            border-right: none;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 4px;
            text-align: center;
            font-size: 10px;
        }

        th {
            background: #f9f9f9;
        }

        .text-right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body onload="window.print()">
    <div class="no-print" style="margin-bottom:10px;">
        <button onclick="window.print()">Imprimir</button>
    </div>

    <div class="header">
        <h3>HOJA DE PREDIO URBANO (PU)</h3>
        <h4>EJERCICIO FISCAL <?php echo $row['anio']; ?></h4>
    </div>

    <!-- DATOS CONTRIBUYENTE -->
    <div class="section">
        <div class="section-title">I. DATOS DEL CONTRIBUYENTE</div>
        <div class="row">
            <div class="col"><strong>Código:</strong> <?php echo $row['codigo_contribuyente']; ?></div>
            <div class="col" style="flex:3;"><strong>Apellidos y Nombres:</strong>
                <?php echo $row['nombres_razon_social']; ?></div>
        </div>
    </div>

    <!-- UBICACION -->
    <div class="section">
        <div class="section-title">II. UBICACIÓN DEL PREDIO</div>
        <div class="row">
            <div class="col" style="flex:3;"><strong>Dirección:</strong> <?php echo $row['direccion_predio']; ?></div>
            <div class="col"><strong>Uso:</strong> <?php echo $row['descripcion_uso']; ?></div>
            <div class="col"><strong>Clasificación:</strong> <?php echo $row['clasificacion'] ?? 'URBANO'; ?></div>
        </div>
    </div>

    <!-- DATOS DEL TERRENO -->
    <div class="section">
        <div class="section-title">III. DETERMINACIÓN DEL VALOR DEL TERRENO</div>
        <div class="row">
            <div class="col"><strong>Área Terreno (m²):</strong> <?php echo number_format($row['area_terreno'], 2); ?>
            </div>
            <div class="col"><strong>Arancel (S/):</strong> <?php echo number_format($row['arancel'] ?? 0, 2); ?></div>
            <div class="col"><strong>Valor Terreno (S/):</strong> <?php echo number_format($row['valor_terreno'], 2); ?>
            </div>
        </div>
    </div>

    <!-- CONSTRUCCIONES -->
    <div class="section">
        <div class="section-title">IV. CONSTRUCCIONES</div>
        <table>
            <thead>
                <tr>
                    <th>Nivel</th>
                    <th>Antig.</th>
                    <th>Material</th>
                    <th>Estado</th>
                    <th>Área Const. (m²)</th>
                    <th>Val. Unit. (S/)</th>
                    <th>Deprec. (S/)</th>
                    <th>Valor Total (S/)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pisos as $p): ?>
                    <tr>
                        <td><?php echo $p['nivel'] ?? $p['id_nivel']; ?></td>
                        <td><?php echo $p['antiguedad']; ?> años</td>
                        <td><?php echo $p['material']; ?></td>
                        <td><?php echo $p['estado_conservacion'] ?? '-'; ?></td>
                        <td class="text-right"><?php echo number_format($p['area_construida'], 2); ?></td>
                        <td class="text-right"><?php echo number_format($p['valor_unitario'], 2); ?></td>
                        <td class="text-right"><?php echo number_format($p['valor_depreciado'], 2); ?></td>
                        <td class="text-right bold"><?php echo number_format($p['valor_total_construccion'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($pisos)): ?>
                    <tr>
                        <td colspan="8">Sin construcciones registradas.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- RESUMEN -->
    <div class="section">
        <div class="section-title">V. VALORIZACIÓN TOTAL</div>
        <div class="row">
            <div class="col">
                <strong>Valor Terreno:</strong> S/ <?php echo number_format($row['valor_terreno'], 2); ?>
            </div>
            <div class="col">
                <strong>Valor Construcción:</strong> S/ <?php echo number_format($row['valor_tconstruccion'], 2); ?>
            </div>
            <div class="col">
                <strong>Otras Instalaciones:</strong> S/ <?php echo number_format($row['valor_tinstalacion'], 2); ?>
            </div>
            <div class="col" style="background: #e0e0e0;">
                <strong style="font-size:12px;">TOTAL AUTOVALÚO: S/
                    <?php echo number_format($row['total_autoavaluo'], 2); ?></strong>
            </div>
        </div>
    </div>

    <div style="font-size:10px; margin-top:20px; text-align:center;">
        Generado el: <?php echo date('d/m/Y H:i:s'); ?> - Sistema de Rentas
    </div>
</body>

</html>