<?php
/**
 * Title: Términos y condiciones de Ellamara
 * Slug: ellamara/terms
 * Categories: ellamara
 * Inserter: no
 */
defined('ABSPATH') || exit;

$contact = get_page_by_path('contacto');
$privacy = get_privacy_policy_url();
$warranty = get_page_by_path('cambios-y-garantias');
$returns = get_page_by_path('devoluciones');
?>
<div class="ellamara-terms">
    <p class="ellamara-terms-intro">Consulta cómo pedir por WhatsApp, qué información recibirás antes de confirmar y a quién acudir si necesitas ayuda con un pedido.</p>

    <div class="ellamara-terms-panel">
        <nav aria-labelledby="indice-terminos">
            <h2 id="indice-terminos">Contenido de esta página</h2>
            <ul>
                <li><a href="#nuestra-tienda">Nuestra tienda</a></li>
                <li><a href="#productos">Productos y disponibilidad</a></li>
                <li><a href="#precios">Precios y pedidos</a></li>
                <li><a href="#entregas">Envíos y entregas</a></li>
                <li><a href="#retracto">Política de devolución</a></li>
                <li><a href="#garantia">Cambios y garantías</a></li>
                <li><a href="#reversion">Reversión del pago</a></li>
                <li><a href="#contacto-terminos">Atención al cliente</a></li>
                <li><a href="#privacidad">Privacidad</a></li>
                <li><a href="#sugerencias">Artículos sugeridos</a></li>
                <li><a href="#contenidos">Contenidos del sitio</a></li>
            </ul>
        </nav>

        <section aria-labelledby="nuestra-tienda">
            <h2 id="nuestra-tienda">Nuestra tienda</h2>
            <p>El Capítulo Oculto de Ellamara ofrece un catálogo de libros físicos, pines y marcapáginas. Somos una tienda en línea de Bogotá, Colombia. Puedes explorar las fichas sin crear una cuenta y hacer pedidos o consultas por <a href="https://wa.me/573193224675">WhatsApp</a>. La compra directa en la web estará disponible más adelante.</p>
        </section>
        <section aria-labelledby="productos">
            <h2 id="productos">Productos y disponibilidad</h2>
            <p>Consulta la ficha de cada libro para conocer la edición, el idioma, la encuadernación y el ISBN cuando esté informado. En los accesorios, revisa la descripción y las fotos para conocer la colección y los diseños que muestra la ficha.</p>
            <p>Si ves la etiqueta «Próximamente», el producto todavía no está disponible para pedir. Si tienes dudas sobre una edición o un diseño en particular, escríbenos.</p>
        </section>
        <section aria-labelledby="precios">
            <h2 id="precios">Precios y pedidos</h2>
            <p>Los precios del catálogo se muestran en pesos colombianos (COP). El precio de cada libro incluye su empaque. Las ofertas de accesorios indican el precio y la cantidad en la ficha correspondiente. Para pedir por WhatsApp, envíanos el título o diseño y la cantidad que buscas. Antes de confirmar recibirás el resumen con la disponibilidad, el medio de pago, el costo de envío por separado cuando aplique y el total a pagar.</p>
        </section>
        <section aria-labelledby="entregas">
            <h2 id="entregas">Envíos y entregas</h2>
            <p>Comparte tu ciudad o municipio para consultar la cobertura. Te informaremos el plazo de entrega antes de que confirmes el pedido. Si el producto deja de estar disponible o se supera el plazo acordado, puedes solicitar la cancelación y la devolución de lo pagado conforme a la ley.</p>
        </section>
        <section aria-labelledby="retracto">
            <h2 id="retracto">Política de devolución</h2>
            <p>En compras a distancia, cuando aplique el derecho de retracto, puedes solicitar la devolución dentro de los cinco días hábiles siguientes a la entrega.</p>
            <?php if ($returns && $returns->post_status === 'publish') : ?>
                <p>Consulta la <a href="<?php echo esc_url(get_permalink($returns)); ?>">Política de devolución</a> para conocer cómo hacer la solicitud, los costos de devolución y el plazo de reembolso.</p>
            <?php endif; ?>
        </section>
        <section aria-labelledby="garantia">
            <h2 id="garantia">Cambios y garantías</h2>
            <p>Si tu pedido llega dañado, recibes un producto distinto o encuentras un defecto, comunícate con nosotros para solicitar ayuda.</p>
            <?php if ($warranty && $warranty->post_status === 'publish') : ?>
                <p>Consulta <a href="<?php echo esc_url(get_permalink($warranty)); ?>">Cambios y garantías</a> para saber cómo presentar tu solicitud.</p>
            <?php endif; ?>

        </section>
        <section aria-labelledby="reversion">
            <h2 id="reversion">Reversión del pago</h2>
            <p>Si pagaste por un medio electrónico y se cumplen las condiciones legales, puedes solicitar la reversión por fraude, una operación no solicitada, falta de entrega o un producto defectuoso o diferente del pedido. Presenta la queja a Ellamara y avisa al emisor del medio de pago dentro del plazo legal de cinco días hábiles, contado desde el hecho que corresponda en tu caso.</p>
        </section>
        <section aria-labelledby="contacto-terminos">
            <h2 id="contacto-terminos">Atención al cliente</h2>
            <?php if ($contact && $contact->post_status === 'publish') : ?>
                <p>Para consultas sobre el catálogo o para presentar una solicitud relacionada con Ellamara, usa nuestros <a href="<?php echo esc_url(get_permalink($contact)); ?>">canales de contacto</a>.</p>
            <?php else : ?>
                <p>Para consultas o solicitudes, escríbenos a <a href="mailto:ellamarahiddenchapter@gmail.com">ellamarahiddenchapter@gmail.com</a>.</p>
            <?php endif; ?>
            <p>También puedes escribirnos a <a href="mailto:ellamarahiddenchapter@gmail.com">ellamarahiddenchapter@gmail.com</a> o al <a href="https://wa.me/573193224675">WhatsApp +57 319 322 4675</a>. Si ya tienes una referencia del pedido o de tu solicitud, inclúyela para que podamos ubicarla.</p>
            <p>Puedes consultar tus derechos como consumidor en el <a href="https://www.sic.gov.co/">sitio de la Superintendencia de Industria y Comercio</a>.</p>
        </section>
        <section aria-labelledby="privacidad">
            <h2 id="privacidad">Privacidad</h2>
            <?php if ($privacy) : ?>
                <p>Consulta nuestra <a href="<?php echo esc_url($privacy); ?>">política de privacidad</a> para conocer cómo se tratan los datos personales y cómo ejercer tus derechos.</p>
            <?php else : ?>
                <p>Si tienes preguntas sobre el tratamiento de tus datos personales o quieres ejercer tus derechos, comunícate con nosotros por los canales de atención.</p>
            <?php endif; ?>
        </section>
        <section aria-labelledby="sugerencias">
            <h2 id="sugerencias">Artículos sugeridos</h2>
            <p>Para recomendarte productos, usamos el almacenamiento temporal del navegador (<code>sessionStorage</code>) para recordar hasta ocho fichas que hayas visitado y tu última búsqueda en la tienda. Esta actividad corresponde a la pestaña y vence dos horas después de la última visita a una ficha o búsqueda registrada.</p>
            <p>Al abrir Artículos sugeridos, enviamos esa actividad al sitio de Ellamara para calcular las recomendaciones. La función no la guarda en la base de datos ni usa cookies propias o servicios de analítica. Puedes borrar la actividad de esta pestaña con <strong>Restablecer sugerencias</strong> y volver a la selección general.</p>
        </section>
        <section aria-labelledby="contenidos">
            <h2 id="contenidos">Contenidos del sitio</h2>
            <p>La identidad gráfica y los contenidos originales de Ellamara están protegidos por la normativa aplicable. Los derechos sobre las portadas y otros materiales de terceros corresponden a sus titulares.</p>
        </section>
    </div>
</div>
