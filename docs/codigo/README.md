# Modificar código y publicar

[Volver al README principal](../../README.md)

## 1. Crear una rama desde main

En PowerShell, dentro de `ellamara-code`, actualiza la base antes de empezar. Elige un nombre que describa el cambio.

```powershell
git switch main
git pull --ff-only origin main
git switch -c gabriel/mejora-del-catalogo
```

Edita el tema en `wp-content/themes/ellamara` o el plugin correspondiente en `wp-content/plugins`.

## 2. Probar en LocalWP

1. Genera el ZIP del componente. Para un cambio en el tema, ejecuta `./package.ps1 -Component ellamara`. Para envíos, ejecuta `./package.ps1 -Component ellamara-shipping`. Los ZIP quedan en `dist`.
2. En LocalWP, abre el sitio con **Start site → WP Admin**. Sube el ZIP desde **Apariencia → Temas → Añadir nuevo → Subir tema** o **Plugins → Añadir nuevo → Subir plugin**. Confirma el reemplazo y verifica que el componente esté activo.
3. Abre el sitio local y comprueba el cambio. Para cambios de catálogo o checkout, prueba también como visitante y en móvil. Genera un ZIP nuevo cada vez que modifiques los archivos.

## 3. Subir la rama y abrir el PR

```powershell
git status --short
git diff
git add -- ruta/del/archivo-modificado
git commit -m "Describe el cambio"
git push -u origin gabriel/mejora-del-catalogo
```

Reemplaza la ruta y el nombre de rama por los tuyos. Si hay varios archivos, añade cada uno que pertenezca al cambio. En GitHub, abre **Compare & pull request**, selecciona `main` como base y tu rama como comparación. Describe qué cambió, qué probaste y si hay algún ajuste de WooCommerce que aplicar aparte. [Crear un PR](https://docs.github.com/en/pull-requests/collaborating-with-pull-requests/proposing-changes-to-your-work-with-pull-requests/creating-a-pull-request).

## 4. Hacer merge y verificar Hostinger

1. Revisa **Files changed** y confirma que el PR contiene los archivos esperados.
2. Haz **Squash and merge** a `main` cuando esté listo. [Merge de un PR](https://docs.github.com/en/pull-requests/how-tos/merge-and-close-pull-requests/merging-a-pull-request).
3. Abre [GitHub Actions](https://github.com/GabrielVillagran/hidden-chapter/actions). Cuando cambian archivos del tema o de los cinco plugins propios en `main`, el flujo respalda los componentes en Hostinger, los copia y verifica su contenido. Un push directo a `main` con cambios en esas rutas también despliega.
4. Espera el resultado verde y revisa [ellamarastore.com](https://ellamarastore.com/) en una ventana privada. Si solo cambiaste documentación, consulta el README en GitHub.

Desde **Revisar o desplegar Ellamara → Run workflow** puedes elegir `preview` para comparar archivos o `deploy` para publicar manualmente. Los respaldos de código quedan en `~/ellamara-deploy-backups`. Para revertir un cambio de código, revierte el commit mediante otro PR y verifica el despliegue. Los ajustes de productos y otros datos se gestionan con la [guía de WooCommerce](../woocommerce/README.md).
