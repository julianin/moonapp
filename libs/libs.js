const libsurl = "http://homecasas.ddns.net/Dobey/LIBS/";

function loadResource(url) {
    const head = document.head;
    if (url.endsWith(".js")) {
        const script = document.createElement("script");
        script.src = url;
        head.appendChild(script);
    } else if (url.endsWith(".css")) {
        const link = document.createElement("link");
        link.href = url;
        link.rel = "stylesheet";
        head.appendChild(link);
    }
}

["http://homecasas.ddns.net/Dobey/LIBS/mibernate/mibernate.js",
"http://homecasas.ddns.net/Dobey/LIBS/fun.js",
"https://code.jquery.com/jquery-3.6.0.min.js",
"https://code.jquery.com/ui/1.12.1/jquery-ui.js",
"https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css",
"https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js",
"https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/js/select2.min.js",
    "https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css",
    "https://cdn.datatables.net/1.11.5/js/jquery.dataTables.js",
"https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-rc.0/css/select2.min.css"].forEach(loadResource);
