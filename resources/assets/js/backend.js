/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

//window.Vue = require('vue');

window.chosen = require('chosen-js');
require('chosen-js/chosen.css');


require('select2');
require('select2/dist/css/select2.min.css');
window.Highcharts = require('highcharts');


import {draggable, timepicker, sortable} from "jquery-ui";

window.timepicker = require('bootstrap-timepicker');
require('bootstrap-timepicker/css/bootstrap-timepicker.css');

require('bootstrap4-datetimepicker');

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

//Vue.component('example-component', require('./components/ExampleComponent.vue'));
/*
const app = new Vue({
    el: '#app'
});
*/


//Sends forms
$(".ajaxForm :submit").attr("disabled", false);
$(document).on("submit", ".ajaxForm", function () {

    var form = this;
    if (!form.submitting) {
        form.submitting = true;
        $(form).find(":submit").attr("disabled", true);
        //$(form).append("<div class='ajaxLoader'>Cargando</div>");
        var ajaxLoader = $(form).find(".ajaxLoader");
        $(ajaxLoader).css({
            left: ($(form).width() / 2 - $(ajaxLoader).width() / 2) + "px",
            top: ($(form).height() / 2 - $(ajaxLoader).height() / 2) + "px"
        });
        $.ajax({
            url: form.action,
            data: $(form).serialize(),
            type: form.method,
            dataType: "json",
            success: function (response) {
                console.log(response);
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
                if ($('#login_captcha').length > 0) {
                    if ($('#login_captcha').is(':empty')) {
                        grecaptcha.render('login_captcha', {
                            'sitekey': site_key
                        });
                    } else {
                        grecaptcha.reset();
                    }
                }

                var html = '';
                $.each(error.responseJSON.errors, function (index, value) {
                    html += '<div class="alert alert-danger" role="alert">' + value[0] + '</div>';
                });

                $(".validacion").html(html);

                $('html, body').animate({
                    scrollTop: $(".validacion").offset().top - 10
                });

                form.submitting = false;
                $(ajaxLoader).remove();
                $(form).find(":submit").attr("disabled", false);
            }
        });
    }
    return false;
});


$(document).ready(function () {
    //Para que el tooltip funcione en los contenidos cargados con ajax
    $( ".tt" ).hover(function() {
        $(this).tooltip({
            html: true,
            trigger: "manual"
        }).tooltip('toggle');
    });
});

//Scroll change html background-color 'test'
$(document).ready(function () {
    window.documentHeight = $(document).height();
    window.documentHeightLimit = 100;

    $(document).scroll(function () {
        if ($(document).scrollTop() > documentHeightLimit && $("html").css("background-color") == "rgb(255, 255, 255)") {
            $("html").css("background-color", "#09132E");
        }

        if ($(document).scrollTop() < documentHeightLimit && $("html").css("background-color") == "rgb(9, 19, 46)") {
            $("html").css("background-color", "#ffffff");
        }
    });
});
require('datatables/media/js/jquery.dataTables');
require('datatables/media/css/jquery.dataTables.css');
