import pandas as pd
import mysql.connector
from datetime import datetime
import os
import sys
import signal

signal.signal(signal.SIGALRM, lambda signum, frame: print("Tiempo de ejecución excedido"))
signal.alarm(600)  # 600 segundos (10 minutos)

# Configuración de la base de datos
db_config = {
    'host': 'localhost',
    'user': 'root',  # Cambia por tu usuario de MySQL
    'password': 'fabrica123',  # Cambia por tu contraseña de MySQL
    'database': 'lisa_back'  # Cambia por el nombre de tu base de datos
}

# Mapeo de estados
state_mapping = {
    'Formacion': 'Formacion',
    'Desertado': 'Desertado',
    'Etapa productiva': 'Etapa_productiva',
    'Retiro voluntario': 'Retiro_voluntario'
}

def import_apprentices(file_path):
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

            # Obtener o crear DocumentType
            cursor.execute("SELECT id FROM document_types WHERE abbreviation = %s", (row['TIPO_DOCUMENTO'],))
            document_type = cursor.fetchone()
            if not document_type:
                cursor.execute("INSERT INTO document_types (abbreviation) VALUES (%s)", (row['TIPO_DOCUMENTO'],))
                document_type_id = cursor.lastrowid
            else:
                document_type_id = document_type[0]

            # Crear User
            cursor.execute("SELECT id FROM users WHERE identity_document = %s", (row['NUMERO_DOCUMENTO'],))
            user = cursor.fetchone()
            if not user:
                cursor.execute(
                    "INSERT INTO users (name, last_name, identity_document, email, document_type_id) VALUES (%s, %s, %s, %s, %s)",
                    (
                        row['NOMBRE'],
                        f"{row['PRIMER_APELLIDO']} {row['SEGUNDO_APELLIDO']}",
                        row['NUMERO_DOCUMENTO'],
                        row['CORREO_ELECTRONICO'],
                        document_type_id
                    )
                )
                user_id = cursor.lastrowid
            else:
                user_id = user[0]

            # Obtener Course
            cursor.execute("SELECT id FROM courses WHERE code = %s", (row['FICHA'],))
            course = cursor.fetchone()
            if not course:
                print(f"Error: No se encontró la ficha {row['FICHA']}")
                continue  # Saltar esta fila si no existe la ficha
            course_id = course[0]

            # Mapear estado
            state = state_mapping.get(row['ESTADO_APRENDIZ'].strip(), 'Formacion')

            # Crear Apprentice
            cursor.execute("SELECT id FROM apprentices WHERE user_id = %s AND course_id = %s", (user_id, course_id))
            if not cursor.fetchone():
                cursor.execute(
                    "INSERT INTO apprentices (user_id, course_id, state) VALUES (%s, %s, %s)",
                    (user_id, course_id, state)
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
        print("Uso: python3 imports_apprentices.py <ruta_al_archivo>")
        sys.exit(1)

    file_path = sys.argv[1]  # Ruta del archivo Excel
    import_apprentices(file_path)
