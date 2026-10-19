<?php
require "dbconfig.php";

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

$editStore = null;

if (isset($_GET["edit"])) {
    $stmt = $pdo->prepare(
        "SELECT * FROM stores WHERE store_id = ?"
    );
    $stmt->execute([(int)$_GET["edit"]]);
    $editStore = $stmt->fetch();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    if ($action === "save") {
        $id = (int)($_POST["store_id"] ?? 0);
        $name = trim($_POST["store_name"] ?? "");
        $location = trim($_POST["location"] ?? "");
        $contact = trim($_POST["contact_number"] ?? "");

        if ($name !== "" && $location !== "") {
            if ($id > 0) {
                $stmt = $pdo->prepare(
                    "UPDATE stores
                     SET store_name=?, location=?, contact_number=?
                     WHERE store_id=?"
                );
                $stmt->execute([$name, $location, $contact, $id]);
            } else {
                $stmt = $pdo->prepare(
                    "INSERT INTO stores
                     (store_name, location, contact_number)
                     VALUES (?, ?, ?)"
                );
                $stmt->execute([$name, $location, $contact]);
            }
        }

        header("Location: stores.php");
        exit;
    }

    if ($action === "delete") {
        $stmt = $pdo->prepare(
            "DELETE FROM stores WHERE store_id = ?"
        );
        $stmt->execute([(int)$_POST["store_id"]]);

        header("Location: stores.php");
        exit;
    }
}

$stores = $pdo->query(
    "SELECT * FROM stores ORDER BY store_id DESC"
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Stores</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<h1>K-pop Stores</h1>
<a href="index.php">Main Menu</a>

<h2><?= $editStore ? "Update Store" : "Add Store" ?></h2>

<form method="post">
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="store_id"
        value="<?= e($editStore["store_id"] ?? 0) ?>">

    <label>Store Name:</label>
    <input name="store_name" required
        value="<?= e($editStore["store_name"] ?? "") ?>">
    <br><br>

    <label>Location:</label>
    <input name="location" required
        value="<?= e($editStore["location"] ?? "") ?>">
    <br><br>

    <label>Contact Number:</label>
    <input name="contact_number"
        value="<?= e($editStore["contact_number"] ?? "") ?>">
    <br><br>

    <button type="submit">
        <?= $editStore ? "Update Store" : "Add Store" ?>
    </button>
    <?php if ($editStore): ?>
        <a href="stores.php">Cancel</a>
    <?php endif; ?>
</form>

<h2>Registered Stores</h2>

<table border="1" cellpadding="8">
<tr>
    <th>ID</th>
    <th>Store Name</th>
    <th>Location</th>
    <th>Contact</th>
    <th>Actions</th>
</tr>

<?php foreach ($stores as $store): ?>
<tr>
    <td><?= e($store["store_id"]) ?></td>
    <td><?= e($store["store_name"]) ?></td>
    <td><?= e($store["location"]) ?></td>
    <td><?= e($store["contact_number"]) ?></td>
    <td>
        <a href="stores.php?edit=<?= e($store["store_id"]) ?>">
            Edit
        </a>

        <form method="post" style="display:inline"
            onsubmit="return confirm('Delete this store and its albums?')">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="store_id"
                value="<?= e($store["store_id"]) ?>">
            <button type="submit">Delete</button>
        </form>
    </td>
</tr>
<?php endforeach; ?>
</table>
</body>
</html>