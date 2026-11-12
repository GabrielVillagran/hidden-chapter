# Ellamara · código del sitio

Copia inicial tomada del código de trabajo local. Validar contra una exportación de los archivos de Hostinger antes de usarla como base exacta de producción.

- `wp-content/themes/ellamara`: tema propio.
- `wp-content/plugins/ellamara-*`: catálogo, carrito, contacto, envíos y sugerencias.

El catálogo, pedidos, clientes, medios, ajustes y personalizaciones guardadas en el Editor del sitio se administran por separado en WordPress. Mantener respaldos y credenciales fuera del repositorio.

## Publicar un cambio

### Actualización puntual del catálogo

El cambio de código elimina el cargo separado por caja. Los precios finales se guardan en WooCommerce para que el mismo valor aparezca en listados, fichas y checkout. GitHub Actions despliega el código. Para aplicar los datos de esta actualización en producción, copia `tools/catalog-pricing.php` a una carpeta fuera de `public_html` en Hostinger, entra a la raíz de WordPress y ejecuta primero:

```sh
wp eval-file ~/catalog-pricing.php > ~/ellamara-catalog-pricing-before.json
```

Revisa el archivo antes de aplicar. Contiene los precios anteriores y nuevos, los kits que pasan a borrador y cualquier ajuste de disponibilidad. Tras respaldar la base y desplegar el código que retira el cargo anterior, ejecuta `wp eval-file ~/catalog-pricing.php apply`. Al volver a ejecutar la vista previa, los libros ya actualizados no deben volver a sumar la caja. Los precios posteriores se editan como importes finales desde WooCommerce.

Al integrar en `main` cambios en el tema o en uno de los cinco plugins propios, GitHub Actions despliega los archivos en Hostinger. Prueba primero en LocalWP y revisa el pull request antes de integrarlo. Un push directo a `main` con cambios en esos archivos también inicia el despliegue.

GitHub Actions ofrece **Revisar o desplegar Ellamara** desde `main`. Usa `preview` para comparar el código con Hostinger o `deploy` para iniciar un despliegue manual. Cada despliegue respalda los seis componentes en `~/ellamara-deploy-backups`, sube los archivos y verifica su contenido. Los archivos exclusivos del servidor se conservan. La base de datos, los medios y la configuración de WordPress quedan fuera de la sincronización.

Para instalar un componente por ZIP, sigue estos pasos:

1. Crear una rama, modificar el componente y probarlo en LocalWP.
2. Ejecutar `./package.ps1 -Component ellamara-shipping` para generar el ZIP del componente, o `./package.ps1` para generar todos los ZIP en dist.
3. Revisar los archivos y probar el paquete en staging cuando afecte el checkout.
4. Respaldar el componente en Hostinger, subir su ZIP desde WordPress o reemplazar sus archivos por SFTP y verificar el resultado. Si hay un ajuste de WordPress asociado, aplicarlo por separado.
5. Si falla el código, restaurar los archivos previos del componente. No importar la base local sobre producción.
