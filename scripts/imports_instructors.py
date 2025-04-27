# import pandas as pd
# import mysql.connector
# import os
# import sys

# # Configuración de la base de datos
# db_config = {
#     'host': 'localhost',
#     'user': 'root',  # Cambia por tu usuario de MySQL
#     'password': 'fabrica123',  # Cambia por tu contraseña de MySQL
#     'database': 'lisa_back'  # Cambia por el nombre de tu base de datos
# }

# def import_instructors(file_path):
#     conn = None
#     cursor = None
#     try:
#         # Verificar si el archivo existe
#         if not os.path.exists(file_path):
#             raise FileNotFoundError(f"El archivo {file_path} no existe")

#         # Leer el archivo Excel
#         df = pd.read_excel(file_path)

#         # Conectar a la base de datos
#         conn = mysql.connector.connect(**db_config)
#         cursor = conn.cursor(buffered=True)  # Usar un cursor buffered

#         for index, row in df.iterrows():
#             # Obtener o crear DocumentType
#             cursor.execute("SELECT id FROM document_types WHERE abbreviation = %s", (row['TIPO_DOCUMENTO'],))
#             document_type = cursor.fetchone()
#             if not document_type:
#                 cursor.execute("INSERT INTO document_types (abbreviation) VALUES (%s)", (row['TIPO_DOCUMENTO'],))
#                 document_type_id = cursor.lastrowid
#             else:
#                 document_type_id = document_type[0]

#             # Crear o actualizar User
#             cursor.execute("SELECT id FROM users WHERE identity_document = %s", (row['NUMERO_DOCUMENTO'],))
#             user = cursor.fetchone()
#             if not user:
#                 cursor.execute(
#                     "INSERT INTO users (name, last_name, identity_document, email, document_type_id) VALUES (%s, %s, %s, %s, %s)",
#                     (
#                         row['NOMBRE'],
#                         f"{row['PRIMER_APELLIDO']} {row['SEGUNDO_APELLIDO']}",
#                         row['NUMERO_DOCUMENTO'],
#                         row['CORREO_ELECTRONICO'],
#                         document_type_id
#                     )
#                 )
#                 user_id = cursor.lastrowid
#             else:
#                 user_id = user[0]

#             # Obtener Training Center
#             cursor.execute("SELECT id FROM training_centers WHERE name = %s", (row['CENTRO_FORMACION'],))
#             training_center = cursor.fetchone()
#             if not training_center:
#                 continue  # Saltar esta fila si no existe el centro de formación
#             training_center_id = training_center[0]

#             # Obtener Knowledge Network
#             cursor.execute("SELECT id FROM knowledge_networks WHERE name = %s", (row['RED_DE_CONOCIMIENTO'],))
#             knowledge_network = cursor.fetchone()
#             if not knowledge_network:
#                 continue  # Saltar esta fila si no existe la red de conocimiento
#             knowledge_network_id = knowledge_network[0]

#             # Crear o actualizar Instructor
#             cursor.execute("SELECT id FROM instructors WHERE user_id = %s", (user_id,))
#             instructor = cursor.fetchone()
#             if not instructor:
#                 cursor.execute(
#                     "INSERT INTO instructors (user_id, training_center_id, state, knowledge_network_id) VALUES (%s, %s, %s, %s)",
#                     (user_id, training_center_id, row['ESTADO_INSTRUCTOR'], knowledge_network_id)
#                 )
#             else:
#                 cursor.execute(
#                     "UPDATE instructors SET training_center_id = %s, state = %s, knowledge_network_id = %s WHERE user_id = %s",
#                     (training_center_id, row['ESTADO_INSTRUCTOR'], knowledge_network_id, user_id)
#                 )

#         conn.commit()
#     except FileNotFoundError as e:
#         print(f"Error: {e}")
#     except Exception as e:
#         if conn:
#             conn.rollback()
#         print(f"Error al importar el archivo: {e}")
#     finally:
#         if cursor:
#             cursor.close()
#         if conn:
#             conn.close()

# if __name__ == "__main__":
#     if len(sys.argv) != 2:
#         print("Uso: python3 imports_instructors.py <ruta_al_archivo>")
#         sys.exit(1)

#     file_path = sys.argv[1]  # Ruta del archivo Excel
#     import_instructors(file_path)

import pandas as pd
import mysql.connector
import os
import sys
import signal

signal.signal(signal.SIGALRM, lambda signum, frame: print("Tiempo de ejecución excedido"))
signal.alarm(600)

# Configuración de la base de datos
db_config = {
    'host': 'localhost',
    'user': 'root',
    'password': 'fabrica123',
    'database': 'lisa_back'
}

