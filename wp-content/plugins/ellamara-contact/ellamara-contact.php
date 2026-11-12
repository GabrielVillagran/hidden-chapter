<?php
/**
 * Plugin Name: Ellamara · Contacto
 * Description: Formulario de atención al cliente con validación, antispam y envío mediante WordPress.
 * Version: 0.1.0
 * Requires PHP: 8.2
 */
declare(strict_types=1);
namespace Ellamara\Contact;
defined('ABSPATH') || exit;
const RECIPIENT = 'ellamarahiddenchapter@gmail.com';

function values(): array {
    $result = [];
    foreach (['name', 'email', 'message'] as $key) {
        $raw = $_POST['ellamara_' . $key] ?? '';
        $raw = is_string($raw) ? wp_unslash($raw) : '';
        $result[$key] = $key === 'message' ? sanitize_textarea_field($raw) : sanitize_text_field($raw);
    }
    return $result;
}

function errors(array $values): array {
    $errors = [];
    if (trim($values['name']) === '' || mb_strlen($values['name']) > 100) { $errors['name'] = 'Escribe tu nombre (máximo 100 caracteres).'; }
    if (!is_email($values['email']) || strlen($values['email']) > 254 || preg_match('/[\r\n]/', $values['email'])) { $errors['email'] = 'Escribe un correo electrónico válido.'; }
    if (mb_strlen(trim($values['message'])) < 10 || mb_strlen($values['message']) > 5000) { $errors['message'] = 'Escribe un mensaje de entre 10 y 5000 caracteres.'; }
    return $errors;
}

add_action('template_redirect', static function (): void {
    if (!is_page('contacto')) { return; }
    if (!defined('DONOTCACHEPAGE')) { define('DONOTCACHEPAGE', true); }
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !isset($_POST['ellamara_contact_submit'])) { return; }
    $GLOBALS['ellamara_contact_errors'] = [];
    $nonce = $_POST['ellamara_contact_nonce'] ?? '';
    $trap = $_POST['ellamara_website'] ?? '';
    if (!is_string($nonce) || !wp_verify_nonce($nonce, 'ellamara_contact') || !is_string($trap) || $trap !== '') {
        $GLOBALS['ellamara_contact_errors'] = ['form' => 'No pudimos validar el formulario. Recarga esta página e inténtalo de nuevo.'];
        return;
    }
    $values = values();
    $GLOBALS['ellamara_contact_errors'] = errors($values);
    if ($GLOBALS['ellamara_contact_errors']) { return; }
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'local';
    $key = 'ellamara_contact_' . hash_hmac('sha256', $ip, wp_salt('nonce'));
    $attempts = (int) get_transient($key);
    if ($attempts >= 5) {
        $GLOBALS['ellamara_contact_errors'] = ['form' => 'Has enviado varias consultas. Espera unos minutos antes de volver a intentarlo.'];
        return;
    }
    set_transient($key, $attempts + 1, 10 * MINUTE_IN_SECONDS);
    $body = "Nueva consulta desde el formulario de Ellamara\n\nNombre: {$values['name']}\nCorreo: {$values['email']}\n\nMensaje:\n{$values['message']}";
    $sent = wp_mail(RECIPIENT, 'Nueva consulta · Ellamara', $body, ['Content-Type: text/plain; charset=UTF-8', 'From: Ellamara <' . RECIPIENT . '>', 'Reply-To: ' . $values['email']]);
    if (!$sent) {
        $GLOBALS['ellamara_contact_errors'] = ['form' => 'No pudimos enviar tu mensaje. Inténtalo más tarde o escríbenos directamente a ' . RECIPIENT . '.'];
        return;
    }
    wp_safe_redirect(add_query_arg('mensaje', 'enviado', get_permalink()) . '#contacto-formulario', 303);
    exit;
});

add_shortcode('ellamara_contact', static function (): string {
    $errors = $GLOBALS['ellamara_contact_errors'] ?? [];
    $values = $errors ? values() : ['name' => '', 'email' => '', 'message' => ''];
    ob_start();
    require __DIR__ . '/form.php';
    return ob_get_clean();
});
add_action('wp_enqueue_scripts', static function (): void {
    if (is_page('contacto')) { wp_enqueue_style('ellamara-contact', plugin_dir_url(__FILE__) . 'contact.css', ['ellamara-experience'], (string) filemtime(__DIR__ . '/contact.css')); }
});
