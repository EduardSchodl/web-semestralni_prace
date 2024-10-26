// Sledování platnosti hesla
let isPasswordValid = false;

(function () {
    'use strict';

    // Výběr všech formulářů s třídou .needs-validation
    const forms = document.querySelectorAll('.needs-validation');

    // Procházení všech formulářů a zabránění odeslání, pokud validace neprojde
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            // Kontrola platnosti formuláře a hesla
            if (!form.checkValidity() || !isPasswordValid) {
                event.preventDefault();
                event.stopPropagation();
            }

            // Označení formuláře validním
            form.classList.add('was-validated');
        }, false);
    });
})();

document.addEventListener("DOMContentLoaded", () => {
    // Hesla a jednotlivé podmínky pro heslo
    const password = document.getElementById("psswd_reg");
    const requirements = document.querySelectorAll(".requirements");
    const leng = document.querySelector(".leng");
    const bigLetter = document.querySelector(".big-letter");
    const num = document.querySelector(".num");
    const specialChar = document.querySelector(".special-char");

    // Nastavení všech požadavků na "nesplněno" při načtení stránky
    requirements.forEach((element) => element.classList.add("wrong"));

    // Zobrazení neplatnosti hesla při zaměření na pole
    password.addEventListener("focus", () => {
        password.classList.add("is-invalid");
    });

    // Validace hesla při každém zadání nového znaku
    password.addEventListener("input", () => {
        const value = password.value;

        // Podmínky pro platné heslo
        const isLengthValid = value.length >= 8;
        const hasUpperCase = /[A-Z]/.test(value);
        const hasNumber = /\d/.test(value);
        const hasSpecialChar = /[!@#$%^&*()\[\]{}\\|;:'",<.>/?`~]/.test(value);

        // Aktualizace tříd podle splněných a nesplněných požadavků
        leng.classList.toggle("good", isLengthValid);
        leng.classList.toggle("wrong", !isLengthValid);
        bigLetter.classList.toggle("good", hasUpperCase);
        bigLetter.classList.toggle("wrong", !hasUpperCase);
        num.classList.toggle("good", hasNumber);
        num.classList.toggle("wrong", !hasNumber);
        specialChar.classList.toggle("good", hasSpecialChar);
        specialChar.classList.toggle("wrong", !hasSpecialChar);

        // Pokud podmínky projdou, heslo je validní
        isPasswordValid = isLengthValid && hasUpperCase && hasNumber && hasSpecialChar;

        // Přidání nebo odstranění tříd validace hesla
        if (isPasswordValid) {
            password.classList.remove("is-invalid");
            password.classList.add("is-valid");
        } else {
            password.classList.remove("is-valid");
            password.classList.add("is-invalid");
        }
    });
});
