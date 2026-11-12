<?php defined('ABSPATH') || exit; ?>
<div class="ellamara-contact-layout">
<section class="ellamara-contact-intro" aria-labelledby="ellamara-contact-title">
<p class="ellamara-contact-eyebrow">Estamos para ayudarte</p>
<h2 id="ellamara-contact-title">Conversemos sobre tu próxima historia</h2>
<p>¿Tienes una pregunta sobre un producto o necesitas ayuda con tu compra? Déjanos tu mensaje.</p>
<div class="ellamara-contact-email"><h3>Atención al cliente</h3><a href="mailto:<?php echo esc_attr(\Ellamara\Contact\RECIPIENT); ?>"><?php echo esc_html(\Ellamara\Contact\RECIPIENT); ?></a></div>
<p>Teléfono: <a href="tel:+573193224675">+57 319 322 4675</a></p>
<p>Horario: Lunes a Viernes, 9:00 a. m. – 10:00 p. m.</p>
<p class="ellamara-contact-tip">Si tu consulta es sobre una compra, incluye el número de pedido. No envíes contraseñas ni datos de tarjetas.</p>
</section>
<section class="ellamara-contact-panel" id="contacto-formulario" aria-labelledby="ellamara-form-title">
<h2 id="ellamara-form-title">Escríbenos</h2>
<div class="ellamara-contact-direct"><p>¿Prefieres conversar directamente?</p><a href="https://wa.me/message/3LIIIQSYBGX5G1" target="_blank" rel="noopener noreferrer">Escríbenos por WhatsApp <span aria-hidden="true">↗</span></a></div>
<p>Todos los campos son obligatorios.</p>
<?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'enviado') : ?>
<div class="ellamara-contact-success" role="status"><strong>Gracias por escribirnos.</strong><p>Tu mensaje se ha enviado para su entrega. Te responderemos al correo que indicaste.</p></div>
<?php endif; ?>
<?php if ($errors) : ?>
<div class="ellamara-contact-error" role="alert"><strong>Revisa tu mensaje</strong><ul><?php foreach ($errors as $error) : ?><li><?php echo esc_html($error); ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>
<form action="<?php echo esc_url(get_permalink()); ?>#contacto-formulario" method="post" class="ellamara-contact-form">
<?php wp_nonce_field('ellamara_contact', 'ellamara_contact_nonce'); ?>
<div class="ellamara-contact-trap" aria-hidden="true"><label>Sitio web<input name="ellamara_website" type="text" tabindex="-1" autocomplete="off" value=""></label></div>
<div><label for="ellamara-name">Nombre</label><input id="ellamara-name" name="ellamara_name" type="text" autocomplete="name" maxlength="100" required value="<?php echo esc_attr($values['name']); ?>" <?php if (isset($errors['name'])) { echo 'aria-invalid="true"'; } ?>></div>
<div><label for="ellamara-email">Correo electrónico</label><input id="ellamara-email" name="ellamara_email" type="email" autocomplete="email" maxlength="254" required value="<?php echo esc_attr($values['email']); ?>" <?php if (isset($errors['email'])) { echo 'aria-invalid="true"'; } ?>></div>
<div><label for="ellamara-message">Mensaje</label><textarea id="ellamara-message" name="ellamara_message" rows="6" minlength="10" maxlength="5000" required <?php if (isset($errors['message'])) { echo 'aria-invalid="true"'; } ?>><?php echo esc_textarea($values['message']); ?></textarea></div>
<p class="ellamara-contact-tip">Usaremos tus datos para responder a esta consulta.</p>
<button type="submit" name="ellamara_contact_submit" value="1">Enviar mensaje <span aria-hidden="true">→</span></button>
</form>
</section>
</div>
