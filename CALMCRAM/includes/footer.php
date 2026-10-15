    </main><!-- /main-content -->

    <script>
        window.CRAMCALM_API = <?= json_encode(API_PATH) ?>;
        window.CRAMCALM_BASE = <?= json_encode(BASE_PATH) ?>;
    </script>

    <!-- Core JS (load order matters) -->
    <script src="<?= asset_url('js/storage.js') ?>"></script>
    <script src="<?= asset_url('js/auth.js') ?>"></script>
    <script src="<?= asset_url('js/animations.js') ?>"></script>
    <script src="<?= asset_url('js/notifications.js') ?>"></script>
    <script src="<?= asset_url('js/state-engine.js') ?>"></script>

    <!-- Feature JS -->
    <script src="<?= asset_url('js/tasks.js') ?>"></script>
    <script src="<?= asset_url('js/timer.js') ?>"></script>
    <script src="<?= asset_url('js/assistant.js') ?>"></script>
    <script src="<?= asset_url('js/wisdom-tree.js') ?>"></script>
    <script src="<?= asset_url('js/cram-detector.js') ?>"></script>
    <script src="<?= asset_url('js/backpack.js') ?>"></script>
    <script src="<?= asset_url('js/personality.js') ?>"></script>

    <!-- App init (must be last) -->
    <script src="<?= asset_url('js/app.js') ?>"></script>
</body>
</html>
