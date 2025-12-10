<?php
// modules/predial/print_hr.php
require_once '../../config/db.php';
require_once '../../includes/calculos.php';

$id_dj = $_GET['id'] ?? '';
if (empty($id_dj))
    die("ID inválido");

$database = new Database();
$db = $database->getConnection();

// Datos Cabecera
$stmt = $db->prepare("SELECT d.*, c.nombres_razon_social, c.codigo, c.dni, c.ruc, df.direccion_fiscal 
                      FROM imp_declaracion_jurada d 
                      JOIN imp_contribuyente c ON d.codigo_contribuyente = c.codigo
                      LEFT JOIN imp_domicilio_fiscal_contribuyente df ON c.codigo = df.codigo_contribuyente
                      WHERE d.id_declaracion_jurada = ?");
$stmt->execute([$id_dj]);
$dj = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$dj)
    die("Declaración no encontrada");

// Predios
$stmt_p = $db->prepare("SELECT * FROM imp_dj_predio WHERE id_declaracion_jurada = ? ORDER BY id_dj_predio");
$stmt_p->execute([$id_dj]);
$predios = $stmt_p->fetchAll(PDO::FETCH_ASSOC);

// Calculos (Recalcular para display seguro)
$calc = new CalculadoraPredial($db, $dj['anio']);
$uit = $calc->getUIT();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>HR - <?php echo $dj['anio']; ?> - <?php echo $dj['codigo']; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .box {
            border: 1px solid #000;
            padding: 5px;
            margin-bottom: 10px;
        }

        .title {
            background: #eee;
            font-weight: bold;
            padding: 3px;
            border-bottom: 1px solid #000;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 4px;
            font-size: 10px;
        }

        th {
            background: #f0f0f0;
        }

        .text-right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        .footer {
            margin-top: 30px;
            font-size: 10px;
        }

        @media print {
            .no-print {
                display: none;
            }

            body {
                margin: 0;
            }
        }
    </style>
</head>

<body onload="window.print()">

    <div class="no-print">
        <button onclick="window.print()">IMPRIMIR</button>
    </div>

    <div class="header">
        <h3>HOJA RESUMEN (HR)</h3>
        <h4>IMPUESTO PREDIAL - EJERCICIO <?php echo $dj['anio']; ?></h4>
    </div>

    <div class="box">
        <div class="title">I. DATOS DEL CONTRIBUYENTE</div>
        <table style="border:none;">
            <tr>
                <td style="border:none; width: 15%;"><strong>Código:</strong> <?php echo $dj['codigo']; ?></td>
                <td style="border:none;"><strong>Nombre/Razón Social:</strong>
                    <?php echo $dj['nombres_razon_social']; ?></td>
            </tr>
            <tr>
                <td style="border:none;"><strong>Doc. Identidad:</strong> <?php echo $dj['dni'] ?: $dj['ruc']; ?></td>
                <td style="border:none;"><strong>Domicilio Fiscal:</strong>
                    <?php echo $dj['direccion_fiscal'] ?: 'NO REGISTRADO'; ?></td>
            </tr>
        </table>
    </div>

    <div class="box">
        <div class="title">II. RELACIÓN DE PREDIOS</div>
        <table>
            <thead>
                <tr>
                    <th>IT</th>
                    <th>CODIG. PREDIO</th>
                    <th>UBICACIÓN DEL PREDIO</th>
                    <th>USO</th>
                    <th>% PROP</th>
                    <th>AUTOVALÚO (S/)</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1;
                foreach ($predios as $p): ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><?php echo str_pad($p['id_dj_predio'], 6, '0', STR_PAD_LEFT); ?></td>
                        <td><?php echo $p['direccion_predio']; ?></td>
                        <td><?php echo $p['id_uso_predio'] == 1 ? 'CASA HABITACION' : 'OTROS'; // Simplificado ?></td>
                        <td class="text-right"><?php echo number_format($p['porcentaje_co_propiedad'], 2); ?>%</td>
                        <td class="text-right"><?php echo number_format($p['total_autoavaluo'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" class="text-right bold">TOTAL BASE IMPONIBLE:</td>
                    <td class="text-right bold"><?php echo number_format($dj['total_base_imponible'], 2); ?></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="box">
        <div class="title">III. DETERMINACIÓN DEL IMPUESTO</div>
        <table style="width: 50%; margin: 0 auto;">
            <tr>
                <td>Total Autovalúo (S/):</td>
                <td class="text-right"><?php echo number_format($dj['total_base_imponible'], 2); ?></td>
            </tr>
            <tr>
                <td>Deducción (S/):</td>
                <td class="text-right">0.00</td>
            </tr>
            <tr>
                <td class="bold">Base Imponible Neta (S/):</td>
                <td class="text-right bold"><?php echo number_format($dj['total_base_imponible'], 2); ?></td>
            </tr>
            <tr>
                <td class="bold">IMPUESTO ANUAL (S/):</td>
                <td class="text-right bold" style="font-size: 14px;">
                    <?php echo number_format($dj['impuesto_anual'], 2); ?></td>
            </tr>
            <tr>
                <td>Impuesto Trimestral (S/):</td>
                <td class="text-right"><?php echo number_format($dj['impuesto_trimestral'], 2); ?></td>
            </tr>
            <tr>
                <td>Valor UIT <?php echo $dj['anio']; ?>:</td>
                <td class="text-right"><?php echo number_format($uit, 2); ?></td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>Declaración jurada conforme a lo dispuesto por el D.Leg. 776 y modificatorias.</p>
        <br><br><br>
        <table style="border:none; margin-top: 20px;">
            <tr>
                <td style="border:none; text-align:center;">______________________________<br>Firma del Contribuyente
                </td>
                <td style="border:none; text-align:center;">______________________________<br>Sello y Firma Receptor
                </td>
            </tr>
        </table>
        <p>Fecha de Emisión: <?php echo date('d/m/Y H:i:s'); ?></p>
    </div>

</body>

</html>