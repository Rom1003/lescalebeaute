

$(document).ready(function () {
    $(document).foundation();

    floatMask();

    //Resize des blocs
    $("div[data-match]").each(function () {
        var elem = '.' + $(this).data("match");
        $("div[data-match]").matchHeight();
    });


    /* Gestion du menu */
    /* On masque tout les sous-menu */
    //Menu de base
    $('.menu-dropdown').on('mouseenter', function () {
        var submenu = $(this).find('.sub-menu');

        if (submenu.is(':hidden')) {
            $('.menu-dropdown').find(".sub-menu:visible").hide();
            submenu.slideDown('hide');
        }
    }).on('mouseleave', function () {
         $('.sub-menu').slideUp();
    });
    //Sous-menu
    $('.sub-dropdown').on('mouseenter', function () {
        var submenu = $(this).find('.sub-menu-2');

        if (submenu.is(':hidden')) {
            $('.menu-dropdown').find(".sub-menu-2:visible").hide();
            submenu.show();
        }
    });

    /****************/

    //Egaliser la hauteur des sous-menu
/*    $(function() {
        $('.sub-menu-2, .sub-menu').matchHeight();
    });
    $.fn.matchHeight._afterUpdate = function(event, groups) {
        $('.sub-menu').each(function () {
            $(this).hide()
        })
    };*/

    //Mettre à la même hauteurs chaques blocs du menu
    max_height = -1;
    $('.sub-menu,.sub-menu-2').each(function () {
        if ($(this).height() > max_height)max_height = $(this).height();
    }).promise().done(function () {
        $('.sub-menu,.sub-menu-2').each(function () {
            $(this).height(max_height);
        });
    });


    $('.mask-phone').mask('00 00 00 00 00');

    $('.timepicker').timepicker({
        timeFormat: 'H:i'
    });

    pageLoader(1000);
});

function pageLoader(sec) {
    sec = sec || 1000;
    elem = $('.page-loader');

    //Faire disparaitre le loader avec un effet de fadeOut après x secondes
    setTimeout(function () {
        elem.fadeOut("slow");
        //Supprimer la div
        elem.remove();
    }, sec);
}

function inputBorder(id, color) {
    if ($.isArray(id)){
        $.each(id, function (key, value) {
            if (!$('#' + value).is('input')) {
                if ($('#' + value).is('select')){
                    $('#' + value).css('border', 'solid 3px '+color).addClass('incomplet');
                } else {
                    $('#' + value).closest('div').css('box-shadow', '0px 0px 4pt 1pt color');
                }
            } else {
                //si c'est un checkbox ou radio
                if ($('#' + value).attr('type') == 'radio' || $('#' + value).attr('type') == 'checkbox'){
                    $('#' + value).css('box-shadow', '0px 0px 4pt 1pt '+color).addClass('incomplet');
                } else {
                    $('#' + value).css('border', 'solid 3px '+color).addClass('incomplet');
                }
            }
        });
    } else {
        //Si un element existe avec cet id
        if (id != '') {
            if ($('#' + id).length > 0) {
                if (!$('#' + id).is('input')) {
                    if ($('#' + id).is('select')){
                        $('#' + id).css('border', 'solid 3px '+color).addClass('incomplet');
                    } else {
                        $('#' + id).closest('div').css('box-shadow', '0px 0px 4pt 1pt color');
                    }

                } else {
                    //si c'est un checkbox ou radio
                    if ($('#' + id).attr('type') == 'radio' || $('#' + id).attr('type') == 'checkbox'){
                        $('#' + id).css('box-shadow', '0px 0px 4pt 1pt '+color).addClass('incomplet');
                    } else {
                        $('#' + id).css('border', 'solid 3px '+color).addClass('incomplet');
                    }
                }
            }
        }
    }
}

function removeInputBorder(elem) {
    elem = elem || 'body';

    $(elem).find('.incomplet').each(function () {
        $(this).css('border', '').css('box-shadow', '').removeClass('incomplet');
    });

}

function sendPost(ajaxURL, mypost, onSuccess, onError) {
    onSuccess = onSuccess || null;
    onError = onError || null;

    removeInputBorder();

    $.ajax({
        type : "POST",
        url : ajaxURL,
        dataType : 'JSON',
        data : mypost,
        success : function(data){
            if (data.etat == 'conf'){
                if (onSuccess === null){
                    showConf(data.message);
                } else {
                    onSuccess(data);
                }
            } else if(data.etat == 'err') {
                if (onError === null){
                    showError(data.message);
                } else {
                    onError(data);
                }
            } else {
                showError('Une erreur est survenue');
            }

        },
        statusCode : {
            404: function () {
                showError('Une erreur est survenue');
            },
            302: function () {
                showError('Une erreur est survenue');
            }
        }
    }).fail(function () {
        showError('Une erreur est survenue');
    });
}
function sendForm(ajaxURL, mypost, onSuccess, onError) {
    onSuccess = onSuccess || null;
    onError = onError || null;

    removeInputBorder();

    $.ajax({
        type : "POST",
        url : ajaxURL,
        dataType : 'JSON',
        data : mypost,
        cache: false,
        contentType: false,
        processData: false,
        success : function(data){
            if (data.etat == 'conf'){
                if (onSuccess === null){
                    showConf(data.message);
                } else {
                    onSuccess(data);
                }
            } else if(data.etat == 'err') {
                if (onError === null){
                    showError(data.message);
                } else {
                    onError(data);
                }
            } else {
                showError('Une erreur est survenue');
            }

        },
        statusCode : {
            404: function () {
                showError('Une erreur est survenue');
            },
            302: function () {
                showError('Une erreur est survenue');
            }
        }
    }).fail(function () {
        showError('Une erreur est survenue');
    });
}
function showError(message, titre) {
    titre = titre || 'Erreur';

    var html = '<div class="reveal callout alert" id="modal">'+
        '<h5>'+titre+'</h5>\n' +
        '<p>'+message+'</p>\n' +
        '<button class="close-button" data-close aria-label="Close modal" type="button">\n' +
        '   <span aria-hidden="true">&times;</span>\n' +
        '</button>\n' +
        '</div>';
    $('body').append(html);
    var modal = new Foundation.Reveal($('#modal'));
    modal.open();

    $(document).on('closed.zf.reveal', '#modal', function() {
        // remove from dom when closed
        $('#modal').remove();
    });
}

