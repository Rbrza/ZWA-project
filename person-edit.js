document.addEventListener("DOMContentLoaded", () => {
    const userId = window.USER_ID;
    const table = document.getElementById("main-table-tbody-person-details");

    if (!userId) {
        table.innerHTML = `<tr><td>Missing USER_ID: ${userId}</td></tr>`;
        return;
    }

    fetch("get-user.php?id=" + encodeURIComponent(userId))
        .then(r => r.json())
        .then(user => {
            if (user.error) {
                table.innerHTML = `<tr><td>${user.error}</td></tr>`;
                return;
            }

            // helper to add one row
            const addInput = (label, value, inputType) => {
                const tr = document.createElement("tr");

                const th = document.createElement("th");
                th.scope = "row";
                th.textContent = label;

                const td = document.createElement("td");

                const input = document.createElement("input");
                input.value = value ?? "";
                input.name = inputType;

                if (inputType === "name" || inputType === "surname") {
                    input.minLength = 2;
                    input.maxLength = 50;
                }

                if (inputType === "phone") input.pattern = "^\\+?[1-9]\\d{7,14}$";

                if (inputType !== "ICO") {
                    input.required = true;
                }

                if (inputType === "email") {
                    input.type = "email";
                } else if (inputType === "DOB") {
                    input.type = "date";
                    input.min = "1900-01-01";
                    input.max = window.MAX_DOB;
                } else {
                    input.type = "text";
                }

                td.appendChild(input);
                tr.appendChild(th);
                tr.appendChild(td);
                table.appendChild(tr);
            };

            const addRow = (label, value) => {
                const tr = document.createElement("tr");
                const th = document.createElement("th");
                th.scope = "row";
                th.textContent = label;

                const td = document.createElement("td");
                td.textContent = value ?? "";

                tr.appendChild(th);
                tr.appendChild(td);
                table.appendChild(tr);
            };

            addInput("Jméno", user.name, "name");
            addInput("Příjmení", user.surname, "surname");
            addInput("Datum narození", user.DOB, "DOB");
            addInput("Email", user.email, "email");
            addInput("Telefon", user.phone, "phone");
            addInput("IČO", user.ICO, "ICO");
            addRow("Měsíční zpoplatnění", user.MT);
            addRow("Pojistné skóre", user.score);
            addRow("Aktivní pojištění", user.active_insurances);
        })
        .catch(err => {
            console.error(err);
            table.innerHTML = `<tr><td>Error loading user.</td></tr>`;
        });
});
