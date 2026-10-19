<?php
require "dbconfig.php";

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

$editAlbum = null;

if (isset($_GET["edit"])) {
    $stmt = $pdo->prepare(
        "SELECT * FROM albums WHERE album_id = ?"
    );
    $stmt->execute([(int)$_GET["edit"]]);
    $editAlbum = $stmt->fetch();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    if ($action === "save") {
        $id = (int)($_POST["album_id"] ?? 0);
        $storeId = (int)($_POST["store_id"] ?? 0);
        $title = trim($_POST["album_title"] ?? "");
        $artist = trim($_POST["artist"] ?? "");
        $version = trim($_POST["album_version"] ?? "");
        $price = $_POST["price"] ?? "";
        $stock = $_POST["stock"] ?? "";

        if (
            $storeId > 0 && $title !== "" && $artist !== "" &&
            is_numeric($price) && (float)$price >= 0 &&
            filter_var($stock, FILTER_VALIDATE_INT) !== false &&
            (int)$stock >= 0
        ) {
            if ($id > 0) {
                $stmt = $pdo->prepare(
                    "UPDATE albums SET store_id=?, album_title=?,
                     artist=?, album_version=?, price=?, stock=?
                     WHERE album_id=?"
                );
                $stmt->execute([
                    $storeId, $title, $artist, $version,
                    $price, $stock, $id
                ]);
            } else {
                $stmt = $pdo->prepare(
                    "INSERT INTO albums
                     (store_id, album_title, artist,
                      album_version, price, stock)
                     VALUES (?, ?, ?, ?, ?, ?)"
                );
                $stmt->execute([
                    $storeId, $title, $artist,
                    $version, $price, $stock
                ]);
            }
        }

        header("Location: albums.php");
        exit;
    }

    if ($action === "delete") {
        $stmt = $pdo->prepare(
            "DELETE FROM albums WHERE album_id = ?"
        );
        $stmt->execute([(int)$_POST["album_id"]]);

        header("Location: albums.php");
        exit;
    }
}

$stores = $pdo->query(
    "SELECT store_id, store_name FROM stores ORDER BY store_name"
)->fetchAll();

$albums = $pdo->query(
    "SELECT a.*, s.store_name
     FROM albums a
     JOIN stores s ON a.store_id = s.store_id
     ORDER BY a.album_id DESC"
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Albums</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<h1>K-pop Albums</h1>
<a href="index.php">Main Menu</a>

<?php if (!$stores): ?>
    <p>Please register a store before adding albums.</p>
    <a href="stores.php">Register a Store</a>
<?php else: ?>

<h2><?= $editAlbum ? "Update Album" : "Add Album" ?></h2>

<form method="post">
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="album_id"
        value="<?= e($editAlbum["album_id"] ?? 0) ?>">

    <label>Store:</label>
    <select name="store_id" required>
        <option value="">Choose a store</option>
        <?php foreach ($stores as $store): ?>
        <option value="<?= e($store["store_id"]) ?>"
            <?= (int)($editAlbum["store_id"] ?? 0)
                === (int)$store["store_id"] ? "selected" : "" ?>>
            <?= e($store["store_name"]) ?>
        </option>
        <?php endforeach; ?>
    </select>
    <br><br>

    <label>Album Title:</label>
    <input name="album_title" required
        value="<?= e($editAlbum["album_title"] ?? "") ?>">
    <br><br>

    <label>Artist/Group:</label>
    <input name="artist" required
        value="<?= e($editAlbum["artist"] ?? "") ?>">
    <br><br>

    <label>Album Version:</label>
    <input name="album_version"
        value="<?= e($editAlbum["album_version"] ?? "") ?>">
    <br><br>

    <label>Price (PHP):</label>
    <input type="number" name="price" min="0" step="0.01"
        required value="<?= e($editAlbum["price"] ?? "0.00") ?>">
    <br><br>

    <label>Stock:</label>
    <input type="number" name="stock" min="0" step="1"
        required value="<?= e($editAlbum["stock"] ?? "0") ?>">
    <br><br>

    <button type="submit">
        <?= $editAlbum ? "Update Album" : "Add Album" ?>
    </button>
    <?php if ($editAlbum): ?>
        <a href="albums.php">Cancel</a>
    <?php endif; ?>
</form>

<?php endif; ?>

<h2>Album Inventory</h2>

<table border="1" cellpadding="8">
<tr>
    <th>ID</th>
    <th>Store</th>
    <th>Album Title</th>
    <th>Artist</th>
    <th>Version</th>
    <th>Price (PHP)</th>
    <th>Stock</th>
    <th>Actions</th>
</tr>

<?php foreach ($albums as $album): ?>
<tr>
    <td><?= e($album["album_id"]) ?></td>
    <td><?= e($album["store_name"]) ?></td>
    <td><?= e($album["album_title"]) ?></td>
    <td><?= e($album["artist"]) ?></td>
    <td><?= e($album["album_version"]) ?></td>
    <td><?= e($album["price"]) ?></td>
    <td><?= e($album["stock"]) ?></td>
    <td>
        <a href="albums.php?edit=<?= e($album["album_id"]) ?>">
            Edit
        </a>

        <form method="post" style="display:inline"
            onsubmit="return confirm('Delete this album?')">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="album_id"
                value="<?= e($album["album_id"]) ?>">
            <button type="submit">Delete</button>
        </form>
    </td>
</tr>
<?php endforeach; ?>
</table>
</body>
</html>
