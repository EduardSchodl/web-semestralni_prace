// Spustí se, když je DOM načten
document.addEventListener("DOMContentLoaded", function() {
    const flashAlert = document.getElementById("flashAlert");

    // Pokud je na stránce prvek s ID "flashAlert" (flash zpráva)
    if (flashAlert) {
        // Nastaví časovač pro skrytí zprávy po 3 vteřinách
        setTimeout(function() {
            $(flashAlert).fadeOut(300, function() {
                $(flashAlert).remove();
            });
        }, 3000);
    }
});

// Funkce pro zobrazení upozornění s daným typem a zprávou
function showAlert(type, message) {
    // Odstraní případnou předchozí zprávu
    $('#flashAlert').remove();

    // HTML prvek pro zobrazení upozornění
    const alertDiv = `
                <div id="flashAlert" class="alert alert-${type} fade show position-fixed top-0 start-50 translate-middle-x" role="alert">
                    ${message}
                </div>
            `;

    $('#alertContainer').append(alertDiv);

    // Nastaví časovač na 3 vteřiny pro automatické skrytí a odstranění zprávy
    setTimeout(() => {
        $('.alert').last().fadeOut(300, function() {
            $(this).remove();
        });
    }, 3000);
}