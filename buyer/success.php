<?php
session_start();

if (!($_SESSION['login']) or $_SESSION['level'] != 'buyer') {
    header("Location: ../login.php");
    exit;
}

require "../connection.php";

$payment_id = intval($_GET['payment_id']);
$buyer_id = $_SESSION['id'];
$name = $_SESSION['name'];

// get payment info
$q = "SELECT *
      FROM bst_payment_detail
      WHERE payment_id = '$payment_id' AND buyer_id = '$buyer_id'";

$res = mysqli_query($conn, $q);
$pay = mysqli_fetch_assoc($res);

if (!$pay) {
    echo "Payment not found";
    exit;
}

// get books info (LEFT JOIN so title still appears even if deleted later)
$q2 = "SELECT s.qty, s.price_at_sale, s.ISBN, b.judul
       FROM bst_sold_books s
       LEFT JOIN bst_books b ON s.ISBN = b.ISBN
       WHERE s.payment_id = '$payment_id'";

$books = mysqli_query($conn, $q2);
?>

<link rel="stylesheet" href="../assets/success.css">

<div class="container">
    <h2>Payment Success</h2>

    <h3>Receipt</h3>

    <p><b>Name:</b> <?php echo $name; ?></p>
    <p><b>Payment ID:</b> <?php echo $payment_id; ?></p>
    <p><b>Date:</b> <?php echo $pay['timestamp']; ?></p>
    <p><b>Total Price:</b> <?php echo $pay['total_harga']; ?></p>
    <p><b>Delivery Type:</b> <?php echo $pay['delivery_type']; ?></p>
    <p><b>Payment Method:</b> <?php echo $pay['payment_method']; ?></p>

    <?php if ($pay['delivery_type'] == "delivery") { ?>
        <h3>Delivery Details</h3>
        <p><b>Address:</b> <?php echo $pay['address']; ?></p>
        <p><b>City:</b> <?php echo $pay['city']; ?></p>
        <p><b>Postal Code:</b> <?php echo $pay['postal_code']; ?></p>
        <p><b>Phone:</b> <?php echo $pay['phone']; ?></p>
    <?php } ?>

    <h3>Books Purchased</h3>

    <table border="1" cellpadding="5">
        <tr>
            <th>Title</th>
            <th>ISBN</th>
            <th>Qty</th>
            <th>Price</th>
            <th>Total</th>
        </tr>

        <?php while ($b = mysqli_fetch_assoc($books)) { 
            $title = $b['judul'] ? $b['judul'] : "(Title no longer available)";
            $t = $b['qty'] * $b['price_at_sale'];
        ?>
        <tr>
            <td><?php echo $title; ?></td>
            <td><?php echo $b['ISBN']; ?></td>
            <td><?php echo $b['qty']; ?></td>
            <td><?php echo $b['price_at_sale']; ?></td>
            <td><?php echo $t; ?></td>
        </tr>
        <?php } ?>
    </table>

    <br>

    <button onclick="window.print()" class="print_btn">Print Receipt</button>

    <br><br>

    <a href="products.php" class="back_btn">Back to Products</a>
</div>
