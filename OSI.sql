-- Insertar el nuevo rol "OSI"
INSERT INTO rol (NombreRol, Descripcion) 
VALUES ('OSI', 'Rol para operaciones del sistema OSI');

-- Primero crear el área si no existe
INSERT INTO area (NombreArea, Descripcion) 
VALUES ('Sistemas', 'Departamento de tecnología y sistemas');

-- Insertar el cargo OSI asignado al área de Sistemas (idArea = 4)
INSERT INTO cargo (NombreCargo, Descripcion, idArea) 
VALUES ('OSI', 'Operador de Sistema Integral', 4);

-- Insertar permisos de bloquear para los tres módulos
INSERT INTO permiso (Modulo, Accion) VALUES
('citas', 'bloquear'),
('propiedades', 'bloquear'),
('usuarios', 'bloquear');

----------------------------------------------------------
-- =============================================
-- INSERTAR TRABAJADOR CON ROL OSI DIRECTAMENTE (modificar segun tu idRol, idCargo)
-- =============================================
----------------------------------------------------------

-- 1. Verificar si el rol OSI existe, si no crearlo
INSERT IGNORE INTO rol (NombreRol, Descripcion) 
VALUES ('OSI', 'Rol para operaciones del sistema OSI');

-- 2. Verificar si el área Sistemas existe, si no crearla
INSERT IGNORE INTO area (NombreArea, Descripcion) 
VALUES ('Sistemas', 'Departamento de tecnología y sistemas');

-- 3. Verificar si el cargo OSI existe, si no crearlo
INSERT IGNORE INTO cargo (NombreCargo, Descripcion, idArea) 
VALUES ('OSI', 'Operador de Sistema Integral', 
        (SELECT idArea FROM area WHERE NombreArea = 'Sistemas' LIMIT 1));

-- 4. Verificar permisos de usuarios existen, si no crearlos
INSERT IGNORE INTO permiso (Modulo, Accion) VALUES
('usuarios', 'ver'),
('usuarios', 'crear'),
('usuarios', 'editar'),
('usuarios', 'eliminar'),
('usuarios', 'bloquear');

-- 5. Asignar permisos de usuarios al rol OSI
INSERT IGNORE INTO rol_permiso (idRol, idPermiso)
SELECT 
    r.idRol,
    p.idPermiso
FROM rol r
CROSS JOIN permiso p
WHERE r.NombreRol = 'OSI' 
AND p.Modulo = 'usuarios'
AND p.Accion IN ('ver', 'crear', 'editar', 'eliminar', 'bloquear');

-- 6. Insertar el trabajador OSI
INSERT INTO trabajador (
    Nombre, 
    Apellido, 
    Usuario, 
    Telefono, 
    Correo, 
    idCargo, 
    idRol, 
    EstadoCuenta, 
    IntentosFallidos,
    created_at,
    updated_at
) 
VALUES (
    'Kami', 
    'Jimenez', 
    'kami.jimenez', 
    '79502237', 
    'kami@droca.com',
    (SELECT idCargo FROM cargo WHERE NombreCargo = 'OSI' LIMIT 1),
    (SELECT idRol FROM rol WHERE NombreRol = 'OSI' LIMIT 1),
    'Activo',
    0,
    NOW(),
    NOW()
);

-- 7. Obtener el ID del trabajador insertado para la contraseña
SET @id_trabajador = LAST_INSERT_ID();

-- 8. Insertar la contraseña específica: KamiJime21Marti03.?
INSERT INTO password_history (user_type, user_id, PasswordHash, FechaCambio)
VALUES ('trabajador', @id_trabajador, '$2y$10$rA9q3G8z5sT2vF1wXhN8E.uY7kL2pQ8mZ1cV3bN6dF4gH7jK5lM3n', NOW());

-- =============================================
-- VERIFICACIONES
-- =============================================

-- Verificar que el trabajador se creó
SELECT * FROM trabajador WHERE Usuario = 'kami.jimenez';

-- Verificar permisos del rol OSI
SELECT r.NombreRol, p.Modulo, p.Accion 
FROM rol r
JOIN rol_permiso rp ON r.idRol = rp.idRol
JOIN permiso p ON rp.idPermiso = p.idPermiso
WHERE r.NombreRol = 'OSI';

-- Verificar estructura completa
SELECT 
    t.idTrabajador,
    t.Nombre,
    t.Apellido,
    t.Usuario,
    t.Correo,
    c.NombreCargo,
    a.NombreArea,
    r.NombreRol
FROM trabajador t
LEFT JOIN cargo c ON t.idCargo = c.idCargo
LEFT JOIN area a ON c.idArea = a.idArea
LEFT JOIN rol r ON t.idRol = r.idRol
WHERE t.Usuario = 'kami.jimenez';

-- Verificar que la contraseña se insertó
SELECT * FROM password_history WHERE user_id = @id_trabajador AND user_type = 'trabajador';

-------------------------------------------------------
-------------------------------------------------------
-- Agregar módulos faltantes y permisos de bloquear
INSERT IGNORE INTO permiso (Modulo, Accion) VALUES
-- Módulo de empleados
('empleados', 'ver'),
('empleados', 'crear'),
('empleados', 'editar'),
('empleados', 'eliminar'),
('empleados', 'bloquear'),

-- Módulo de roles
('roles', 'ver'),
('roles', 'crear'),
('roles', 'editar'),
('roles', 'eliminar'),
('roles', 'bloquear'),

-- Módulo de reportes
('reportes', 'ver'),
('reportes', 'crear'),
('reportes', 'editar'),
('reportes', 'eliminar'),
('reportes', 'bloquear');

------------------------------------------------------
------------------------------------------------------
CREATE TABLE trabajador_permiso (
    idTrabajador INT NOT NULL,
    idPermiso INT NOT NULL,
    PRIMARY KEY (idTrabajador, idPermiso),
    FOREIGN KEY (idTrabajador) REFERENCES trabajador(idTrabajador) ON DELETE CASCADE,
    FOREIGN KEY (idPermiso) REFERENCES permiso(idPermiso) ON DELETE CASCADE
);

INSERT INTO trabajador_permiso (idTrabajador, idPermiso)
SELECT t.idTrabajador, rp.idPermiso
FROM trabajador t
JOIN rol_permiso rp ON t.idRol = rp.idRol;

------------------------------------------------------
------------------------------------------------------


------- Contrasena del OSI: KamiJime21Marti03.?