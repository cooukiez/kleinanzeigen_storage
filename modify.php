<?php
if (1) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

session_name("kleinanzeigen");
session_start();

include_once 'config.php';

if (isset($_POST['submit'])) {
    //
    // database update
    //
    $anzeige_id = intval($_POST['select_anzeige']);

    $category_id = 0;
    $zustand_id = intval($_POST['select_zustand']);

    $price = intval($_POST['price']);
    $vb = isset($_POST['vb']) ? 1 : 0;

    $versand = isset($_POST['versand']) ? 1 : 0;
    $versand_kosten = intval($_POST['versand_kosten']);
    $location_id = intval($_POST['select_location']);

    $sold = isset($_POST['sold']) ? 1 : 0;
    $ready = isset($_POST['ready']) ? 1 : 0;

    $category_id = $_POST['select_category_l0'];
    $category_id .= !empty($_POST['select_category_l1']) ? '/' . $_POST['select_category_l1'] : '';
    $category_id .= !empty($_POST['select_category_l2']) ? '/' . $_POST['select_category_l2'] : '';

    $update = $db->prepare("UPDATE anzeigen SET `short` = ?, `title` = ?, `description` = ?, `category_id` = ?, `zustand_id` = ?, `price` = ?, `vb` = ?, `versand` = ?, `versand_kosten` = ?, `location_id` = ?, `sold` = ?, `ready` = ? WHERE id = ?");
    $update->bind_param("ssssiiiiiiiii",
        $_POST['short'],
        $_POST['title'],
        $_POST['description'],
        $category_id,
        $zustand_id,
        $price,
        $vb,
        $versand,
        $versand_kosten,
        $location_id,
        $sold,
        $ready,
        $anzeige_id
    );


    $update->execute();

    if ($update->affected_rows > 0) {
        $_SESSION['message'] = "Anzeige in Datenbank erfolgreich verändert.";
    } else {
        $_SESSION['message'] = "Keine Änderung vorgenommen.";
    }

    //
    // file upload
    //
    $target_dir = "images/";
    $allow_types = array('jpg', 'png', 'jpeg', 'gif');

    $sql_values = $error_msg = $error_upload = $error_upload_type = '';
    $file_names = array_filter($_FILES['images']['name']);
    if (!empty($file_names)) {
        foreach ($_FILES['images']['name'] as $key => $val) {
            $file_name = 
                $_POST['short'] .
                '_' .
                uniqid() .
                '.' .
                pathinfo($_FILES['images']['name'][$key], PATHINFO_EXTENSION);
            
            $target_file_path = $target_dir . $file_name;

            // check file type
            $file_type = pathinfo($target_file_path, PATHINFO_EXTENSION);
            if (in_array($file_type, $allow_types)) {
                // upload file
                if (move_uploaded_file($_FILES["images"]["tmp_name"][$key], $target_file_path)) {
                    chmod($target_file_path, 0777);
                    
                    $sql_values .= "('" . $file_name . "', " . $anzeige_id . "),";
                } else {
                    $error_upload .= $_FILES['images']['name'][$key] . ' | ';
                }
            } else {
                $error_upload_type .= $_FILES['images']['name'][$key] . ' | ';
            }
        }

        // create error message
        $errorUpload = !empty($errorUpload) ? 'Fehler beim Hochladen - ' . trim($errorUpload, ' | ') : '';
        $errorUploadType = !empty($errorUploadType) ? 'Falscher Dateityp - ' . trim($errorUploadType, ' | ') : '';
        $errorMsg = !empty($errorUpload) ? '<br/>' . $errorUpload . '<br/>' . $errorUploadType : '<br/>' . $errorUploadType;

        if (!empty($sql_values)) {
            $sql_values = trim($sql_values, ',');
            $sql = $db->query("INSERT INTO images (file_name, anzeige_id) VALUES $sql_values");

            if ($sql) {
                $_SESSION['message'] .= "<br>Bild erfolgreich in die Datenbank eingefügt." . $errorMsg;
            } else {
                $_SESSION['message'] .= "<br>Fehler beim Einfügen in die Datenbank.";
            }
        } else {
            $_SESSION['message'] .= "<br>Fehler beim hochladen." . $errorMsg;
        }
    }
}

header("Location: redirect.php");
?>