def import_instructors(file_path):
    conn = None
    cursor = None
    try:
        # Verificar si el archivo existe
        if not os.path.exists(file_path):
            raise FileNotFoundError(f"El archivo {file_path} no existe")

        # Leer el archivo Excel
        df = pd.read_excel(file_path).fillna('')

        # Conectar a la base de datos
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor(buffered=True)

        for index, row in df.iterrows():
            print(f"\nProcesando fila {index + 1}")

            try:
                # 1. Obtener DocumentType
                cursor.execute("SELECT id FROM document_types WHERE abbreviation = %s", (row['TIPO_DOCUMENTO'],))
                document_type = cursor.fetchone()
                if not document_type:
                    cursor.execute("INSERT INTO document_types (abbreviation) VALUES (%s)", (row['TIPO_DOCUMENTO'],))
                    document_type_id = cursor.lastrowid
                else:
                    document_type_id = document_type[0]

                # 2. Crear/Actualizar User
                identity_doc = str(row['NUMERO_DOCUMENTO']).strip()
                cursor.execute("SELECT id FROM users WHERE identity_document = %s", (identity_doc,))
                user = cursor.fetchone()

                if not user:
                    last_name = f"{row['PRIMER_APELLIDO']} {row['SEGUNDO_APELLIDO']}".strip()
                    cursor.execute(
                        """INSERT INTO users
                        (name, last_name, identity_document, email, document_type_id)
                        VALUES (%s, %s, %s, %s, %s)""",
                        (
                            str(row['NOMBRE']).strip(),
                            last_name,
                            identity_doc,
                            str(row['CORREO_ELECTRONICO']).strip(),
                            document_type_id
                        )
                    )
                    user_id = cursor.lastrowid
                    print(f"  → Nuevo usuario creado (ID: {user_id})")
                else:
                    user_id = user[0]
                    print(f"  → Usuario existente (ID: {user_id})")

                # 3. Obtener Training Center por código (ID_CENTRO)
                centro_code = str(row['ID_CENTRO']).strip()
                cursor.execute("SELECT id FROM training_centers WHERE code = %s", (centro_code,))
                training_center = cursor.fetchone()

                if not training_center:
                    print(f"  × Error: Centro con código {centro_code} no encontrado")
                    continue

                training_center_id = training_center[0]
                print(f"  → Centro de formación encontrado (ID: {training_center_id})")

                # 4. Obtener Knowledge Network
                red_conocimiento = str(row['RED_DE_CONOCIMIENTO']).strip()
                cursor.execute("SELECT id FROM knowledge_networks WHERE name = %s", (red_conocimiento,))
                knowledge_network = cursor.fetchone()

                if not knowledge_network:
                    print(f"  × Error: Red de conocimiento {red_conocimiento} no encontrada")
                    continue

                knowledge_network_id = knowledge_network[0]
                print(f"  → Red de conocimiento encontrada (ID: {knowledge_network_id})")

                # 5. Insertar/Actualizar Instructor
                estado = str(row['ESTADO_INSTRUCTOR']).strip() if row['ESTADO_INSTRUCTOR'] else 'Activo'
                cursor.execute("SELECT id FROM instructors WHERE user_id = %s", (user_id,))
                instructor = cursor.fetchone()

                if not instructor:
                    cursor.execute(
                        """INSERT INTO instructors
                        (user_id, training_center_id, state, knowledge_network_id)
                        VALUES (%s, %s, %s, %s)""",
                        (user_id, training_center_id, estado, knowledge_network_id)
                    )
                    print(f"  → Nuevo instructor creado")
                else:
                    cursor.execute(
                        """UPDATE instructors SET
                        training_center_id = %s,
                        state = %s,
                        knowledge_network_id = %s
                        WHERE user_id = %s""",
                        (training_center_id, estado, knowledge_network_id, user_id)
                    )
                    print(f"  → Instructor actualizado")

                # 6. Insertar en role_training_center_user (ROL 3 para instructores)
                cursor.execute(
                    """SELECT id FROM role_training_center_user
                    WHERE user_id = %s AND role_id = 3 AND training_center_id = %s""",
                    (user_id, training_center_id)
                )

                if not cursor.fetchone():
                    cursor.execute(
                        """INSERT INTO role_training_center_user
                        (user_id, role_id, training_center_id)
                        VALUES (%s, %s, %s)""",
                        (user_id, 3, training_center_id)
                    )
                    print(f"  → Rol asignado (Centro: {training_center_id})")

            except Exception as row_error:
                print(f"  × Error en fila {index + 1}: {str(row_error)}")
                continue

        conn.commit()
        print("\n✅ Importación completada con éxito")

    except Exception as e:
        if conn: conn.rollback()
        print(f"\n❌ ERROR GLOBAL: {str(e)}")
    finally:
        if cursor: cursor.close()
        if conn: conn.close()

if __name__ == "__main__":
    if len(sys.argv) != 2:
        print("Uso: python3 imports_instructors.py <ruta_al_archivo>")
        sys.exit(1)
    import_instructors(sys.argv[1])
