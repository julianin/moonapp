### Documentación de Mibernate - ORM para JavaScript (Versión Completa)

#### Descripción General
Mibernate es un ORM (Object-Relational Mapping) simplificado diseñado para agilizar la comunicación entre aplicaciones JavaScript en el lado del cliente y bases de datos SQL en el servidor, manejado a través de PHP.

Al Iniciarse a través de llamar a 'mibernate.php?accion=INICIAR', Mibernate genera dinámicamente un archivo JavaScript (mibernate.js) que contendrá toda la funcionalidad JS de mibernate y todas las clases que representarán exactamente las tablas de la base de datos. Las clases generadas se basan en la estructura de la base de datos y contienen propiedades que corresponden a las columnas de la tabla.

TODAS LAS TABLAS DEBEN TENER COMO PRIMERA COLUMN UNA LLAVE PRIMARIA AUTOINCREMENTAL (da igual el nombre). Que represente el código del registro en la tabla.

(Nota: Mibernate.js utiliza jQuery para las solicitudes AJAX.)

#### Configuración y Generación de Mibernate.js
1. **Configuración del Servidor (PHP):**
    - Configura los detalles de conexión de la base de datos (servidor, base de datos, usuario, contraseña) en el script `mibernate.php`.
    - Define `$mibernatejs` como el nombre del archivo JS que interactuará con el script PHP.

2. **Generación de Mibernate.js:**
    - Accede a `mibernate.php` en tu navegador con la consulta `?accion=INICIAR`. Este paso ejecutará el script PHP, generando dinámicamente `mibernate.js` basado en la estructura de tu base de datos.
    - El archivo `mibernate.js` generado contendrá clases necesarias para interactuar con las tablas de la base de datos y funciones para realizar operaciones CRUD.

3. **Inicialización en el Cliente (JavaScript):**
    - Asegúrate de incluir jQuery para las solicitudes AJAX.
    - Incluye el script `mibernate.js` en tu proyecto HTML.
    - Define un array (`mibernateArray`) con los nombres de los objetos (clases) que correspondan a las tablas que deseas interactuar.
    - Utiliza `minit(mibernateArray)` para inicializar y sincronizar las variables con el estado inicial de la base de datos.

#### Funciones de Mibernate.js

- `mibernateLink(a, o)`: Enlaza acciones con el servidor. Maneja internamente las solicitudes AJAX.
- `minit(e)`: Sobrecarga 1: Acepta un array y crea variables globales para cada clase especificada, cargando sus datos. Sobrecarga 2: Sin argumentos, actualiza todas las variables `mi_*` existentes con datos frescos de la base de datos.
- `miVarCreate(constructorName)`: Crea y sincroniza una variable global basada en un nombre de constructor, cargando sus datos.
- `miINSERT_F(obj)`: Inserta un objeto o una colección de objetos en la base de datos.
- `miSELECT(o)`: Obtiene objetos ya cargados y almacenados en el cliente.
- `miUPDATE(o)`: Actualiza un objeto en la base de datos y en la variable del cliente.
- `miINSERT(o)`: Inserta un nuevo objeto en la base de datos y lo añade a la variable del cliente.
- `miCOMMIT()`: Envía todas las operaciones pendientes (acciones registradas) al servidor para ser ejecutadas en la base de datos.

#### Ejemplos de Uso

1. **Inicialización y Carga de Datos:**
    ```javascript
    $(document).ready(function() {
        // Define los objetos que quieres cargar desde la base de datos
        var mibernateArray = ["Usuario", "Producto"];
        // Inicializa y carga los datos
        minit(mibernateArray);
    });
    ```

2. **Insertar un Nuevo Objeto:**
    ```javascript
    // Supongamos que tienes una clase 'Usuario' que corresponde a una tabla en tu base de datos
    let nuevoUsuario = new Usuario('nombreUsuario', 'contraseña');
    miINSERT(nuevoUsuario);
    // Para hacer efectivo el cambio en la base de datos
    miCOMMIT();
    ```

