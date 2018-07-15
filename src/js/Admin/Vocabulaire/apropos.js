
$(document).ready(function () {
    /*----------------------------*
     * Formulaire edition apropos *
    /*----------------------------*/

    //Ajout d'un input d'upload
    $('#add_image_input').on('click', function () {
        addInputFileBlock('image[]', '#block_uploader', 6);
    }).click();
    $('#block_uploader').on('click', '.inputFile_remove', 'click', function () {
        removeInputFileBlock($(this), 'image[]');
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
        });
    });


});


