

$(document).ready(function () {
    $(document).foundation();

    //Resize des blocs
    $("div[data-match]").each(function () {
        var elem = '.' + $(this).data("match");
        $("div[data-match]").matchHeight();
    });


    /* Gestion du menu */
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
    $(function() {
        $('.sub-menu-2, .sub-menu').matchHeight();
    });
    $.fn.matchHeight._afterUpdate = function(event, groups) {
        $('.sub-menu').each(function () {
            $(this).hide()
        })
    };

    $('.mask-phone').mask('00 00 00 00 00');

    pageLoader(0);
});

function pageLoader(sec) {
    sec = sec || 1000;
    elem = $('.page-loader');

    //Faire disparaitre le loader avec un effet de fadeOut après x secondes
    setTimeout(function () {
        elem.fadeOut("slow");
        //Supprimer la div
        setTimeout(function () {
            elem.remove();
        }, 3000);
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

function sendForm(ajaxURL, mypost, onSuccess, onError) {
    onSuccess = onSuccess || null;
    onError = onError || null;

    removeInputBorder();

    $.ajax({
        type : "POST",
        url : ajaxURL,
        dataType : 'JSON',
        data : mypost,
        success : function(data){
            console.log(data.etat);
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

function showError(message, titre){
    titre = titre || 'Erreur';

    html = '<h5>'+titre+'</h5>\n' +
        '<p>'+message+'</p>\n' +
        '<button class="close-button" data-close aria-label="Close modal" type="button">\n' +
        '   <span aria-hidden="true">&times;</span>\n' +
        '</button>';

    $('#modal').html(html).addClass('callout').addClass('alert').foundation('open');
}

function showConf(message, titre){
    titre = titre || 'Confirmation';

    html = '<h5>'+titre+'</h5>\n' +
        '<p>'+message+'</p>\n' +
        '<button class="close-button" data-close aria-label="Close modal" type="button">\n' +
        '   <span aria-hidden="true">&times;</span>\n' +
        '</button>';

    $('#modal').html(html).addClass('callout').addClass('success').foundation('open');
}