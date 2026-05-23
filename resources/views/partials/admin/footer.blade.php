@php
    use App\Models\Utility;
@endphp
<!-- [ Main Content ] end -->
<footer class="dash-footer">
    <div class="footer-wrapper">
        <div class="py-1">
            <p class="mb-0 text-muted"> &copy;
                {{ date('Y') }} {{ Utility::getValByName('footer_text') ? Utility::getValByName('footer_text') : config('app.name', 'Alphainno ERP') }}
            </p>
        </div>

    </div>
</footer>

<!-- Required Js -->
<script src="{{ asset('js/jquery.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/popper.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/simplebar.min.js') }}"></script>

<script src="{{ asset('assets/js/plugins/bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/feather.min.js') }}"></script>
<script src="{{ asset('assets/js/dash.js') }}"></script>
<script src="{{ asset('js/moment.min.js') }}"></script>


<script src="{{ asset('assets/js/plugins/bootstrap-switch-button.min.js') }}"></script>

<script src="{{ asset('assets/js/plugins/sweetalert2.all.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/simple-datatables.js') }}"></script>

<!-- Apex Chart -->
<script src="{{ asset('assets/js/plugins/apexcharts.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/main.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/choices.min.js') }}"></script>
<script src="{{ asset('assets/js/plugins/flatpickr.min.js') }}"></script>



<script src="{{ asset('js/jscolor.js') }}"></script>
<script src="{{ asset('js/custom.js') }}"></script>

@if($message = Session::get('success'))
    <script>
        show_toastr('success', @json($message));
    </script>
@endif
@if($message = Session::get('error'))
    <script>
        show_toastr('error', @json($message));
    </script>
@endif
{{-- Cookie banner is for public pages only; it can reload scripts on admin pages --}}
@stack('script-page')

<script>

    if (typeof feather !== 'undefined') {
        feather.replace();
    }
    var pctoggle = document.querySelector("#pct-toggler");
    if (pctoggle) {
        pctoggle.addEventListener("click", function () {
            var pctCustomizer = document.querySelector(".pct-customizer");
            if (!pctCustomizer) {
                return;
            }
            if (!pctCustomizer.classList.contains("active")) {
                pctCustomizer.classList.add("active");
            } else {
                pctCustomizer.classList.remove("active");
            }
        });
    }
    var themescolors = document.querySelectorAll(".themes-color > a");
    for (var h = 0; h < themescolors.length; h++) {
        var c = themescolors[h];

        c.addEventListener("click", function (event) {
            var targetElement = event.target;
            if (targetElement.tagName == "SPAN") {
                targetElement = targetElement.parentNode;
            }
            var temp = targetElement.getAttribute("data-value");
            removeClassByPrefix(document.querySelector("body"), "theme-");
            document.querySelector("body").classList.add(temp);
        });
    }

    var custthemebg = document.querySelector("#cust-theme-bg");
    if (custthemebg) {
        custthemebg.addEventListener("click", function () {
            var sidebar = document.querySelector(".dash-sidebar");
            var header = document.querySelector(".dash-header:not(.dash-mob-header)");
            if (!sidebar || !header) {
                return;
            }
            if (custthemebg.checked) {
                sidebar.classList.add("transprent-bg");
                header.classList.add("transprent-bg");
            } else {
                sidebar.classList.remove("transprent-bg");
                header.classList.remove("transprent-bg");
            }
        });
    }


    function removeClassByPrefix(node, prefix) {
        for (let i = 0; i < node.classList.length; i++) {
            let value = node.classList[i];
            if (value.startsWith(prefix)) {
                node.classList.remove(value);
            }
        }
    }
</script>

