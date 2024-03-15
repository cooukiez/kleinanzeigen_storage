<?php
session_name("kleinanzeigen");
session_start();

include_once 'config.php';

$json_data = file_get_contents('php://input');
$image_data = json_decode($json_data, true);

if (isset($image_data['image_order'])) {
    $image_order = $image_data['image_order'];
    try {
        foreach ($image_order as $position => $file_name) {
            $sql = "UPDATE images SET img_position = ? WHERE file_name = ?";
            $query = $db->prepare($sql);
            $query->bind_param('is', $position, $file_name);
            $query->execute();
        }

        $_SESSION['message'] = "Reihenfolge wurde erfolgreich in der Datenbank gespeichert.";
        http_response_code(200);
    } catch (PDOException $exception) {
        $_SESSION['message'] = "Fehler beim Speichern der Reihenfolge in der Datenbank. Status - " . $exception->getMessage();
        http_response_code(500);
    }
} else {
    $_SESSION['message'] = "Fehlerhafte Bildreihenfolge enthalten.<br>Erhaltene Daten - " . $image_data;
    http_response_code(400);
}
