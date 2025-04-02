$(document).ready(function() {
    // Asegurar que exista un elemento para mostrar errores de fecha
    if ($('#fecha-error').length === 0) {
        $('<div id="fecha-error" class="error-message" style="display: none;"></div>').insertAfter('#fecha_cita');
    }

    // Configurar datepicker para deshabilitar domingos y validar fechas
    $('#fecha_cita').datepicker({
        beforeShowDay: function(date) {
            return [date.getDay() !== 0]; // Deshabilitar domingos (0 = domingo)
        },
        minDate: 0,
        dateFormat: 'yy-mm-dd',
        onSelect: function(dateText) {
            validateDate(dateText);
        }
    });

    // Validar fecha al cambiar el doctor o la fecha
    $('#dentistas, #fecha_cita').change(function() {
        var selectedDate = $('#fecha_cita').val();
        if (selectedDate) {
            validateDate(selectedDate);
        }
    });

    // Función para validar la disponibilidad del doctor en la fecha seleccionada
    function validateDate(selectedDate) {
        var selectedDoctor = $('#dentistas').val();
        if (!selectedDoctor) return;

        $.ajax({
            url: './php/get_unavailable_dates.php',
            type: 'POST',
            data: { id_doctor: selectedDoctor },
            dataType: 'json',
            success: function(unavailableDates) {
                var $fechaError = $('#fecha-error');
                if (unavailableDates.includes(selectedDate)) {
                    $fechaError.text('El doctor no está disponible en esta fecha. Por favor, elija otra fecha.').show();
                    $('#fecha_cita').val('');
                } else {
                    $fechaError.hide();
                }
            },
            error: function() {
                $('#fecha-error').text('Error al verificar la disponibilidad del doctor.').show();
            }
        });
    }

    // Validar disponibilidad antes de enviar el formulario
    $('#appointment-form').submit(function(e) {
        var selectedDate = $('#fecha_cita').val();
        var selectedDoctor = $('#dentistas').val();
        var $fechaError = $('#fecha-error');

        $.ajax({
            url: './php/get_unavailable_dates.php',
            type: 'POST',
            async: false, // Síncrono para esperar la respuesta antes de enviar el formulario
            data: { id_doctor: selectedDoctor },
            dataType: 'json',
            success: function(unavailableDates) {
                if (unavailableDates.includes(selectedDate)) {
                    $fechaError.text('El doctor no está disponible en esta fecha. Por favor, elija otra fecha.').show();
                    e.preventDefault();
                } else {
                    $fechaError.hide();
                }
            },
            error: function() {
                $fechaError.text('Error al verificar la disponibilidad del doctor.').show();
                e.preventDefault();
            }
        });
    });
});