var mibernate_actions_reg = [];
var mibernate_LAST_INSERT = 0;

// Función para realizar las peticiones AJAX a mibernate.php
async function mibernateLink(a, o) {
    var token = sessionStorage.getItem('TOKEN');
    const removeNulls = obj => Object.fromEntries(Object.entries(obj).filter(([k, v]) => v !== null));
    try {
        const r = await $.post("https://homecasas.ddns.net/moonapp/libs/mibernate/mibernate.php", { accion: a, tabla: o.constructor.name, objeto: removeNulls(o), token: token });
        return a === "INSERT" ? (mibernate_LAST_INSERT = r) : a === "SELECTALL" ? JSON.parse(r) : r;
    } catch (e) {
        console.error("mibernateLink() Error:", e);
        throw e;
    }
}

// Inicializa o actualiza las variables window.mi_* con los datos de la base de datos
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

// Actualiza todas las variables window.mi_*
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
            mibernateLink("INSERTM", obj);
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
            mibernateLink("UPDATEM", obj);
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
        return window["mi_" + o.constructor.name];
    } else if (typeof o === "string") {
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

// Commit
function miCOMMIT() {
    mibernateLink("COMMIT", mibernate_actions_reg);
    // Limpiar el array
    mibernate_actions_reg = [];
}

// Clase genérica para objetos de la base de datos
class DatabaseObject {
    constructor() {
        // Define las propiedades comunes a todos los objetos de la base de datos aquí
    }
}

// Los objetos de Hibernate se generarán dinámicamente a partir de esta línea por mibernate.php

class SESSION_LOG extends DatabaseObject {
    constructor(SLOG_CODIGO, USER_CODIGO, SLOG_STATE, SLOG_FECHA) {
        super();
        this.SLOG_CODIGO = SLOG_CODIGO;
        this.USER_CODIGO = USER_CODIGO;
        this.SLOG_STATE = SLOG_STATE;
        this.SLOG_FECHA = SLOG_FECHA;
    }
}


class USER extends DatabaseObject {
    constructor(USER_ID, USER_NOMBRE, USER_PASS, USER_EMAIL, USER_FECHA_ALTA, USER_FECHA_BAJA, USER_SLOG) {
        super();
        this.USER_ID = USER_ID;
        this.USER_NOMBRE = USER_NOMBRE;
        this.USER_PASS = USER_PASS;
        this.USER_EMAIL = USER_EMAIL;
        this.USER_FECHA_ALTA = USER_FECHA_ALTA;
        this.USER_FECHA_BAJA = USER_FECHA_BAJA;
        this.USER_SLOG = USER_SLOG;
    }
}
