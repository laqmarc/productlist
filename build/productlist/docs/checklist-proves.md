# Checklist de proves

Quan instal.lem el modul en una botiga PrestaShop:

## Backoffice

- El modul apareix com **Product List Views**.
- La instal.lacio no dona errors.
- La pantalla de configuracio permet desar:
  - Vista per defecte.
  - Graella activa/inactiva.
  - Llista activa/inactiva.
  - Taula activa/inactiva.
  - Compacta activa/inactiva.
  - Showcase activa/inactiva.
  - Masonry activa/inactiva.
  - Recordar vista del client.
  - Fallback automatic del selector.
- Si es desactiven totes les vistes, mostra error i no desa una configuracio invalida.
- La pantalla de configuracio mostra sis pestanyes de card: graella, llista, taula, compacta, showcase i masonry.
- Les pestanyes canvien sense recarregar pagina i el panell actiu queda clar.
- Cada pestanya permet activar/desactivar imatge, titol, descripcio, preu i accions.
- Cada pestanya permet activar/desactivar referencia, marca, disponibilitat i flags quan el tema dona aquestes dades.
- Cada pestanya permet activar/desactivar quantitat.
- Cada pestanya permet activar/desactivar vista rapida i colors/combinacions independentment de comprar.
- Products per page a 0 conserva el valor del tema.
- Products per page amb valor positiu afegeix `resultsPerPage` a l'URL.
- El mode paginacio mostra la paginacio normal del tema.
- El mode scroll infinit amaga la paginacio normal i carrega la pagina seguent amb el mateix `resultsPerPage`.

## Frontoffice

- A una categoria amb productes es mostra el selector.
- La vista de graella conserva el comportament normal del tema.
- La vista de graella mostra la card generada pel modul.
- La vista de graella mostra exactament les columnes configurades quan hi ha prou amplada.
- La vista de llista mostra una card horitzontal diferent de la graella.
- La vista de taula mostra una card compacta diferent de graella i llista.
- La vista de taula mostra capcalera i files alineades.
- La vista compacta mostra files denses amb imatge petita, dades clau, preu i accions.
- La vista showcase mostra el primer producte destacat i no es veu com una graella normal.
- La vista masonry mostra columnes irregulars amb cards d'alçada variable.
- En treure camps d'una pestanya, aquests camps desapareixen nomes d'aquella vista.
- Si la descripcio esta activa pero no apareix, comprovar que el tema imprimeix la descripcio curta dins el HTML del llistat.
- Si el tema executa `displayProductListReviews`, la descripcio curta ha de venir del JSON ocult del modul encara que la miniatura original no la mostri.
- Si la miniatura original te formulari d'afegir al carret, els botons `-` i `+` actualitzen la quantitat enviada.
- Si la miniatura no mostra boto de compra pero hi ha `id_product` i URL de carret, apareix el boto fallback d'afegir al carret.
- Amb scroll infinit actiu, en arribar al final es carreguen productes de la pagina seguent.
- Les cards generades tambe s'apliquen als productes carregats per scroll infinit.
- El comptador de scroll infinit incrementa amb els productes carregats.
- En mobil, llista, taula, compacta, showcase i masonry tornen a una disposicio llegible.
- En canviar de pagina o aplicar filtres, la vista seleccionada es mante.
- Si `Recordar vista` esta actiu, la vista es conserva en recarregar la pagina.
- Si `Recordar vista` esta inactiu, es torna a la vista per defecte.
- `?pl_view=grid`, `?pl_view=list`, `?pl_view=table`, `?pl_view=compact`, `?pl_view=showcase` i `?pl_view=masonry` forcen la vista esperada.

## Compatibilitat de tema

- Si el tema executa `displayProductListTop`, el selector surt per plantilla Smarty.
- Si el tema no executa el hook i el fallback esta actiu, el selector s'injecta abans del llistat.
- Si el fallback esta inactiu i el tema no executa el hook, no s'injecta selector.

## Ajustos visuals probables

- Revisar amplada d'imatges a la vista llista.
- Revisar columnes de la vista taula segons el markup del tema actiu.
- Revisar botons d'accio si el tema els amaga fins a hover.
