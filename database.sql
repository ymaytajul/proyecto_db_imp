BEGIN;

-- =====================================================
-- IMPUESTO PREDIAL V3 - Modelo Desacoplado (PostgreSQL)
-- Lógica: Distrito -> (Vía, Sector, Habilitación) -> Predio
-- =====================================================

-- -----------------------------------------------------
-- 1. ESTRUCTURA GEOGRÁFICA (UBIGEO)
-- -----------------------------------------------------
CREATE TABLE imp_departamento (
    id_departamento SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL
);

CREATE TABLE imp_provincia (
    id_provincia SERIAL PRIMARY KEY,
    id_departamento INT NOT NULL REFERENCES imp_departamento(id_departamento),
    nombre VARCHAR(100) NOT NULL
);

CREATE TABLE imp_distrito (
    id_distrito SERIAL PRIMARY KEY,
    id_provincia INT NOT NULL REFERENCES imp_provincia(id_provincia),
    nombre VARCHAR(100) NOT NULL
);

-- -----------------------------------------------------
-- 1.b Tablas locales (Entidades Geográficas del Distrito)
-- NOTA: Se definen UNIQUE (id, distrito) para permitir validación cruzada más adelante.
-- -----------------------------------------------------

-- SECTORES
CREATE TABLE imp_sector (
    id_sector SERIAL PRIMARY KEY,
    estado VARCHAR(20),
    nombre_sector VARCHAR(150) NOT NULL,
    id_distrito INT NOT NULL REFERENCES imp_distrito(id_distrito),
    CONSTRAINT uq_imp_sector_id_sector_distrito UNIQUE (id_sector, id_distrito)
);

-- HABILITACIONES URBANAS
CREATE TABLE imp_tipo_habilitacion_urbana (
    id_tipo_habilitacion SERIAL PRIMARY KEY,
    descripcion VARCHAR(200),
    abreviacion VARCHAR(20)
);

CREATE TABLE imp_habilitacion_urbana (
    id_habilitacion SERIAL PRIMARY KEY,
    estado VARCHAR(20),
    nombre_habilitacion VARCHAR(150) NOT NULL,
    numero_partida VARCHAR(100),
    id_tipo_habilitacion INT REFERENCES imp_tipo_habilitacion_urbana(id_tipo_habilitacion),
    id_distrito INT NOT NULL REFERENCES imp_distrito(id_distrito),
    CONSTRAINT uq_imp_habilitacion_idhab_distrito UNIQUE (id_habilitacion, id_distrito)
);

-- ASOCIACIONES
CREATE TABLE imp_asociacion (
    id_asociacion SERIAL PRIMARY KEY,
    estado VARCHAR(20),
    nombre_asociacion VARCHAR(150) NOT NULL,
    id_distrito INT NOT NULL REFERENCES imp_distrito(id_distrito),
    CONSTRAINT uq_imp_asociacion_idaso_distrito UNIQUE (id_asociacion, id_distrito)
);

-- VÍAS (CORREGIDO - SOLUCIÓN 2)
-- La Vía ya no depende de Sector ni Habilitación, solo del Distrito.
CREATE TABLE imp_tipo_via (
    id_tipo_via SERIAL PRIMARY KEY,
    tipo_via VARCHAR(100),
    abreviacion VARCHAR(20)
);

CREATE TABLE imp_via (
    id_via SERIAL PRIMARY KEY,
    estado VARCHAR(20),
    nombre_via VARCHAR(150) NOT NULL,
    id_tipo_via INT REFERENCES imp_tipo_via(id_tipo_via),
    id_distrito INT NOT NULL REFERENCES imp_distrito(id_distrito),
    
    -- Constraint necesario para que el Predio valide que la vía es del distrito correcto
    CONSTRAINT uq_imp_via_idvia_distrito UNIQUE (id_via, id_distrito)
);

-- -----------------------------------------------------
-- 2. CONTRIBUYENTE Y DOMICILIO FISCAL
-- -----------------------------------------------------
CREATE TABLE imp_contribuyente (
    codigo VARCHAR(15) PRIMARY KEY,
    estado VARCHAR(20) NOT NULL,
    tipo_persona VARCHAR(20) NOT NULL,
    genero VARCHAR(10),
    fecha_nacimiento DATE,
    dni VARCHAR(10),
    ruc VARCHAR(15),
    otros_doc_ident VARCHAR(50),
    nro_doc_ident VARCHAR(50),
    nombres_razon_social VARCHAR(255) NOT NULL,
    observaciones TEXT,
    telefono_fijo VARCHAR(20),
    celular VARCHAR(20),
    celular_whatsapp VARCHAR(20),
    correo_electronico VARCHAR(100),
    fecha_creacion DATE DEFAULT CURRENT_DATE,
    codigo_anterior VARCHAR(15),
    doc_ident_rep_legal VARCHAR(50),
    nro_doc_rep_legal VARCHAR(50)
);

