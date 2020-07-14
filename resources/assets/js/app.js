
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.select = require('select2');
require('select2/dist/css/select2.min.css');
require('select2/dist/js/select2.min.js');

window.moment = require('moment');

import draggable from "jquery-ui";
import jsPlumb from "jsplumb";

import datetimepicker from "bootstrap4-datetimepicker";
require("bootstrap4-datetimepicker/src/sass/bootstrap-datetimepicker-build.scss");


window.chosen = require('chosen-js');
require('chosen-js/chosen.css');

//Sends forms
$(".ajaxForm :submit").attr("disabled", false);
$(document).on("submit", ".ajaxForm", function () {
    var form = this;
    if (!form.submitting) {
        form.submitting = true;
        $(form).find(":submit").attr("disabled", true);
        //$(form).find(":submit").button('loading');
        $(form).find(":submit").html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>Enviando...');
        //$(form).append("<div class='ajaxLoader'>Cargando</div>");
        // var ajaxLoader = $('.ajaxForm').find(".ajaxLoader");
        // $(ajaxLoader).show();
        $.ajax({
            url: form.action,
            data: $('*:not(.camposvisibilidad)', form).serialize(),
            type: form.method,
            dataType: "json",
            success: function (response) {
                if (response.validacion) {
                    if (response.redirect) {
                        window.location = response.redirect;
                    } else {
                        var f = window[$(form).data("onsuccess")];
                        f(form);
                    }
                }
            },
            error: function (error) {
                var $submitBtn = $(form).find(":submit");

                if ($('#login_captcha').length > 0) {
                    if ($('#login_captcha').is(':empty')) {
                        grecaptcha.render('login_captcha', {
                            'sitekey': site_key
                        });
                    } else {
                        grecaptcha.reset();
                    }
                }
                // si el boton tiene definida etiqueta, se agrega su valor al texto del btn
                if ($submitBtn.data('etiqueta') != undefined) {
                    $submitBtn.html($submitBtn.data('etiqueta'));
                } else {
                    $submitBtn.html('Siguiente');
                }

                var html = '';
                $.each(error.responseJSON.errors, function (index, value) {
                    html += '' +
                        '<div class="alert alert-danger alert-dismissible fade show" role="alert">\n' +
                        value[0] +
                        '  <button type="button" class="close" data-dismiss="alert" aria-label="Close">\n' +
                        '    <span aria-hidden="true">&times;</span>\n' +
                        '  </button>\n' +
                        '</div>';
                });

                $(".validacion").html(html);

                $('html, body').animate({
                    scrollTop: $(".validacion").offset().top - 10
                });

                form.submitting = false;
                //$(ajaxLoader).remove();
                $submitBtn.button('reset');
                $submitBtn.attr("disabled", false);
            }
        });
    }
    return false;
});
require('datatables/media/js/jquery.dataTables');
require('datatables/media/css/jquery.dataTables.css');
