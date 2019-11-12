jQuery(document).ready(function ($) {
    var body = $('body');
    body.on('click', '.fed_ajax_show_content_in_popup', function (e) {
        var click = $(this);
        var popup = $('#fed_p_popup');
        $.fed_toggle_loader();
        $.ajax({
            type: 'POST',
            url: click.data('url'),
            success: function (results) {
                $.fed_toggle_loader();
                popup.find('.modal-body').html(results.data.html);
                popup.modal('show');
            },
            error: function (xhr, textStatus, errorThrown) {
                alert(errorThrown);
            }
        });
        e.preventDefault();
    });

    body.on('click','.fed_print_invoice',function (e) {
        console.log('am firing 2');
        window.print();
        e.preventDefault();
    });
});