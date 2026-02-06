/* public/scripts/add_goal.js */

const targetInput = document.getElementById('target_amount');
const monthlyInput = document.getElementById('monthly_contribution');
const dateOutput = document.getElementById('predicted-date');
const hiddenDateInput = document.getElementById('hidden_date');
const fileInput = document.getElementById('image-upload');
const fileNameDisplay = document.getElementById('file-name-display');

function calculateDate() {
    const target = parseFloat(targetInput.value);
    const monthly = parseFloat(monthlyInput.value);

    if (target > 0 && monthly > 0) {
        const monthsNeeded = Math.ceil(target / monthly);
        const today = new Date();
        
        today.setMonth(today.getMonth() + monthsNeeded);
        
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        dateOutput.innerText = today.toLocaleDateString('pl-PL', options);

        const year = today.getFullYear();
        const month = String(today.getMonth() + 1).padStart(2, '0');
        const day = String(today.getDate()).padStart(2, '0');
        
        hiddenDateInput.value = `${year}-${month}-${day}`;
    } else {
        dateOutput.innerText = "-- -- ----";
        hiddenDateInput.value = ""; 
    }
}

if (targetInput && monthlyInput) {
    targetInput.addEventListener('input', calculateDate);
    monthlyInput.addEventListener('input', calculateDate);
}

if (fileInput) {
    fileInput.addEventListener('change', function() {
        if(this.files && this.files.length > 0) {
            fileNameDisplay.innerText = this.files[0].name;
        }
    });
}