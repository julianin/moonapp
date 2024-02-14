<?php
//SETTINGS SETUP PERMISOS Y ERRORES
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
error_reporting(E_ALL); ini_set('display_errors', '1');
//BBDDD SETUP
$servername = "192.168.1.238:3306";
$database = "moonapp";
$username = "user";
$password = "sadm";
//nombre del JS
$mibernatejs = "mibernate.js";
$phpliburl = "https://homecasas.ddns.net/moonapp/libs/mibernate/mibernate.php";
$accion = $_POST["accion"] ?? $_GET["accion"] ?? die("mibernate ERROR 1");
$token = $_POST["token"] ?? $_GET["token"] ?? die("mibernate ERROR 1");
session_start();



try {
    $conexion = new PDO("mysql:host=$servername;dbname=$database", "$username", "$password");
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($token)) {
        // Verificar si el token existe en la tabla SESSION_LOG
        $stmt = $conexion->prepare("SELECT * FROM SESSION_LOG WHERE SLOG_TOKEN = :token");
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            // Si el token existe, permitir las acciones
            if ($accion === "INSERT") {
                echo handleInsertAction($conexion, $_POST);
            } elseif ($accion === "SELECT") {
                echo handleSelectAction($conexion, $_POST);
            } elseif ($accion === "UPDATE") {
                echo handleUpdateAction($conexion, $_POST);
            } elseif ($accion === "SELECTALL") {
                echo handleSelectAllAction($conexion, $_POST);
            } elseif ($accion === "COMMIT") {
                echo handleCommitAction($conexion, $_POST);
            } elseif ($accion === "INSERTM") {
                echo handleInsertMultipleAction($conexion, $_POST);
            } elseif ($accion === "UPDATEM") {
                handleUpdateMultipleAction($conexion, $_POST);
            }
        } else {
            // Si el token no existe, mostrar un mensaje de error o tomar otra acción
            echo "Token no válido. Acceso no autorizado.";
        }
    }elseif ($accion === "INICIAR") {
        echo generarMibernateJS($phpliburl);
    }else {
        echo "ACCION ERROR";
    }
} catch (PDOException $e) {
    die("BBDD ERROR:" . $e->getMessage());
}


//GENERADORES DE mibernate.js
function generarClasesJSDesdeJSON($json) {
    $clases = [];
    foreach ($json as $tablaNombre => $tabla) {
        $propiedades = [];
        $constructorParams = [];
        foreach ($tabla as $columna) {
            $nombre = $columna['Field'];
            $propiedades[] = "this.$nombre = $nombre;";
            $constructorParams[] = "$nombre";
        }

        $constructorParamsStr = implode(", ", $constructorParams);
        $propiedadesStr = implode("\n", $propiedades);

        $clase = "class $tablaNombre extends DatabaseObject {\n";
        $clase .= "    constructor($constructorParamsStr) {\n";
        $clase .= "        super();\n";
        $clase .= "        $propiedadesStr\n";
        $clase .= "    }\n";
        $clase .= "}\n";

        $clases[] = $clase;
    }
    return implode("\n\n", $clases);
}

