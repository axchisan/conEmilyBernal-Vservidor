<?php
include_once('conexionDB.php');

if (isset($_POST['term'])) {
    $term = mysqli_real_escape_string($link, trim($_POST['term']));
    $query = "SELECT id_paciente, nombre, apellido FROM pacientes 
              WHERE nombre LIKE '%$term%' OR apellido LIKE '%$term%' 
              ORDER BY nombre, apellido LIMIT 10";
    $result = mysqli_query($link, $query);
    $patients = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $patients[] = $row;
    }
    echo json_encode($patients);
} else {
    echo json_encode([]);
}
mysqli_close($link);
?>