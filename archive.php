<?php
if (1) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

session_name("kleinanzeigen");
session_start();

include_once 'config.php';

$_SESSION['previous_page'] = 'archive.php';

$anzeigen = $db->query("SELECT * FROM anzeigen WHERE sold = 1 AND short NOT LIKE ''");

$conditions = $db->query("SELECT * FROM `zustand`");
$locations = $db->query("SELECT * FROM `location`");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset='utf-8'>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LKS ™ Archive</title>
</head>

<body>
    <h1 style="margin-bottom: 0">LKS Archive</h1>
    <p style="margin-top: 0; margin-bottom: 5px">Lokale Kleinanzeigen Services</p>
    <button style="margin-top: 0" onclick="window.location.href='index.php';">Go to Home</button><br><br>
    <br>
    <form id="ad_form" action="modify.php" method="post" enctype="multipart/form-data">
        <label for="select_anzeige">Select Ad</label><br>
        <select id="select_anzeige" name="select_anzeige" style="display: inline">
            <?php
            if ($anzeigen->num_rows > 0) {
                while ($row = $anzeigen->fetch_assoc()) {
                    echo "<option value='" . $row['id'] . "'>" . $row['short'] . "</option>";
                }
            }
            ?>
        </select><button type="button" id="delete" style="margin-left: 15px">Delete Ad</button>
        <br><br>
        <hr><br>

        <p style="display: inline">Anzeige Id: </p>
        <p id="anzeige_id" style="display: inline"></p><br><br>

        <label for="short">Enter Short (lowercase and max. 12 characters)</label><br>
        <input type="text" id="short" name="short" required pattern="[a-z_]{1,24}"><br><br>

        <label for="title">Enter Title</label><br>
        <input type="text" id="title" name="title" required><br><br>

        <label for="select_category_l0">Select Category</label><br>
        <select id="select_category_l0" name="select_category_l0" style="display: inline">
            <option value='0'>Not Selected</option>
        </select>
        <select id="select_category_l1" name="select_category_l1" style="display: none">
        </select>
        <select id="select_category_l2" name="select_category_l2" style="display: none">
        </select><br><br>

        <label for="description">Enter Description</label><br>
        <textarea id="description" name="description" rows="4" cols="50" required></textarea><br><br>

        <label for="price">Enter Price (Only numbers)</label><br>
        <input type="text" id="price" name="price" required pattern="[0-9][0-9]{0,2}|1000"><br>
        <label for="vb">VB:</label>
        <input type="checkbox" id="vb" name="vb" value="1"><br><br>

        <label for="select_zustand">Condition</label><br>
        <select id="select_zustand" name="select_zustand">
            <option value='0'>Not Selected</option>
            <?php
            if ($conditions->num_rows > 0) {
                while ($row = $conditions->fetch_assoc()) {
                    echo "<option value='" . $row['id'] . "'>" . $row['name'] . "</option>";
                }
            }
            ?>
        </select><br><br>

        <label for="select_location">Location</label><br>
        <select id="select_location" name="select_location">
            <option value='0'>Not Selected</option>
            <?php
            if ($locations->num_rows > 0) {
                while ($row = $locations->fetch_assoc()) {
                    echo "<option value='" . $row['id'] . "'>" . $row['short'] . "</option>";
                }
            }
            ?>
        </select><br><br>

        <label for="versand">Versand:</label>
        <input type="checkbox" id="versand" name="versand" value="1"><br>
        <label for="versand_kosten">Versandkosten:</label>
        <input type="text" style="margin-left: 4px" id="versand_kosten" name="versand_kosten" required pattern="[0-9][0-9]{0,2}|1000"><br><br>

        <label for="sold">Item Sold:</label>
        <input type="checkbox" id="sold" name="sold" value="1"><br>
        <label for="ready">Is Ready to be Sold:</label>
        <input type="checkbox" id="ready" name="ready" value="1"><br><br>

        <label id="image_label" for="image">Upload Images</label><br>
        <input type="file" id="images" name="images[]" accept="image/*" multiple><br><br>

        <div id="image_display" style="height: 220px; white-space: nowrap; overflow-x: auto; width: 100%;"></div>
        <br>
        <input id="submit" type="submit" name="submit" value="Update">
    </form>
    <br>
    <br>
    <?php
    if (isset($_SESSION['message'])) {
        echo '<hr><p>' . $_SESSION['message'] . '</p><hr>';
        unset($_SESSION['message']);
    } else {
        echo '<hr><p>Status messages will pop up here.</p><hr>';
    }
    ?>
    <script>
        <?php
        $anzeigen = $db->query("SELECT * FROM anzeigen");

        if ($anzeigen) {
            $list = array();
            while ($row = $anzeigen->fetch_assoc()) {
                $list[] = $row;
            }
            echo "var anzeigen = " . json_encode($list) . ";";
        } else {
            $_SESSION['message'] = "Fehler beim ermitteln der Anzeigen - " . $db->error;
        }
        ?>
        <?php
        $categories = $db->query("SELECT * FROM category");

        if ($categories) {
            $list = array();
            while ($row = $categories->fetch_assoc()) {
                $list[] = $row;
            }
            echo "var categories = " . json_encode($list) . ";";
        } else {
            $_SESSION['message'] = "Fehler beim ermitteln der Kategorien - " . $db->error;
        }
        ?>

        function build_category_tree(categories, parent_id = '') {
            const tree = {};
            categories
                .filter((category) => category.previous_id === parent_id)
                .forEach((category) => {
                    tree[category.id] = {
                        name: category.name,
                        subcategories: build_category_tree(categories, category.id),
                    };
                });
            return tree;
        }

        const tree = build_category_tree(categories);

        var select_l0 = document.getElementById('select_category_l0');
        var select_l1 = document.getElementById('select_category_l1');
        var select_l2 = document.getElementById('select_category_l2');

        document.addEventListener("DOMContentLoaded", function() {
            var select = document.getElementById('select_anzeige');
            var select_cache = localStorage.getItem('archive_select_anzeige');
            var scroll_cache = localStorage.getItem('archive_scroll');

            Object.keys(tree).forEach(category_id => {
                const option = document.createElement('option');
                option.value = category_id;
                option.textContent = tree[category_id].name;
                select_l0.appendChild(option);
            });

            if (select_cache != null && anzeigen.some(obj => obj.id == select_cache && obj.sold == '1')) {
                select.value = select_cache;
            } else {
                select.selectedIndex = 0;
            }

            if (scroll_cache) {
                window.scrollTo(0, scroll_cache);
                sessionStorage.removeItem('archive_scroll');
            }

            update_form(select);
        });

        function populate_select(select_element, categories) {
            select_element.innerHTML = '';
            select_element.style.display = 'inline';

            const default_option = document.createElement('option');
            default_option.text = 'Not selected';
            default_option.value = '0';
            select_element.add(default_option);

            Object.keys(categories).forEach(category_id => {
                const option = document.createElement('option');
                option.value = category_id;
                option.textContent = categories[category_id].name;
                select_element.add(option);
            });
        }

        select_l0.addEventListener('change', function() {
            update_l1();
        });

        function update_l1() {
            select_l2.style.display = 'none';

            if (select_l0.value != '0') {
                const l0_category = tree[select_l0.value];

                if (l0_category && l0_category.subcategories && Object.keys(l0_category.subcategories).length > 0) {
                    populate_select(select_l1, l0_category.subcategories);
                } else {
                    select_l1.style.display = 'none';
                }
            } else {
                select_l1.style.display = 'none';
            }
        }

        select_l1.addEventListener('change', function() {
            update_l2();
        });

        function update_l2() {
            if (select_l1.value != '0') {
                const l1_category = tree[select_l0.value].subcategories[select_l1.value];

                if (l1_category && l1_category.subcategories && Object.keys(l1_category.subcategories).length > 0) {
                    populate_select(select_l2, l1_category.subcategories);
                } else {
                    select_l2.style.display = 'none';
                }
            } else {
                select_l2.style.display = 'none';
            }
        }

        window.addEventListener('beforeunload', function() {
            sessionStorage.setItem('archive_scroll', window.scrollY);
        });

        document.getElementById('select_anzeige').addEventListener('change', function() {
            update_form();
        });

        function update_form() {
            var select = document.getElementById('select_anzeige');
            localStorage.setItem('archive_select_anzeige', select.value);

            var anzeige = anzeigen.find(obj => obj.id === select.value);
            document.getElementById('anzeige_id').innerHTML = anzeige.id;

            try {
                categorie_ids = anzeige.category_id.split("/");
                select_l0.value = categorie_ids[0];
                update_l1();
                select_l1.value = categorie_ids[1];
                update_l2();
                select_l2.value = categorie_ids[2];
            } catch (error) {
                select_l0.value = '0';
                update_l1();
            }

            ['short', 'title', 'description', 'price', 'versand_kosten'].forEach(field => {
                document.getElementById(field).value = anzeige[field];
            });

            ['versand', 'vb', 'sold', 'ready'].forEach(field => {
                document.getElementById(field).checked = anzeige[field] === '1';
            });

            document.getElementById("select_zustand").value = parseInt(anzeige.zustand_id);
            document.getElementById("select_location").value = parseInt(anzeige.location_id);

            document.getElementById('image_display').innerHTML = '';

            display_images(anzeige.id);
        }

        function display_images(anzeige_id) {
            var image_url = 'get_images.php?anzeige_id=' + anzeige_id;

            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        var imageData = JSON.parse(xhr.responseText);

                        var imageDisplay = document.getElementById('image_display');

                        imageData.forEach(function(data) {
                            var img = document.createElement('img');
                            img.src = data.path;
                            img.alt = 'image';
                            img.id = data.id;

                            img.style.maxWidth = '100%';
                            img.style.maxHeight = '200px';
                            img.style.marginRight = '5px';
                            img.style.cursor = 'pointer';
                            img.style.user_drag = 'none';

                            img.setAttribute('draggable', 'true');
                            img.addEventListener('dragstart', function(event) {
                                event.dataTransfer.setData('text/plain', event.target.id);
                                event.target.style.opacity = '0';
                            });

                            img.onclick = function() {
                                var confirmed = confirm('Delete this image?');
                                if (confirmed) {
                                    delete_image(data.id, data.path);
                                    img.parentNode.removeChild(img);
                                }
                            };

                            imageDisplay.appendChild(img);
                        });
                    } else {
                        console.error('Konnte Bilder nicht abrufen. Status - ' + xhr.status);
                    }
                }
            };

            xhr.open('GET', image_url, true);
            xhr.send();
        }

        document.addEventListener('dragover', function(event) {
            event.preventDefault();
        });

        document.addEventListener('dragend', function(event) {
            event.preventDefault();
            event.target.style.opacity = '1';
        });


        document.addEventListener('drop', function(event) {
            event.preventDefault();
            var data = event.dataTransfer.getData('text/plain');
            var target = event.target;
            var parent = target.parentNode;


            if (target && parent.id === 'image_display') {
                var dragged = document.getElementById(data);
                if (target != dragged) {
                    const after_dragged = dragged.nextElementSibling;
                    const parent = dragged.parentNode;
                    if (target === after_dragged) {
                        parent.insertBefore(target, dragged);
                    } else {
                        target.replaceWith(dragged);
                        parent.insertBefore(target, after_dragged);
                    }

                    image_names = Array.from(parent.children).map(child => {
                        var url = child.src;
                        return url.substring(url.lastIndexOf('/') + 1);
                    });

                    var image_data = JSON.stringify({
                        image_order: image_names
                    });

                    var url = 'update_image_order.php';
                    var xhr = new XMLHttpRequest();
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState === XMLHttpRequest.DONE) {
                            if (xhr.status === 200) {
                                // it worked
                            } else {
                                console.error('Konnte Reihenfolge nicht in Datenbank ändern. Status - ' + xhr.status);
                            }
                        }
                    };

                    xhr.open('POST', url, true);
                    xhr.setRequestHeader('Content-Type', 'application/json');
                    xhr.send(image_data);
                }
            }
        });

        function delete_image(id, path) {
            var encoded_path = encodeURIComponent(path);
            var delete_url = 'delete_img.php?id=' + id + '&path=' + encoded_path;

            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        // it worked
                    } else {
                        console.error('Konnte Bild nicht löschen. Status - ' + xhr.status);
                    }
                }
            };

            xhr.open('GET', delete_url, true);
            xhr.send();
        }

        document.getElementById('delete').addEventListener('click', function() {
            var select_anzeige = document.getElementById('select_anzeige');
            var selectedIndex = select_anzeige.selectedIndex;
            var selectedOptionText = select_anzeige.options[selectedIndex].text;

            var confirmed = confirm('Are you Sure you want to delete ' + selectedOptionText + '?');
            if (confirmed) {
                var anzeige = anzeigen.find(function(obj) {
                    return obj.id === select_anzeige.value;
                });
                var delete_url = 'delete_ad.php?anzeige_id=' + anzeige.id;

                var xhr = new XMLHttpRequest();
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === XMLHttpRequest.DONE) {
                        if (xhr.status === 200) {
                            // it worked
                            localStorage.setItem('index_select_anzeige', 'new');
                            location.reload();
                        } else {
                            console.error('Konnte anzeige nicht löschen. - ' + xhr.status);
                        }
                    }
                };

                xhr.open('GET', delete_url, true);
                xhr.send();
            }
        });
    </script>
</body>

</html>
<!-- <?php phpinfo(); ?> -->