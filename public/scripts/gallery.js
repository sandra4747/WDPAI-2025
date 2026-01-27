document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("details-modal");
    const closeBtn = document.querySelector(".close-modal");
    
    // 1. Kliknięcie w element galerii
    document.querySelectorAll(".gallery-item").forEach(item => {
        item.addEventListener("click", () => {
            const goalId = item.getAttribute("data-id");
            openGoalDetails(goalId);
        });
    });

    closeBtn.addEventListener("click", () => modal.style.display = "none");
    window.addEventListener("click", (e) => {
        if (e.target === modal) modal.style.display = "none";
    });

    // 2. Wyszukiwarka (Filtr w JS - szybki sposób)
    const searchInput = document.getElementById("gallery-search");
    searchInput.addEventListener("keyup", (e) => {
        const term = e.target.value.toLowerCase();
        document.querySelectorAll(".gallery-item").forEach(item => {
            const title = item.innerText.toLowerCase();
            item.style.display = title.includes(term) ? "block" : "none";
        });
    });

    // 3. Główna funkcja pobierająca dane
    async function openGoalDetails(id) {
        try {
            const response = await fetch('/getGoalDetails', {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ id: id })
            });
            
            const data = await response.json();
            if(data.goal) {
                fillModal(data.goal, data.logs);
                modal.style.display = "flex";
            }
        } catch (error) {
            console.error("Błąd pobierania:", error);
        }
    }

    function fillModal(goal, logs) {
        // A. Wypełnij teksty - ZMIANA NA camelCase (zgodnie z DTO)
        document.getElementById("modal-title").innerText = goal.title;
        
        // Zmieniamy z target_amount na targetAmount oraz current_amount na currentAmount
        const target = parseFloat(goal.targetAmount) || 0;
        const current = parseFloat(goal.currentAmount) || 0;
        const missing = target - current;

        document.getElementById("modal-missing").innerText = missing > 0 ? `${missing.toFixed(2)} PLN` : "Cel osiągnięty! 🎉";
        
        // B. Pasek - ZMIANA NA camelCase
        const perc = target > 0 ? Math.min(100, (current / target) * 100) : 0;
        document.getElementById("modal-bar").style.width = `${perc}%`;

        // C. Kalkulator
        const calcInput = document.getElementById("calc-monthly");
        const calcResult = document.getElementById("calc-result-text");
        
        // Czyścimy poprzedni wynik kalkulatora przy otwarciu nowego celu
        calcInput.value = "";
        calcResult.innerText = missing > 0 ? "Wpisz kwotę, aby obliczyć datę." : "Cel już został zrealizowany!";

        calcInput.oninput = () => {
            const monthly = parseFloat(calcInput.value);
            if(monthly > 0 && missing > 0) {
                const months = Math.ceil(missing / monthly);
                const date = new Date();
                date.setMonth(date.getMonth() + months);
                calcResult.innerText = `Cel osiągniesz: ${date.toLocaleDateString()} (za ${months} mies.)`;
                calcResult.style.color = "#FF0080";
            } else if (missing <= 0) {
                calcResult.innerText = "Nic więcej nie musisz wpłacać!";
            } else {
                calcResult.innerText = "Wpisz poprawną kwotę.";
                calcResult.style.color = "#666";
            }
        };

        // D. Kalendarz (Renderowanie ostatnich 30 dni)
        renderCalendar(logs);
    }
    
    function renderCalendar(logs) {
        const calendarContainer = document.getElementById("mini-calendar");
        calendarContainer.innerHTML = ""; // Czyścimy

        // Pobieramy daty wpłat z logów (tylko rok-miesiąc-dzień)
        const logDates = logs.map(log => log.change_date.split(' ')[0]);

        // Generujemy ostatnie 28 dni
        for (let i = 27; i >= 0; i--) {
            const d = new Date();
            d.setDate(d.getDate() - i);
            const dateString = d.toISOString().split('T')[0]; // format YYYY-MM-DD
            const dayNum = d.getDate();

            const dayDiv = document.createElement("div");
            dayDiv.classList.add("calendar-day");
            dayDiv.innerText = dayNum;

            // Sprawdzamy czy w tym dniu była wpłata
            if (logDates.includes(dateString)) {
                dayDiv.classList.add("active");
                dayDiv.title = "W tym dniu dokonano wpłaty!";
            }

            calendarContainer.appendChild(dayDiv);
        }
    }
});