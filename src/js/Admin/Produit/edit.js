
$(document).ready(function () {
    /*----------------------------*
     * Formulaire edition produit *
    /*----------------------------*/

    $('#form_edit_produit').submit(function (e) {
        e.preventDefault(); //Empeche d'envoyer le formulaire
        var url = $(this).attr('action');
        var formData = new FormData($(this)[0]);
        sendForm(url, formData, function (data) {
            window.location.href = data.url;
        });
    });

});
