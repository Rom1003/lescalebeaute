function addNewRow(type) {
    //Récupération de la dernière clé
    var key = getLastKey() + 1;

    var html = '' +
        '                    <tr data-key="'+key+'">\n' +
        '                        <td>'+key+'</td>\n' +
        '                        <td><input type="text" name="libelle['+key+']"></td>\n' +
        '                        <td><input type="text" name="prix['+key+']"></td>\n' +
        '                        <td><i class="fas fa-times-circle color-err fa-2x btn-delete cursor-p" title="Supprimer" data-etat="new"></i></td>\n' +
        '                        <input type="hidden" name="type['+key+']" value="' + type + '">\n' +
        '                    </tr>';

    $('table[data-type=' + type + '] tbody:last-child').append(html);
}

function getLastKey() {
    var key = 1;
    $('tr[data-key]').each(function () {
        if ($(this).data('key') > key){
            key = $(this).data('key');
        }
    });
    return key;
}

$(document).ready(function () {

    //Ajout d'une ligne
    $('.add-row').on('click', function () {
        addNewRow($(this).data('type'));
    });

    //Supression d'une ligne
    $('.table_admin_epilation').on('click', '.btn-delete', function () {
        var block = $(this).parents('tr');
        if ($(this).data('etat') === 'exist'){
            //On supprime en bdd
            var id = block.find('input[name="id['+block.data('key')+']"]').val();
            sendPost('epilations/remove', {id : id}, function (data) {
                showConf(data.message);
                //On retire la ligne
                block.remove();
            })
        } else {
            //On retire la ligne
            block.remove();
        }
    });

    //Envoi du formulaire
    $('#form_edit_epilation').submit(function (e) {
        e.preventDefault(); //Empeche d'envoyer le formulaire
        var url = $(this).attr('action');
        var formData = new FormData($(this)[0]);

        //On retire les lignes en erreur
        $('tr[data-key]').each(function () {
            $(this).removeClass('in-error');
        }).promise().done(function () {
            sendForm(url, formData, function () {
                location.reload();
            }, function (data) {
                if (data.anomalies) {
                    var texte = '<ul>';
                    $.each(data.anomalies, function (key, message) {
                        if ($.isNumeric(key)){
                            $('tr[data-key='+key+']').addClass('in-error');
                        }
                        texte = texte +"<li>"+message+"</li>";
                    });
                    texte = texte +"</ul>";
                    showError(texte);
                }
            })
        });
    });
});