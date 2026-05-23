/**
 *
 * You can write your JS code here, DO NOT touch the default style file
 * because it will make it harder for you to update.
 *
 */

"use strict";
// for pos system
var session_key = $(location).attr("href").split('/').pop();
//

$(function () {
    if ($('.custom-scroll').length) {
        $(".custom-scroll").niceScroll();
        $(".custom-scroll-horizontal").niceScroll();
    }


    // loadConfirm();


});

$(document).ready(function () {
    if ($(".datatable").length > 0) {
        const dataTable =  new simpleDatatables.DataTable(".datatable");
    }

    select2();
    summernote();
    daterange();
    // loadConfirm();
});

$(document).ready(function () {
    if ($(".pc-dt-simple").length > 0) {
        $( $(".pc-dt-simple") ).each(function( index,element ) {
            var id = $(element).attr('id');
            const dataTable = new simpleDatatables.DataTable("#"+id);
        });
    }


    select2();
    summernote();
    daterange();
});


function daterange() {
    if ($("#pc-daterangepicker-1").length > 0) {
        document.querySelector("#pc-daterangepicker-1").flatpickr({
            mode: "range"
        });
    }
}


function select2() {
    if ($(".select2").length > 0) {
        $($(".select2")).each(function (index, element) {
            var id = $(element).attr('id');
            var multipleCancelButton = new Choices(
                '#' + id, {
                    removeItemButton: true,
                }
            );
        });

    }

}

function show_toastr(type, message) {
    // Remove any existing AI notification
    var existing = document.getElementById('ai-notification-popup');
    if (existing) existing.remove();

    var isSuccess = (type === 'success' || type === 'Success');

    var icon = isSuccess
        ? '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>'
        : '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>';

    var gradient = isSuccess
        ? 'linear-gradient(135deg, #2563eb 0%, #7c3aed 100%)'
        : 'linear-gradient(135deg, #7c3aed 0%, #4f46e5 100%)';

    var iconBg = isSuccess
        ? 'rgba(255,255,255,0.18)'
        : 'rgba(255,255,255,0.18)';

    var popup = document.createElement('div');
    popup.id = 'ai-notification-popup';
    popup.setAttribute('role', 'alert');
    popup.setAttribute('aria-live', 'assertive');

    popup.style.cssText = [
        'position:fixed',
        'top:50%',
        'left:50%',
        'transform:translate(-50%,-50%) scale(0.82)',
        'z-index:999999',
        'min-width:320px',
        'max-width:420px',
        'width:90vw',
        'background:' + gradient,
        'border-radius:20px',
        'box-shadow:0 32px 80px rgba(37,99,235,0.38), 0 8px 24px rgba(0,0,0,0.22)',
        'padding:28px 28px 24px',
        'display:flex',
        'align-items:flex-start',
        'gap:16px',
        'opacity:0',
        'transition:opacity 0.22s ease, transform 0.22s cubic-bezier(0.34,1.56,0.64,1)',
        'pointer-events:auto',
        'backdrop-filter:blur(12px)',
        '-webkit-backdrop-filter:blur(12px)',
    ].join(';');

    popup.innerHTML =
        '<div style="width:46px;height:46px;border-radius:14px;background:' + iconBg + ';display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#fff;box-shadow:0 4px 14px rgba(0,0,0,0.15);">' + icon + '</div>' +
        '<div style="flex:1;min-width:0;">' +
            '<div style="font-size:0.72rem;font-weight:800;text-transform:uppercase;letter-spacing:0.1em;color:rgba(255,255,255,0.65);margin-bottom:5px;">' + (isSuccess ? 'Success' : 'Error') + '</div>' +
            '<div style="font-size:0.95rem;font-weight:600;color:#fff;line-height:1.45;word-break:break-word;">' + message + '</div>' +
        '</div>' +
        '<button onclick="this.closest(\'#ai-notification-popup\').remove()" style="background:rgba(255,255,255,0.15);border:none;border-radius:10px;width:32px;height:32px;display:flex;align-items:center;justify-content:center;cursor:pointer;flex-shrink:0;color:#fff;font-size:1rem;line-height:1;padding:0;transition:background 0.15s;" onmouseover="this.style.background=\'rgba(255,255,255,0.25)\'" onmouseout="this.style.background=\'rgba(255,255,255,0.15)\'" aria-label="Close">' +
            '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>' +
        '</button>';

    // Overlay backdrop
    var overlay = document.createElement('div');
    overlay.id = 'ai-notification-overlay';
    overlay.style.cssText = 'position:fixed;inset:0;background:rgba(15,22,41,0.45);z-index:999998;opacity:0;transition:opacity 0.22s ease;backdrop-filter:blur(3px);-webkit-backdrop-filter:blur(3px);';
    overlay.addEventListener('click', function() {
        dismissNotification();
    });

    document.body.appendChild(overlay);
    document.body.appendChild(popup);

    // Animate in
    requestAnimationFrame(function() {
        requestAnimationFrame(function() {
            overlay.style.opacity = '1';
            popup.style.opacity = '1';
            popup.style.transform = 'translate(-50%,-50%) scale(1)';
        });
    });

    function dismissNotification() {
        popup.style.opacity = '0';
        popup.style.transform = 'translate(-50%,-50%) scale(0.88)';
        overlay.style.opacity = '0';
        setTimeout(function() {
            if (popup.parentNode) popup.parentNode.removeChild(popup);
            if (overlay.parentNode) overlay.parentNode.removeChild(overlay);
        }, 220);
    }

    // Auto-dismiss after 3.5s
    var timer = setTimeout(dismissNotification, 3500);

    // Cancel auto-dismiss on hover
    popup.addEventListener('mouseenter', function() { clearTimeout(timer); });
    popup.addEventListener('mouseleave', function() { timer = setTimeout(dismissNotification, 2000); });
}

