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
    dialog.multiple = true;
    dialog.directory = true;
    dialog.webkitdirectory = true;
    dialog.addEventListener("change", async () => {
        let files = {};
        for (let file of dialog.files) {
            files[file.webkitRelativePath] = await manage_load_base64(file);
        }
        API.send("manage", "import", {files: files}, (success, result) => {
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
            link.download = "Export.tar.gz";
            link.click();
        } else {
            alert(result);
        }
    }, Authenticate.authenticate());
}