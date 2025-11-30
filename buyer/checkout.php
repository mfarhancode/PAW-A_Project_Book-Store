<?php
session_start();
if (!($_SESSION['login']) or $_SESSION['level'] != 'buyer') {
    header("Location: ../login.php");
    exit;
}

require "../connection.php";

if (!isset($_POST['picked']) or count($_POST['picked']) == 0) {
    echo "No books selected";
    exit;
}

$picked = $_POST['picked'];
$delivery_type = $_POST['delivery_type'];
$buyer_id = $_SESSION['id'];

$ids = implode(",", array_map('intval', $picked));

$q = "SELECT c.id, c.qty, b.judul, b.harga, b.ISBN
      FROM bst_cart c
      JOIN bst_books b ON c.ISBN = b.ISBN
      WHERE c.id IN ($ids)";

$res = mysqli_query($conn, $q);

$items = [];
$total = 0;

while ($row = mysqli_fetch_assoc($res)) {

    // normal subtotal
    $normal = $row['qty'] * $row['harga'];

    // digital subtotal (10%)
    $digital = intval($row['harga'] * 0.10) * $row['qty'];

    if ($delivery_type == "digital") {
        $sub = $digital;
    } else {
        $sub = $normal;
    }

    $row['normal_sub']  = $normal;
    $row['digital_sub'] = $digital;

    $items[] = $row;
    $total  += $sub;
}
?>
<link rel="stylesheet" href="../assets/checkout.css">

<h2>Checkout</h2>

<h3>Items</h3>
<table border="1" cellpadding="5">
    <tr>
        <th>Title</th>
        <th>ISBN</th>
        <th>Qty</th>
        <th>Unit Price</th>
        <th>Subtotal</th>
    </tr>

    <?php foreach ($items as $i) { ?>
    <tr>
        <td><?php echo $i['judul']; ?></td>
        <td><?php echo $i['ISBN']; ?></td>
        <td><?php echo $i['qty']; ?></td>

        <td>
            <?php 
            if ($delivery_type == "digital") {
                echo intval($i['harga'] * 0.10);
            } else {
                echo $i['harga'];
            }
            ?>
        </td>

        <td>
            <?php 
            if ($delivery_type == "digital") {
                echo $i['digital_sub'];
            } else {
                echo $i['normal_sub'];
            }
            ?>
        </td>
    </tr>
    <?php } ?>
</table>

<h3>Delivery Type</h3>
<?php echo $delivery_type; ?>

<h3>Total Price: <?php echo $total; ?></h3>

<form method="POST" action="proses-payment.php">
    <input type="hidden" name="delivery_type" value="<?php echo $delivery_type; ?>">
    <input type="hidden" name="total_harga" value="<?php echo $total; ?>">

    <?php foreach ($picked as $id) { ?>
        <input type="hidden" name="picked[]" value="<?php echo $id; ?>">
    <?php } ?>

    <?php if ($delivery_type == "delivery") { ?>
    <h3>Delivery Info</h3>
    Address: <input type="text" name="address"><br><br>
    City: <input type="text" name="city"><br><br>
    Postal Code: <input type="text" name="postal_code"><br><br>
    Phone: <input type="text" name="phone"><br><br>
    <?php } ?>

    <h3>Payment</h3>
    Payment Method:
    <select name="payment_method">
        <?php if ($delivery_type == "digital") { ?>
        <option value="bank">Bank Transfer</option>
        <option value="ewallet">E Wallet</option>
        <?php } else { ?>
        <option value="bank">Bank Transfer</option>
        <option value="ewallet">E Wallet</option>
        <option value="cod">Cash on Delivery</option>
        <?php } ?>
    </select>

    <br><br>
    <button type="submit">Confirm Payment</button>
    <a href="cart.php" class="cancel_link">Cancel - Go to Cart</a>
</form>
