document.addEventListener("DOMContentLoaded", () => {
    const userId = window.USER_ID;
    const table = document.getElementById("main-table-tbody-person-details");

    const showMessageRow = (message) => {
        // clear table safely
        while (table.firstChild) table.removeChild(table.firstChild);

        const tr = document.createElement("tr");
        const td = document.createElement("td");
        td.colSpan = 2; // your details table is 2 columns: th + td
        td.textContent = message;

        tr.appendChild(td);
        table.appendChild(tr);
    };


    if (userId === undefined || userId === null || userId === "") {
        showMessageRow("Missing USER_ID");
        return;
    }


    fetch("get-user.php?id=" + encodeURIComponent(userId))
        .then(r => r.json())
        .then(user => {
            if (user.error) {
                showMessageRow(user.error);
                return;
            }


            if (user.photo) {
                const tr = document.createElement("tr");

                const th = document.createElement("th");
                th.scope = "row";
                th.textContent = "Fotka";

                const td = document.createElement("td");
                const img = document.createElement("img");
                img.src = user.photo;
                img.alt = "Profilová fotka";
                img.style.maxWidth = "160px";
                img.style.borderRadius = "12px";

                td.appendChild(img);
                tr.appendChild(th);
                tr.appendChild(td);
                table.appendChild(tr);
            }


            // helper to add one row
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

            const addDateRow = (label, isoDate) => {
                const tr = document.createElement("tr");

                const th = document.createElement("th");
                th.scope = "row";
                th.textContent = label;

                const td = document.createElement("td");

                const time = document.createElement("time");
                time.setAttribute("datetime", isoDate ?? "");
                time.textContent = formatLocalDateFromISO(isoDate);

                td.appendChild(time);
                tr.appendChild(th);
                tr.appendChild(td);
                table.appendChild(tr);
            };


            addRow("Jméno", user.name);
            addRow("Příjmení", user.surname);
            addDateRow("Datum narození", user.DOB);
            addRow("Email", user.email);
            addRow("Telefon", user.phone);
            addRow("IČO", user.ICO);
            addRow("Měsíční zpoplatnění", user.MT);
            addRow("Pojistné skóre", user.score);
            addRow("Aktivní pojištění", user.active_insurances_display || "");

            function formatLocalDateFromISO(iso) {
                if (!iso) return "";

                const parts = String(iso).split("-");
                if (parts.length !== 3) return String(iso);

                const y = Number(parts[0]);
                const m = Number(parts[1]) - 1;
                const d = Number(parts[2]);
                if (!y || m < 0 || d < 1) return String(iso);

                // Use UTC to avoid timezone shifting the date
                const date = new Date(Date.UTC(y, m, d));

                return new Intl.DateTimeFormat(navigator.language, {
                    year: "numeric",
                    month: "2-digit",
                    day: "2-digit",
                }).format(date);
            }

        })
        .catch(err => {
            console.error(err);
            showMessageRow("Error loading user.");
        });

});
