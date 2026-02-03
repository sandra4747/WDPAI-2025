const editBtn = document.getElementById('editBtn');
const saveBtn = document.getElementById('saveBtn');
const photoBadge = document.getElementById('photoBadge');
const avatarInput = document.getElementById('avatarInput');
const profileForm = document.getElementById('profileForm');

if (profileForm && editBtn && saveBtn && avatarInput) {

    // Obsługa przycisku EDYTUJ 
    editBtn.addEventListener('click', () => {
        const inputs = profileForm.querySelectorAll('input');
        inputs.forEach(input => input.disabled = false);

        editBtn.style.display = 'none';
        saveBtn.style.display = 'block';
        if (photoBadge) photoBadge.style.display = 'flex';
    });

    // Podgląd zdjęcia na żywo
    avatarInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();

            reader.onload = function(e) {
                let preview = document.getElementById('preview');
                const placeholder = document.getElementById('placeholder');

                if (placeholder) {
                    const img = document.createElement('img');
                    img.id = 'preview';
                    img.className = 'avatar-img';
                    img.src = e.target.result;
                    placeholder.replaceWith(img);
                } 
                else if (preview) {
                    preview.src = e.target.result;
                }
            };

            reader.readAsDataURL(this.files[0]);
        }
    });
}
