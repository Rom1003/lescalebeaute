function showServices(page) {
    categorie_id = $('#categorie_id').val();
    var post = {page : page, nbParPage : 15, categorie_id : categorie_id};
    sendPost('/administration/services/ajax/paginate', post, function (data) {
        $('#pagination').html(data.html.pagination);
        $('#list_services').html(data.html.tableau);
    });
}

/**
 * Ajout d'un block input file
 * @param name - Name de l'input
 * @param block - Le block qui va acceuillir l'input
 * @param limit - Le nombre maximum d'input dans le block défini
 */
function addInputTarifBlock(name, block, limit) {
    var nb = $('.tarif_duree_block').length;

    var html =
        '<div class="tarif_duree_block grid-x grid-padding-x">\n' +
        '     <div class="medium-3 cell">\n' +
        '         <div class="input-group">\n' +
        '             <span class="input-group-label"><i class="far fa-clock"></i></span>\n' +
        '             <input class="input-group-field" type="text" name="duree[]" id="duree_'+nb+'" placeholder="ex: 50 minutes">\n' +
        '         </div>' +
        '     </div>\n' +
        '     <div class="medium-2 cell">\n' +
        '         <div class="input-group">\n' +
        '             <span class="input-group-label">€</span>\n' +
        '             <input class="input-group-field mask-float" type="text" name="tarif[]" id="tarif_'+nb+'" placeholder="ex: 100">\n' +
        '         </div>' +
        '     </div>';

    if (nb > 0){
        html +=
        '     <div class="medium-2 cell">\n' +
        '         <button type="button" class="button tiny alert inputTarif_remove"><i class="fas fa-minus"></i>\n' +
        '             Retirer\n' +
        '         </button>\n' +
        '     </div>\n';
    }

    html += '</div>';

    if (nb < limit){
        $(block).append(html);
        floatMask();
    } else {
        showWarn("Nombre maximum atteint");
    }
}



$(document).ready(function () {
    showServices(1);
    $('#pagination').on('click', '.pagination-page', function () {
        showServices($(this).data('page'));
    });
});