$(document).on('click', 'a[data-ajax-popup="true"], button[data-ajax-popup="true"], div[data-ajax-popup="true"]', function () {

    var data = {};
    var title1 = $(this).data("title");

    var title2 = $(this).data("bs-original-title");
    var title3 = $(this).data("original-title");
    var title = (title1 != undefined) ? title1 : title2;
    var title=(title != undefined) ? title : title3;

    $('.modal-dialog').removeClass('modal-xl');
    var size = ($(this).data('size') == '') ? 'md' : $(this).data('size');

    var url = $(this).data('url');
    $("#commonModal .modal-title").html(title);
    $("#commonModal .modal-dialog").addClass('modal-' + size);

    if ($('#vc_name_hidden').length > 0) {
        data['vc_name'] = $('#vc_name_hidden').val();
    }
    if ($('#warehouse_name_hidden').length > 0) {
        data['warehouse_name'] = $('#warehouse_name_hidden').val();
    }
    if ($('#discount_hidden').length > 0) {
        data['discount'] = $('#discount_hidden').val();
    }
    $.ajax({
        url: url,
        data: data,
        success: function (data) {
            $('#commonModal .body').html(data);
            $("#commonModal").modal('show');
            // daterange_set();
            taskCheckbox();
            common_bind("#commonModal");
            commonLoader();

        },
        error: function (data) {
            data = data.responseJSON;
            show_toastr('Error', data.error, 'error')
        }
    });

});


function arrayToJson(form) {
    var data = $(form).serializeArray();
    var indexed_array = {};

    $.map(data, function (n, i) {
        indexed_array[n['name']] = n['value'];
    });

    return indexed_array;
}


function common_bind() {
    select2();
}


function taskCheckbox() {
    var checked = 0;
    var count = 0;
    var percentage = 0;

    count = $("#check-list input[type=checkbox]").length;
    checked = $("#check-list input[type=checkbox]:checked").length;
    percentage = parseInt(((checked / count) * 100), 10);
    if (isNaN(percentage)) {
        percentage = 0;
    }
    $(".custom-label").text(percentage + "%");
    $('#taskProgress').css('width', percentage + '%');


    $('#taskProgress').removeClass('bg-warning');
    $('#taskProgress').removeClass('bg-primary');
    $('#taskProgress').removeClass('bg-success');
    $('#taskProgress').removeClass('bg-danger');

    if (percentage <= 15) {
        $('#taskProgress').addClass('bg-danger');
    } else if (percentage > 15 && percentage <= 33) {
        $('#taskProgress').addClass('bg-warning');
    } else if (percentage > 33 && percentage <= 70) {
        $('#taskProgress').addClass('bg-primary');
    } else {
        $('#taskProgress').addClass('bg-success');
    }
}


