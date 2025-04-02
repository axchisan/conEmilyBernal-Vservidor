(function ($) {

  "use strict";

    // PRE LOADER
    $(window).load(function(){
      $('.preloader').fadeOut(1000); // set duration in brackets    
    });


    //Navigation Section
    $('.navbar-collapse a').on('click',function(){
      $(".navbar-collapse").collapse('hide');
    });


    // Owl Carousel
    $('.owl-carousel').owlCarousel({
      animateOut: 'fadeOut',
      items:1,
      loop:true,
      autoplay:true,
    })


    // PARALLAX EFFECT
    $.stellar();  


    // SMOOTHSCROLL
    $(function() {
      $('.navbar-default a, #home a, footer a').on('click', function(event) {
        var $anchor = $(this);
          $('html, body').stop().animate({
            scrollTop: $($anchor.attr('href')).offset().top - 49
          }, 1000);
            event.preventDefault();
      });
    });  


    // WOW ANIMATION
    new WOW({ mobile: false }).init();

})(jQuery);



$(document).ready(function() {
    // Elementos del formulario
    var $fechaInput = $('#fecha_cita');
    var $dentistasInput = $('#dentistas');
    var $horaSelect = $('#hora');
    var $errorDiv = $('#hora-error');

    // Función para actualizar los horarios disponibles
    function updateTimeSlots() {
        var selectedDate = $fechaInput.val();
        var selectedDoctor = $dentistasInput.val();

        console.log('Fecha seleccionada:', selectedDate);
        console.log('Doctor seleccionado:', selectedDoctor);

        if (!selectedDate || !selectedDoctor) {
            // Si no se ha seleccionado fecha o doctor, deshabilitar todos los horarios
            $horaSelect.find('option').each(function() {
                if ($(this).val() !== "") {
                    $(this).prop('disabled', true).css('color', '#999');
                }
            });
            return;
        }

        // Consultar los horarios ocupados mediante AJAX
        $.ajax({
            url: './php/get_unavailable_times.php',
            type: 'POST',
            data: {
                id_doctor: selectedDoctor,
                fecha_cita: selectedDate
            },
            dataType: 'json',
            success: function(data) {
                console.log('Respuesta de get_unavailable_times.php:', data);

                if (data.error) {
                    // Si hay un error en la respuesta
                    $errorDiv.text(data.error).show();
                    $horaSelect.find('option').each(function() {
                        if ($(this).val() !== "") {
                            $(this).prop('disabled', true).css('color', '#999');
                        }
                    });
                    return;
                }

                // Si no hay error, actualizamos los horarios
                var unavailableTimes = data;
                console.log('Horarios ocupados:', unavailableTimes);

                $horaSelect.find('option').each(function() {
                    if ($(this).val() === "") return; // Ignorar la opción "Seleccione una hora"

                    if (unavailableTimes.includes($(this).val())) {
                        $(this).prop('disabled', true).css('color', '#999'); // Horario ocupado en gris
                    } else {
                        $(this).prop('disabled', false).css('color', '#000'); // Horario disponible en negro
                    }
                });

                // Si el horario seleccionado ya no está disponible, limpiar la selección
                if ($horaSelect.val() && unavailableTimes.includes($horaSelect.val())) {
                    $horaSelect.val("");
                    $errorDiv.text("La hora seleccionada ya no está disponible. Por favor, elija otra.").show();
                } else {
                    $errorDiv.hide();
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al consultar horarios ocupados:', error);
                $errorDiv.text("Error al verificar la disponibilidad de horarios. Intente de nuevo.").show();
                $horaSelect.find('option').each(function() {
                    if ($(this).val() !== "") {
                        $(this).prop('disabled', true).css('color', '#999');
                    }
                });
            }
        });
    }

    // Actualizar los horarios cuando cambie la fecha o el doctor
    $fechaInput.on('change', updateTimeSlots);
    $dentistasInput.on('change', updateTimeSlots);

    // Validar el formulario al enviarlo
    $('#appointment-form').on('submit', function(e) {
        if (!$horaSelect.val()) {
            $errorDiv.text("Por favor, seleccione una hora para la cita.").show();
            e.preventDefault();
        }
    });

    // Llamada inicial para establecer el estado de los horarios
    updateTimeSlots();
});