<script src="js/main.min.js?<?= $_ENV['timestamp'] ?>"></script>
<script>
    window.APP.JIRA.baseUrl = '<?= $_ENV['JIRA_BASE_URL'] ?>';
    window.APP.translations = <?= $_ENV['translations'] ?>;
    window.APP.language = '<?= $_ENV['language'] ?>';
</script>
