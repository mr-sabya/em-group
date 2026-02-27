(
    document.querySelectorAll("[toast-list]") ||
    document.querySelectorAll("[data-choices]") ||
    document.querySelectorAll("[data-provider]")
) && (
        document.writeln(
            "<script type='text/javascript' src='/assets/backend/libs/toastify/toastify.js'><\/script>"
        ),
        document.writeln(
            "<script type='text/javascript' src='/assets/backend/libs/choices.js/public/assets/scripts/choices.min.js'><\/script>"
        ),
        document.writeln(
            "<script type='text/javascript' src='/assets/backend/libs/flatpickr/flatpickr.min.js'><\/script>"
        )
    );
