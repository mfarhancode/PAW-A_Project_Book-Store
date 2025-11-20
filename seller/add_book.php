<?php
session_start();
if (!($_SESSION['login']) || $_SESSION['level'] != 'seller') {
    header("Location: ../login.php");
    exit;
}

require "../connection.php";


$name =  $_SESSION['name'];
echo "<h2>Welcome $name!</h2>";

echo "<h3>Add books</h3>";

echo "<hr>";

$isbn = $_POST['isbn'] ?? "";
$judul = $_POST['judul'] ?? "";
$penulis = $_POST['penulis'] ?? "";
$tahun = $_POST['tahun'] ?? "";
$stok = $_POST['stok'] ?? "";
$description = $_POST['description'] ?? "";
$harga = $_POST['harga'] ?? "";
$pdf_file = $_POST['pdf_file'] ?? "";
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {


    $image = $_FILES['image']['name'] ?? '';

    if (!empty($image)) {
        $target = '../uploads/' . basename($image);
        move_uploaded_file($_FILES['image']['tmp_name'], $target);
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

    // add book
    $sql = "INSERT INTO bst_books(ISBN, judul, penulis, tahun, image, stok, description, harga, seller_id, pdf_file)
    VALUES('$isbn', '$judul', '$penulis', '$tahun', '$image', '$stok', '$description', '$harga', '$seller_id', '$pdf_file')";

    
    if ($conn->query($sql) === TRUE) {
        // if query is excuted, redirect user to dashboard page
        echo '<script>
            alert("Book added successfully!\nRedirecting to dashboard")
            window.location.href = "seller_dashboard.php";
            </script>';
    } else {
        echo "Error: " . $conn->error;
    }
        }

}



?>

<!DOCTYPE html>
<body>

<?php foreach($errors as $error): ?>
    <p style="color:red"><?= $error ?></p>
<?php endforeach; ?>

<form method="post" enctype="multipart/form-data">

    <label>ISBN:</label>
    <input type="text" name="isbn" value="<?= htmlspecialchars($isbn) ?>">
    <br>

    <label>Judul:</label>
    <input type="text" name="judul" value="<?= htmlspecialchars($judul) ?>">
    <br>

    <label>Penulis:</label>
    <input type="text" name="penulis" value="<?= htmlspecialchars($penulis) ?>">
    <br>

    <label>Tahun:</label>
    <input type="number" name="tahun" value="<?= htmlspecialchars($tahun) ?>">
    <br>

    <label>Stok:</label>
    <input type="number" name="stok" value="<?= htmlspecialchars($stok) ?>">
    <br>

    <label>Harga:</label>
    <input type="number" name="harga" value="<?= htmlspecialchars($harga) ?>">
    <br>

    <label>Description:</label>
    <textarea name="description"><?= htmlspecialchars($description) ?></textarea>
    <br>

    <label>PDF File (enter link if it is digital book):</label>
    <input type="text" name="pdf_file" value="<?= htmlspecialchars($pdf_file) ?>">
    <br>

    <label>Image:</label>
    <input type="file" name="image">
    <br>

    <input type="submit" value="Add">
    <a href="seller_dashboard.php"><button type="button">Cancel</button></a>


</form>

<hr>
<a href='../logout.php'>Logout</a>
</body>
</html>


