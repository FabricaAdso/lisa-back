-- Eliminar el procedimiento si ya existe
DROP PROCEDURE IF EXISTS CargarDatosDesdeExcel;

-- Crear el procedimiento almacenado
CREATE PROCEDURE CargarDatosDesdeExcel()
BEGIN
    DECLARE v_codigo_sede VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    DECLARE v_sede VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    DECLARE v_codigo_regional VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    DECLARE v_regional VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    DECLARE v_ficha VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    DECLARE v_estado_ficha VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    DECLARE v_codigo_programa VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    DECLARE v_version_programa VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    DECLARE v_programa VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    DECLARE v_nivel_formacion VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    DECLARE v_tipo_documento VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    DECLARE v_numero_documento VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    DECLARE v_nombre VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    DECLARE v_primer_apellido VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    DECLARE v_segundo_apellido VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    DECLARE v_estado_aprendiz VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    DECLARE v_identificador_convenio VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    DECLARE v_ampliacion_cobertura VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    DECLARE v_convenio VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    DECLARE v_tipo_documento_empresa VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    DECLARE v_numero_documento_empresa VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    DECLARE v_empresa VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

    -- Cursor para recorrer los datos del Excel
    DECLARE cur CURSOR FOR
    SELECT
        CODIGO_SEDE, SEDE, CODIGO_REGIONAL, REGIONAL, FICHA, ESTADO_FICHA,
        CODIGO_PROGRAMA, VERSION_PROGRANA, PROGRAMA, NIVEL_DE_FORMACION,
        TIPO_DOCUMENTO, NUMERO_DOCUMENTO, NOMBRE, PRIMER_APELLIDO, SEGUNDO_APELLIDO,
        ESTADO_APRENDIZ, IDENTIFICADOR_CONVENIO, AMPLIACION_COVERTURA, CONVENIO,
        TIPO_DOCUMENTO_EMPRESA, NUMERO_DOCUMENTO_EMPRESA, EMPRESA
    FROM temp_excel_table;

    -- Abrir el cursor
    OPEN cur;

    -- Recorrer los datos
    read_loop: LOOP
        FETCH cur INTO
            v_codigo_sede, v_sede, v_codigo_regional, v_regional, v_ficha, v_estado_ficha,
            v_codigo_programa, v_version_programa, v_programa, v_nivel_formacion,
            v_tipo_documento, v_numero_documento, v_nombre, v_primer_apellido, v_segundo_apellido,
            v_estado_aprendiz, v_identificador_convenio, v_ampliacion_cobertura, v_convenio,
            v_tipo_documento_empresa, v_numero_documento_empresa, v_empresa;

        -- Insertar en la tabla training_centers (evitar duplicados)
        INSERT IGNORE INTO training_centers (code, name) VALUES (v_codigo_sede, v_sede);

        -- Insertar en la tabla regionals (evitar duplicados)
        INSERT IGNORE INTO regionals (code, name) VALUES (v_codigo_regional, v_regional);

        -- Insertar en la tabla courses (evitar duplicados)
        INSERT IGNORE INTO courses (code, state) VALUES (v_ficha, REPLACE(v_estado_ficha, ' ', '_'));

        -- Insertar en la tabla programs (evitar duplicados)
        INSERT IGNORE INTO programs (code, version, name) VALUES (v_codigo_programa, v_version_programa, v_programa);

        -- Insertar en la tabla education_levels (evitar duplicados)
        INSERT IGNORE INTO education_levels (name) VALUES (v_nivel_formacion);

        -- Insertar en la tabla document_types (evitar duplicados)
        INSERT IGNORE INTO document_types (abbreviation) VALUES (v_tipo_documento);

        -- Obtener el ID del tipo de documento recién insertado
        SET @document_type_id = (SELECT id FROM document_types WHERE abbreviation = v_tipo_documento COLLATE utf8mb4_unicode_ci LIMIT 1);

        -- Insertar en la tabla users (evitar duplicados)
        INSERT IGNORE INTO users (identity_document, name, last_name, document_type_id)
        VALUES (v_numero_documento, v_nombre, CONCAT(v_primer_apellido, ' ', v_segundo_apellido), @document_type_id);

        -- Insertar en la tabla apprentices (evitar duplicados)
        INSERT IGNORE INTO apprentices (state) VALUES (REPLACE(v_estado_aprendiz, ' ', '_'));

    END LOOP;

    -- Cerrar el cursor
    CLOSE cur;
END;
