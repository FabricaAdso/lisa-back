import pandas as pd
import mysql.connector
import os
import sys

# Configuración de la base de datos
db_config = {
    'host': 'localhost',
    'user': 'root',  # Cambia por tu usuario de MySQL
    'password': 'antonio123',  # Cambia por tu contraseña de MySQL
    'database': 'lisa_back'  # Cambia por el nombre de tu base de datos
}

def import_instructors(file_path):
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
        cursor = conn.cursor(buffered=True)  # Usar un cursor buffered

        for index, row in df.iterrows():
            # Obtener o crear DocumentType
            cursor.execute("SELECT id FROM document_types WHERE abbreviation = %s", (row['TIPO_DOCUMENTO'],))
            document_type = cursor.fetchone()
            if not document_type:
                cursor.execute("INSERT INTO document_types (abbreviation) VALUES (%s)", (row['TIPO_DOCUMENTO'],))
                document_type_id = cursor.lastrowid
            else:
                document_type_id = document_type[0]

            # Crear o actualizar User
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

            # Obtener Training Center
            cursor.execute("SELECT id FROM training_centers WHERE name = %s", (row['CENTRO_FORMACION'],))
            training_center = cursor.fetchone()
            if not training_center:
                continue  # Saltar esta fila si no existe el centro de formación
            training_center_id = training_center[0]

            # Obtener Knowledge Network
            cursor.execute("SELECT id FROM knowledge_networks WHERE name = %s", (row['RED_DE_CONOCIMIENTO'],))
            knowledge_network = cursor.fetchone()
            if not knowledge_network:
                continue  # Saltar esta fila si no existe la red de conocimiento
            knowledge_network_id = knowledge_network[0]

            # Crear o actualizar Instructor
            cursor.execute("SELECT id FROM instructors WHERE user_id = %s", (user_id,))
            instructor = cursor.fetchone()
            if not instructor:
                cursor.execute(
                    "INSERT INTO instructors (user_id, training_center_id, state, knowledge_network_id) VALUES (%s, %s, %s, %s)",
                    (user_id, training_center_id, row['ESTADO_INSTRUCTOR'], knowledge_network_id)
                )
            else:
                cursor.execute(
                    "UPDATE instructors SET training_center_id = %s, state = %s, knowledge_network_id = %s WHERE user_id = %s",
                    (training_center_id, row['ESTADO_INSTRUCTOR'], knowledge_network_id, user_id)
                )

        conn.commit()
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
        print("Uso: python3 imports_instructors.py <ruta_al_archivo>")
        sys.exit(1)

    file_path = sys.argv[1]  # Ruta del archivo Excel
    import_instructors(file_path)
