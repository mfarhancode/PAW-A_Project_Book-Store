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



// get books info

$q2 = "SELECT s.qty, s.price_at_sale, s.ISBN, b.judul

       FROM bst_sold_books s

       JOIN bst_books b ON s.ISBN = b.ISBN

       WHERE s.payment_id = '$payment_id'";



$books = mysqli_query($conn, $q2);

?>

<h2>Payment Success</h2>

<h3>Receipt</h3>

<p>Name: <?php echo $name; ?></p>

<p>Payment ID: <?php echo $payment_id; ?></p>

<p>Date: <?php echo $pay['timestamp']; ?></p>

<p>Total Price: <?php echo $pay['total_harga']; ?></p>

<p>Delivery Type: <?php echo $pay['delivery_type']; ?></p>

<p>Payment Method: <?php echo $pay['payment_method']; ?></p>

<?php if ($pay['delivery_type'] == "delivery") { ?>

<h3>Delivery Details</h3><p>Address: <?php echo $pay['address']; ?></p><p>City: <?php echo $pay['city']; ?></p><p>Postal Code: <?php echo $pay['postal_code']; ?></p><p>Phone: <?php echo $pay['phone']; ?></p>



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

    $t = $b['qty'] * $b['price_at_sale'];?><tr>

    <td><?php echo $b['judul']; ?></td>

    <td><?php echo $b['ISBN']; ?></td>

    <td><?php echo $b['qty']; ?></td>

    <td><?php echo $b['price_at_sale']; ?></td>

    <td><?php echo $t; ?></td></tr><?php } ?>



</table>

<br>

<a href="products.php">Back to Products</a>
