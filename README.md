# Product List Views

Modul de PrestaShop per canviar la vista dels productes a pagines de categoria i altres llistats.

## Estat actual

Primera implementacio funcional:

- Selector de vista al frontal.
- Vistes: graella, llista, taula, compacta, showcase i masonry.
- Cards propies per cada vista, generades a partir de la miniatura original del tema.
- Sis pestanyes de configuracio per decidir que es veu a cada card: graella, llista, taula, compacta, showcase i masonry.
- Plantilla al hook `displayProductListReviews` per llegir dades reals del producte dins la miniatura.
- Selector de quantitat amb botons `-` i `+` quan existeix accio d'afegir al carret al tema.
- Boto fallback d'afegir al carret quan el tema no mostra un formulari visible pero PrestaShop dona URL de carret i id de producte.
- Switches independents per vista rapida i colors/combinacions.
- Configuracio de productes per pagina amb `resultsPerPage`.
- Selector entre paginacio normal i scroll infinit, compartint el mateix valor de productes per pagina.
- Graella amb targetes consistents, imatge enquadrada, hover i alçades més estables.
- Llista amb layout comparatiu: imatge, informació, preu i accions més separades.
- Taula compacta amb capçalera, files alineades i versió mòbil amb etiquetes.
- Vista compacta per compra rapida amb files denses, preu i accions visibles.
- Vista showcase amb primer producte destacat i composicio mes editorial.
- Vista masonry real (estil Pinterest): cada card es col.loca a la columna mes curta segons la seva alçada real, mantenint l'ordre; es recalcula en redimensionar, en carregar imatges i amb scroll infinit. Fallback CSS `columns` si el JS no s'executa.
- Opcions de mes fotos: segona imatge al passar el cursor, o carrusel de totes les imatges (fletxes, punts, swipe, teclat).
- Carrusel accessible (teclat i lector de pantalla) i suport de `prefers-reduced-motion`.
- Configuracio al backoffice.
- Preferencia del client guardada al navegador.
- La vista seleccionada es conserva en paginacio, ordenacio i filtres amb `pl_view`.
- Fallback JavaScript si el tema no mostra el hook `displayProductListTop`.
- Reaplicacio de la vista despres de filtres i paginacio Ajax.
- Capcalera visual per a la vista de taula.
- Configuracio de columnes de graella i amplades d'imatge.
- Assets carregats nomes a llistats de productes.

## Instal.lacio prevista

1. Copiar la carpeta `productlist` dins `modules/`.
2. Instal.lar el modul des de **Moduls > Module Manager**.
3. Configurar les vistes actives i la vista per defecte.
4. Provar una categoria amb productes.

## Opcions de configuracio

- Vista per defecte: `grid`, `list`, `table`, `compact`, `showcase` o `masonry`.
- Activar vista de graella.
- Activar vista de llista.
- Activar vista de taula.
- Activar vista compacta.
- Activar vista showcase.
- Activar vista masonry.
- Recordar la vista del client amb `localStorage`.
- Injectar automaticament el selector si el tema no renderitza el hook.
- Columnes de graella en escriptori.
- Columnes de graella en tablet.
- Amplada d'imatge en vista llista.
- Amplada d'imatge en vista taula.
- Productes per pagina (`resultsPerPage`) i mode de paginacio (normal o scroll infinit).
- Comportament d'imatge de producte: imatge unica, segona imatge al passar el cursor, o carrusel de totes les imatges.

## Parametre d'URL per proves

Es pot forcar una vista concreta afegint `pl_view` a l'URL:

- `?pl_view=grid`
- `?pl_view=list`
- `?pl_view=table`
- `?pl_view=compact`
- `?pl_view=showcase`
- `?pl_view=masonry`

## Fitxers principals

- `productlist.php`: classe principal del modul.
- `views/templates/hook/view-switcher.tpl`: selector Smarty quan el tema executa el hook.
- `views/js/productlist.js`: canvi de vista, preferencia local i fallback d'injeccio.
- `views/css/productlist.css`: estils de graella, llista, taula, compacta, showcase i masonry.
- `docs/vistes-categoria.md`: definicio funcional de les vistes possibles.
- `docs/checklist-proves.md`: proves a fer quan l'instal.lem.
- `tools/package.ps1`: genera un paquet `.zip` instal.lable.
- `upgrade/install-0.2.0.php`: upgrade de configuracio per passar de versions inicials a 0.2.0.
- `upgrade/install-0.3.0.php`: upgrade de versio visual sense canvis de configuracio.
- `upgrade/install-0.4.0.php`: upgrade per la generacio de cards propies per vista.
- `upgrade/install-0.4.1.php`: correccio de graella per respectar exactament el nombre de columnes configurat.
- `upgrade/install-0.5.0.php`: configuracio de visibilitat de camps per cada card.
- `upgrade/install-0.5.1.php`: millora visual de les pestanyes de configuracio de cards.
- `upgrade/install-0.5.2.php`: deteccio ampliada de descripcio curta en miniatures de tema.
- `upgrade/install-0.6.0.php`: hook de dades de producte i nous camps configurables.
- `upgrade/install-0.6.1.php`: millora visual dels switches ON/OFF al backoffice.
- `upgrade/install-0.6.2.php`: switch ON/OFF mes llegible i sense dependencia de selectors CSS moderns.
- `upgrade/install-0.7.0.php`: selector de quantitat per a les accions d'afegir al carret.
- `upgrade/install-0.7.1.php`: boto fallback d'afegir al carret via Ajax.
- `upgrade/install-0.8.0.php`: configuracio independent de vista rapida i colors/combinacions.
- `upgrade/install-0.9.0.php`: productes per pagina i scroll infinit.
- `upgrade/install-0.9.1.php`: selector de mode paginacio/scroll infinit i comptador de productes carregats.
- `upgrade/install-0.10.0.php`: activa les vistes compacta i showcase en botigues ja instal.lades.
- `upgrade/install-0.11.0.php`: activa la vista masonry en botigues ja instal.lades.
- `upgrade/install-0.12.0.php`: inicialitza el comportament d'imatge de producte (`PRODUCTLIST_IMAGE_MODE`).
- `upgrade/install-0.12.1.php`: correccio de persistencia de vista en paginacio i filtres.
- `upgrade/install-0.12.2.php`: correccio de duplicats en scroll infinit.
- `views/css/productlist-admin.css` i `views/js/productlist-admin.js`: assets del backoffice (sense JS/CSS inline, per complir el validador).
- `.github/workflows/ci.yml`: comprovacions de sintaxi PHP/JS i integritat del `.zip`.
- `CHANGELOG.md`: historial de canvis.

## Generar paquet

Des de la carpeta del modul:

```powershell
.\tools\package.ps1
```

El paquet es crea a `build\productlist.zip`.

## Notes de compatibilitat

El modul esta pensat per PrestaShop 1.7 i 8. La primera versio no fa overrides ni substitueix plantilles del tema, de manera que es mes segura per instal.lacions existents. La vista de taula es construeix amb CSS sobre el markup del tema, per tant pot necessitar ajustos visuals segons el tema actiu.

## Seguent fase

- Afegir una vista de comparador amb camps tecnics editables.
- Afegir una vista minimalista sense imatge per catalegs B2B.
- Afegir camps tecnics a la vista de taula quan coneguem el tema i les dades necessaries.
- Preparar paquet `.zip` d'instal.lacio quan es vulgui provar a PrestaShop.
