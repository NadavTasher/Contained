function load() {
    manage_load_tree("/");
}

function manage_load_tree(directory) {

}

function manage_export() {
    API.send("manage", "export", {}, (success, result) => {
        if (success) {
            let url = "data:application/tar;base64," + result;
            window.open(url, "_blank").focus();
        } else {
            alert(result);
        }
    }, Authenticate.authenticate());
}