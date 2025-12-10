<?php

require_once __DIR__ . '/../config/config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer
{
    /**
     * Send a password reset email via SMTP (PHPMailer).
     * Expects env vars: SMTP_HOST, SMTP_PORT, SMTP_USER, SMTP_PASS, SMTP_FROM, SMTP_FROM_NAME.
     */
    public static function sendResetEmail(string $toEmail, string $resetUrl, ?string $token = null): bool
    {
        if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        if (!self::loadLibrary()) {
            if (class_exists('Logger')) {
                Logger::error('PHPMailer library not found for SMTP mail');
            }
            return false;
        }

        $from = env('SMTP_FROM', 'no-reply@example.com');
        $fromName = env('SMTP_FROM_NAME', 'CRM');

        $subject = 'Reset your CRM password';
        $body = "Hello,\n\nWe received a request to reset your password.\n\n";
        if ($token !== null) {
            $body .= "Reset code: {$token}\n\n";
        }
        $body .= "You can also click the link below to set a new password:\n{$resetUrl}\n\nIf you did not request this, you can ignore this email.\n";

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = env('SMTP_HOST');
            $mail->Port = (int)env('SMTP_PORT', 587);
            $mail->SMTPAuth = true;
            $mail->Username = env('SMTP_USER');
            $mail->Password = env('SMTP_PASS');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

            $mail->setFrom($from, $fromName);
            $mail->addAddress($toEmail);

            $mail->Subject = $subject;
            $mail->Body = $body;

            return $mail->send();
        } catch (Exception $e) {
            if (class_exists('Logger')) {
                Logger::error('SMTP send failed', ['email' => $toEmail, 'error' => $e->getMessage()]);
            }
            return false;
        }
    }

    private static function loadLibrary(): bool
    {
        if (class_exists(PHPMailer::class)) {
            return true;
        }

        // Try Composer autoload if present
        $composerAutoload = dirname(__DIR__, 2) . '/vendor/autoload.php';
        if (is_file($composerAutoload)) {
            require_once $composerAutoload;
            if (class_exists(PHPMailer::class)) {
                return true;
            }
        }

        $base = dirname(__DIR__, 2);
        $candidates = [
            $base . '/vendor/phpmailer/phpmailer/src',
            $base . '/vendor/phpmailer/src',
            $base . '/assets/phpmailer/src',
            $base . '/src/assets/phpmailer/src',
            $base . '/public/assets/phpmailer/src',
        ];

        foreach ($candidates as $dir) {
            $exception = $dir . '/Exception.php';
            $phpmailer = $dir . '/PHPMailer.php';
            $smtp = $dir . '/SMTP.php';
            if (is_file($exception) && is_file($phpmailer) && is_file($smtp)) {
                require_once $exception;
                require_once $phpmailer;
                require_once $smtp;
                return class_exists(PHPMailer::class);
            }
        }
        return false;
    }
}