function commonLoader() {
    $('[data-toggle="tooltip"]').tooltip();
    if ($('[data-toggle="tags"]').length > 0) {
        $('[data-toggle="tags"]').tagsinput({tagClass: "badge badge-primary"});
    }


    // $(function(){
    //
    //     var dtToday = new Date();
    //
    //     var month = dtToday.getMonth() + 1;
    //     var day = dtToday.getDate();
    //     var year = dtToday.getFullYear();
    //     if(month < 10)
    //         month = '0' + month.toString();
    //     if(day < 10)
    //         day = '0' + day.toString();
    //
    //     var maxDate = year + '-' + month + '-' + day;
    //
    //     $("input[type='date']").attr('max', maxDate);
    // });

    var e = $(".scrollbar-inner");
    e.length && e.scrollbar().scrollLock()

    var e1 = $(".custom-input-file");
    e1.length && e1.each(function () {
        var e1 = $(this);
        e1.on("change", function (t) {
            !function (e, t, a) {
                var n, o = e.next("label"), i = o.html();
                t && t.files.length > 1 ? n = (t.getAttribute("data-multiple-caption") || "").replace("{count}", t.files.length) : a.target.value && (n = a.target.value.split("\\").pop()), n ? o.find("span").html(n) : o.html(i)
            }(e1, this, t)
        }), e1.on("focus", function () {
            !function (e) {
                e.addClass("has-focus")
            }(e1)
        }).on("blur", function () {
            !function (e) {
                e.removeClass("has-focus")
            }(e1)
        })
    })

    // var e2 = $('[data-toggle="autosize"]');
    // e2.length && autosize(e2);


    if ($(".jscolor").length) {
        jscolor.installByClassName("jscolor");
    }
    summernote();
    // for Choose file
    $(document).on('change', 'input[type=file]', function () {
        var fileclass = $(this).attr('data-filename');
        var finalname = $(this).val().split('\\').pop();
        $('.' + fileclass).html(finalname);
    });
}



function summernote() {
    if ($(".summernote-simple").length) {
        $('.summernote-simple').summernote({
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'strikethrough']],
                ['list', ['ul', 'ol', 'paragraph']],
                ['insert', ['link', 'unlink']],
            ],
            height: 250,
});
        $('.dropdown-toggle').dropdown();
    }

    if ($(".summernote-simple-2").length) {
        $('.summernote-simple-2').summernote({
            dialogsInBody: !0,
            minHeight: 200,
            maxHeight: 300,
            toolbar: [
                ['style', ['style']],
                ["font", ["bold", "italic", "underline", "clear", "strikethrough"]],
                ['fontname', ['fontname']],
                ['color', ['color']],
                ["para", ["ul", "ol", "paragraph"]],
            ],

        });
    }

}



$(document).on("click", '.bs-pass-para', function () {
    var form = $(this).closest("form");
    const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
            confirmButton: 'btn btn-success',
            cancelButton: 'btn btn-danger'
        },
        buttonsStyling: false
    })
    swalWithBootstrapButtons.fire({
        title: 'Are you sure?',
        text: "This action can not be undone. Do you want to continue?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes',
        cancelButtonText: 'No',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {

            form.submit();

        } else if (
            result.dismiss === Swal.DismissReason.cancel
        ) {
        }
    })
});

//only pos system delete button
$(document).on("click", '.bs-pass-para-pos', function () {

    const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
            confirmButton: 'btn btn-success',
            cancelButton: 'btn btn-danger'
        },
        buttonsStyling: false
    })
    swalWithBootstrapButtons.fire({
        title: 'Are you sure?',
        text: "This action can not be undone. Do you want to continue?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes',
        cancelButtonText: 'No',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {

            document.getElementById($(this).data('confirm-yes')).submit();

        } else if (
            result.dismiss === Swal.DismissReason.cancel
        ) {
        }
    })
});


