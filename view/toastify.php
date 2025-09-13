<?php if (isset($_GET['toast'])): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        showToast("<?= addslashes(urldecode($_GET['toast'])) ?>", "<?= $_GET['type'] ?? 'success' ?>");
    });
</script>
<?php endif; ?>
