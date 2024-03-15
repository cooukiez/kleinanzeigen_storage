<?php
session_name("kleinanzeigen");
session_start();

include_once 'config.php';

if (isset($_GET['anzeige_id'])) {
    $anzeige_id = $_GET['anzeige_id'];

    $sql = "SELECT * FROM images WHERE anzeige_id = $anzeige_id";
    $images = $db->query($sql);

    if ($images) {
        $imageData = array();
        // collect image data
        while ($row = $images->fetch_assoc()) {
            $imagePath = 'images/' . $row['file_name'];

            $imageData[] = array(
                'path' => $imagePath,
                'id' => $row['id'],
                'img_position' => $row['img_position']
            );
        }

        // return image data as json response
        header('Content-Type: application/json');
        echo json_encode($imageData);
    } else {
        // if there is error, return empty array
        echo json_encode(array());
    }
} else {
    echo json_encode(array('error' => 'Parameter anzeige_id fehlt.'));
}
?>