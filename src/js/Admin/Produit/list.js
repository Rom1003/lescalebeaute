function showProduits(page) {
    var gamme = $('#search_gamme').val();
    var libelle = $('#search_libelle').val();
    var post = {page : page, nbParPage : 15, search_libelle : libelle, search_gamme : gamme};
    sendPost('/administration/produit/ajax/paginate', post, function (data) {
        $('#pagination').html(data.html.pagination);
        $('#list_produits').html(data.html.tableau);
    });
}

$(document).ready(function () {
    showProduits(1);
    $('#search_btn').on('click', function () {
        showProduits(1);
    });
    $('#pagination').on('click', '.pagination-page', function () {
        showProduits($(this).data('page'));
    });

    //Modifier l'état du produit
    $('#list_produits').on('click', '.etat_produit', function () {
        var actif = $(this).data('etat');
        var id = $(this).parents('tr').data('id');
        var etat;

        if (actif === 0){
            etat = 'désactivé';
        } else {
            etat = 'activé';
        }

        showInfo('Valider la modification de l\'état du produit à : "'+etat+'" ?<br><br><button class="button" id="valid_modal">Valider</button>', 'Confirmation de modification');
        $('#modal').on('click', '#valid_modal', function () {
            $('#modal').find('.close-button').click();
            sendPost('/administration/produit/etat/'+id+'/'+actif, null, function (data) {
                showConf(data.message);
                showProduits($(this).data('page'));
            });
        });

    })

});