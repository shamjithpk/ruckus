<?php
/**
 * WiFi Manager - Configuration
 *
 * IMPORTANT: Replace the Firebase config values below with your own.
 * Go to: Firebase Console → Project Settings → Web App → Config
 */

// Firebase Web App Configuration
define('FIREBASE_API_KEY', 'AIzaSyALAe4B2brPQWK0GLZGmZgnZlrZfVaRsLk');
define('FIREBASE_AUTH_DOMAIN', 'wifi-manage-58558.firebaseapp.com');
define('FIREBASE_DATABASE_URL', 'https://wifi-manage-58558-default-rtdb.firebaseio.com');
define('FIREBASE_PROJECT_ID', 'wifi-manage-58558');
define('FIREBASE_STORAGE_BUCKET', 'wifi-manage-58558.firebasestorage.app');
define('FIREBASE_MESSAGING_SENDER_ID', '737019625829');
define('FIREBASE_APP_ID', '1:737019625829:web:8b1369605656d870c1618c');

// App Settings
define('APP_NAME', 'WiFi Manage');
define('DEFAULT_FEE', 20);
define('DEFAULT_BILLING_DAYS', 30);
define('DEFAULT_CURRENCY', 'SAR');
define('DEFAULT_PIN', '1234');
define('DEFAULT_ADMINS', json_encode(['Javaid', 'Sujeesh']));
?>