function postAjax(url, data, cb) {
    var token = $('meta[name="csrf-token"]').attr('content');
    var jdata = {_token: token};

    for (var k in data) {
        jdata[k] = data[k];
    }

    $.ajax({
        type: 'POST',
        url: url,
        data: jdata,
        success: function (data) {
            if (typeof (data) === 'object') {
                cb(data);
            } else {
                cb(data);
            }
        },
    });
}

//end only pos system delete button


function deleteAjax(url, data, cb) {
    var token = $('meta[name="csrf-token"]').attr('content');
    var jdata = {_token: token};

    for (var k in data) {
        jdata[k] = data[k];
    }

    $.ajax({
        type: 'DELETE',
        url: url,
        data: jdata,
        success: function (data) {
            if (typeof (data) === 'object') {
                cb(data);
            } else {
                cb(data);
            }
        },
    });
}

// Google calendar
$(document).on('click', '.local_calender .fc-daygrid-event, .fc-timegrid-event', function (e) {
    // if (!$(this).hasClass('project')) {
    e.preventDefault();
    var event = $(this);
    var title1 = $('.fc-event-title').html();
    var title2 = $(this).data("bs-original-title");
    var title = (title1 != undefined) ? title1 : title2;
    // var size = ($(this).data('size') == '') ? 'md' : $(this).data('size');
    var size = 'md';
    var url = $(this).attr('href');
    $("#commonModal .modal-title").html(title);
    $("#commonModal .modal-dialog").addClass('modal-' + size);
    $.ajax({
        url: url,
        success: function (data) {
            $('#commonModal .body').html(data);
            $("#commonModal").modal('show');
            common_bind();
        },
        error: function (data) {
            data = data.responseJSON;
            toastrs('Error', data.error, 'error')
        }
    });
    // }
});

//date value 4

// $(function(){
//
//     var dtToday = new Date();
//
//     var month = dtToday.getMonth() + 1;
//     var day = dtToday.getDate();
//     var year = dtToday.getFullYear();
//     if(month < 10)
//         month = '0' + month.toString();
//     if(day < 10)
//         day = '0' + day.toString();
//
//     var maxDate = year + '-' + month + '-' + day;
//
//     $("input[type='date']").attr('max', maxDate);
// });

function addCommas(num) {
    var number = parseFloat(num).toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
    return ((site_currency_symbol_position == "pre") ? site_currency_symbol : '') + number + ((site_currency_symbol_position == "post") ? site_currency_symbol : '');
}



// PLUS MINUS QUANTITY JS
function wcqib_refresh_quantity_increments() {
    jQuery("div.quantity:not(.buttons_added), td.quantity:not(.buttons_added)").each(function (a, b) {
        var c = jQuery(b);
        c.addClass("buttons_added"),
            c.children().first().before('<input type="button" value="-" class="minus" />'),
            c.children().last().after('<input type="button" value="+" class="plus" />')
    })
}

String.prototype.getDecimals || (String.prototype.getDecimals = function () {
    var a = this,
        b = ("" + a).match(/(?:\.(\d+))?(?:[eE]([+-]?\d+))?$/);
    return b ? Math.max(0, (b[1] ? b[1].length : 0) - (b[2] ? +b[2] : 0)) : 0
}), jQuery(document).ready(function () {
    wcqib_refresh_quantity_increments()
}), jQuery(document).on("updated_wc_div", function () {
    wcqib_refresh_quantity_increments()
}), jQuery(document).on("click", ".plus, .minus", function () {
    var a = jQuery(this).closest(".quantity").find('input[name="quantity"], input[name="quantity[]"]'),
        b = parseFloat(a.val()),
        c = parseFloat(a.attr("max")),
        d = parseFloat(a.attr("min")),
        e = a.attr("step");
    b && "" !== b && "NaN" !== b || (b = 0), "" !== c && "NaN" !== c || (c = ""), "" !== d && "NaN" !== d || (d = 0), "any" !== e && "" !== e && void 0 !== e && "NaN" !== parseFloat(e) || (e = 1), jQuery(this).is(".plus") ? c && b >= c ? a.val(c) : a.val((b + parseFloat(e)).toFixed(e.getDecimals())) : d && b <= d ? a.val(d) : b > 0 && a.val((b - parseFloat(e)).toFixed(e.getDecimals())), a.trigger("change")
});

