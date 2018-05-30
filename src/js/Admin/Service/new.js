
$(document).ready(function () {
    /*----------------------------*
     * Formulaire nouveau service *
    /*----------------------------*/

    //Ajout d'un input de tarif
    $('#add_tarif_input').on('click', function () {
        addInputTarifBlock('tarif[]', '#liste_tarifs_duree', 6);
    }).click();
    $('#liste_tarifs_duree').on('click', '.inputTarif_remove', 'click', function () {
        $(this).parents('.tarif_duree_block').remove();
    });

    //Ajout d'un input d'upload
    $('#add_image_input').on('click', function () {
        addInputFileBlock('image[]', '#block_uploader', 6);
    }).click();
    $('#block_uploader').on('click', '.inputFile_remove', 'click', function () {
        removeInputFileBlock($(this), 'image[]');
    });


    $('#form_new_service').submit(function (e) {
        e.preventDefault(); //Empeche d'envoyer le formulaire
        var url = $(this).attr('action');
        var formData = new FormData($(this)[0]);
        console.log(formData);
        sendForm(url, formData, function (data) {
            window.location.href = data.url;
        });
    });

    $('#tarif_type').on('change', function () {
        if ($(this).val() === '1'){
            $('.tarif_duree').slideDown();
            $('.tarif_fixe').slideUp();
        } else {
            $('.tarif_duree').slideUp();
            $('.tarif_fixe').slideDown();
        }
    }).change();


});

