<?php
// --- SMTP Email Configuration ---
// Yahan apni email ki details daalein jisse aap email bhejna chahte hain.

define('SMTP_HOST', 'smtp.gmail.com');          // Aapka email provider ka SMTP server (Gmail ke liye yehi hai)
define('SMTP_USERNAME', 'jsnjworkmail@gmail.com'); // Aapka poora email address
define('SMTP_PASSWORD', 'jsnjinfomedia5564');    // Aapka email password. Gmail ke liye 'App Password' istemal karein.
define('SMTP_PORT', 587);                        // SMTP port (587 for TLS, 465 for SSL)
define('SMTP_SECURE', 'tls');                    // Encryption (tls ya ssl)

// Email bhejte waqt "From" mein kya naam dikhega
define('SMTP_FROM_NAME', 'Chhavi Company Name'); // Aapki company ka naam