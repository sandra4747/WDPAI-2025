const modal = document.getElementById("funds-modal");

if (modal) {
    const closeBtn = document.querySelector(".close-modal");
    const hiddenInput = document.getElementById("modal-goal-id");
    const amountInput = document.getElementById("amount-input");

    // OTWIERANIE MODALA 
    document.querySelectorAll(".add-funds-btn").forEach(button => {
        button.addEventListener("click", () => {
            hiddenInput.value = button.getAttribute("data-id");
            amountInput.value = "";
            modal.style.display = "flex";
            amountInput.focus();
        });
    });

    // ZAMYKANIE MODALA 
    if (closeBtn) {
        closeBtn.addEventListener("click", () => {
            modal.style.display = "none";
        });
    }

    window.addEventListener("click", (e) => {
        if (e.target === modal) {
            modal.style.display = "none";
        }
    });

    // OBSŁUGA FORMULARZA (FETCH) 
    const form = modal.querySelector("form");

    if (form) {
        form.addEventListener("submit", async (e) => {
            e.preventDefault();

            const goalId = hiddenInput.value;
            const amount = amountInput.value;

            try {
                const response = await fetch("/addFunds", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        goal_id: goalId,
                        amount: amount
                    })
                });

                const data = await response.json();

                if (data.status === "success") {

                    // Aktualizacja paska konkretnego celu 
                    const goalBar = document.getElementById(`goal-bar-${goalId}`);
                    const goalText = document.getElementById(`goal-perc-${goalId}`);

                    if (goalBar && goalText) {
                        goalBar.style.width = `${data.newGoalPercent}%`;
                        goalText.innerText = `${data.newGoalPercent}%`;
                    }

                    // Aktualizacja głównego paska 
                    const totalBar = document.getElementById("total-progress-bar");
                    const totalText = document.getElementById("total-progress-text");

                    if (totalBar && totalText) {
                        totalBar.style.width = `${data.newTotalPercent}%`;
                        totalText.innerText = `${data.newTotalPercent}%`;
                    }

                    // Zamknięcie modala 
                    modal.style.display = "none";
                    amountInput.value = "";
                }

            } catch (error) {
                console.error("Błąd:", error);
                alert("Coś poszło nie tak przy wpłacie.");
            }
        });
    }
}
