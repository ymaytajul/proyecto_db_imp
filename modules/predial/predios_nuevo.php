<?php
// modules/predial/predios_nuevo.php (Refactorizado con Tabs)
require_once '../../config/db.php';

$database = new Database();
$db = $database->getConnection();

$id_dj = isset($_REQUEST['id_dj']) ? $_REQUEST['id_dj'] : '';
if (!$id_dj) die("ID de DJ requerido.");

// Cargar Listas
$materiales = [];
$estados = [];
$clasif = [];
$usos = [];

if ($db) {
    // Si falla uno, no rompe todo
    try { $materiales = $db->query("SELECT * FROM imp_material_estructural_predio")->fetchAll(PDO::FETCH_ASSOC); } catch (Exception $e){}
    try { $estados = $db->query("SELECT * FROM imp_estado_conservacion")->fetchAll(PDO::FETCH_ASSOC); } catch (Exception $e){}
    try { $clasif = $db->query("SELECT * FROM imp_clasificacion_terreno")->fetchAll(PDO::FETCH_ASSOC); } catch (Exception $e){}
    try { $usos = $db->query("SELECT * FROM imp_uso_predio WHERE estado='activo'")->fetchAll(PDO::FETCH_ASSOC); } catch (Exception $e){}
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $db) {
    try {
        $db->beginTransaction();
        
        // --- 1. Guardar Predio ---
        // Insertamos predio dummy o buscamos uno existente (lógica simplificada)
        // Check distrito default
        $dist_chk = $db->query("SELECT id_distrito FROM imp_distrito LIMIT 1")->fetch();
        $id_dist = $dist_chk ? $dist_chk['id_distrito'] : 1; 

        $stmt_pred = $db->prepare("INSERT INTO imp_predio (estado, id_distrito, nombre_predio, observaciones) VALUES ('activo', ?, 'PREDIO URBANO', ?) RETURNING id_predio");
        // Nota: RETURNING funciona en PGSQL, si no usar lastInsertId
        $stmt_pred->execute([$id_dist, $_POST['referencia']]);
        
        // Fallback lastInsertId para PG
        $id_predio = $db->lastInsertId('imp_predio_id_predio_seq');

        // --- 2. Guardar Detalle DJ ---
        // Cálculos
        $area_t = $_POST['area_terreno'];
        $arancel = $_POST['arancel'];
        $val_terreno = $area_t * $arancel;
        
        // Sumar construcciones (JSON del form)
        $pisos_json = $_POST['pisos_data'];
        $pisos = json_decode($pisos_json, true);
        $val_construccion_total = 0;
        $area_construida_total = 0;

        if (is_array($pisos)) {
            foreach ($pisos as $p) {
                $val_construccion_total += $p['valor_piso'];
                $area_construida_total += $p['area'];
            }
        }

        $total_auto = $val_terreno + $val_construccion_total;

        $stmt_dj_p = $db->prepare("INSERT INTO imp_dj_predio 
            (id_declaracion_jurada, id_predio, direccion_predio, id_uso_predio, area_terreno, id_arancel_urbano, 
             valor_terreno, total_area_construida, valor_tconstruccion, total_autoavaluo, base_imponible)
            VALUES (?, ?, ?, ?, ?, NULL, ?, ?, ?, ?, ?)");
        
        $stmt_dj_p->execute([
            $id_dj, 
            $id_predio, 
            $_POST['direccion'], 
            $_POST['id_uso'], 
            $area_t, 
            $val_terreno, 
            $area_construida_total, 
            $val_construccion_total, 
            $total_auto, 
            $total_auto
        ]);
        
        $id_dj_predio = $db->lastInsertId('imp_dj_predio_id_dj_predio_seq');

        // --- 3. Guardar Pisos (Construcciones) ---
        if (is_array($pisos)) {
            $stmt_const = $db->prepare("INSERT INTO imp_predio_construccion 
                (id_dj_predio, antiguedad, area_construida, valor_total_construccion, id_material_estructural_predio, id_categoria_edificacion)
                VALUES (?, ?, ?, ?, ?, ?)");
            
            foreach ($pisos as $p) {
                // Mapeo simple
                $stmt_const->execute([
                    $id_dj_predio,
                    $p['antiguedad'],
                    $p['area'],
                    $p['valor_piso'],
                    $p['material'],
                    'A' // Hardcoded categoría dummy por ahora
                ]);
            }
        }

        // --- 4. Update Header ---
        $db->exec("UPDATE imp_declaracion_jurada SET total_base_imponible = total_base_imponible + $total_auto, total_predios_declarados = total_predios_declarados + 1 WHERE id_declaracion_jurada = $id_dj");

        $db->commit();
        header("Location: declaraciones.php?codigo=".$_POST['redir_cod']); // Redirigir al dashboard
        exit;

    } catch (Exception $e) {
        $db->rollBack();
        $error = "Error al guardar: " . $e->getMessage();
    }
}

// Obtener codigo contribuyente para redirect
$redir_cod = '';
if ($db) {
    $st = $db->prepare("SELECT codigo_contribuyente FROM imp_declaracion_jurada WHERE id_declaracion_jurada = ?");
    $st->execute([$id_dj]);
    $redir_cod = $st->fetchColumn();
}


include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="container-fluid pt-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h5 fw-bold text-uppercase"><i class="fas fa-building"></i> Ficha Predial Urbana</h1>
        <a href="declaraciones.php?codigo=<?php echo $redir_cod; ?>" class="btn btn-sm btn-secondary"><i class="fas fa-times"></i> Cancelar</a>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger mb-3"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" id="formPredio">
        <input type="hidden" name="id_dj" value="<?php echo $id_dj; ?>">
        <input type="hidden" name="redir_cod" value="<?php echo $redir_cod; ?>">
        <input type="hidden" name="pisos_data" id="pisos_data"> <!-- JSON Output -->

        <!-- NAV TABS -->
        <ul class="nav nav-tabs" id="predioTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold" id="ubicacion-tab" data-bs-toggle="tab" data-bs-target="#ubicacion" type="button" role="tab" aria-selected="true">1. Ubicación y Terreno</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold" id="construccion-tab" data-bs-toggle="tab" data-bs-target="#construccion" type="button" role="tab" aria-selected="false">2. Construcciones (Pisos)</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold" id="otras-tab" data-bs-toggle="tab" data-bs-target="#otras" type="button" role="tab" aria-selected="false">3. Otras Instalaciones</button>
            </li>
        </ul>

        <div class="tab-content border border-top-0 p-3 bg-white shadow-sm" id="predioTabsContent">
            
            <!-- AB 1: UBICACION -->
            <div class="tab-pane fade show active" id="ubicacion" role="tabpanel">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Dirección Completa del Predio *</label>
                        <input type="text" name="direccion" class="form-control form-control-sm" required placeholder="Calle / Jr / Av  +  N°  +  Urb/Sector">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Referencia</label>
                        <input type="text" name="referencia" class="form-control form-control-sm">
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">Condic. Propiedad</label>
                        <select class="form-select form-select-sm">
                            <option>PROPIETARIO UNICO</option>
                            <option>SUCESION INDIVISA</option>
                            <option>SOCIEDAD CONYUGAL</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Uso Predio</label>
                        <select name="id_uso" class="form-select form-select-sm">
                            <?php foreach($usos as $u): ?>
                                <option value="<?php echo $u['id_uso']; ?>"><?php echo $u['descripcion_uso']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Estado Terreno</label>
                        <select class="form-select form-select-sm">
                            <option>CONSTRUIDO</option>
                            <option>SIN CONSTRUIR</option>
                        </select>
                    </div>
                    
                    <div class="col-12 mt-4"><h6 class="text-primary fw-bold border-bottom pb-1">Datos del Terreno</h6></div>
                    
                    <div class="col-md-3">
                        <label class="form-label">Área Terreno (m²)</label>
                        <input type="number" step="0.01" name="area_terreno" id="area_terreno" class="form-control form-control-sm text-end" value="0.00" oninput="calcTerreno()" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Arancel (S/ x m²)</label>
                        <input type="number" step="0.01" name="arancel" id="arancel" class="form-control form-control-sm text-end" value="0.00" oninput="calcTerreno()" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Valor Terreno (S/)</label>
                        <input type="text" id="val_terreno" class="form-control form-control-sm text-end bg-light fw-bold" readonly>
                    </div>
                </div>
            </div>

            <!-- TAB 2: CONSTRUCCION -->
            <div class="tab-pane fade" id="construccion" role="tabpanel">
                <div class="d-flex justify-content-between mb-2">
                    <h6 class="text-primary fw-bold">Detalle de Pisos / Niveles</h6>
                    <button type="button" class="btn btn-sm btn-success" onclick="addPiso()"><i class="fas fa-plus"></i> Agregar Piso</button>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-sm table-condensed" id="tablaPisos">
                        <thead class="table-dark">
                            <tr>
                                <th width="50">N°</th>
                                <th width="80">Piso</th>
                                <th width="80">Antig. (Años)</th>
                                <th width="150">Material</th>
                                <th width="120">Estado</th>
                                <th>Categorías (M-T-P-PV-R-B-IE)</th>
                                <th width="100">Área (m²)</th>
                                <th width="120">Valor Piso (S/)</th>
                                <th width="50"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Rows dynamicos -->
                        </tbody>
                    </table>
                </div>
                <div class="text-end fw-bold">
                    Total Construcción: <span id="total_construccion" class="text-primary">S/ 0.00</span>
                </div>
            </div>

            <!-- TAB 3: OTRAS INSTALACIONES -->
            <div class="tab-pane fade" id="otras" role="tabpanel">
                <div class="alert alert-info py-2 small">
                    <i class="fas fa-info-circle"></i> Aquí se registran obras complementarias como cercos, piscinas, tanques elevados, etc.
                </div>
                <table class="table table-bordered table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Descripción</th>
                            <th>Unidad</th>
                            <th>Cantidad/Área</th>
                            <th>Valor Unitario</th>
                            <th>Valor Total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td colspan="6" class="text-center text-muted">No implementado en esta versión.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Footer Actions -->
        <div class="card mt-3 border-0">
            <div class="card-body d-flex justify-content-between align-items-center bg-light rounded">
                <div>
                    <span class="fw-bold">Total Autoavalúo Estimado:</span>
                    <span id="gran_total" class="fs-5 fw-bold text-success ms-2">S/ 0.00</span>
                </div>
                <button type="button" class="btn btn-primary px-4" onclick="submitForm()"><i class="fas fa-save"></i> GUARDAR PREDIO</button>
            </div>
        </div>
    </form>
</div>

<!-- DATA para JS -->
<script>
const MATERIALES = <?php echo json_encode($materiales); ?>;
const ESTADOS = <?php echo json_encode($estados); ?>;

function calcTerreno() {
    let area = parseFloat(document.getElementById('area_terreno').value) || 0;
    let arancel = parseFloat(document.getElementById('arancel').value) || 0;
    let total = area * arancel;
    document.getElementById('val_terreno').value = total.toFixed(2);
    calcGrandTotal();
}

function addPiso() {
    let tbody = document.querySelector('#tablaPisos tbody');
    let rowIdx = tbody.rows.length + 1;
    let tr = document.createElement('tr');
    
    // Selects options
    let matOpts = MATERIALES.map(m => `<option value="${m.id_material_estructural_predio}">${m.denominacion}</option>`).join('');
    let estOpts = ESTADOS.map(e => `<option value="${e.id_estado_conservacion}">${e.codigo} - ${e.denominacion}</option>`).join('');

    tr.innerHTML = `
        <td class="text-center">${rowIdx}</td>
        <td><input type="number" class="form-control form-control-sm inp-piso" value="${rowIdx}"></td>
        <td><input type="number" class="form-control form-control-sm inp-antig" value="0"></td>
        <td><select class="form-select form-select-sm inp-mat">${matOpts}</select></td>
        <td><select class="form-select form-select-sm inp-est">${estOpts}</select></td>
        <td>
            <div class="input-group input-group-sm">
                <input type="text" class="form-control px-1 text-center" placeholder="M" maxlength="1">
                <input type="text" class="form-control px-1 text-center" placeholder="T" maxlength="1">
                <input type="text" class="form-control px-1 text-center" placeholder="P" maxlength="1">
                <input type="text" class="form-control px-1 text-center" placeholder="V" maxlength="1">
            </div>
        </td>
        <td><input type="number" class="form-control form-control-sm text-end inp-area" value="0" oninput="calcRow(this)"></td>
        <td><input type="text" class="form-control form-control-sm text-end bg-light fw-bold inp-val" readonly value="0.00"></td>
        <td class="text-center"><button type="button" class="btn btn-xs btn-danger" onclick="delRow(this)"><i class="fas fa-times"></i></button></td>
    `;
    tbody.appendChild(tr);
}

function delRow(btn) {
    btn.closest('tr').remove();
    calcPisos();
}

function calcRow(inp) {
    let tr = inp.closest('tr');
    let area = parseFloat(inp.value) || 0;
    let valUnitDummy = 500.00; // Simulación de valor unitario por categorías
    let total = area * valUnitDummy;
    tr.querySelector('.inp-val').value = total.toFixed(2);
    calcPisos();
}

function calcPisos() {
    let total = 0;
    document.querySelectorAll('.inp-val').forEach(inp => total += parseFloat(inp.value) || 0);
    document.getElementById('total_construccion').textContent = 'S/ ' + total.toFixed(2);
    calcGrandTotal();
}

function calcGrandTotal() {
    let t_terr = parseFloat(document.getElementById('val_terreno').value) || 0;
    let t_const = parseFloat(document.getElementById('total_construccion').textContent.replace('S/ ', '')) || 0;
    document.getElementById('gran_total').textContent = 'S/ ' + (t_terr + t_const).toFixed(2);
}

function submitForm() {
    // Collect Pisos Data
    let data = [];
    document.querySelectorAll('#tablaPisos tbody tr').forEach(tr => {
        data.push({
            piso: tr.querySelector('.inp-piso').value,
            antiguedad: tr.querySelector('.inp-antig').value,
            material: tr.querySelector('.inp-mat').value,
            area: parseFloat(tr.querySelector('.inp-area').value)||0,
            valor_piso: parseFloat(tr.querySelector('.inp-val').value)||0
        });
    });
    document.getElementById('pisos_data').value = JSON.stringify(data);
    document.getElementById('formPredio').submit();
}
</script>

<?php include '../../includes/footer.php'; ?>