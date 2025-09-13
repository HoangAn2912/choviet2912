<script>
    document.querySelectorAll('.tab-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelectorAll('.tab-link').forEach(l => l.classList.remove('tab-active'));
            document.querySelectorAll('.tab-content').forEach(t => t.style.display = 'none');
            
            this.classList.add('tab-active');
            const target = document.querySelector(this.dataset.target);
            if (target) target.style.display = 'block';
        });
    });
</script>