CREATE TABLE imp_tipo_interior (
    id_tipo_interior SERIAL PRIMARY KEY,
    descripcion VARCHAR(200),
    otros TEXT
);

CREATE TABLE imp_domicilio_fiscal_contribuyente (
    id_domicilio SERIAL PRIMARY KEY,
    codigo_contribuyente VARCHAR(15) NOT NULL REFERENCES imp_contribuyente(codigo),
    id_distrito INT NOT NULL REFERENCES imp_distrito(id_distrito),
    
    -- Ubicación detallada
    id_via INT,
    id_sector INT,
    id_habilitacion INT,
    id_asociacion INT,
    
    -- Datos de dirección
    numero VARCHAR(20),
    letra VARCHAR(5),
    id_tipo_interior INT REFERENCES imp_tipo_interior(id_tipo_interior),
    numero_interior VARCHAR(20),
    manzana VARCHAR(10),
    lote VARCHAR(10),
    sublote VARCHAR(10),
    bloque VARCHAR(10),
    edificio VARCHAR(50),
    piso VARCHAR(10),
    numeracion_ampliada VARCHAR(100),
    direccion_fiscal VARCHAR(255),
    referencia_direccion VARCHAR(255),

    -- VALIDACIÓN CRUZADA (SOLUCIÓN 2)
    -- Asegura que la Vía/Sector/Hab elegidos pertenezcan al Distrito del domicilio
    CONSTRAINT fk_domfisc_via_valida 
        FOREIGN KEY (id_via, id_distrito) REFERENCES imp_via (id_via, id_distrito),
    CONSTRAINT fk_domfisc_sector_valido 
        FOREIGN KEY (id_sector, id_distrito) REFERENCES imp_sector (id_sector, id_distrito),
    CONSTRAINT fk_domfisc_hab_valida 
        FOREIGN KEY (id_habilitacion, id_distrito) REFERENCES imp_habilitacion_urbana (id_habilitacion, id_distrito),
    CONSTRAINT fk_domfisc_aso_valida 
        FOREIGN KEY (id_asociacion, id_distrito) REFERENCES imp_asociacion (id_asociacion, id_distrito)
);

-- -----------------------------------------------------
-- 3. CATÁLOGOS Y PARÁMETROS (Ordenados al inicio para evitar errores de referencia)
-- -----------------------------------------------------
CREATE TABLE imp_nivel (
    id_nivel VARCHAR(10) PRIMARY KEY,
    codigo VARCHAR(10) UNIQUE,
    denominacion VARCHAR(100) NOT NULL,
    incremento_sp NUMERIC(5,2) DEFAULT 0.00,
    aplicar_5_por_ciento BOOLEAN DEFAULT FALSE
);

CREATE TABLE imp_tipo_registro_predio (
    id_tipo_registro_predio SERIAL PRIMARY KEY,
    denominacion VARCHAR(100) NOT NULL,
    activo BOOLEAN DEFAULT TRUE
);

CREATE TABLE imp_material_estructural_predio (
    id_material_estructural_predio SERIAL PRIMARY KEY,
    codigo VARCHAR(10) UNIQUE,
    denominacion VARCHAR(100) NOT NULL,
    valor_referencia NUMERIC(10,2)
);

CREATE TABLE imp_motivo_dj (
    id_motivo_dj SERIAL PRIMARY KEY,
    codigo VARCHAR(10) UNIQUE,
    denominacion VARCHAR(100) NOT NULL,
    abreviatura VARCHAR(10),
    transferencia BOOLEAN DEFAULT FALSE
);

CREATE TABLE imp_clasificacion_terreno (
    id_clasificacion_terreno SERIAL PRIMARY KEY,
    denominacion VARCHAR(150) NOT NULL
);

CREATE TABLE imp_categoria_terreno_ext (
    id_categoria_terreno_ext SERIAL PRIMARY KEY,
    categoria VARCHAR(50) NOT NULL,
    abreviacion VARCHAR(10)
);

CREATE TABLE imp_grupo_tierra (
    id_grupo_tierra SERIAL PRIMARY KEY,
    denominacion VARCHAR(100) NOT NULL
);

