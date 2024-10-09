function showAlert(type, message) {
    const alertDiv = `
                <div class="alert alert-${type} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;

    $('#alertContainer').append(alertDiv);

    setTimeout(() => {
        $('.alert').last().fadeOut(300, function() {
            $(this).remove();
        });
    }, 3000);
}