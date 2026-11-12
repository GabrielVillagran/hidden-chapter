<?php
/** Ellamara account sidebar. Native WooCommerce endpoint helpers. @version 9.3.0 */
defined('ABSPATH') || exit;
do_action('woocommerce_before_account_navigation');
$items = wc_get_account_menu_items();
?>
<aside class="ellamara-account-sidebar" aria-label="Mi espacio">
    <p class="ellamara-account-eyebrow">Mi espacio</p>
    <nav class="woocommerce-MyAccount-navigation" aria-label="Páginas de cuenta">
        <ul>
        <?php foreach ($items as $endpoint => $label) : if ($endpoint === 'customer-logout') { continue; } ?>
            <li class="<?php echo esc_attr(wc_get_account_menu_item_classes($endpoint)); ?>">
                <a href="<?php echo esc_url(wc_get_account_endpoint_url($endpoint)); ?>" <?php echo wc_is_current_account_menu_item($endpoint) ? 'aria-current="page"' : ''; ?>>
                    <?php echo \Ellamara\Theme\account_icon($endpoint); ?><span><?php echo esc_html($label); ?></span>
                </a>
            </li>
        <?php endforeach; ?>
        </ul>
    </nav>
    <section class="ellamara-account-help" aria-label="Ayuda con tu cuenta">
        <h2>¿Necesitas ayuda?</h2>
        <p>Estamos para acompañarte.</p>
        <a href="<?php echo esc_url(\Ellamara\Theme\account_contact_url()); ?>">Contactar con Ellamara <span aria-hidden="true">→</span></a>
    </section>
    <?php if (isset($items['customer-logout'])) : ?>
        <a class="ellamara-account-logout" href="<?php echo esc_url(wc_get_account_endpoint_url('customer-logout')); ?>"><?php echo \Ellamara\Theme\account_icon('customer-logout'); ?>Cerrar sesión</a>
    <?php endif; ?>
</aside>
<?php do_action('woocommerce_after_account_navigation'); ?>
