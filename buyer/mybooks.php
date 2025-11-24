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

    echo "No books purchased";

    exit;

}



$ids = implode(",", array_keys($payments));



$q2 = "SELECT s.ISBN, s.qty, s.price_at_sale, s.payment_id, b.judul, b.pdf_file

       FROM bst_sold_books s

       JOIN bst_books b ON s.ISBN = b.ISBN

       WHERE s.payment_id IN ($ids)";



$books = mysqli_query($conn, $q2);

?>

<h2>Your Purchased Books</h2>

<table border="1" cellpadding="5">

    <tr>

        <th>Title</th>

        <th>ISBN</th>

        <th>Qty</th>

        <th>Type</th>

        <th>Action</th>

    </tr>

<?php while ($b = mysqli_fetch_assoc($books)) { 

    $type = $payments[$b['payment_id']];?><tr>

    <td><?php echo $b['judul']; ?></td>

    <td><?php echo $b['ISBN']; ?></td>

    <td><?php echo $b['qty']; ?></td>

    <td><?php echo $type; ?></td>

   <td> <?php if ($type == "digital" and $b['pdf_file'] != "") { ?> <a href="<?php echo $b['pdf_file']; ?>" target="_blank">Open PDF</a> <?php } else { ?> Hard copy purchased <?php } ?> </td>
</tr><?php } ?>



</table>

<br>

<a href="products.php">Back to Products</a>