3. **Actualizar un Objeto:**
    ```javascript
    // Supongamos que tienes un objeto usuario cargado en mi_Usuario
    mi_Usuario[0].password = 'nuevaContraseña';
    miUPDATE(mi_Usuario[0]);
    // Para hacer efectivo el cambio en la base de datos
    miCOMMIT();
    ```

4. **Recargar Datos Actualizados:**
    ```javascript
    // Si necesitas recargar los datos de todas las variables mi_* para reflejar cambios recientes
    minit();
    ```

#### Manejo de Errores y Seguridad
- Los errores se manejan mostrando mensajes descriptivos tanto en el lado del servidor (PHP) como del cliente (JavaScript).
- Las consultas a la base de datos se realizan utilizando sentencias preparadas para prevenir la inyección SQL.
- Implementa medidas de seguridad adicionales según sea necesario, como la autenticación de usuarios y la validación de entrada en el servidor.

### Mejores Prácticas y Consideraciones Finales
- Realiza pruebas exhaustivas para garantizar que las operaciones de base de datos se manejen correctamente.
- Asegúrate de que las clases y variables en el cliente estén sincronizadas con la estructura de tu base de datos.
- Mantén el código limpio, bien documentado y sigue las mejores prácticas de desarrollo para asegurar la escalabilidad y el mantenimiento del proyecto.
- Considera expandir la documentación con una sección de Preguntas Frecuentes (FAQ) para resolver dudas comunes y ofrecer soluciones a problemas típicos.

Mibernate simplifica la gestión de bases de datos en aplicaciones web, permitiéndote concentrarte en desarrollar las características únicas de tu aplicación mientras maneja las operaciones de datos de manera eficiente y segura.


### Sección 4: Uso de Mibernate.js y Funciones Específicas



#### 4.1 Inicialización y Carga de Datos (`minit`)

La función `minit` es fundamental en el proceso de inicialización de Mibernate. Se utiliza para cargar y sincronizar datos de la base de datos con variables en el cliente. La función tiene dos formas de sobrecarga:

1. **Sobrecarga 1 (`minit(e)`):**
   - **Propósito:** Crea variables globales para cada clase especificada en el array `e`, cargando sus datos desde la base de datos.
   - **Uso:**
     ```javascript
     $(document).ready(function() {
         // Define los objetos que quieres cargar desde la base de datos
         var mibernateArray = ["Usuario", "Producto"];
         // Inicializa y carga los datos
         minit(mibernateArray);
     });
     ```
   - **Detalles de Implementación:** Por cada nombre de clase en el array, se crea una variable global que almacena los objetos recuperados de la base de datos correspondiente a esa clase.

2. **Sobrecarga 2 (`minit()` sin argumentos):**
   - **Propósito:** Actualiza todas las variables `mi_*` existentes con datos frescos de la base de datos. Es útil para recargar los datos y reflejar los cambios recientes.
   - **Uso:**
     ```javascript
     // Si necesitas recargar los datos de todas las variables mi_* para reflejar cambios recientes
     minit();
     ```
   - **Detalles de Implementación:** Esta forma de la función identifica todas las variables `mi_*` en el contexto global y realiza una solicitud para actualizar cada una con datos frescos de la base de datos.

#### 4.2 Operaciones Específicas en Mibernate.js

1. **`miVarCreate(constructorName)`**
   - **Propósito:** Crea y sincroniza una variable global basada en un nombre de constructor, cargando sus datos.
   - **Uso:**
     ```javascript
     miVarCreate("Usuario");
     ```
   - **Detalles de Implementación:** Utiliza `mibernateLink` para cargar datos desde la base de datos y asignarlos a una variable global `mi_<ConstructorName>`.

