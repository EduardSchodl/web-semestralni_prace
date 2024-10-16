let editorInstance = null;
let isEditing = false;

let contentElement = null;
let titleElement = null;
let titleInp = null;
let title = null;

let editorWrapper = document.getElementById("editorWrapper");
let editSaveButton = document.getElementById("editSave");

// Aktualizace článku pomocí AJAXu
function reloadArticle(){
    $.ajax({
        url: window.location.pathname,
        method: 'GET',
        success: function(response) {
            $('#reload').html(response);
        },
        error: function(error) {
            console.error("Error loading user table:", error);
            showAlert("danger", "There was an error loading the user table.");
        }
    });
}

// Funkce pro přepínání mezi režimy úprav a ukládání
function toggleEditor(id_article) {
    if (isEditing) {
        saveChanges(id_article);
    } else {
        editArticle(id_article);
    }
}

// Funkce pro spuštění úpravy článku
function editArticle(){
    // Kontrola, zda existuje instance editoru
    if(editorInstance != null){
        return;
    }

    // Vytvoří HTML formulář pro úpravu
    const editForm = '<label for="titleInput" class="form-label">Title:</label>\n' +
        '                    <input type="text" class="form-control w-50 mb-3" id="titleInput">\n' +
        '\n' +
        '                    <label for="editor" class="form-label">Abstract:</label>\n' +
        '                    <textarea name="editor" id="editor"></textarea>';

    editorWrapper.innerHTML = editForm;
    editorWrapper.style.display = "block"; // Zobrazí editor

    // Původní obsah
    contentElement = document.getElementById("content");
    titleElement = document.getElementById("headerDiv");
    titleInp = document.getElementById("titleInput");
    title = document.getElementById("title").textContent;

    titleInp.value = title;

    editSaveButton.innerText = "Save";
    isEditing = true;

    // Skryje původní obsah
    contentElement.style.display = "none";
    titleElement.style.display = "none";

    // Inicializuje CKEditor
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

            let content = document.getElementById("articleContent").innerHTML;

            editorInstance.setData(content);
            console.log('Editor initialized successfully:', editor);
        })
        .catch(error => {
            console.error('Error initializing CKEditor:', error);
        });
}

// Funkce pro skrytí editoru
function hideEditor(){
    editSaveButton.removeEventListener("click", saveChanges);

    editSaveButton.innerText = "Edit";
    isEditing = false;

    document.getElementById("editorWrapper").style.display = "none";

    // Zničí instanci CKEditoru
    editorInstance.destroy()
        .then(() => {
            console.log("Editor destroyed successfully");
            editorInstance = null;
        })
        .catch(error => {
            console.error("Error destroying editor:", error);
        });
}

// Funkce pro uložení změn článku
function saveChanges(id_article){
    if(!editorInstance){
        return;
    }

    // Získání hodnot
    let data = editorInstance.getData();
    let title = document.getElementById("titleInput").value;

    // Odstraní obsah obalu editoru
    editorWrapper.innerHTML = "";

    // Kontrola, zda nejsou pole prázdná
    if(!title){
        showAlert("warning", "Title is missing!")
        return
    }

    if(!data){
        showAlert("warning", "Abstract text is missing!")
        return
    }

    // Odešle AJAX požadavek pro uložení změn
    $.ajax({
        url: 'articles/update',
        method: 'POST',
        data: {
            title: title,
            content: data,
            article_id: id_article
        },
        success: function(response) {
            console.log("Article updated successfully.");
            showAlert("success", "Article updated!")
            hideEditor(id_article)
            reloadArticle()
        },
        error: function(error) {
            console.error("Error updating article:", error);
            showAlert("danger", "There was an error updating the article.")
        }
    });
}

// Funkce pro smazání článku
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

// Přidá událost na kliknutí tlačítka pro úpravu/uložení
editSaveButton.addEventListener("click", function() {
    // Získá ID článku z atributu tlačítka
    const id_article = this.getAttribute("data-article-id");

    // Přepíná mezi režimem úprav a ukládání
    toggleEditor(id_article);
});