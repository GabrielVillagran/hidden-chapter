<?php
/** Ellamara dashboard; native extension/security hooks retained. @version 4.4.0 */
defined('ABSPATH') || exit;
$user = wp_get_current_user();
$name = $user->first_name ?: $user->display_name;
?>
<section class="ellamara-account-overview" aria-labelledby="ellamara-account-welcome">
    <p class="ellamara-account-eyebrow">Resumen de tu cuenta</p>
    <h2 id="ellamara-account-welcome">Hola, <?php echo esc_html($name); ?></h2>
    <p class="ellamara-account-intro">Consulta el estado de tus pedidos, gestiona tus direcciones de envío y facturación, y actualiza tus datos personales y tu contraseña.</p>
    <div class="ellamara-account-cards">
    <?php foreach ([
        ['orders', 'Mis pedidos', 'Consulta el estado y los detalles de tus compras.'],
        ['edit-address', 'Mis direcciones', 'Gestiona tus direcciones de envío y facturación.'],
        ['edit-account', 'Datos de mi cuenta', 'Actualiza tus datos personales y tu contraseña.'],
    ] as [$endpoint, $title, $description]) : ?>
        <a class="ellamara-account-card<?php echo $endpoint === 'orders' ? ' is-primary' : ''; ?>" href="<?php echo esc_url(wc_get_account_endpoint_url($endpoint)); ?>">
            <?php echo \Ellamara\Theme\account_icon($endpoint); ?>
            <strong><?php echo esc_html($title); ?><span aria-hidden="true">→</span></strong>
            <span><?php echo esc_html($description); ?></span>
        </a>
    <?php endforeach; ?>
    </div>
</section>
<?php
do_action('woocommerce_account_dashboard');
do_action('woocommerce_before_my_account');
do_action('woocommerce_after_my_account');