function showConf(message, titre) {
    titre = titre || 'Confirmation';

    var html = '<div class="reveal callout success" id="modal">'+
        '<h5>'+titre+'</h5>\n' +
        '<p>'+message+'</p>\n' +
        '<button class="close-button" data-close aria-label="Close modal" type="button">\n' +
        '   <span aria-hidden="true">&times;</span>\n' +
        '</button>\n' +
        '</div>';
    $('body').append(html);
    var modal = new Foundation.Reveal($('#modal'));
    modal.open();

    $(document).on('closed.zf.reveal', '#modal', function() {
        // remove from dom when closed
        $('#modal').remove();
    });
}

function showWarn(message, titre) {
    titre = titre || 'Attention';

    var html = '<div class="reveal callout warning" id="modal">'+
        '<h5>'+titre+'</h5>\n' +
        '<p>'+message+'</p>\n' +
        '<button class="close-button" data-close aria-label="Close modal" type="button">\n' +
        '   <span aria-hidden="true">&times;</span>\n' +
        '</button>\n' +
        '</div>';
    $('body').append(html);
    var modal = new Foundation.Reveal($('#modal'));
    modal.open();

    $(document).on('closed.zf.reveal', '#modal', function() {
        // remove from dom when closed
        $('#modal').remove();
    });
}

function showInfo(message, titre) {
    titre = titre || 'Information';

    var html = '<div class="reveal callout secondary" id="modal">'+
        '<h5>'+titre+'</h5>\n' +
        '<p>'+message+'</p>\n' +
        '<button class="close-button" data-close aria-label="Close modal" type="button">\n' +
        '   <span aria-hidden="true">&times;</span>\n' +
        '</button>\n' +
        '</div>';
    $('body').append(html);
    var modal = new Foundation.Reveal($('#modal'));
    modal.open();

    $(document).on('closed.zf.reveal', '#modal', function() {
        // remove from dom when closed
        $('#modal').remove();
    });
}

/**
 * Ajout d'un block input file
 * @param name - Name de l'input
 * @param block - Le block qui va acceuillir l'input
 * @param limit - Le nombre maximum d'input dans le block défini
 */
function addInputFileBlock(name, block, limit) {
    var nb = $('div[data-name="'+name+'"]').length;

    var dataname = name;
    if (name.substr(-2) == '[]'){
        name = name.substr(0, (name.length -1))+nb+']';
    }

    var html = '            ' +
        '           <div class="grid-x grid-padding-x" data-name="'+dataname+'">\n' +
        '                <div class="medium-5 cell">\n' +
        '                    <input type="file" id="'+name+'" name="'+name+'" class="">\n' +
        '                </div>\n';

    if (nb > 0){
        html +=
            '           <div class="medium-1 cell">\n' +
            '               <button type="button" class="button tiny alert inputFile_remove"><i class="fas fa-minus"></i> Retirer</button>\n' +
            '           </div>\n';
    }

    html += '           </div>';

    if (nb < limit){
        $(block).append(html);
    } else {
        showWarn("Nombre maximum atteint");
    }
}

function removeInputFileBlock(delete_btn, name){
    var div = delete_btn.parents('div[data-name="'+name+'"]');
    var inputName = div.find('input').attr('name');
    div.remove();
}

function floatMask() {
    $('.mask-float').mask('nnnnnnnnnnn', {
        translation : {
            'n' : {
                pattern : /[0-9.,]+/
            }
        }
    });
}

function confirm(title, message, callback) {
    // create your modal template
    var modal = '<div class="reveal small" id="confirmation">'+
        '<h2>'+title+'</h2>'+
        '<p class="lead">'+message+'</p>'+
        '<button class="button success yes">Yes</button>'+
        '<button class="button alert float-right" data-close>No</button>'+
        '</div>';
    // appending new reveal modal to the page
    $('body').append(modal);
    // registergin this modal DOM as Foundation reveal
    var confirmation = new Foundation.Reveal($('#modal'));
    // open
    confirmation.open();
    // listening for yes click

    $('#modal').children('.yes').on('click', function() {
        // close and REMOVE FROM DOM to avoid multiple binding
        confirmation.close();
        $('#modal').remove();
        // calling the function to process
        callback.call();
    });
    $(document).on('closed.zf.reveal', '#confirmation', function() {
        // remove from dom when closed
        $('#modal').remove();
    });

}