function getDatabaseInfo() {
    try {
        global $conexion;
        $consulta = "SHOW TABLES";
        $resultado = $conexion->query($consulta);
        $tablas = $resultado->fetchAll(PDO::FETCH_COLUMN);
        $informacionTablas = [];
        foreach ($tablas as $tablaNombre) {
            $consulta = "DESCRIBE $tablaNombre";
            $resultado = $conexion->query($consulta);
            $columnas = $resultado->fetchAll(PDO::FETCH_ASSOC);
            $informacionTablas[$tablaNombre] = $columnas;
        }
        $conexion = null;
        return $informacionTablas;
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

function generarMibernateJS($phpliburl) {
    $databaseInfo = getDatabaseInfo();
    $clasesJS = generarClasesJSDesdeJSON($databaseInfo);

    $javascriptCode = <<<EOD
var mibernate_actions_reg = [];
var mibernate_LAST_INSERT = 0;

// Función para realizar las peticiones AJAX a mibernate.php
async function mibernateLink(a, o) {
    const removeNulls = obj => Object.fromEntries(Object.entries(obj).filter(([k, v]) => v !== null));
    try {
        const r = await $.post("http://homecasas.ddns.net/Dobey/LIBS/mibernate/mibernate.php", { accion: a, tabla: o.constructor.name, objeto: removeNulls(o) });
        return a === "INSERT" ? (mibernate_LAST_INSERT = r) : a === "SELECTALL" ? JSON.parse(r) : r;
    } catch (e) {
        console.error("mibernateLink() Error:", e);
        throw e;
    }
}

//Inicializa o actualiza las variables window.mi_* con los datos de la base de datos,
function mibernate(e) {
    e.forEach(t => {
        const variableName = "mi_" + t;
        if (!window[variableName]) {
            const constructorFn = eval(t);
            mibernateLink("SELECTALL", new constructorFn())
                .then(r => window["mi_" + t] = r)
                .catch(e => console.error("Error", e));
        } else {
            mibernateLink("SELECTALL", new window[variableName]())
                .then(r => window[variableName] = r)
                .catch(e => console.error("Error al actualizar", variableName, e));
        }
    });
}

//Actualiza todas las variables window.mi_*
function mibernateAll() {
    // Crea un array con el nombre de todas las variables window.mi_*
    var arr = Object.keys(window).filter(function (k) {
        return k.indexOf("mi_") === 0;
    });

    // Realiza una solicitud para obtener y actualizar todas las variables window.mi_*
    Promise.all(arr.map(function (key) {
        var constructorName = key.substring(3); // Elimina el prefijo "mi_"
        const constructorFn = eval(constructorName);

        return mibernateLink("SELECTALL", new constructorFn())
            .then(r => window[key] = r)
            .catch(e => console.error("Error al actualizar", key, e));
    }))
        .then(() => console.log("Todas las variables actualizadas con éxito"))
        .catch(error => console.error("Error al actualizar todas las variables", error));
}

// miINSERT_F inserta un registro o colección de registros en la base de datos
function miINSERT_F(obj) {
    if (Array.isArray(obj)) {
        if (obj.length > 0) {
            obj.constructorName = obj[0].constructor.name;
            mibernateLink("INSERTM", item);
        } else {
            console.error("La colección está vacía, no se puede determinar el nombre del constructor.");
        }
    } else if (typeof obj === 'object') {
        mibernateLink("INSERT", obj);
    } else {
        console.error("Tipo de dato no válido para inserción");
    }
}

function miUPDATE_F(obj){
    if (Array.isArray(obj)) {
        if (obj.length > 0) {
            obj.constructorName = obj[0].constructor.name;
            mibernateLink("UPDATEM", item);
        } else {
            console.error("La colección está vacía, no se puede determinar el nombre del constructor.");
        }
    } else if (typeof obj === 'object') {
        mibernateLink("UPDATE", obj);
    } else {
        console.error("Tipo de dato no válido para inserción");
    }
}


function miSELECT(o) {
    if (typeof o === "object") {
        return window["mi_" + o.constructor.name]}
    else if (typeof o === "string") {
        return window["mi_" + o];
    }
}

function miUPDATE(o) {
    try {
        var Sobj = window["mi_" + o.constructor.name];
        var key = Object.keys(o)[0];
        for (var i = 0; i < Sobj.length; i++) {
            if (Sobj[i][key] == o[key]) {
                Sobj[i] = o;
                break;
            }
        }
    } catch (error) {
        console.log("Error en miUPDATE", error);
    }
    mibernate_actions_reg.push({ action: "UPDATE", obj: o, tabla: o.constructor.name });
}

function miINSERT(o) {
    var Sobj = window["mi_" + o.constructor.name];
    Sobj.push(o);
    mibernate_actions_reg.push({ action: "INSERT", obj: o, tabla: o.constructor.name });
}

//commit
function miCOMMIT() {
    mibernateLink("COMMIT", mibernate_actions_reg)
    //limpiar el array
    mibernate_actions_reg = [];
}

// Clase genérica para objetos de la base de datos
class DatabaseObject {
    constructor() {
        // Define las propiedades comunes a todos los objetos de la base de datos aquí
    }
}

// Los objetos de Hibernate se generarán dinámicamente a partir de esta linea por mibernate.php

EOD;
    $mibernateContent = str_replace("http://mibernate.php",$phpliburl,$javascriptCode) . "\n" . $clasesJS;
    file_put_contents("mibernate.js", $mibernateContent);
}


function handleInsertAction($conexion, $postData) {
    $tabla = $postData["tabla"];
    $objeto = $postData["objeto"];
    unset($objeto[0]);
    $columnas = [];
    $valores = [];
    foreach ($objeto as $clave => $valor) {
        if ($valor !== null && $valor !== "") {
            $columnas[] = $clave;
            $valores[] = ":$clave";
        }
    }
    $columnasStr = implode(", ", $columnas);
    $valoresStr = implode(", ", $valores);
    $sql = "INSERT INTO $tabla ($columnasStr) VALUES ($valoresStr)";
    $stmt = $conexion->prepare($sql);
    foreach ($objeto as $clave => $valor) {
        if ($valor !== null && $valor !== "") {
            $stmt->bindValue(":$clave", $valor);
        }
    }
    $stmt->execute();
    echo $conexion->lastInsertId();
}

function handleSelectAction($conexion, $postData) {
    $tabla = $postData["tabla"];
    $idColumn = $conexion->query("DESCRIBE $tabla")->fetch(PDO::FETCH_ASSOC);
    $idColumn = array_values($idColumn)[0];
    $id = reset($postData["objeto"]);
    $sql = "SELECT * FROM $tabla WHERE $idColumn = :id";
    $stmt = $conexion->prepare($sql);
    $stmt->bindValue(":id", $id);
    $stmt->execute();
    echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
}

function handleUpdateAction($conexion, $postData) {
    $tabla = $postData["tabla"];
    $idColumn = $conexion->query("DESCRIBE $tabla")->fetch(PDO::FETCH_ASSOC);
    $idColumn = array_values($idColumn)[0];
    $id = reset($postData["objeto"]);
    $objeto = array_slice($postData["objeto"], 1);
    $actualizaciones = [];
    $bindData = [];
    foreach ($objeto as $clave => $valor) {
        if ($valor !== null) {
            $actualizaciones[] = "$clave = :$clave";
            $bindData[":$clave"] = $valor;
        }
    }
    $actualizaciones = implode(", ", $actualizaciones);
    $sql = "UPDATE $tabla SET $actualizaciones WHERE $idColumn = :id";
    $stmt = $conexion->prepare($sql);
    $stmt->bindValue(":id", $id);
    foreach ($bindData as $clave => $valor) {
        $stmt->bindValue($clave, $valor);
    }
    $stmt->execute();
    echo $stmt->rowCount();
}

function handleSelectAllAction($conexion, $postData) {
    $tabla = $postData["tabla"];
    $sql = "SELECT * FROM $tabla";
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($resultados);
}

function handleCommitAction($conexion, $postData) {
    $objetos = $postData["objeto"];
    foreach ($objetos as $accionObj) {
        $accion = $accionObj["accion"];
        $objAccion = $accionObj["objeto"];
        $tabla = $accionObj["tabla"];

        switch ($accion) {
            case "UPDATE":
                handleUpdateAction($conexion, [
                    "tabla" => $tabla,
                    "objeto" => $objAccion
                ]);
                break;

            case "INSERT":
                handleInsertAction($conexion, [
                    "tabla" => $tabla,
                    "objeto" => $objAccion
                ]);
                break;

            default:
                echo "ACCION ERROR (COMMIT) ";
                break;
        }
    }
}

function handleInsertMultipleAction($conexion, $postData) {
    $tabla = $postData["tabla"];
    $objetos = $postData["objeto"];
    $ids = [];
    foreach ($objetos as $objeto) {
        unset($objeto[0]);
        foreach ($objeto as $clave => &$valor) {
            if ($valor === "") {
                $valor = null;
            }
        }
        $columnas = implode(", ", array_keys($objeto));
        $valores = ":" . implode(", :", array_keys($objeto));
        $sql = "INSERT INTO $tabla ($columnas) VALUES ($valores)";
        $stmt = $conexion->prepare($sql);
        foreach ($objeto as $clave => $valor) {
            $stmt->bindValue(":$clave", $valor);
        }
        $stmt->execute();
        $ids[] = $conexion->lastInsertId();
    }

    echo $ids;
}


function handleUpdateMultipleAction($conexion, $postData) {
    $tabla = $postData["tabla"];
    $objetos = $postData["objeto"];
    $idColumn = $conexion->query("DESCRIBE $tabla")->fetch(PDO::FETCH_ASSOC);
    $idColumn = array_values($idColumn)[0];
    $updated = 0;
    foreach ($objetos as $objeto) {
        $id = reset($objeto);
        $objeto = array_slice($objeto, 1);
        foreach ($objeto as $clave => &$valor) {
            if ($valor === "") {
                $valor = null;
            }
        }
        $actualizaciones = [];
        foreach ($objeto as $clave => $valor) {
            $actualizaciones[] = "$clave = :$clave";
        }
        $actualizaciones = implode(", ", $actualizaciones);
        $sql = "UPDATE $tabla SET $actualizaciones WHERE $idColumn = :id";
        $stmt = $conexion->prepare($sql);
        $stmt->bindValue(":id", $id);
        foreach ($objeto as $clave => $valor) {
            $stmt->bindValue(":$clave", $valor, PDO::PARAM_NULL);
        }
        $stmt->execute();
        $updated = $stmt->rowCount();
    }
    echo $updated;
}
