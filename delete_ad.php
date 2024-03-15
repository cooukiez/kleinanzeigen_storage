<?php
session_name("kleinanzeigen");
session_start();

include_once 'config.php';

if (isset($_GET['anzeige_id'])) {
    $anzeige_id = $_GET['anzeige_id'];
    $sql = "DELETE FROM anzeigen WHERE id = $anzeige_id";

    if ($db->query($sql) === TRUE) {
        $_SESSION['message'] = "Anzeige erfolgreich gelöscht.";
    } else {
        $_SESSION['message'] = "Fehler beim Löschen der Anzeige - " . $db->error;
    }
} else {
    // If anzeige_id is not provided, return an error message
    echo json_encode(array('error' => 'Paramter anzeige_id fehlt.'));
}
?>