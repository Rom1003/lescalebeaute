function showProduits(page) {
    gamme_id = $('#gamme_id').val();
    var post = {page : page, nbParPage : 15, gamme_id : gamme_id};
    sendPost('/administration/produit/ajax/paginate', post, function (data) {
        $('#pagination').html(data.html.pagination);
        $('#list_produits').html(data.html.tableau);
    });
}

$(document).ready(function () {
    showProduits(1);
    $('#search_btn').on('click', function () {
        showProduits($(this).data('page'));
    });
});