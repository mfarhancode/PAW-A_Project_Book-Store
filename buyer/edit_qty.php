<?php
session_start();
if (!($_SESSION['login']) || $_SESSION['level'] != 'buyer') {
    header("Location: ../login.php");
    exit;
}
require "../connection.php";

$buyer_id = $_SESSION['id'];

// HANDLE SAVE first (before any output)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $cid = intval($_POST['cart_id']);
    $new_qty = intval($_POST['qty']);
    if ($new_qty < 1) $new_qty = 1;

    // double-check stock before saving
    $q2 = mysqli_query($conn, "
        SELECT b.stok
        FROM bst_cart c
        JOIN bst_books b ON c.ISBN = b.ISBN
        WHERE c.id = $cid AND c.buyer_id = $buyer_id
    ");
    $r2 = mysqli_fetch_assoc($q2);
    $available = intval($r2['stok']);
    if ($new_qty > $available) $new_qty = $available;

    mysqli_query($conn, "UPDATE bst_cart SET qty=$new_qty WHERE id=$cid AND buyer_id=$buyer_id");
    header("Location: cart.php");
    exit;
}

// GET and show edit form
if (!isset($_GET['id'])) {
    header("Location: cart.php");
    exit;
}

$cart_id = intval($_GET['id']);

// fetch current cart row and book stock
$q = mysqli_query($conn,
    "SELECT c.id, c.qty, b.ISBN, b.judul, b.stok
     FROM bst_cart c
     JOIN bst_books b ON c.ISBN = b.ISBN
     WHERE c.id = $cart_id AND c.buyer_id = $buyer_id
    ");

if (mysqli_num_rows($q) == 0) {
    header("Location: cart.php");
    exit;
}

$row = mysqli_fetch_assoc($q);
$current_qty = intval($row['qty']);
$stock = intval($row['stok']);
$title = $row['judul'];

include "../partials/header.php";
?>
<link rel="stylesheet" href="../assets/seller_books.css">

<div class="container">
    <h2>Edit Quantity</h2>
    <p><b><?= htmlspecialchars($title) ?></b></p>

    <form method="POST" action="edit_qty.php">
        <input type="hidden" name="cart_id" value="<?= $cart_id ?>">
        <label>Qty (available: <?= $stock ?>)</label><br>
        <input type="number" name="qty" value="<?= $current_qty ?>" min="1" max="<?= $stock ?>"><br><br>
        <button type="submit" name="save">Save</button>
        <a href="cart.php" class="link_button">Cancel</a>
    </form>
</div>

<?php include "../partials/footer.php"; ?>
