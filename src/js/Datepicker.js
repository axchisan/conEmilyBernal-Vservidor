$(function () {
    $("#datepicker").datepicker();
});

$(function () {
    $('#fecha_cita').datepicker({
        beforeShowDay: function(date) {
            var day = date.getDay();
            return [day != 0];
        },
        minDate: 0,
        dateFormat: 'yy-mm-dd'
    });
});



