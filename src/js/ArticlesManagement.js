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

function update(data, action){
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

function removeReview(idReview, idArticle){
    update({"idReview": idReview, "idArticle": idArticle}, "removeReview")
}

function addReviewer(idArticle, idUser){
    update({"idArticle": idArticle, "idUser": idUser}, "addReviewer")
}

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

function acceptArticle(idArticle) {
    checkReviewsAndUpdateArticle(idArticle, "acceptArticle");
}

function rejectArticle(idArticle) {
    checkReviewsAndUpdateArticle(idArticle, "rejectArticle");
}

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

function reconsider(idArticle){
    updateArticle(idArticle, "reconsider")
}