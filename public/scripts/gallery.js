const modal = document.getElementById("details-modal");

if (modal) {
    const closeBtn = document.querySelector(".close-modal");

    // Kliknięcie w element galerii 
    document.querySelectorAll(".gallery-item").forEach(item => {
        item.addEventListener("click", () => {
            const goalId = item.getAttribute("data-id");
            openGoalDetails(goalId);
        });
    });

    if (closeBtn) {
        closeBtn.addEventListener("click", () => modal.style.display = "none");
    }

    window.addEventListener("click", (e) => {
        if (e.target === modal) modal.style.display = "none";
    });

    // Wyszukiwarka galerii 
    const searchInput = document.getElementById("gallery-search");

    if (searchInput) {
        searchInput.addEventListener("keyup", (e) => {
            const term = e.target.value.toLowerCase();

            document.querySelectorAll(".gallery-item").forEach(item => {
                const title = item.innerText.toLowerCase();
                item.style.display = title.includes(term) ? "block" : "none";
            });
        });
    }

    // Pobieranie szczegółów celu 
    async function openGoalDetails(id) {
        try {
            const response = await fetch('/getGoalDetails', {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ id })
            });

            const data = await response.json();

            if (data.goal) {
                fillModal(data.goal, data.logs || []);
                modal.style.display = "flex";
            }
        } catch (error) {
            console.error("Błąd pobierania:", error);
        }
    }

    function fillModal(goal, logs) {
        document.getElementById("modal-title").innerText = goal.title;

        const target = parseFloat(goal.targetAmount) || 0;
        const current = parseFloat(goal.currentAmount) || 0;
        const missing = target - current;

        const missingEl = document.getElementById("modal-missing");
        missingEl.innerText =
            missing > 0
                ? `${missing.toFixed(2)} PLN`
                : "Cel osiągnięty! 🎉";

        // Pasek postępu
        const perc = target > 0 ? Math.min(100, (current / target) * 100) : 0;
        document.getElementById("modal-bar").style.width = `${perc}%`;

        // Kalkulator 
        const calcInput = document.getElementById("calc-monthly");
        const calcResult = document.getElementById("calc-result-text");

        calcInput.value = "";
        calcResult.innerText =
            missing > 0
                ? "Wpisz kwotę, aby obliczyć datę."
                : "Cel już został zrealizowany!";

        calcInput.oninput = () => {
            const monthly = parseFloat(calcInput.value);

            if (monthly > 0 && missing > 0) {
                const months = Math.ceil(missing / monthly);
                const date = new Date();
                date.setMonth(date.getMonth() + months);

                calcResult.innerText =
                    `Cel osiągniesz: ${date.toLocaleDateString('pl-PL')} (za ${months} mies.)`;
                calcResult.style.color = "#FF0080";
            } else if (missing <= 0) {
                calcResult.innerText = "Nic więcej nie musisz wpłacać!";
            } else {
                calcResult.innerText = "Wpisz poprawną kwotę.";
                calcResult.style.color = "#666";
            }
        };

        // Mini kalendarz 
        renderCalendar(logs);
    }

    function renderCalendar(logs) {
        const calendarContainer = document.getElementById("mini-calendar");
        calendarContainer.innerHTML = "";

        const logDates = logs.map(log =>
            log.change_date.split(" ")[0]
        );

        for (let i = 27; i >= 0; i--) {
            const d = new Date();
            d.setDate(d.getDate() - i);

            const dateString = d.toISOString().split("T")[0];
            const dayNum = d.getDate();

            const dayDiv = document.createElement("div");
            dayDiv.classList.add("calendar-day");
            dayDiv.innerText = dayNum;

            if (logDates.includes(dateString)) {
                dayDiv.classList.add("active");
                dayDiv.title = "W tym dniu dokonano wpłaty!";
            }

            calendarContainer.appendChild(dayDiv);
        }
    }
}
