# Administrar WooCommerce en LocalWP y Hostinger

[Volver al README principal](../../README.md)

**Producción es la fuente de verdad del inventario, los pedidos y los clientes.** LocalWP sirve para ensayar y preparar cambios. Para que el catálogo coincida entre ambos entornos, traslada los productos o campos que cambiaron y usa el mismo SKU para cada edición.

| Entorno | Cómo entrar | Cuándo usarlo |
| --- | --- | --- |
| LocalWP | **Start site → WP Admin** en Ellamara | Preparar fichas, probar categorías e importaciones |
| Hostinger | [WordPress de producción](https://ellamarastore.com/wp-admin/) | Publicar productos y gestionar el catálogo y stock reales |

## Agregar, editar o retirar productos

1. Ve a **Productos → Añadir nuevo**. Para editar uno existente, ve a **Productos → Todos los productos → Editar**.
2. Completa título y descripción. En **Datos del producto → General**, guarda el precio final en COP con el empaque incluido. En **Inventario**, asigna un SKU único y establece las existencias. Usa el mismo SKU en ambos entornos y uno distinto por edición.
3. Para un libro que aún no llega, deja stock vendible en cero, desactiva reservas y elige **Disponibilidad Ellamara → Próximamente**. Completa autor, ISBN y encuadernación en los campos de Ellamara cuando correspondan.
4. Selecciona la categoría, asigna la portada y guarda como **Borrador** mientras lo revisas. Pulsa **Publicar** para mostrar un producto nuevo o **Actualizar** para guardar uno existente.
5. Para retirar una ficha, cambia su estado a **Borrador** si quieres conservarla para más adelante, o usa **Mover a la papelera** para quitarla. Si también existe en el otro entorno, repite allí el cambio por SKU. Omitirla de un CSV deja su copia existente intacta.

## Agregar, sustituir o quitar fotos

1. Abre el producto. En **Imagen del producto**, pulsa **Establecer imagen del producto** y sube la portada o elige una existente en la biblioteca de ese entorno.
2. Usa **Galería del producto** para fotos adicionales. Para retirar una foto, quítala de la imagen principal o de la galería y guarda.
3. Si vas a eliminar el archivo de **Medios**, comprueba primero que no lo use otra ficha o página. Para subir una portada en ambos sitios, usa el mismo archivo original y asígnalo desde cada panel.

## Agregar, cambiar o eliminar categorías

1. En **Productos → Categorías**, introduce nombre, slug y categoría superior si corresponde. Guarda.
2. Edita el producto, marca la categoría y guarda. Mantén el mismo slug y jerarquía en ambos entornos.
3. Para retirar una categoría, reasigna primero sus productos y revisa sus enlaces en el menú. Después edítala o elimínala desde **Productos → Categorías**. Un CSV de productos puede trasladar asignaciones de categorías, mientras que eliminar o renombrar una categoría requiere revisar ambos paneles.

## Si editaste en Hostinger y quieres actualizar LocalWP

Para pocos productos, repite en LocalWP los cambios mirando el SKU en producción. Para varios, exporta desde **Productos → Todos los productos → Exportar** en Hostinger y usa el procedimiento CSV de abajo con LocalWP como destino. Retira manualmente en LocalWP los productos que pasaron a papelera o dejaron de existir en producción.

## Si preparaste productos en LocalWP y quieres publicarlos

Para pocos productos, crea o edita las fichas correspondientes en Hostinger con los mismos SKU. Sube las fotos a la biblioteca de producción. Para varios, exporta las filas necesarias desde LocalWP y usa el procedimiento CSV con Hostinger como destino. Antes de modificar existencias de productos existentes, consulta el stock actual en Hostinger. Si se vendieron ejemplares desde tu última copia local, conserva ese movimiento al calcular la cantidad final.

## Trasladar varios productos por CSV

1. En el origen, entra a **Productos → Todos los productos → Exportar**. Incluye los metadatos personalizados si vas a trasladar campos de Ellamara. Guarda el archivo en UTF-8 y conserva los SKU como texto.
2. Deja solo los productos y columnas que deseas trasladar. **Retira la columna ID** para que el otro entorno haga la correspondencia por SKU. Separa los productos nuevos de las actualizaciones. Para actualizar solo stock, por ejemplo, usa `SKU` y `Stock`. La cantidad es el valor final, no un incremento.
3. En el destino, abre **Productos → Importar**. Marca **Actualizar productos existentes** para el archivo de actualizaciones. Para el archivo de altas, deja la casilla desmarcada y comprueba que los SKU sean nuevos.
4. Revisa el mapeo de columnas. Selecciona **No importar** para los campos que quieras conservar en destino. Si una columna de fotos trae enlaces locales como `http://ellamara-hidden-chapter/`, omítela y sube esas imágenes en el panel de destino. Para importar por URL, usa enlaces directos que el destino pueda descargar.
5. Prueba con una o dos filas y revisa el informe. Comprueba SKU, precio, stock, portada, categorías y disponibilidad. Para un lote en producción, guarda un respaldo reciente y vuelve a comprobar las existencias antes de importar el resto. [Importador y exportador CSV de WooCommerce](https://woocommerce.com/document/product-csv-importer-exporter/).

Una copia completa de producción puede servir para refrescar **una nueva instalación local** si también necesitas páginas, medios y ajustes. Conserva tu LocalWP anterior antes de importar y usa una copia sin datos de clientes cuando sea posible. Para sincronizar de local a producción, usa cambios puntuales en el panel o CSV por SKU, ya que restaurar una base completa puede revertir ventas y cambios recientes. [Importar y exportar con LocalWP](https://localwp.com/help-docs/getting-started/how-to-import-a-wordpress-site-into-local/).

## Referencia del ajuste inicial de precios con empaque

`tools/catalog-pricing.php` corresponde a la actualización inicial que incorporó la caja al precio. Los precios nuevos se guardan directamente como importes finales desde WooCommerce. Para auditar esa actualización, con el script fuera de `public_html` y desde la raíz de WordPress, ejecuta `wp eval-file ~/catalog-pricing.php --use-include`. Una lista vacía indica que el script no propone cambios. El argumento `apply` guarda los cambios propuestos. [WP-CLI eval-file](https://developer.wordpress.org/cli/commands/eval-file/).
