document.addEventListener("click", (e) => {
    const btn = e.target.closest(".js-delete");
    if (!btn) return;

    const id = btn.dataset.userId;
    if (!id) return;

    if (!confirm("Opravdu chcete tohoto uživatele smazat?")) return;

    fetch("delete-user.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "id=" + encodeURIComponent(id)
    })
        .then(r => r.json())
        .then(res => {
            if (res.ok) {
                btn.closest("tr").remove();
            } else {
                alert(res.error || "Delete failed");
            }
        })
        .catch(err => {
            console.error(err);
            alert("Server error");
        });
});

document.querySelectorAll("time.js-date").forEach(t => {
    const iso = t.getAttribute("datetime"); // "YYYY-MM-DD"
    if (!iso) return;

    // Avoid timezone shifts by building a UTC date from parts
    const parts = iso.split("-");
    if (parts.length !== 3) return;

    const y = Number(parts[0]);
    const m = Number(parts[1]) - 1;
    const d = Number(parts[2]);

    const date = new Date(Date.UTC(y, m, d));

    const formatted = new Intl.DateTimeFormat(navigator.language, {
        year: "numeric",
        month: "2-digit",
        day: "2-digit",
    }).format(date);

    t.textContent = formatted;
});
