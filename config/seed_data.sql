-- config/seed_data.sql (FIXED)

-- 1. Geografia Dummy (PARA FKs)
INSERT INTO imp_departamento (id_departamento, nombre) VALUES (1, 'DEPARTAMENTO DEMO') ON CONFLICT DO NOTHING;
INSERT INTO imp_provincia (id_provincia, id_departamento, nombre) VALUES (1, 1, 'PROVINCIA DEMO') ON CONFLICT DO NOTHING;
INSERT INTO imp_distrito (id_distrito, id_provincia, nombre) VALUES (1, 1, 'DISTRITO DEMO') ON CONFLICT DO NOTHING;

-- 2. Motivos DJ
INSERT INTO imp_motivo_dj (codigo, denominacion, abreviatura, transferencia) VALUES
('01', 'INSCRIPCION', 'INSC', FALSE),
('02', 'AUMENTO DE VALOR', 'AUM', FALSE),
('03', 'DISMINUCION DE VALOR', 'DISM', FALSE),
('04', 'COMPRA', 'COMP', TRUE),
('05', 'VENTA', 'VENT', TRUE),
('06', 'FISCALIZACION', 'FISC', FALSE)
ON CONFLICT (codigo) DO NOTHING;

-- 3. Clasificación Terreno
INSERT INTO imp_clasificacion_terreno (denominacion) VALUES
('URBANO'),
('RUSTICO')
ON CONFLICT DO NOTHING;

-- 4. Usos Predio
INSERT INTO imp_uso_predio (estado, descripcion_uso) VALUES
('activo', 'CASA HABITACION'),
('activo', 'COMERCIO'),
('activo', 'INDUSTRIA'),
('activo', 'TERRENO SIN CONSTRUIR'),
('activo', 'EDUCATIVO'),
('activo', 'RELIGIOSO')
ON CONFLICT DO NOTHING;

-- 5. Tipos de Vía
INSERT INTO imp_tipo_via (id_tipo_via, tipo_via, abreviacion) VALUES
(1, 'AVENIDA', 'AV.'),
(2, 'JIRON', 'JR.'),
(3, 'CALLE', 'CA.'),
(4, 'PASAJE', 'PJ.'),
(5, 'CARRETERA', 'CARR.')
ON CONFLICT (id_tipo_via) DO NOTHING;

-- 6. Vías (Referencia Distrito 1)
-- imp_via no tiene 'codigo' ni 'abreviatura'.
INSERT INTO imp_via (estado, nombre_via, id_tipo_via, id_distrito) VALUES
('activo', 'PRÓCERES DE LA INDEPENDENCIA', 1, 1),
('activo', 'LOS JAZMINES', 3, 1),
('activo', 'MIRAFLORES', 2, 1),
('activo', 'SIMÓN BOLÍVAR', 1, 1),
('activo', 'LOS OLIVOS', 4, 1)
ON CONFLICT DO NOTHING;

-- 7. Sectores
-- imp_sector no tiene 'codigo'.
INSERT INTO imp_sector (estado, nombre_sector, id_distrito) VALUES
('activo', 'CASCO URBANO', 1),
('activo', 'URB. SANTA ROSA', 1),
('activo', 'AA.HH. LOS OLIVOS', 1),
('activo', 'C.P. MIRAFLORES', 1)
ON CONFLICT DO NOTHING;

-- 8. Materiales
INSERT INTO imp_material_estructural_predio (codigo, denominacion, valor_referencia) VALUES
('01', 'CONCRETO ARMADO', 1000.00),
('02', 'LADRILLO', 800.00),
('03', 'ADOBE', 200.00),
('04', 'QUINCHA', 150.00),
('05', 'MADERA', 300.00)
ON CONFLICT (codigo) DO NOTHING;

-- 9. Estados Conservación
INSERT INTO imp_estado_conservacion (codigo, denominacion, factor_depreciacion) VALUES
('01', 'MUY BUENO', 0.00),
('02', 'BUENO', 5.00),
('03', 'REGULAR', 20.00),
('04', 'MALO', 50.00)
ON CONFLICT (codigo) DO NOTHING;
