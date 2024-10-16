// Funkce pro znovunačtení recenze článku na základě ID článku
function reloadReview(idArticle) {
    $.ajax({
        url: 'articles-management/update-card',
        method: 'GET',
        data:{
            idArticle: idArticle
        },
        success: function(response) {
            console.log("Response received:", response);
            $('#card-' + idArticle).html(response);
        },
        error: function(error) {
            console.error("Error loading user table:", error);
            showAlert("danger", "There was an error loading the user table.");
        }
    });
}

// Obecná funkce pro aktualizaci článku nebo recenze na základě dat a akce
function update(data, action){
    // Zobrazení upozornění, pokud nebyl vybrán recenzent
    if (action === "addReviewer" && !data["idUser"]) {
        showAlert("info", "Please select a reviewer.")
        return;
    }

    $.ajax({
        url: 'articles-management',
        method: 'POST',
        data: {
            values: data,
            action: action
        },
        success: function(response) {
            const jsonResponse = JSON.parse(response);
            if (jsonResponse.status === "success") {
                console.log("Action was successful.");
                showAlert("success", "Action was successful!")
                reloadReview(data["idArticle"])
                //location.reload();
            } else {
                showAlert("danger", "Error: " + jsonResponse.message)
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("Error performing action: ", errorThrown);
            showAlert("danger", "There was an error performing the action: " + errorThrown)
        }
    });
}

// Funkce pro odstranění recenze na základě ID recenze a ID článku
function removeReview(idReview, idArticle){
    update({"idReview": idReview, "idArticle": idArticle}, "removeReview")
}

// Funkce pro přidání recenzenta na článek
function addReviewer(idArticle, idUser){
    update({"idArticle": idArticle, "idUser": idUser}, "addReviewer")
}

// Funkce pro kontrolu stavu recenzí před aktualizací článku
function checkReviewsAndUpdateArticle(idArticle, action) {
    $.ajax({
        url: 'articles-management/article-status-update',
        method: 'POST',
        data: {
            values: {"idArticle": idArticle},
            action: "checkReviews"
        },
        success: function(response) {
            const jsonResponse = JSON.parse(response);

            // Pokud jsou recenze v pořádku, pokračuje s aktualizací článku
            if (jsonResponse.status === 'success') {
                updateArticle(idArticle, action);
            } else {
                showAlert("danger", jsonResponse.message);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("Error: ", errorThrown);
            showAlert("danger", "There was an error checking reviews: " + errorThrown);
        }
    });
}

// Funkce pro přijetí článku na základě recenzí
function acceptArticle(idArticle) {
    checkReviewsAndUpdateArticle(idArticle, "acceptArticle");
}

// Funkce pro odmítnutí článku na základě recenzí
function rejectArticle(idArticle) {
    checkReviewsAndUpdateArticle(idArticle, "rejectArticle");
}

// Funkce pro aktualizaci karty správy článku na základě jeho ID a akce
function updateArticle(idArticle, action){
    $.ajax({
        url: 'articles-management/updateArticle',
        method: 'POST',
        data: {
            idArticle: idArticle,
            action: action
        },
        success: function(response) {
            const jsonResponse = JSON.parse(response);
            if (jsonResponse.status === "success") {
                console.log("Action was successful.");
                showAlert("success", "Action was successful!")
                reloadReview(idArticle)
            } else {
                showAlert("danger", "Error: " + jsonResponse.message)
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("Error performing action: ", errorThrown);
            showAlert("danger", "There was an error performing the action: " + errorThrown)
        }
    });
}

// Funkce pro přehodnocení článku, např. vrácení do fáze recenze
function reconsider(idArticle){
    updateArticle(idArticle, "reconsider")
}