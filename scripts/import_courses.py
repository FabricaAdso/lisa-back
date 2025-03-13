import pandas as pd
import mysql.connector
from datetime import datetime
import os
import sys

# Configuración de la base de datos
db_config = {
    'host': 'localhost',
    'user': 'root',  # Cambia por tu usuario de MySQL
    'password': '@p1061701851',  # Cambia por tu contraseña de MySQL
    'database': 'lisa_back'  # Cambia por el nombre de tu base de datos
}

# Mapeo de estados
state_mapping = {
    'Terminada por fecha': 'Terminada_por_fecha',
    'En ejecucion': 'En_ejecucion',
    'Terminada': 'Terminada',
    'Terminada por unificacion': 'Termindad_por_unificacion'
}

def import_courses(file_path):
    conn = None
    cursor = None
    try:
        # Verificar si el archivo existe
        if not os.path.exists(file_path):
            raise FileNotFoundError(f"El archivo {file_path} no existe")

        # Leer el archivo Excel
        df = pd.read_excel(file_path)

        # Conectar a la base de datos
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor()

        for index, row in df.iterrows():
            print(f"Procesando fila {index + 1}: {row}")  # Depuración

            # Obtener o crear Regional
            cursor.execute("SELECT id FROM regionals WHERE name = %s", (row['REGIONAL'],))
            regional = cursor.fetchone()
            if not regional:
                cursor.execute("INSERT INTO regionals (name) VALUES (%s)", (row['REGIONAL'],))
                regional_id = cursor.lastrowid
            else:
                regional_id = regional[0]

            # Obtener o crear TrainingCenter
            cursor.execute("SELECT id FROM training_centers WHERE code = %s", (row['ID_CENTRO'],))
            training_center = cursor.fetchone()
            if not training_center:
                cursor.execute(
                    "INSERT INTO training_centers (code, name, regional_id) VALUES (%s, %s, %s)",
                    (row['ID_CENTRO'], row['CENTRO'], regional_id)
                )
                training_center_id = cursor.lastrowid
            else:
                training_center_id = training_center[0]

            # Obtener o crear EducationLevel
            cursor.execute("SELECT id FROM education_levels WHERE name = %s", (row['NIVEL'],))
            education_level = cursor.fetchone()
            if not education_level:
                cursor.execute("INSERT INTO education_levels (name) VALUES (%s)", (row['NIVEL'],))
                education_level_id = cursor.lastrowid
            else:
                education_level_id = education_level[0]

            # Obtener o crear Program
            cursor.execute(
                "SELECT id FROM programs WHERE code = %s AND version = %s",
                (row['CODIGO_PROGRAMA'], row['VERSION_PROGRAMA'])
            )
            program = cursor.fetchone()
            if not program:
                try:
                    cursor.execute(
                        "INSERT INTO programs (code, version, name, education_level_id, training_center_id) VALUES (%s, %s, %s, %s, %s)",
                        (row['CODIGO_PROGRAMA'], row['VERSION_PROGRAMA'], row['PROGRAMA'], education_level_id, training_center_id)
                    )
                    program_id = cursor.lastrowid
                except mysql.connector.Error as err:
                    if err.errno == 1062:  # Código de error para duplicados
                        print(f"Programa duplicado: {row['CODIGO_PROGRAMA']} (Versión: {row['VERSION_PROGRAMA']})")
                        cursor.execute(
                            "SELECT id FROM programs WHERE code = %s AND version = %s",
                            (row['CODIGO_PROGRAMA'], row['VERSION_PROGRAMA'])
                        )
                        program = cursor.fetchone()
                        if program:  # Verificar si program no es None
                            program_id = program[0]
                        else:
                            print(f"Error: No se encontró el programa duplicado {row['CODIGO_PROGRAMA']} (Versión: {row['VERSION_PROGRAMA']})")
                            continue  # Saltar esta fila y continuar con la siguiente
                    else:
                        raise err
            else:
                program_id = program[0]  # Usar el ID existente

            # Obtener estado mapeado
            state = state_mapping.get(row['ESTADO_FICHA'].strip(), 'En_ejecucion')

            # Insertar Course si no existe
            cursor.execute("SELECT id FROM courses WHERE code = %s", (row['FICHA'],))
            if not cursor.fetchone():
                cursor.execute(
                    "INSERT INTO courses (code, date_start, date_end, state, shift, program_id) VALUES (%s, %s, %s, %s, %s, %s)",
                    (
                        row['FICHA'],
                        row['FECHA_INICIO_FICHA'].to_pydatetime(),  # Convertir Timestamp a datetime
                        row['FECHA_FIN_FICHA'].to_pydatetime(),    # Convertir Timestamp a datetime
                        state,
                        row['JORNADA'],
                        program_id
                    )
                )

        conn.commit()
        print("Archivo importado correctamente")
    except FileNotFoundError as e:
        print(f"Error: {e}")
    except Exception as e:
        if conn:
            conn.rollback()
        print(f"Error al importar el archivo: {e}")
    finally:
        if cursor:
            cursor.close()
        if conn:
            conn.close()

if __name__ == "__main__":
    if len(sys.argv) != 2:
        print("Uso: python3 import_courses.py <ruta_al_archivo>")
        sys.exit(1)

    file_path = sys.argv[1]  # Ruta del archivo Excel
    import_courses(file_path)
