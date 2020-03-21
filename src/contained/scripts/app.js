function load() {
    manage_load_tree("/");
}

function manage_load_tree(directory) {

}

function manage_export() {
    API.send("manage", "export", {}, (success, result) => {
        if (success) {
            let link = document.createElement("a");
            link.href = "data:application/gzip;base64," + result;
            link.download = "Export.tar.gz";
            link.click();
        } else {
            alert(result);
        }
    }, Authenticate.authenticate());
}