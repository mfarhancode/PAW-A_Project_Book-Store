<?php
session_start();

if (!($_SESSION['login']) or $_SESSION['level'] != 'buyer') {
    header("Location: ../login.php");
    exit;
}

require "../connection.php";

$buyer_id = $_SESSION['id'];

// get all payments done by buyer
$q = "SELECT payment_id, delivery_type
      FROM bst_payment_detail
      WHERE buyer_id = '$buyer_id'";

$res = mysqli_query($conn, $q);

$payments = [];
while ($r = mysqli_fetch_assoc($res)) {
    $payments[$r['payment_id']] = $r['delivery_type'];
}

if (empty($payments)) {
    echo "<link rel='stylesheet' href='../assets/mybooks.css'>";
    echo "<div class='container'>No books purchased<br><br>";
    echo "<a class='btn_back' href='products.php'>Back to Products</a></div>";
    exit;
}

$ids = implode(",", array_map('intval', array_keys($payments)));

$q2 = "SELECT s.ISBN, s.qty, s.price_at_sale, s.payment_id, 
              b.judul, b.pdf_file
       FROM bst_sold_books s
       LEFT JOIN bst_books b ON s.ISBN = b.ISBN
       WHERE s.payment_id IN ($ids)";

$resBooks = mysqli_query($conn, $q2);


$grouped = [];

while ($b = mysqli_fetch_assoc($resBooks)) {
    $isbn = $b['ISBN'];

    if (!isset($grouped[$isbn])) {
        $grouped[$isbn] = [
            "judul" => $b['judul'] ?: "(Book removed)",
            "qty" => 0,
            "types" => [],
            "pdf_file" => $b['pdf_file']
        ];
    }

    // add quantity
    $grouped[$isbn]["qty"] += $b['qty'];

    // add purchase type (digital OR delivery)
    $type = $payments[$b['payment_id']];
    if (!in_array($type, $grouped[$isbn]["types"])) {
        $grouped[$isbn]["types"][] = $type;
    }
}
include "../partials/header.php";
?>

<link rel="stylesheet" href="../assets/mybooks.css">

<div class="container">
<h2>Your Purchased Books</h2>

<table>
    <tr>
        <th>Title</th>
        <th>ISBN</th>
        <th>Total Qty</th>
        <th>Types Purchased</th>
        <th>Action</th>
    </tr>

<?php foreach ($grouped as $isbn => $b) { 
    $types_string = implode(" + ", $b['types']);
?>
<tr>
    <td><?= $b['judul']; ?></td>
    <td><?= $isbn; ?></td>
    <td><?= $b['qty']; ?></td>
    <td><?= $types_string; ?></td>

    <td>
        <?php if (in_array("digital", $b['types']) && $b['pdf_file'] != "") { ?>
            <a class="link_button" href="<?= $b['pdf_file']; ?>" target="_blank">Open PDF</a>
        <?php } else { ?>
            Hard Copy Purchased
        <?php } ?>
    </td>
</tr>
<?php } ?>
</table>

<br>

<a href="products.php" class="btn_back">Back to Products</a>

</div>

<?php include "../partials/footer.php";?>
