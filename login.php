<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIADT - Ventana de Acceso</title>
    <!-- Bootstrap 5 CSS (solo para utilidades básicas de layout si necesario, pero overrides mandan) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Estilos SIADT -->
    <link href="assets/css/siadt_styles.css" rel="stylesheet">
</head>

<body>

    <!-- Header Rojo -->
    <div class="siadt-header">
        Municipalidad Distrital de Pacocha
    </div>

    <!-- Contenedor Principal -->
    <div class="login-container">

        <!-- Panel Izquierdo (Azul) -->
        <div class="login-left-panel">
            <div class="siadt-title">SIADT</div>
            <div class="siadt-subtitle">
                Sistema Integrado de Administración Tributaria<br>
                <strong>Ventana de Acceso</strong>
            </div>

            <div class="login-card">
                <h5><i class="fas fa-key me-2"></i>Conectarse al Servidor</h5>
                <form action="dashboard.php" method="POST"> <!-- Redirección directa para demo -->
                    <div class="mb-3">
                        <label for="usuario" class="form-label small fw-bold">Nombre de Usuario</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" id="usuario" name="usuario" placeholder="Usuario"
                                required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label small fw-bold">Contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password"
                                placeholder="******" required>
                        </div>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary fw-bold">Iniciar sesión</button>
                        <button type="button" class="btn btn-secondary fw-bold">Salir</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Panel Derecho (Escudo) -->
        <div class="login-right-panel">
            <div class="text-center">
                <!-- Placeholder para el escudo -->
                <i class="fas fa-shield-alt fa-10x text-secondary mb-4" style="opacity: 0.2;"></i>
                <h1 class="text-secondary fw-bold">Municipalidad Distrital<br>de Pacocha</h1>
            </div>
        </div>

    </div>

    <!-- Footer -->
    <div class="siadt-footer">
        Se autoriza el uso de este producto a: Municipalidad Distrital de Pacocha<br>
        Todos los Derechos Reservados (C) Harold Gerardo Inga Bruz
    </div>

</body>

</html>