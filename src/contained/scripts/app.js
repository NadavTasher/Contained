function load() {
    manage_load_list();
}

function manage_load_list() {
    API.send("manage", "list", {}, (success, result) => {
        if (success) {
            // File list
            let list = UI.get("paths");
            // Clear list
            UI.clear(list);
            // Add files
            for (let path of result) {
                let option = document.createElement("option");
                option.value = path;
                option.innerText = path.endsWith("/") ? "Directory" : "File";
                list.appendChild(option);
            }
        } else {
            alert(result);
        }
    }, Authenticate.authenticate());
}

function manage_base64(file) {
    return new Promise((resolve) => {
        let reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = () => resolve(reader.result);
        reader.onerror = (e) => console.log(e);
    });
}

function manage_import() {
    let dialog = document.createElement("input");
    dialog.type = "file";
    dialog.accept = ".zip";
    dialog.addEventListener("change", async () => {
        API.send("manage", "import", {
            file: (await manage_base64(dialog.files[0])).split(",").pop()
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
            link.href = "data:application/zip;base64," + result;
            link.download = "Export.zip";
            link.click();
        } else {
            alert(result);
        }
    }, Authenticate.authenticate());
}

function manage_remove() {
    API.send("manage", "remove", {
        file: UI.get("remove").value
    }, (success, result) => {
        if (success) {
            window.location.reload();
        } else {
            alert(result);
        }
    }, Authenticate.authenticate());
}