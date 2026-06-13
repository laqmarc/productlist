# Modul Product List: vistes de categoria

Aquest modul afegeix un selector de vista a les pagines de categoria de PrestaShop per permetre canviar com es mostren els productes.

## Vistes possibles

1. **Graella classica**
   Productes en 2, 3 o 4 columnes. Es la vista habitual i funciona be per catalegs visuals.

2. **Llista compacta**
   Imatge petita, nom, preu, disponibilitat i boto de compra en una fila. Es practica per comparar molts productes rapidament.

3. **Llista detallada**
   Similar a la llista compacta, pero amb descripcio curta, referencia, marca, atributs o etiquetes destacades.

4. **Taula comparativa**
   Columnes com imatge, nom, referencia, preu, estoc, quantitat i accio. Es util per recanvis, productes tecnics i catalegs B2B.

5. **Vista cataleg / mosaic**
   Targetes mes grans, centrades en imatge i marca. Bona per productes on la imatge pesa molt en la decisio de compra.

6. **Vista rapida de compra**
   Files amb selector de quantitat i boto d'afegir directe. Ideal per clients recurrents o comandes amb molts articles.

7. **Vista per variants**
   Productes agrupats per color, mida, format o altres combinacions.

8. **Vista minimalista**
   Nom, preu i accio principal, amb poca imatge o sense. Adequada per catalegs professionals i productes estandarditzats.

9. **Targetes amb informacio tecnica**
   Cards amb SKU, dimensions, pes, material, compatibilitat o termini d'entrega.

10. **Vista alternable pel client**
    Selector entre diverses vistes i preferencia guardada al navegador.

## Vistes implementades

La implementacio actual inclou:

- **Graella**
- **Llista compacta**
- **Taula**
- **Compacta**
- **Showcase**
- **Masonry**

La versio actual millora cada vista:

- Les vistes poden tenir una card diferent. El modul llegeix la miniatura original del tema i genera una card propia per graella, llista, taula, compacta, showcase i masonry.
- Al backoffice hi ha una pestanya per cada vista per activar o desactivar imatge, titol, descripcio, preu, accions, vista rapida, colors, quantitat i dades extra.
- La plantilla del modul dins `displayProductListReviews` dona acces a dades publiques del producte com referencia, marca, disponibilitat i flags.
- Les cards poden mostrar selector de quantitat amb botons `-` i `+` quan la miniatura original ofereix accio d'afegir al carret.
- El modul pot forcar el nombre de productes per pagina amb el parametre `resultsPerPage`.
- Es pot triar entre paginacio normal o scroll infinit. Els dos modes fan servir el mateix valor de productes per pagina.
- El scroll infinit carrega la pagina seguent del llistat, afegeix els productes al final i mostra un comptador de productes carregats.
- **Graella**: targetes amb alçada estable, imatges enquadrades, titol limitat a dues linies, preu separat i hover discret.
- **Llista**: fila comparativa amb imatge, informacio principal, preu i accions separades.
- **Taula**: capcalera visual, files compactes, preu i accio en columnes, i adaptacio mobil amb etiquetes.
- **Compacta**: fila densa amb imatge petita, dades clau, preu i accions per compra rapida.
- **Showcase**: vista editorial amb primer producte destacat, imatge gran i composicio diferent de la graella.
- **Masonry**: columnes irregulars amb cards d'alçada variable, adequada per catalegs visuals.

El mòdul permet configurar des del backoffice:

- Vista per defecte.
- Activar o desactivar la vista de graella.
- Activar o desactivar la vista de llista.
- Activar o desactivar la vista de taula.
- Activar o desactivar la vista compacta.
- Activar o desactivar la vista showcase.
- Activar o desactivar la vista masonry.
- Recordar la vista escollida pel client al navegador.
- Injectar automaticament el selector si el tema no mostra el hook del llistat.

## Enfoc tecnic

La primera versio evita overrides i canvis profunds al tema. El modul:

- Insereix un selector mitjancant el hook `displayProductListTop`.
- Carrega CSS i JavaScript amb `displayHeader`.
- Aplica classes al contenidor de productes.
- Desa la preferencia del client a `localStorage`.
- Reaplica la vista quan PrestaShop refresca el llistat per Ajax.
- Genera una capcalera visual per a la vista de taula.

Aquest enfoc es mes segur per instal.lacions existents. Si mes endavant cal una taula amb dades especifiques, quantitats multiples o camps tecnics, es pot evolucionar cap a una plantilla de producte mes personalitzada.
