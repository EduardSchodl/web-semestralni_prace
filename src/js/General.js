document.addEventListener("DOMContentLoaded", function() {
    const flashAlert = document.getElementById("flashAlert");

    if (flashAlert) {
        setTimeout(function() {
            $(flashAlert).fadeOut(300, function() {
                $(flashAlert).remove();
            });
        }, 3000);
    }
});

function showAlert(type, message) {
    $('#flashAlert').remove();
    const alertDiv = `
                <div id="flashAlert" class="alert alert-${type} fade show position-fixed top-0 start-50 translate-middle-x" role="alert">
                    ${message}
                </div>
            `;

    $('#alertContainer').append(alertDiv);

    setTimeout(() => {
        $('.alert').last().fadeOut(300, function() {
            $(this).remove();
        });
    }, 3000);
}