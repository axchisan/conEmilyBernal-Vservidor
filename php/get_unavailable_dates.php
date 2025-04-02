<?php
include_once('conexionDB.php');

if (isset($_POST['id_doctor'])) {
    $id_doctor = $_POST['id_doctor'];
    $query = "SELECT unavailable_date FROM unavailable_dates WHERE id_doctor = '$id_doctor'";
    $result = mysqli_query($link, $query);
    $unavailableDates = [];
    while ($row = mysqli_fetch_array($result)) {
        $unavailableDates[] = $row['unavailable_date'];
    }
    echo json_encode($unavailableDates);
} else {
    echo json_encode([]);
}
mysqli_close($link);
?>