function showAlert(type, message) {
    const alertDiv = `
                <div class="alert alert-${type} fade show position-fixed top-0 start-50 translate-middle-x" role="alert">
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