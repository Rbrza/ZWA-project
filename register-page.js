document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("form");

    const fields = {
        name: document.getElementById("register-name"),
        surname: document.getElementById("register-surname"),
        email: document.getElementById("register-email"),
        phone: document.getElementById("register-phone"),
        dob: document.getElementById("register-DOB"),
        p1: document.getElementById("password"),
        p2: document.getElementById("password2"),
    };

    const errors = {
        name: document.getElementById("err-register-name"),
        surname: document.getElementById("err-register-surname"),
        email: document.getElementById("err-register-email"),
        phone: document.getElementById("err-register-phone"),
        dob: document.getElementById("err-register-DOB"),
        p1: document.getElementById("err-password"),
        p2: document.getElementById("err-password2"),
    };

    const phoneRegex = /^\+?[1-9]\d{7,14}$/;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;

    function setError(input, small, msg) {
        const label = input.closest("label");
        if (msg) {
            input.classList.add("error");
            if (label) label.classList.add("error");
            small.textContent = msg;
            small.classList.add("show");
            return false;
        } else {
            input.classList.remove("error");
            if (label) label.classList.remove("error");
            small.textContent = "";
            small.classList.remove("show");
            return true;
        }
    }

    function isAtLeast18(dobStr) {
        if (!dobStr) return false;
        const parts = dobStr.split("-");
        if (parts.length !== 3) return false;

        const y = Number(parts[0]);
        const m = Number(parts[1]) - 1;
        const d = Number(parts[2]);

        const dob = new Date(Date.UTC(y, m, d));
        if (Number.isNaN(dob.getTime())) return false;

        const today = new Date();
        const cutoff = new Date(Date.UTC(today.getUTCFullYear() - 18, today.getUTCMonth(), today.getUTCDate()));
        return dob <= cutoff;
    }

    function validateName() {
        const v = fields.name.value.trim();
        if (!v) return setError(fields.name, errors.name, "Zadejte jméno.");
        if (v.length < 2) return setError(fields.name, errors.name, "Jméno musí mít alespoň 2 znaky.");
        if (v.includes(" ")) return setError(fields.name, errors.name, "Jméno nesmí obsahovat mezery.");
        return setError(fields.name, errors.name, "");
    }

    function validateSurname() {
        const v = fields.surname.value.trim();
        if (!v) return setError(fields.surname, errors.surname, "Zadejte příjmení.");
        if (v.length < 2) return setError(fields.surname, errors.surname, "Příjmení musí mít alespoň 2 znaky.");
        if (v.includes(" ")) return setError(fields.surname, errors.surname, "Příjmení nesmí obsahovat mezery.");
        return setError(fields.surname, errors.surname, "");
    }

    function validateEmail() {
        const v = fields.email.value.trim();
        if (!v) return setError(fields.email, errors.email, "Zadejte email.");
        if (!emailRegex.test(v)) return setError(fields.email, errors.email, "Neplatný email.");
        return setError(fields.email, errors.email, "");
    }

    function validatePhone() {
        const v = fields.phone.value.trim();
        if (!v) return setError(fields.phone, errors.phone, "Zadejte telefon.");
        if (!phoneRegex.test(v)) return setError(fields.phone, errors.phone, "Neplatné telefonní číslo. Použijte např. +420777888999.");
        return setError(fields.phone, errors.phone, "");
    }

    function validateDOB() {
        const v = fields.dob.value;
        if (!v) return setError(fields.dob, errors.dob, "Vyberte datum narození.");
        if (!isAtLeast18(v)) return setError(fields.dob, errors.dob, "Uživatel musí mít alespoň 18 let.");
        return setError(fields.dob, errors.dob, "");
    }

    function validatePasswords() {
        const p1 = fields.p1.value;
        const p2 = fields.p2.value;

        let ok = true;

        if (!p1) ok = setError(fields.p1, errors.p1, "Zadejte heslo.") && ok;
        else ok = setError(fields.p1, errors.p1, "") && ok;

        if (!p2) ok = setError(fields.p2, errors.p2, "Potvrďte heslo.") && ok;
        else if (p1 !== p2) ok = setError(fields.p2, errors.p2, "Hesla se neshodují.") && ok;
        else ok = setError(fields.p2, errors.p2, "") && ok;

        return ok;
    }

    function validateAll() {
        const a = validateName();
        const b = validateSurname();
        const c = validateEmail();
        const d = validatePhone();
        const e = validateDOB();
        const f = validatePasswords();
        return a && b && c && d && e && f;
    }

    // live validation
    fields.name.addEventListener("input", validateName);
    fields.surname.addEventListener("input", validateSurname);
    fields.email.addEventListener("input", validateEmail);
    fields.phone.addEventListener("input", validatePhone);
    fields.dob.addEventListener("input", validateDOB);
    fields.p1.addEventListener("input", validatePasswords);
    fields.p2.addEventListener("input", validatePasswords);

    // block submit if invalid
    form.addEventListener("submit", (e) => {
        if (!validateAll()) {
            e.preventDefault();
        }
    });
});
