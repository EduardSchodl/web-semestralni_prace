// Funkce pro aktualizace tabulky uživatelů
function reloadUserTable() {
    $.ajax({
        url: 'users',
        method: 'GET',
        success: function(response) {
            $('#tab').html(response);
        },
        error: function(error) {
            console.error("Error loading user table:", error);
            showAlert("danger", "There was an error loading the user table.");
        }
    });
}

// Funkce pro aktualizaci role uživatele
function updateRole(id_user, value){
    $.ajax({
        url: 'users',
        method: 'POST',
        data: {
            action: "update",
            id_user: id_user,
            id_role: value
        },
        success: function(response) {
            console.log("Role updated successfully.");
            showAlert("success", "Role updated!");
            reloadUserTable();
        },
        error: function(error) {
            console.error("Error updating role:", error);
            showAlert("danger", "There was an error updating the role.")
        }
    });
}

// Aktualizace stavu uživatele (ban, unban, delete)
function updateUserAction(id_user, action) {
    $.ajax({
        url: 'users',
        method: 'POST',
        data: {
            action: action,
            id_user: id_user
        },
        success: function(response) {
            console.log("User " + action + " successfully.");
            showAlert("success", "User " + action + " updated!")
            reloadUserTable()
        },
        error: function(error) {
            console.error("Error updating user " + action + ":", error);
            showAlert("danger", "There was an error updating the user " + action + ".")
        }
    });
}

// Funkce pro banování uživatele
function banUser(id_user){
    updateUserAction(id_user, "ban")
}

// Funkce pro odbanování uživatele
function unBanUser(id_user){
    updateUserAction(id_user, "unban")
}

// Funkce pro smazání uživatele
function deleteUser(id_user){
    updateUserAction(id_user, "delete")
}