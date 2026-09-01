    <!-- Firebase SDK -->
    <script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-database-compat.js"></script>

    <script>
        const firebaseConfig = {
            apiKey:            "<?php echo FIREBASE_API_KEY; ?>",
            authDomain:        "<?php echo FIREBASE_AUTH_DOMAIN; ?>",
            databaseURL:       "<?php echo FIREBASE_DATABASE_URL; ?>",
            projectId:         "<?php echo FIREBASE_PROJECT_ID; ?>",
            storageBucket:     "<?php echo FIREBASE_STORAGE_BUCKET; ?>",
            messagingSenderId: "<?php echo FIREBASE_MESSAGING_SENDER_ID; ?>",
            appId:             "<?php echo FIREBASE_APP_ID; ?>"
        };
        firebase.initializeApp(firebaseConfig);
        const db = firebase.database();

        const APP_DEFAULTS = {
            fee:         <?php echo DEFAULT_FEE; ?>,
            billingDays: <?php echo DEFAULT_BILLING_DAYS; ?>,
            currency:    "<?php echo DEFAULT_CURRENCY; ?>",
            pin:         "<?php echo DEFAULT_PIN; ?>",
            admins:      <?php echo DEFAULT_ADMINS; ?>
        };
    </script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- App -->
    <script src="assets/js/app.js?v=<?php echo filemtime(__DIR__ . '/../assets/js/app.js'); ?>"></script>
</body>
</html>
