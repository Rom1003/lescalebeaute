function showProduits(page) {
    var post = {page : page, nbParPage : 12};
    sendPost('/produits/ajax/paginate', post, function (data) {
        $('#pagination').html(data.html.pagination);
        $('#list_produits').html(data.html.tableau);
        $(window).resize();
    });
}

$(document).ready(function () {
    showProduits(1);
    $('#pagination').on('click', '.pagination-page', function () {
        showProduits($(this).data('page'));
    });
});