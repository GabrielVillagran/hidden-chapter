<?php
defined('ABSPATH') || exit;
global $product;
$available = \Ellamara\Product\remaining($product);
$stock = $product->managing_stock() ? (int) $product->get_stock_quantity() : -1;
?>
<div class="quantity ellamara-pdp-quantity" data-product-id="<?php echo esc_attr($product->get_id()); ?>" data-stock-id="<?php echo esc_attr($product->get_stock_managed_by_id()); ?>" data-stock="<?php echo esc_attr($stock); ?>" data-individual="<?php echo $product->is_sold_individually() ? '1' : '0'; ?>">
<label for="<?php echo esc_attr($input_id); ?>">Cantidad</label>
<select id="<?php echo esc_attr($input_id); ?>" name="<?php echo esc_attr($input_name); ?>" class="qty ellamara-pdp-select" aria-describedby="<?php echo esc_attr($input_id); ?>-help" <?php disabled($available, 0); ?>>
<?php if (!$available) : ?><option value="0">Sin unidades adicionales</option><?php endif; ?>
<?php for ($quantity = 1; $quantity <= $available; $quantity++) : ?>
<option value="<?php echo esc_attr($quantity); ?>" <?php selected($quantity, min($available, max(1, (int) $input_value))); ?>><?php echo esc_html($quantity . ($quantity === 1 ? ' ejemplar' : ' ejemplares')); ?></option>
<?php endfor; ?>
</select>
<input type="hidden" name="ellamara_pdp_quantity" value="1">
<p id="<?php echo esc_attr($input_id); ?>-help" class="ellamara-pdp-help" aria-live="polite" <?php echo $available === 1 ? 'hidden' : ''; ?>><?php echo esc_html($available > 1 ? 'Selecciona cuántos ejemplares añadir. El límite tiene en cuenta tu carrito y las existencias.' : ($available ? '' : 'Ya tienes todas las unidades disponibles en tu carrito.')); ?></p>
</div>
<a class="ellamara-pdp-view-cart button" href="<?php echo esc_url(wc_get_cart_url()); ?>" <?php echo $available ? 'hidden' : ''; ?>>Ver carrito</a>
