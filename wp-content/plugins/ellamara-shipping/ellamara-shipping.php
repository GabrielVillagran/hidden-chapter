<?php
/**
 * Plugin Name: Ellamara · Preparación de envíos
 * Description: Configuración de empaques y revisión de datos logísticos antes de conectar el cotizador de transporte.
 * Version: 0.1.0
 * Requires PHP: 8.2
 * Requires Plugins: woocommerce
 */
declare(strict_types=1);
namespace Ellamara\Shipping;
defined('ABSPATH') || exit;

const SECTION = 'ellamara_packaging';
require_once __DIR__ . '/packaging-charges.php';

/** Known dimensions are external centimetres; weights remain unconfirmed. */
function packages(): array {
    $result = [];
    foreach (['small'=>['Caja pequeña',6652,33,11.3,10], 'large'=>['Caja grande',8352,30,26,11]] as $key=>$defaults) {
        [$name,$cost,$length,$width,$height] = $defaults;
        $prefix = 'ellamara_package_'.$key.'_';
        $result[$key] = [
            'name'=>$name,
            'cost'=>(float)get_option($prefix.'cost',$cost),
            'length_cm'=>(float)get_option($prefix.'length',$length),
            'width_cm'=>(float)get_option($prefix.'width',$width),
            'height_cm'=>(float)get_option($prefix.'height',$height),
            'empty_weight_kg'=>get_option($prefix.'weight',''),
            'capacity'=>get_option($prefix.'capacity',''),
        ];
    }
    return $result;
}

add_filter('woocommerce_get_sections_shipping', static function(array $sections): array {
    $sections[SECTION] = 'Empaques Ellamara';
    return $sections;
});

add_filter('woocommerce_get_settings_shipping', static function(array $settings, string $section): array {
    if ($section !== SECTION) { return $settings; }
    $settings = [
        ['title'=>'Preparación de envíos · Ellamara','type'=>'title','id'=>'ellamara_shipping_preparation','desc'=>'Registra los empaques antes de conectar Envia.com. Se cobra un empaque individual por ejemplar según su ISBN. Ocho títulos usan caja pequeña; los demás libros usan caja grande. No cotiza transporte ni modifica el PVP.'],
        ['title'=>'Ciudad de despacho','id'=>'ellamara_shipping_origin_city','type'=>'text','default'=>'','desc'=>'Ciudad o municipio de origen. La dirección completa se configurará en la cuenta logística; no se mostrará en el footer.'],
        ['title'=>'Referencia de capacidad','id'=>'ellamara_shipping_capacity_note','type'=>'textarea','default'=>'','desc'=>'Referencia general para la preparación. Debe confirmarse qué caja corresponde a cada combinación antes de automatizar el cargo.'],
        ['title'=>'Modelo de cobro previsto','type'=>'text','id'=>'ellamara_shipping_charge_policy','default'=>'Empaque separado por pedido','custom_attributes'=>['readonly'=>'readonly'],'desc'=>'El PVP de los productos se conserva. El transporte se calculará por separado con el cotizador.'],
        ['type'=>'sectionend','id'=>'ellamara_shipping_preparation'],
    ];
    foreach (packages() as $key=>$package) {
        $prefix = 'ellamara_package_'.$key.'_';
        $settings[] = ['title'=>$package['name'],'type'=>'title','id'=>$prefix.'section'];
        $settings[] = ['title'=>'Costo completo de empaque (COP)','id'=>$prefix.'cost','type'=>'number','default'=>$key==='small'?6652:8352,'custom_attributes'=>['min'=>'0','step'=>'1'],'desc'=>'Incluye caja y materiales de empaque según el desglose proporcionado. No es el precio del transporte.'];
        foreach (['length'=>['Largo (cm)',$package['length_cm']], 'width'=>['Ancho (cm)',$package['width_cm']], 'height'=>['Alto (cm)',$package['height_cm']]] as $field=>[$label,$default]) {
            $settings[] = ['title'=>$label,'id'=>$prefix.$field,'type'=>'number','default'=>$default,'custom_attributes'=>['min'=>'0.01','step'=>'0.01']];
        }
        $settings[] = ['title'=>'Peso del empaque vacío (kg)','id'=>$prefix.'weight','type'=>'number','default'=>'','placeholder'=>'Pendiente de medir','custom_attributes'=>['min'=>'0.001','step'=>'0.001'],'desc'=>'Caja más papel y relleno, sin los productos. No se ha estimado ni asignado un peso ficticio.'];
        $settings[] = ['title'=>'Capacidad de referencia (libros)','id'=>$prefix.'capacity','type'=>'number','default'=>'','placeholder'=>'Pendiente de comprobar','custom_attributes'=>['min'=>'1','step'=>'1'],'desc'=>'Dato de planificación: prueba cuántos libros con accesorios caben protegidos. El grosor y los demás artículos pueden variar; este dato por sí solo no activa una selección automática.'];
        $settings[] = ['type'=>'sectionend','id'=>$prefix.'section'];
    }
    return $settings;
}, 20, 2);

/** Reject negative/non-finite measurements while preserving genuinely unknown data. */
add_filter('woocommerce_admin_settings_sanitize_option', static function($value, array $option, $raw) {
    $id = (string)($option['id']??'');
    if (!str_starts_with($id,'ellamara_package_') || ($option['type']??'')!=='number') { return $value; }
    if (trim((string)$raw)==='') { return ''; }
    $number = wc_format_decimal($raw);
    $minimum = (float)($option['custom_attributes']['min']??0);
    if (!is_numeric($number) || !is_finite((float)$number) || (float)$number<$minimum) {
        \WC_Admin_Settings::add_error('El valor de «'.$option['title'].'» no es válido. Se conserva el valor anterior.');
        return get_option($id,$option['default']??'');
    }
    if (str_ends_with($id,'capacity') && floor((float)$number)!==(float)$number) {
        \WC_Admin_Settings::add_error('La capacidad debe ser un número entero.');
        return get_option($id,$option['default']??'');
    }
    return $number;
},20,3);

