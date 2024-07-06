document.addEventListener('DOMContentLoaded', function() {
    let lastChecked = null;
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
    
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('click', function(e) {
            if (lastChecked && e.shiftKey) {
                let start = Array.from(checkboxes).indexOf(this);
                let end = Array.from(checkboxes).indexOf(lastChecked);
                
                checkboxes.forEach((box, index) => {
                    if ((start <= index && index <= end) || (end <= index && index <= start)) {
                        box.checked = lastChecked.checked;
                    }
                });
            }
            lastChecked = this;
        });
    });

    document.querySelectorAll('.md-delete-webp').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const file = this.getAttribute('data-file');
            if (confirm('Вы уверены, что хотите удалить этот WebP файл?')) {
                fetch(webpConverter.ajax_url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                    },
                    body: 'action=delete_webp_file&file=' + encodeURIComponent(file)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.data);
                        this.parentElement.classList.remove('md-file-converted');
                        this.remove();
                    } else {
                        alert('Ошибка: ' + data.data);
                    }
                });
            }
        });
    });
});
