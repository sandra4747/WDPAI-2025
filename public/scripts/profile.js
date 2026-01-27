document.addEventListener('DOMContentLoaded', () => {
    const editBtn = document.getElementById('editBtn');
    const saveBtn = document.getElementById('saveBtn');
    const photoBadge = document.getElementById('photoBadge');
    const avatarInput = document.getElementById('avatarInput');
    const profileForm = document.getElementById('profileForm');

    // 1. Obsługa przycisku EDYTUJ
    editBtn.addEventListener('click', () => {
        const inputs = profileForm.querySelectorAll('input');
        inputs.forEach(input => input.disabled = false);

        editBtn.style.display = 'none';
        saveBtn.style.display = 'block';
        photoBadge.style.display = 'flex';
    });

    // 2. Podgląd zdjęcia na żywo (Live Preview)
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
                } else if (preview) {
                    preview.src = e.target.result;
                }
            };
            
            reader.readAsDataURL(this.files[0]);
        }
    });
});