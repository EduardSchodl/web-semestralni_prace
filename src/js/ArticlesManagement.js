function update(data, action){
    if (action === "addReviewer" && !data["idUser"]) {
        alert("Please select a reviewer.");
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
                alert("Action was successful!");
                location.reload();
            } else {
                alert("Error: " + jsonResponse.message);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("Error performing action: ", errorThrown);
            alert("There was an error performing the action: " + errorThrown);
        }
    });
}

function removeReview(idReview){
    update({"idReview": idReview}, "removeReview")
}

function addReviewer(idArticle, idUser){
    update({"idArticle": idArticle, "idUser": idUser}, "addReviewer")
}

function acceptArticle(idArticle) {
    $.ajax({
        url: 'articles-management/article-status-update',  // Make sure this is your correct backend URL
        method: 'POST',
        data: {
            values: {"idArticle": idArticle},
            action: "checkReviews"
        },
        success: function(response) {
            const jsonResponse = JSON.parse(response);

            if (jsonResponse.status === 'success') {
                // All reviews are submitted, proceed with accepting the article
                updateArticle(idArticle, "acceptArticle");
            } else {
                // Display a message if not all reviews are submitted
                alert(jsonResponse.message);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("Error: ", errorThrown);
            alert("There was an error checking reviews: " + errorThrown);
        }
    });
}

function rejectArticle(idArticle){
    $.ajax({
        url: 'articles-management/article-status-update',  // Make sure this is your correct backend URL
        method: 'POST',
        data: {
            values: {"idArticle": idArticle},
            action: "checkReviews"
        },
        success: function(response) {
            const jsonResponse = JSON.parse(response);

            if (jsonResponse.status === 'success') {
                // All reviews are submitted, proceed with accepting the article
                updateArticle(idArticle, "rejectArticle");
            } else {
                // Display a message if not all reviews are submitted
                alert(jsonResponse.message);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("Error: ", errorThrown);
            alert("There was an error checking reviews: " + errorThrown);
        }
    });
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
                alert("Action was successful!");
                location.reload();
            } else {
                alert("Error: " + jsonResponse.message);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("Error performing action: ", errorThrown);
            alert("There was an error performing the action: " + errorThrown);
        }
    });
}

function reconsider(idArticle){
    updateArticle(idArticle, "reconsider")
}