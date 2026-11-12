<?php
/** Apply the approved accessory descriptions with WP-CLI eval-file --use-include. */
if (PHP_SAPI !== 'cli' || !defined('ABSPATH')) { exit(1); }

$copy = [
    'EL-PIN-DRAGON-GRANDE' => [
        'Un dragón de alas extendidas para que tu mochila o tote bag lleve un poco de tus mundos favoritos.',
        'Entre alas rojas, verdes y violetas, cada diseño tiene su propia presencia. Si te gustan los accesorios que se notan a primera vista, esta colección es un buen lugar para empezar.',
    ],
    'EL-PIN-DRAGON-PLATEADO' => [
        'Un dragón de acabado plateado oscuro para quienes prefieren la fantasía con un toque gótico.',
        'El relieve marca las escamas y las alas, y los detalles cambian según cómo les dé la luz. Queda bien sobre una mochila oscura o junto a otros pines de tonos fríos.',
    ],
    'EL-PIN-DRAGON-ADORABLE' => [
        'Dragones de ojos enormes y alas pequeñas que dan ganas de llevar a todas partes.',
        'Hay un dragón verde que parece estar a punto de curiosear y otro azul con aire travieso. Mira la foto para encontrar el que más te recuerda a tu compañero de aventuras favorito.',
    ],
    'EL-PIN-DRAGON-DORADO' => [
        'Un pequeño dragón de alas de colores, enmarcado por bordes dorados.',
        'El contorno dorado dibuja cada ala y hace que incluso los tonos más oscuros destaquen. Es un detalle discreto para tu estuche o bolso y queda bonito junto a otros pines.',
    ],
    'EL-PIN-GATO-METALICO' => [
        'Gatitos con humor, actitud y, por supuesto, uno que siempre lleva un libro.',
        'Esta colección tiene desde un gato lector con gafas hasta un gato negro que viaja en platillo volador. Es un regalo para esa persona que manda fotos de su gato a diario, o un detalle para tu propia bolsa de lectura.',
    ],
    'EL-PIN-GATO-ACRILICO' => [
        'Gatitos negros en pequeñas aventuras, desde leer bajo la luna hasta esconderse en una caja.',
        'Algunos llevan café, otros tienen sombrero de bruja y uno se asoma junto a una porción de pizza. Busca en la foto la escena que más se parece a tu gato o a ti en un día libre.',
    ],
    'EL-MP-ILUSTRADOS' => [
        'Guarda tu página con un dragón entre tormentas o un fantasma que parece haberse quedado leyendo contigo.',
        'La colección Dragones reúne cielos de fuego, montañas y criaturas aladas. En Fantasmas encontrarás lunas, libros y escenas de otoño. Escoge un marcapáginas para tu lectura actual o arma un trío para repartir entre tus libros y regalar uno.',
    ],
];

$products = [];
foreach ($copy as $sku => [$intro, $description]) {
    $product = wc_get_product(wc_get_product_id_by_sku($sku));
    if (!$product) { throw new RuntimeException('No se encontró el SKU ' . $sku); }
    $products[$sku] = $product;
}

foreach ($copy as $sku => [$intro, $description]) {
    $product = $products[$sku];
    $body = '<p>' . esc_html($description) . '</p>';
    if ($sku === 'EL-MP-ILUSTRADOS') {
        $body .= '<p>En la oferta de tres puedes combinar diseños de Dragones y Fantasmas.</p>';
    }
    if (($args[0] ?? '') === 'apply') {
        $product->set_short_description($intro);
        $product->set_description($body);
        $product->save();
    }
    echo wp_json_encode(['sku' => $sku, 'url' => get_permalink($product->get_id())], JSON_UNESCAPED_SLASHES) . PHP_EOL;
}
