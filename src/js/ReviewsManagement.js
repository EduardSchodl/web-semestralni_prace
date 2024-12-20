// Uchovává mapu inicializovaných CKEditor instancí podle jejich elementů
const initializedEditors = new Map();

// Funkce pro dynamické zobrazení formuláře a inicializaci editoru
function toggleForm(button, index, reviewId) {
    const formContainer = button.closest('.card-body').querySelector('.review-form');
    const buttonContainer = button.closest('.card-body').querySelector('.btn-div');
    const closeButton = button.closest('.float-end').querySelector('.close-button');

    // Pokud je formulář skrytý nebo není naplněn HTML obsahem, zobrazí formulář
    if (formContainer.style.display === 'none' || formContainer.innerHTML === '') {
        const formHtml = `
                        <form id="form-${index}" method="post">
                            <input type="hidden" name="reviewId" value="${reviewId}">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="input-group mg-b bg-color">
                                        <div class="input-group-text">Content</div>
                                        <input type="number" class="form-control" name="content" max="5" min="0" step="0.5" value="0">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="input-group mg-b">
                                        <div class="input-group-text">Formality</div>
                                        <input type="number" class="form-control" name="formality" max="5" min="0" step="0.5" value="0">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="input-group mg-b">
                                        <div class="input-group-text">Up-to-date</div>
                                        <input type="number" class="form-control" name="up_to_date" max="5" min="0" step="0.5" value="0">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="input-group mg-b">
                                        <div class="input-group-text">Language</div>
                                        <input type="number" class="form-control" name="language" max="5" min="0" step="0.5" value="0">
                                    </div>
                                </div>
                            </div>
                            <div class="container mt-5">
                                <div id="editor-${index}" class="editor"></div>
                                <textarea name="editorContent" id="editorContent-${index}" style="display: none"></textarea>
                            </div>
                        </form>
                    `;

        formContainer.innerHTML = formHtml;

        const submitButtonHtml = `<button type="button" class="btn btn-success submit-button" form="form-${index}" onclick="submitForm(${index})">Submit</button>`;
        // Přidá tlačítko pro odeslání formuláře
        buttonContainer.insertAdjacentHTML('beforeend', submitButtonHtml);

        formContainer.style.display = 'block';
        closeButton.style.display = 'inline-block';
        button.style.display = 'none';

        // Vybere příslušný element pro CKEditor
        const editorElement = formContainer.querySelector(`#editor-${index}`);
        // Pokud CKEditor ještě není inicializován pro daný element, tak ho vytvoří
        if (!initializedEditors.has(editorElement)) {
            ClassicEditor
                .create(editorElement,{
                    toolbar: ['bold', 'italic', '|', 'undo', 'redo']
                })
                .then(editor => {
                    initializedEditors.set(editorElement, editor);
                })
                .catch(error => {
                    console.error('Error initializing CKEditor:', error);
                });
        }
    }
}

// Funkce pro zavření formuláře a odstranění editoru
function closeForm(button, index) {
    const formContainer = button.closest('.card-body').querySelector('.review-form');
    const submitButton = button.closest('.card-body').querySelector('.submit-button');
    const reviewButton = button.closest('.float-end').querySelector('.review-button');
    const editorElement = formContainer.querySelector(`#editor-${index}`);

    // Pokud je CKEditor inicializován, zníčí ho a odstraní z mapy
    if (initializedEditors.has(editorElement)) {
        initializedEditors.get(editorElement).destroy()
            .then(() => {
                initializedEditors.delete(editorElement);
                console.log('Editor destroyed successfully');
            })
            .catch(error => {
                console.error('Error destroying CKEditor:', error);
            });
    }

    formContainer.innerHTML = '';
    formContainer.style.display = 'none';
    submitButton.remove();

    reviewButton.style.display = 'inline-block';
    button.style.display = 'none';
}

// Funkce pro odeslání formuláře
function submitForm(index) {
    const editorElement = document.querySelector(`#editor-${index}`);
    // Skrytá textarea pro uložení obsahu editoru
    const hiddenTextarea = document.querySelector(`#editorContent-${index}`);

    // Získá instanci CKEditoru
    const editor = initializedEditors.get(editorElement);

    if (editor) {
        // Nastaví hodnotu skrytého textarea na obsah editoru
        hiddenTextarea.value = editor.getData();
        if(!hiddenTextarea.value){
            showAlert("warning", "Comment must not be empty!")
            return;
        }
        // Odeslání formuláře "ručně"
        document.getElementById(`form-${index}`).submit();
    } else {
        console.error('Editor instance not found for index:', index);
    }
}
