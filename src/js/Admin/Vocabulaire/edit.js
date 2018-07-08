
$(document).ready(function () {
    /*--------------------------------*
     * Formulaire edition vocabulaire *
    /*--------------------------------*/

    $('#form_edit_vocabulaire').submit(function (e) {
        e.preventDefault(); //Empeche d'envoyer le formulaire
        var url = $(this).attr('action');
        var formData = new FormData($(this)[0]);
        console.log(formData);
        sendForm(url, formData, function (data) {
            window.location.href = data.url;
        });
    });

});


