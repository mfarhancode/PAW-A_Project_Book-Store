<?php
session_start();
if (!($_SESSION['login']) || $_SESSION['level'] != 'seller') {
    header("Location: ../login.php");
    exit;
}

require "../connection.php";



$name =  $_SESSION['name'];


$id = $_GET['id'];

// Get current data
$result = mysqli_query($conn, "SELECT * FROM bst_books WHERE id=$id");
$data = mysqli_fetch_assoc($result);

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
$isbn = $_POST['isbn'];
$judul = $_POST['judul'];
$penulis = $_POST['penulis'];
$tahun = $_POST['tahun'];
$category = $_POST['category'];
$stok = $_POST['stok'];
$description = $_POST['description'];
$harga = $_POST['harga'];
$pdf_file = $_POST['pdf_file'];

    if (!empty($_FILES['image']['name'])) {
      $image = $_FILES['image']['name'];
      $target = '../uploads/' . basename($image);
      move_uploaded_file($_FILES['image']['tmp_name'], $target);
    } else {
      $image = $data['image'];
    }


    if (empty($isbn)) $errors[] = 'ISBN cannot be empty!';
    if (!empty($isbn) && strlen($isbn) < 8) $errors[] = 'ISBN must have at least 8 digits!';

    if (empty($judul)) $errors[] = 'Title is required!';
    if (!empty($judul) && strlen($judul) < 3) $errors[] = 'Judul must have at least 3 characters!';

    if (empty($penulis)) $errors[] = 'Author cannot be empty!';
    if (!empty($penulis) && strlen($penulis) < 3) $errors[] = 'Author must have at least 3 characters!';

    if (empty($tahun)) $errors[] = 'Year field cannot be empty!';
    if (!empty($tahun) && ($tahun < 1500 || $tahun > 2025)) $errors[] = 'Year must be within (1500 - 2025)';

    if (empty($stok)) $errors[] = 'Stok cannot be empty!';
    if (!empty($stok) && $stok <= 0) $errors[] = 'Stok must be at least 1!';

    if (empty($harga)) $errors[] = 'Harga cannot be empty!';
    if (!empty($harga) && $harga <= 0) $errors[] = 'Price must be more than 0!';


    if (empty($errors)){
            
    $seller_id = $_SESSION['id'];




$sql = "UPDATE bst_books SET 
    ISBN='$isbn',
    judul='$judul',
    penulis='$penulis',
    tahun='$tahun',
    category='$category',
    stok='$stok',
    harga='$harga',
    description='$description',
    pdf_file='$pdf_file',
    image='$image'
 WHERE id=$id AND seller_id=$seller_id";

if ($conn->query($sql) === TRUE) {
    echo '<script>
        alert("Book updated successfully! Redirecting...");
        window.location.href = "seller_books.php";
        </script>';
} else {
    echo "Error: " . $conn->error;
}
        }

}


include "../partials/header.php";
?>

<!DOCTYPE html>
<head>
    <link rel="stylesheet" href="../assets/add_book.css">
</head>
<body>
<div class="container">
    <h3>Edit book's data</h3>
   <hr>

<?php foreach($errors as $error): ?>
    <p style="color:red"><?= $error ?></p>
<?php endforeach; ?>

<form method="post" enctype="multipart/form-data">

    <label>ISBN:</label>
    <input type="text" name="isbn" value="<?= $data['ISBN'] ?>">
    <br>

    <label>Judul:</label>
    <input type="text" name="judul" value="<?= $data['judul']  ?>">
    <br>

    <label>Penulis:</label>
    <input type="text" name="penulis" value="<?= $data['penulis']  ?>">
    <br>

    <label>Tahun:</label>
    <input type="number" name="tahun" value="<?= $data['tahun']  ?>">
    <br>

    <label>Category:</label>
    <input type="text" name="category" value="<?= $data['category']  ?>">
    <br>

    <label>Stok:</label>
    <input type="number" name="stok" value="<?= $data['stok']  ?>">
    <br>

    <label>Harga:</label>
    <input type="number" name="harga" value="<?= $data['harga']  ?>">
    <br>

    <label>Description:</label>
    <textarea name="description"><?= $data['description']  ?></textarea>
    <br>

    <label>PDF File (enter link if it is digital book):</label>
    <input type="text" name="pdf_file" value="<?= $data['pdf_file']  ?>">
    <br>

    <label>Current Image:</label><br>
    <img src="../uploads/<?php echo $data['image']; ?>" width="100"><br>

    <label>Upload New Image:</label>
    <input type="file" name="image"><br><br>
    <br>

    <input type="submit" value="Edit">
    <a href="seller_books.php"><button type="button">Cancel</button></a>


</form>
</div>

</body>
</html>
<?php include "../partials/footer.php"; ?>