$(document).on('click', 'input[name="quantity"], input[name="quantity[]"]', function (e) {
    // Allow: backspace, delete, tab, escape, enter and .
    if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 190]) !== -1 ||
        // Allow: Ctrl+A
        (e.keyCode == 65 && e.ctrlKey === true) ||
        // Allow: home, end, left, right
        (e.keyCode >= 35 && e.keyCode <= 39)) {
        // let it happen, don't do anything
        return;
    }
    // Ensure that it is a number and stop the keypress
    if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
        e.preventDefault();
    }
});


//for ai module
$(document).on('click', 'a[data-ajax-popup-over="true"], button[data-ajax-popup-over="true"], div[data-ajax-popup-over="true"]', function () {
    var validate = $(this).attr('data-validate');
    var id = '';
    if (validate) {
        id = $(validate).val();
    }
    var title_over = $(this).data('title');
    $('#commonModalOver .modal-dialog').removeClass('modal-lg');
    var size_over = ($(this).data('size') == '') ? 'md' : $(this).data('size');

    var url = $(this).data('url');
    $("#commonModalOver .modal-title").html(title_over);
    $("#commonModalOver .modal-dialog").addClass('modal-' + size_over);
    $.ajax({
        url: url + '?id=' + id,
        success: function (data) {
            $('#commonModalOver .modal-body').html(data);
            $("#commonModalOver").modal('show');
            taskCheckbox();
        },
        error: function (data) {
            data = data.responseJSON;
            show_toastr('Error', data.error, 'error')
        }
    });
});



//start input serach box
function JsSearchBox() {
    if ($(".js-searchBox").length)
    {
        $( ".js-searchBox" ).each(function( index ) {
            if($(this).parent().find('.formTextbox').length == 0)
            {
                $(this).searchBox({ elementWidth: '250'});
            }
        });
    }
}

$(document).ready(function() {
    JsSearchBox();

    function JsSearchBox() {
        if ($(".js-searchBox").length) {
            $(".js-searchBox").each(function(index) {
                if ($(this).parent().find('.formTextbox').length === 0) {
                    $(this).searchBox({ elementWidth: '250' });
                }
            });
        }
    }
});

//end input serach box






// ─── AI Modern Delete Confirmation ───────────────────────────────────────────
$(document).on('click', '.ai-delete-btn', function () {
    var btn    = $(this);
    var form   = btn.closest('.ai-delete-form');
    var entity = btn.data('entity') || 'record';

    Swal.fire({
        html:
            '<div style="display:flex;flex-direction:column;align-items:center;gap:0;">' +
                '<div style="width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,#7c3aed,#4f46e5);display:flex;align-items:center;justify-content:center;margin-bottom:20px;box-shadow:0 12px 32px rgba(124,58,237,0.35);">' +
                    '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path><path d="M10 11v6"></path><path d="M14 11v6"></path><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"></path></svg>' +
                '</div>' +
                '<div style="font-size:1.15rem;font-weight:800;color:#0f172a;margin-bottom:8px;">Delete ' + entity.charAt(0).toUpperCase() + entity.slice(1) + '?</div>' +
                '<div style="font-size:0.875rem;color:#64748b;line-height:1.55;">This action <strong style="color:#7c3aed;">cannot be undone</strong>.<br>Are you sure you want to continue?</div>' +
            '</div>',
        showCancelButton: true,
        confirmButtonText: '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-right:6px;vertical-align:middle;"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path></svg>Yes, Delete',
        cancelButtonText: 'Cancel',
        focusCancel: true,
        buttonsStyling: false,
        customClass: {
            popup:         'ai-swal-popup',
            confirmButton: 'ai-swal-confirm',
            cancelButton:  'ai-swal-cancel',
            actions:       'ai-swal-actions',
        },
        showClass: {
            popup: 'ai-swal-show'
        },
        hideClass: {
            popup: 'ai-swal-hide'
        },
    }).then(function (result) {
        if (result.isConfirmed) {
            form[0].submit();
        }
    });
});
