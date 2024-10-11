let editorInstance = null

document.addEventListener('DOMContentLoaded', function() {
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

function publishArticle(){
    const hiddenTextarea = document.querySelector(`#abstract`);
    const title = document.getElementById("titleInput").value.trim()
    const file = document.getElementById("file").files.length

    hiddenTextarea.value = editorInstance.getData();

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

    document.getElementById(`publishForm`).submit();
}