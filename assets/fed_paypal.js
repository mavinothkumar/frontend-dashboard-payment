jQuery(document).ready(function ($) {
    var body = $('body');

    body.on('click', '.fed_replace_ajax', function (e) {
        var click = $(this);
        $.fed_toggle_loader();
        $.ajax({
            type: 'POST',
            url: click.data('url'),
            success: function (results) {
                $.fed_toggle_loader();
                click.closest('.fed_ajax_replace_container').html(results.data.html);
            },
            error: function (xhr, textStatus, errorThrown) {
                alert(errorThrown);
            }
        });
        e.preventDefault();
    });

    body.on('click', '.fed_alert_replace_ajax', function (e) {
        var click = $(this);
        $.fed_toggle_loader();
        $.ajax({
            type: 'POST',
            url: click.data('url'),
            success: function (results) {
                $.fed_toggle_loader();
                if (results.success) {
                    swal({
                        title: results.data.message || '',
                        type: results.data.type || "success",
                        confirmButtonColor: '#0AAAAA'
                    });
                    click.closest('.fed_ajax_replace_container').html(results.data.html);
                } else {
                    swal({
                        title: results.data.message || 'Something went wrong',
                        type: "error",
                        confirmButtonColor: '#0AAAAA'
                    });
                }
            },
            error: function (xhr, textStatus, errorThrown) {
                alert(errorThrown);
            }
        });
        e.preventDefault();
    });

    body.on('click', '.fed_pay_close_container', function (e) {
        if ($('.fed_payment_definition .payment_definition_wrapper').length <= 1) {
            swal({
                title: 'Sorry! you should have at least one Payment Definition to add a Plan',
                type: "info",
                confirmButtonColor: '#0AAAAA'
            });
            return false;
        }
        $(this).closest('.payment_definition_wrapper').remove();
    });

    body.on('click', '.fed_pay_close_container_item', function (e) {
        if ($('.fed_pay_single_item_wrapper .fed_pay_single_item').length <= 1) {
            swal({
                title: 'Sorry! you should have at least one Item',
                type: "info",
                confirmButtonColor: '#0AAAAA'
            });
            return false;
        }
        $(this).closest('.fed_pay_single_item').remove();
    });

    body.on('click', '.fed_add_new_payment_definition', function (e) {
        var click = $(this);
        $.fed_toggle_loader();
        $.ajax({
            type: 'POST',
            url: click.data('url'),
            success: function (results) {
                $.fed_toggle_loader();
                click.closest('.fed_payment_definition_container').find('.fed_payment_definition').prepend(results.data.html);
            },
            error: function (xhr, textStatus, errorThrown) {
                alert(errorThrown);
            }
        });
        e.preventDefault();
    });
    body.on('click', '.fed_add_new_single_item', function (e) {
        var click = $(this);
        $.fed_toggle_loader();
        $.ajax({
            type: 'POST',
            url: click.data('url'),
            success: function (results) {
                $.fed_toggle_loader();
                click.closest('.fed_pay_single_item_list').find('.fed_pay_single_item_wrapper').prepend(results.data.html);
            },
            error: function (xhr, textStatus, errorThrown) {
                alert(errorThrown);
            }
        });
        e.preventDefault();
    });

    body.on('submit', '.fed_save_plan_form', function (e) {
        var form = $(this);
        $.fed_toggle_loader();
        $.ajax({
            type: 'POST',
            url: form.attr('action'),
            data: form.serialize(),
            success: function (results) {
                console.log(results);
                if (results.success) {
                    swal({
                        title: results.data.message || '',
                        type: results.data.type || "success",
                        confirmButtonColor: '#0AAAAA'
                    });
                } else {
                    swal({
                        title: results.data.message || 'Something went wrong',
                        type: "error",
                        confirmButtonColor: '#0AAAAA'
                    });
                }

                $.fed_toggle_loader();
            }
        });

        e.preventDefault();
    });

    body.on('click', '.fed_pay_ajax_response', function (e) {
        var form = $(this);
        console.log(form.data('url'));
        $.fed_toggle_loader();
        $.ajax({
            type: 'POST',
            url: form.data('url'),
            data: {},
            success: function (results) {
                console.log(results);
                if (results.success === true) {
                    form.closest('tr').remove();
                    swal({
                        title: results.data.message || '',
                        type: results.data.type || "success",
                        confirmButtonColor: '#0AAAAA'
                    });
                } else if (results.success === false) {
                    swal({
                        title: results.data.message || '',
                        type: results.data.type || "success",
                        confirmButtonColor: '#0AAAAA'
                    });
                }
                else {
                    swal({
                        title: 'Something went wrong',
                        type: "error",
                        confirmButtonColor: '#0AAAAA'
                    });
                }
                $.fed_toggle_loader();
            }
        });
        e.preventDefault();
    });

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
        console.log('am firing');
        window.print();
        e.preventDefault();
    });
});