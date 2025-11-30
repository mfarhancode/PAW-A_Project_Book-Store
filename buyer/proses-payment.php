<?php

session_start();

if (!($_SESSION['login']) || $_SESSION['level'] != 'buyer') {
    header("Location: ../login.php");
    exit;
}

require "../connection.php";

$buyer_id = $_SESSION['id'];
$delivery_type = $_POST['delivery_type'];
$total_harga = intval($_POST['total_harga']);
$picked = $_POST['picked'];

$address = $_POST['address'] ?? "";
$city = $_POST['city'] ?? "";
$postal_code = $_POST['postal_code'] ?? "";
$phone = $_POST['phone'] ?? "";
$payment_method = $_POST['payment_method'];

// Validate delivery fields
if ($delivery_type == "delivery") {
    if ($address == "" || $city == "" || $postal_code == "" || $phone == "") {
        header("Location: checkout.php?error=incomplete_delivery_info");
        exit;
    }
} else {
    // digital → clear delivery fields
    $address = "";
    $city = "";
    $postal_code = "";
    $phone = "";
}

// Insert payment detail
$q = "INSERT INTO bst_payment_detail
      (buyer_id, total_harga, delivery_type, address, city, postal_code, phone, payment_method)
      VALUES
      ('$buyer_id', '$total_harga', '$delivery_type', '$address', '$city', '$postal_code', '$phone', '$payment_method')";

mysqli_query($conn, $q);

$payment_id = mysqli_insert_id($conn);

// All picked cart IDs
$ids = implode(",", array_map('intval', $picked));

// Get items from cart
$q2 = "SELECT c.qty, b.harga, b.ISBN
       FROM bst_cart c
       JOIN bst_books b ON c.ISBN = b.ISBN
       WHERE c.id IN ($ids)";

$res = mysqli_query($conn, $q2);

// Insert each into sold_books + reduce stock
while ($row = mysqli_fetch_assoc($res)) {

    $qty = $row['qty'];
    $ISBN = $row['ISBN'];
    $price = $row['harga'];

    // Insert into sold_books table
    mysqli_query(
        $conn,
        "INSERT INTO bst_sold_books (ISBN, payment_id, qty, price_at_sale, delivery_type)
         VALUES ('$ISBN', '$payment_id', '$qty', '$price', '$delivery_type')"
    );

    // Reduce stock only for physical delivery
    if ($delivery_type == "delivery") {
        mysqli_query(
            $conn,
            "UPDATE bst_books 
             SET stok = stok - $qty 
             WHERE ISBN = '$ISBN'"
        );
    }
}

// Delete items from cart
mysqli_query($conn, "DELETE FROM bst_cart WHERE id IN ($ids)");

// Redirect to success page
header("Location: success.php?payment_id=" . $payment_id);
exit;

?>
