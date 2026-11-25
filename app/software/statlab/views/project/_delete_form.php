<form id="stat-project-delete-form" method="post" action="<?= stat_e(stat_url('controller=project&action=delete')) ?>" hidden></form>
<script>
function statDeleteProject(id) {
    const form = document.getElementById('stat-project-delete-form');
    if (!form) return;
    form.action = <?= json_encode(stat_url('controller=project&action=delete&id='), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?> + encodeURIComponent(id);
    form.submit();
}
</script>