CREATE TABLE imp_categoria_terreno (
    id_categoria_terreno SERIAL PRIMARY KEY,
    denominacion VARCHAR(100) NOT NULL
);

CREATE TABLE imp_estado_conservacion (
    id_estado_conservacion SERIAL PRIMARY KEY,
    codigo VARCHAR(10) UNIQUE,
    denominacion VARCHAR(100) NOT NULL,
    factor_depreciacion NUMERIC(5,2)
);

CREATE TABLE imp_arancel_urbano (
    id_arancel_urbano SERIAL PRIMARY KEY,
    anio SMALLINT NOT NULL,
    valor_arancel_m2 NUMERIC(14,2) NOT NULL,
    direccion_predio VARCHAR(255),
    CONSTRAINT uq_imp_arancel_anio_direccion UNIQUE (anio, direccion_predio)
);

CREATE TABLE imp_uso_predio (
    id_uso SERIAL PRIMARY KEY,
    estado VARCHAR(20),
    descripcion_uso VARCHAR(200) NOT NULL
);

CREATE TABLE imp_param_principales (
    id_param_principal SERIAL PRIMARY KEY,
    anio SMALLINT NOT NULL UNIQUE,
    numero_cuponeras INT,
    porcentaje_quinta NUMERIC(6,4),
    valor_uit NUMERIC(18,2),
    moneda VARCHAR(20),
    cuotas INT,
    impuesto_minimo NUMERIC(14,2),
    tope_emision NUMERIC(14,2),
    porcentaje_incremento NUMERIC(6,4),
    factor_oficializacion_otras_instalaciones NUMERIC(8,4),
    glosa_base_legal TEXT
);

CREATE TABLE imp_escala_impuesto (
    id_escala_impuesto SERIAL PRIMARY KEY,
    anio SMALLINT NOT NULL,
    desde_autovaluo NUMERIC(18,2) NOT NULL,
    hasta_autovaluo NUMERIC(18,2) NOT NULL,
    tasa_impuesto NUMERIC(6,4),
    impuesto_acumulado NUMERIC(18,2),
    CONSTRAINT uq_imp_escala_anio_desde_hasta UNIQUE (anio, desde_autovaluo, hasta_autovaluo)
);

CREATE TABLE imp_categoria_edificacion (
    id_categoria CHAR(1) PRIMARY KEY,
    descripcion VARCHAR(200),
    muros_y_columnas VARCHAR(200),
    techos VARCHAR(200),
    pisos VARCHAR(200),
    puertas_ventanas VARCHAR(200),
    revestimiento VARCHAR(200),
    banos VARCHAR(200),
    instalaciones_electricas_sanitarias VARCHAR(200)
);

CREATE TABLE imp_valor_unitario_edificacion (
    id_valor_unitario_edificacion SERIAL PRIMARY KEY,
    anio SMALLINT NOT NULL,
    id_categoria CHAR(1) NOT NULL REFERENCES imp_categoria_edificacion(id_categoria),
    muros_y_columnas NUMERIC(14,2),
    techos NUMERIC(14,2),
    pisos NUMERIC(14,2),
    puertas_ventanas NUMERIC(14,2),
    revestimientos NUMERIC(14,2),
    banos NUMERIC(14,2),
    instalaciones_electricas_sanitarias NUMERIC(14,2),
    base_legal TEXT,
    CONSTRAINT uq_imp_valorunit_edif_anio_cat UNIQUE (anio, id_categoria)
);

CREATE TABLE imp_obra_complementaria (
    id_obra CHAR(3) PRIMARY KEY,
    descripcion VARCHAR(255) NOT NULL,
    componente VARCHAR(100),
    unidad VARCHAR(50),
    CONSTRAINT ck_imp_obra_id_formato CHECK (id_obra ~ '^[0-9]{3}$')
);

CREATE TABLE imp_valor_unitario_obra (
    id_valor_unitario_obra SERIAL PRIMARY KEY,
    anio SMALLINT NOT NULL,
    id_obra CHAR(3) NOT NULL REFERENCES imp_obra_complementaria(id_obra),
    valor_unitario NUMERIC(14,2),
    CONSTRAINT uq_imp_valorobra_anio_obra UNIQUE (anio, id_obra)
);

