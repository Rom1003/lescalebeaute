
$(document).ready(function () {
    /*----------------------------*
     * Formulaire edition service *
    /*----------------------------*/

    floatMask();

    //Ajout d'un input de tarif
    $('#add_tarif_input').on('click', function () {
        addInputTarifBlock('tarif[]', '#liste_tarifs_duree', 6);
    });
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


    $('#form_edit_service').submit(function (e) {
        e.preventDefault(); //Empeche d'envoyer le formulaire
        var url = $(this).attr('action');
        var formData = new FormData($(this)[0]);
        console.log(formData);
        sendForm(url, formData, function (data) {
            window.location.href = data.url;
        });
    });

    $('.image-delete-btn').on('click', function () {
        var id = $(this).data('id');
        var url = $('#image_liste').data('url');
        var block = $(this).parents('.image-delete');
        showInfo('Valider la suppression de cette image ?<br><br><button class="button" id="valid_modal">Valider</button>', 'Confirmation de suppression');
        $('#modal').on('click', '#valid_modal', function () {
            $('#modal').find('.close-button').click();
            sendPost(url, {id : id}, function () {
                block.remove();
                showConf(data.message);
            });
        })
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