2. **`miINSERT_F(obj)`**
   - **Propósito:** Facilita la inserción de objetos individuales o múltiples en la base de datos.
   - **Uso:**
     ```javascript
     let nuevoUsuario = new Usuario('nombreUsuario', 'contraseña');
     miINSERT_F(nuevoUsuario);
     ```
   - **Detalles de Implementación:** Detecta si el objeto es una colección (array) o un objeto individual y realiza la inserción correspondiente. Si es una colección, utiliza `INSERTM` para inserción múltiple.

3. **`miUPDATE(o)`**
   - **Propósito:** Actualiza un objeto tanto en la base de datos como en la variable del cliente.
   - **Uso:**
     ```javascript
     mi_Usuario[0].password = 'nuevaContraseña';
     miUPDATE(mi_Usuario[0]);
     ```
   - **Detalles de Implementación:** Actualiza el objeto en la variable del cliente y registra la acción en `mibernate_actions_reg` para ser comprometida en la base de datos al llamar `miCOMMIT`.

4. **`miINSERT(o)`**
   - **Propósito:** Inserta un nuevo objeto en la base de datos y lo añade a la variable del cliente correspondiente.
   - **Uso:**
     ```javascript
     let nuevoProducto = new Producto('nombreProducto', 'descripción');
     miINSERT(nuevoProducto);
     ```
   - **Detalles de Implementación:** Añade el objeto a la variable del cliente y registra la acción de inserción en `mibernate_actions_reg`.

5. **`miCOMMIT()`**
   - **Propósito:** Envía todas las operaciones pendientes al servidor para ser ejecutadas en la base de datos.
   - **Uso:**
     ```javascript
     miCOMMIT();
     ```
   - **Detalles de Implementación:** Itera sobre `mibernate_actions_reg` y envía las acciones registradas al servidor para ser ejecutadas de forma atómica.

#### 4.3 Ejemplos Expandidos y Uso Práctico

- **Inserción y Actualización:**
    ```javascript
    // Crear un nuevo usuario
    let usuario = new Usuario('john_doe', 'password123');
    miINSERT(usuario);
    miCOMMIT();  // Hace efectiva la inserción en la base de datos

    // Actualizar la contraseña del usuario
    usuario.password = 'new_password';
    miUPDATE(usuario);
    miCOMMIT();  // Hace efectiva la actualización en la base de datos
    ```

- **Recargar Datos Después de Cambios Externos:**
    ```javascript
    // Supongamos que los datos en la base de datos han cambiado debido a acciones de otros usuarios
    // Recargar datos para reflejar los cambios más recientes
    minit();
    ```

#### 4.4 Manejo de Errores y Seguridad

- Los errores se manejan mostrando mensajes descriptivos en la consola. Es importante que estos mensajes sean informativos para facilitar la depuración.
- Todas las consultas a la base de datos se realizan utilizando sentencias preparadas para prevenir inyecciones SQL.
- Implementa medidas de seguridad adicionales en el servidor, como la autenticación de usuarios y la validación de entrada para proteger los datos.

### Consideraciones Finales y Mejores Prácticas

- **Pruebas Rigurosas:** Asegúrate de realizar pruebas exhaustivas, especialmente en operaciones que modifican datos en la base de datos.
- **Sincronización de Datos:** Mantén una sincronización adecuada entre las variables del cliente y la base de datos para evitar inconsistencias.
- **Documentación y Comentarios:** Mantén el código bien documentado y comenta las partes complejas para facilitar el mantenimiento y la escalabilidad del proyecto.
- **Seguridad:** No te olvides de la seguridad en el lado del servidor, especialmente si tu aplicación está expuesta en Internet.

Mibernate ofrece una forma estructurada y eficiente de manejar la persistencia de datos en aplicaciones web. Con una configuración adecuada y un uso cuidadoso, puede ser una herramienta valiosa en tu arsenal de desarrollo, permitiéndote concentrarte en crear experiencias de usuario únicas y atractivas.