CREATE TABLE imp_depreciacion (
    id_depreciacion SERIAL PRIMARY KEY,
    anio SMALLINT NOT NULL,
    clasificacion VARCHAR(150) NOT NULL,
    material_predominante VARCHAR(100),
    antiguedad_anios INT,
    muy_bueno NUMERIC(4,2),
    bueno NUMERIC(4,2),
    regular NUMERIC(4,2),
    malo NUMERIC(4,2),
    muy_malo NUMERIC(4,2),
    CONSTRAINT uq_imp_depreciacion_anio_clasif UNIQUE (anio, clasificacion)
);

CREATE TABLE imp_exoneracion (
    id_exoneracion SERIAL PRIMARY KEY,
    estado VARCHAR(20),
    tributo VARCHAR(100) NOT NULL,
    descripcion TEXT,
    abreviacion VARCHAR(20),
    requiere_sustento BOOLEAN DEFAULT FALSE
);

CREATE TABLE imp_valor_exoneracion (
    id_valor_exoneracion SERIAL PRIMARY KEY,
    anio SMALLINT NOT NULL,
    id_exoneracion INT NOT NULL REFERENCES imp_exoneracion(id_exoneracion),
    monto_exonerado NUMERIC(14,2),
    porcentaje_exonerado NUMERIC(6,4),
    CONSTRAINT uq_imp_valor_exoneracion_anio_exo UNIQUE (anio, id_exoneracion)
);

CREATE TABLE imp_vencimiento_emision (
    id_vencimiento_emision SERIAL PRIMARY KEY,
    tributo VARCHAR(100) NOT NULL,
    anio SMALLINT NOT NULL,
    periodo SMALLINT,
    fecha_vencimiento DATE,
    fecha_prorroga DATE,
    base_legal TEXT,
    derecho_emision NUMERIC(14,2),
    costo_por_predio NUMERIC(14,2),
    cantidad_predios_exceso INT,
    CONSTRAINT uq_imp_venc_emision_tr_anio_periodo UNIQUE (tributo, anio, periodo)
);

-- -----------------------------------------------------
-- 4. TABLA MAESTRA PREDIO (Integración del Modelo Solución 2)
-- -----------------------------------------------------
CREATE TABLE imp_junta_vecinal (
    id_junta_vecinal SERIAL PRIMARY KEY,
    descripcion VARCHAR(200)
);

CREATE TABLE imp_predio (
    id_predio SERIAL PRIMARY KEY,
    estado VARCHAR(20) NOT NULL
        CHECK (estado IN ('activo','anulado','subdividido')),
    
    -- Ubicación Jerárquica Principal
    id_distrito INT NOT NULL REFERENCES imp_distrito(id_distrito),
    
    -- Componentes de Ubicación (Relacionados al Distrito)
    id_via INT,
    id_sector INT,
    id_habilitacion INT,
    id_asociacion INT,

    -- Detalle de Dirección
    numero VARCHAR(20),
    letra VARCHAR(10),
    nombre_predio VARCHAR(255),
    id_tipo_interior INT REFERENCES imp_tipo_interior(id_tipo_interior),
    numero_interior VARCHAR(20),
    manzana VARCHAR(10),
    lote VARCHAR(10),
    sublote VARCHAR(10),
    bloque VARCHAR(10),
    edificio VARCHAR(50),
    piso VARCHAR(10),
    numeracion_ampliada VARCHAR(100),
    id_junta_vecinal INT REFERENCES imp_junta_vecinal(id_junta_vecinal),
    observaciones TEXT,

    -- VALIDACIÓN CRUZADA FUERTE (SOLUCIÓN 2)
    -- "Ata" los componentes al distrito. Si el predio es de Lima, la calle DEBE ser de Lima.
    CONSTRAINT fk_predio_via_valida 
        FOREIGN KEY (id_via, id_distrito) REFERENCES imp_via (id_via, id_distrito),
    CONSTRAINT fk_predio_sector_valido 
        FOREIGN KEY (id_sector, id_distrito) REFERENCES imp_sector (id_sector, id_distrito),
    CONSTRAINT fk_predio_habilitacion_valida 
        FOREIGN KEY (id_habilitacion, id_distrito) REFERENCES imp_habilitacion_urbana (id_habilitacion, id_distrito),
    CONSTRAINT fk_predio_asociacion_valida 
        FOREIGN KEY (id_asociacion, id_distrito) REFERENCES imp_asociacion (id_asociacion, id_distrito)
);

