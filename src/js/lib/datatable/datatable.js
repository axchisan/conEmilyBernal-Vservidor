$(document).ready(function () {
    miDataTable();
});

function miDataTable() {
    // Verificar si la tabla #example existe
    if ($('#example').length) {
        // Verificar si la tabla ya ha sido inicializada como DataTable
        if ($.fn.DataTable.isDataTable('#example')) {
            // Si ya está inicializada, destruimos la instancia anterior
            $('#example').DataTable().destroy();
        }

        // Inicializar DataTable
        $('#example').DataTable({
            responsive: true, // Habilitar el modo responsivo
            columnDefs: [
                { responsivePriority: 1, targets: 0 }, // Nombre completo (alta prioridad)
                { responsivePriority: 2, targets: 4 }, // Hora
                { responsivePriority: 3, targets: 5 }, // Estado
                { responsivePriority: 100, targets: [7, 8] } // Columnas de acción (Ver Historia y Descargar PDF)
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json' // Cargar traducción desde una URL externa
            },
            lengthMenu: [[5, 10, -1], [5, 10, "Todos"]], // Opciones de cantidad de registros por página
            iDisplayLength: 5 // Mostrar 5 registros por página por defecto
        });
    }
}