function load() {
    manage_load_tree("/");
}

function manage_load_tree(directory) {

}

function manage_load_base64(file) {
    return new Promise(resolve => {
        let reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = () => resolve(reader.result);
    });
}

function manage_import() {
    let dialog = document.createElement("input");
    dialog.type = "file";
    dialog.accept = ".zip";
    dialog.addEventListener("change", async () => {
        API.send("manage", "import", {
            file: (await manage_load_base64(dialog.files[0])).split(",").pop()
        }, (success, result) => {
            if (success) {
                window.location.reload();
            } else {
                alert(result);
            }
        }, Authenticate.authenticate());
    });
    dialog.click();
}

function manage_export() {
    API.send("manage", "export", {}, (success, result) => {
        if (success) {
            let link = document.createElement("a");
            link.href = "data:application/gzip;base64," + result;
            link.download = "Export.zip";
            link.click();
        } else {
            alert(result);
        }
    }, Authenticate.authenticate());
}