-- -----------------------------------------------------
-- 5. DECLARACIÓN JURADA (Cabecera)
-- -----------------------------------------------------
CREATE TABLE imp_declaracion_jurada (
    id_declaracion_jurada SERIAL PRIMARY KEY,
    codigo_contribuyente VARCHAR(15) NOT NULL REFERENCES imp_contribuyente(codigo),
    id_motivo_dj INT NOT NULL REFERENCES imp_motivo_dj(id_motivo_dj),
    estado VARCHAR(20),
    anio SMALLINT NOT NULL,
    fecha_recepcion DATE,
    otros_motivos_declaracion TEXT,
    total_predios_declarados SMALLINT,
    anio_desde SMALLINT,
    trimestre_desde SMALLINT,
    fecha_declaracion DATE,
    total_base_imponible NUMERIC(18,2),
    impuesto_anual NUMERIC(18,2),
    impuesto_trimestral NUMERIC(18,2),
    observaciones TEXT,
    total_multa NUMERIC(18,2),
    total_multa_descuento NUMERIC(18,2)
);

-- -----------------------------------------------------
-- 6. DJ_PREDIO (Detalle y Colindancias)
-- -----------------------------------------------------
CREATE TABLE imp_dj_predio (
    id_dj_predio SERIAL PRIMARY KEY,
    id_declaracion_jurada INT NOT NULL REFERENCES imp_declaracion_jurada(id_declaracion_jurada),
    id_predio INT REFERENCES imp_predio(id_predio),
    id_tipo_registro_predio INT REFERENCES imp_tipo_registro_predio(id_tipo_registro_predio),
    direccion_predio VARCHAR(255),
    condicion_propiedad VARCHAR(50),
    id_uso_predio INT REFERENCES imp_uso_predio(id_uso),
    porcentaje_co_propiedad NUMERIC(5,2) DEFAULT 100.00,
    luz VARCHAR(50),
    agua VARCHAR(50),
    licencia_construccion VARCHAR(50),
    conformidad_obra VARCHAR(50),
    declaracion_fabrica VARCHAR(50),
    sustento TEXT,
    fecha_adquisicion DATE,
    area_terreno NUMERIC(10,2),
    area_comun NUMERIC(8,2),
    id_arancel_urbano INT REFERENCES imp_arancel_urbano(id_arancel_urbano),
    partida_registral VARCHAR(50),
    total_area_construida NUMERIC(8,2),
    total_area_instalacion NUMERIC(8,2),
    autoavaluo_copropietario NUMERIC(8,2),
    valor_tconstruccion NUMERIC(8,2),
    valor_tinstalacion NUMERIC(8,2),
    valor_terreno NUMERIC(8,2),
    total_autoavaluo NUMERIC(8,2),
    base_imponible NUMERIC(14,2) NOT NULL,
    url_foto_dj VARCHAR(255),
    id_valor_exoneracion INT REFERENCES imp_valor_exoneracion(id_valor_exoneracion)
);

CREATE TABLE imp_predio_colindante (
    id_predio_colindante SERIAL PRIMARY KEY,
    id_dj_predio INT NOT NULL REFERENCES imp_dj_predio(id_dj_predio),
    predio_norte VARCHAR(100),
    propietario_norte VARCHAR(200),
    medida_norte NUMERIC(10,2),
    predio_sur VARCHAR(100),
    propietario_sur VARCHAR(200),
    medida_sur NUMERIC(10,2),
    predio_este VARCHAR(100),
    propietario_este VARCHAR(200),
    medida_este NUMERIC(10,2),
    predio_oeste VARCHAR(100),
    propietario_oeste VARCHAR(200),
    medida_oeste NUMERIC(10,2)
);

-- -----------------------------------------------------
-- 7. DETALLE DEL AUTOVALÚO (Construcción, Terreno, Otros)
-- -----------------------------------------------------
CREATE TABLE imp_predio_construccion (
    id_predio_construccion SERIAL PRIMARY KEY,
    id_dj_predio INT NOT NULL REFERENCES imp_dj_predio(id_dj_predio),
    id_nivel VARCHAR(10) REFERENCES imp_nivel(id_nivel),
    id_material_estructural_predio INT REFERENCES imp_material_estructural_predio(id_material_estructural_predio),
    id_depreciacion INT REFERENCES imp_depreciacion(id_depreciacion),
    id_categoria_edificacion CHAR(1) REFERENCES imp_categoria_edificacion(id_categoria),
    antiguedad INT,
    area_construida NUMERIC(10,2) NOT NULL,
    valor_unitario NUMERIC(10,2),
    valor_depreciado NUMERIC(10,2),
    valor_total_construccion NUMERIC(14,2)
);

