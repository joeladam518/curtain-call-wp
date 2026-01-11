<?php
/**
 * Plugin Name: Mailpit Configuration
 * Description: Configures WordPress to send emails through Mailpit for local development
 * Version: 1.0.0
 * Author: Auto-generated
 */

/**
 * Configure PHPMailer to use Mailpit SMTP server
 *
 * @param PHPMailer $phpmailer The PHPMailer instance
 */
add_action('phpmailer_init', function ($phpmailer) {
    $phpmailer->isSMTP();
    $phpmailer->Host = 'mailpit';
    $phpmailer->Port = 1025;
    $phpmailer->SMTPAuth = false;
    $phpmailer->SMTPSecure = false;
    $phpmailer->SMTPAutoTLS = false;
    $phpmailer->From = 'wordpress@wpsite.test';
    $phpmailer->FromName = 'CurtainCallWP Dev';
});
