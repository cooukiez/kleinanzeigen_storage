<?php
session_name("kleinanzeigen");
session_start();

include_once 'config.php';

$query_string = $_SERVER['QUERY_STRING'];
parse_str($query_string, $query_params);

if (isset($query_params['path'])) {
    $encoded_path = $_GET['path'];
    $decoded_path = urldecode($encoded_path);

    if (file_exists($decoded_path)) {
        if (unlink($decoded_path)) {
            $_SESSION['message'] = "Bild wurde erfolgreich gelöscht.";
        } else {
            $_SESSION['message'] = "Fehler beim Löschen des Bildes.";
        }
    } else {
        $_SESSION['message'] = "Bild wurde nicht gefunden.";
    }
} else {
    echo json_encode(array('error' => 'Parameter image_path fehlt.'));
}

if(isset($query_params['id'])) {
    $id = intval($_GET['id']);
    $sql = "DELETE FROM images WHERE id = $id";

    if ($db->query($sql) === TRUE) {
        $_SESSION['message'] .= "<br>Bild wurde erfolgreich aus der Datenbank entfernt.";
    } else {
        $_SESSION['message'] .= "<br>Fehler beim entfernen aus der Datenbank - " . $db->error;
    }
} else {
    echo json_encode(array('error' => 'Parameter image_id fehlt.'));
}
?>