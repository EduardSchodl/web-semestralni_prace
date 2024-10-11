document.getElementById("editor").style.display = "none";

let editorInstance = null;

let contentElement = document.getElementById("content");
let titleElement = document.getElementById("headerDiv");
let title = document.getElementById("title").textContent;

function editArticle(id_article){
    if(editorInstance != null){
        return;
    }

    let editSaveButton = document.getElementById("editSave");
    editSaveButton.innerText = "Save";
    editSaveButton.addEventListener("click", function (){
        saveChanges(id_article)
    });

    contentElement.style.display = "none";
    titleElement.style.display = "none";

    document.getElementById("editorWrapper").style.display = "block";

    ClassicEditor
        .create(document.querySelector('#editor'), {
            heading: {
                options: [
                    { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                    { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
                    { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' },
                    { model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3' }
                ]
            }
        })
        .then(editor => {
            editorInstance = editor;

            let paragraphs = contentElement.getElementsByTagName("p");
            let content = '';

            for (let i = 0; i < paragraphs.length; i++) {
                content += '<p>' + paragraphs[i].innerHTML + '</p>';
            }

            editorInstance.setData('<h1>' + title + '</h1>' + content);
            console.log('Editor initialized successfully:', editor);
        })
        .catch(error => {
            console.error('Error initializing CKEditor:', error);
        });
}

function saveChanges(id_article){
    if(!editorInstance){
        return;
    }

    let data = editorInstance.getData();
    data = data.replace(/<br\s*\/?>/gi, '\n');

    let tempDiv = document.createElement("div");
    tempDiv.innerHTML = data;

    let title = tempDiv.querySelector("h1") ? tempDiv.querySelector("h1") : "";

    let paragraphs = tempDiv.querySelectorAll("p");
    //let content = Array.from(paragraphs).map(p => p.innerHTML).join("\n\n");

    alert(data)
    return;

    $.ajax({
        url: 'articles/update',
        method: 'POST',
        data: {
            title: title,
            content: content,
            article_id: id_article
        },
        success: function(response) {
            console.log("Article updated successfully.");
            showAlert("success", "Article updated!")
            location.reload();
        },
        error: function(error) {
            console.error("Error updating article:", error);
            showAlert("danger", "There was an error updating the article.")
        }
    });
}

function deleteArticle(id_article){
    $.ajax({
        url: 'articles/delete',
        method: 'POST',
        data: {
            article_id: id_article
    },
        success: function(response) {
            try {
                console.log(response)
                const jsonResponse = JSON.parse(response);
                if (jsonResponse.status === "success") {
                    console.log("Article deleted successfully.");
                    showAlert("success", "Article deleted!")
                    window.location.replace("profile/articles");
                } else {
                    showAlert("danger", "Error: " + jsonResponse.message)
                }
            } catch (error) {
                console.error("Error parsing JSON: ", error);
                showAlert("danger", "Unexpected response from the server.")
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("Error performing action: ", errorThrown);
            showAlert("danger", "There was an error performing the action: " + errorThrown)
        }
    });
}