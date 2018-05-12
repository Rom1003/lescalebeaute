$(document).ready(function () {
    sendForm('/administration/services/ajax/paginate', {nbParPage : 2}, function (data) {
        $('#pagination').html(data.pagine)
    });
});

