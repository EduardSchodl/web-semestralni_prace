let editorInstance = null

// Funkce se spustí, jakmile je načtena celá stránka (DOM)
document.addEventListener('DOMContentLoaded', function() {
    // Inicializace CKEditoru na elementu s id 'editor'
    ClassicEditor
        .create(document.querySelector('#editor'), {
            heading: {
                options: [
                    { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                    { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
                    { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' },
                    { model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3' }
                ]
            },
            // Definice toolbaru s možnostmi (tučné, kurzíva, zpět, znovu)
            toolbar: ['bold', 'italic', '|', 'undo', 'redo']
        })
        .then(editor => {
            editorInstance = editor
            console.log('Editor initialized successfully:', editor);
        })
        .catch(error => {
            console.error('Error initializing CKEditor:', error);
        });
});

// Funkce pro publikování článku
function publishArticle(){
    const hiddenTextarea = document.querySelector(`#abstract`);
    const title = document.getElementById("titleInput").value.trim()
    const file = document.getElementById("file").files.length

    // Nastaví obsah CKEditoru do skrytého textarea pro odeslání
    hiddenTextarea.value = editorInstance.getData();

    // Kontrola polí formuláře
    if (!title) {
        showAlert("warning", "Title must not be empty!");
        return;
    }

    if (!hiddenTextarea.value) {
        showAlert("warning", "Abstract must not be empty!");
        return;
    }

    if (file === 0) {
        showAlert("warning", "You must select a file!");
        return;
    }

    // Pokud jsou všechny podmínky splněny, odešle formulář
    document.getElementById(`publishForm`).submit();
}