CREATE TABLE imp_predio_terreno (
    id_predio_terreno SERIAL PRIMARY KEY,
    id_dj_predio INT NOT NULL REFERENCES imp_dj_predio(id_dj_predio),
    id_clasificacion_terreno INT NOT NULL REFERENCES imp_clasificacion_terreno(id_clasificacion_terreno),
    id_categoria_terreno_ext INT NOT NULL REFERENCES imp_categoria_terreno_ext(id_categoria_terreno_ext),
    arancel NUMERIC(14,2),
    cantidad NUMERIC(10,2),
    valor NUMERIC(14,2)
);

CREATE TABLE imp_predio_otra_instalacion (
    id_predio_otra_instalacion SERIAL PRIMARY KEY,
    id_dj_predio INT NOT NULL REFERENCES imp_dj_predio(id_dj_predio),
    id_nivel VARCHAR(10) REFERENCES imp_nivel(id_nivel),
    id_material_estructural_predio INT REFERENCES imp_material_estructural_predio(id_material_estructural_predio),
    id_obra_complementaria CHAR(3) REFERENCES imp_obra_complementaria(id_obra),
    id_depreciacion INT REFERENCES imp_depreciacion(id_depreciacion),
    antiguedad INT,
    area NUMERIC(10,2) NOT NULL,
    valor_instalacion NUMERIC(14,2)
);

-- -----------------------------------------------------
-- 8. DETALLE RÚSTICO Y PAGOS
-- -----------------------------------------------------
CREATE TABLE imp_grupo_tierra_detalle (
    id_grupo_tierra_detalle SERIAL PRIMARY KEY,
    id_grupo_tierra INT NOT NULL REFERENCES imp_grupo_tierra(id_grupo_tierra),
    denominacion VARCHAR(100),
    activo BOOLEAN,
    eliminado BOOLEAN,
    usuario_ingreso VARCHAR(50),
    fecha_ingreso TIMESTAMP
);

CREATE TABLE imp_area_rustica (
    id_area_rustica SERIAL PRIMARY KEY,
    id_grupo_tierra_detalle INT NOT NULL REFERENCES imp_grupo_tierra_detalle(id_grupo_tierra_detalle),
    id_categoria_terreno INT NOT NULL REFERENCES imp_categoria_terreno(id_categoria_terreno),
    valor NUMERIC(18,2)
);

CREATE TABLE imp_pago (
    id_pago SERIAL PRIMARY KEY,
    declaracion_predio_id INT NOT NULL REFERENCES imp_dj_predio(id_dj_predio),
    cuota INT NOT NULL,
    fecha_vencimiento DATE,
    monto NUMERIC(14,2) NOT NULL,
    monto_pagado NUMERIC(14,2) DEFAULT 0,
    fecha_pago DATE,
    estado VARCHAR(15) DEFAULT 'pendiente'
        CHECK (estado IN ('pendiente','pagado','vencido')),
    id_vencimiento_emision INT REFERENCES imp_vencimiento_emision(id_vencimiento_emision)
);

-- -----------------------------------------------------
-- 9. CONTROL FINANCIERO Y MORATORIO
-- -----------------------------------------------------
CREATE TABLE imp_param_moratorio (
    id_param_moratorio SERIAL PRIMARY KEY,
    anio SMALLINT NOT NULL,
    factor_ipm NUMERIC(5,4) NOT NULL,
    tasa_tim NUMERIC(5,4) NOT NULL,
    CONSTRAINT uq_imp_param_moratorio_anio UNIQUE (anio)
);

CREATE TABLE imp_cuenta_corriente (
    id_movimiento SERIAL PRIMARY KEY,
    codigo_contribuyente VARCHAR(15) NOT NULL REFERENCES imp_contribuyente(codigo),
    anio SMALLINT NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    tipo_movimiento VARCHAR(20) NOT NULL 
        CHECK (tipo_movimiento IN ('CARGO', 'ABONO', 'AJUSTE')),
    concepto VARCHAR(50) NOT NULL 
        CHECK (concepto IN ('IMPUESTO', 'DE_EMISION', 'MULTA', 'INTERES', 'PAGO', 'EXTORNO')),
    monto NUMERIC(14,2) NOT NULL,
    saldo NUMERIC(14,2) NOT NULL,
    id_origen_dj INT REFERENCES imp_declaracion_jurada(id_declaracion_jurada),
    id_origen_pago INT REFERENCES imp_pago(id_pago)
);

COMMIT;