<?php
require "../connection.php";
session_start();
if (!($_SESSION['login'])) {
    header("Location: ../login.php");
    exit;
}
$query = "SELECT * FROM bst_user";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #bde0fe;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .dashboard-container {
            width: 90%;
            max-width: 1000px;
            height: 80vh;
            background-color: #f8f9fe;
            display: flex;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border-radius: 12px;
            overflow: hidden;
        }

        
        .sidebar {
            width: 250px;
            background-color: #f8f9fe;
            padding: 30px;
            border-right: 1px solid #e0e0e0;
        }
        .sidebar h2 { font-size: 24px; color: #333; margin-bottom: 20px; }
        .menu { list-style: none; }
        .menu li { margin-bottom: 15px; }
        .menu a { text-decoration: none; color: #333; display: block; transition: 0.3s; }
        .menu a:hover { color: #5351e0; font-weight: bold; }

        
        .main-content {
            flex: 1;
            padding: 40px;
            overflow-y: auto;
        }
        .main-content h2 { margin-bottom: 20px; font-weight: bold; }

        
        .user-list-box {
            background-color: #6ca0f5; 
            border-radius: 10px;
            padding: 20px;
            color: white;
            min-height: 300px;
        }

        
        .table-row {
            display: grid;
            grid-template-columns: 50px 1fr 1fr 80px 140px; 
            gap: 15px;
            align-items: center;
            padding: 12px 10px;
        }

        
        .table-header {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 14px;
            border-bottom: 2px solid rgba(255,255,255,0.4); 
            margin-bottom: 10px;
        }

        
        .table-data {
            font-size: 15px;
            border-bottom: 1px solid rgba(255,255,255,0.2); 
        }
        
        .table-data:last-child { border-bottom: none; }

        
        .btn {
            border: none;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            cursor: pointer;
            background-color: white;
            color: #333;
            font-weight: 600;
            margin-right: 5px;
        }
        .btn:hover { background-color: #e0e0e0; }
    </style>
    <script>
        function konfirmasiHapus(id) {
            if (confirm("Apakah anda yakin ingin menghapus user ini?")) {
                location.href = 'delete_user.php?id=' + id;
            }
        }
    </script>
</head>
<body>

    <div class="dashboard-container">
        
        <div class="sidebar">
            <h2>Admin</h2>
            <ul class="menu">
                <li><a href="admin_dashboard.php">Dashboard</a></li>
                <li><a href="users_data.php">Manage users</a></li>
                <li><a href="all_books.php">All Books</a></li>
                <li><a href="admin_report.php">Report</a></li>
            </ul>
        </div>

        <div class="main-content">
            <h2><?php include "../partials/header.php";?></h2>

            <div class="user-list-box">
                
                <div class="table-row table-header">
                    <div>No</div>
                    <div>Username</div>
                    <div>Nama</div>
                    <div>Level</div>
                    <div style="text-align: center;">Action</div>
                </div>

                <?php
                if (mysqli_num_rows($result) > 0) {
                    $no = 1;
                    while ($row = mysqli_fetch_assoc($result)) {
                ?>
                        <div class="table-row table-data">
                            <div><?php echo $no; ?></div> 
                            
                            <div><?php echo $row['username']; ?></div>
                            
                            <div><?php echo $row['name']; ?></div> 
                            
                            <div><?php echo $row['level']; ?></div>
                            
                            <div style="text-align: center;">
                                <a class="btn" href="edit_user.php?id=<?=$row['id']?>" >edit</a>
                            </div>
                        </div>
                <?php
                        $no++;
                    }
                } else {
                    echo "<div style='padding:20px; text-align:center;'>Tidak ada data user.</div>";
                }
                ?>
            </div>
            <?php include "../partials/footer.php"; ?>
        </div>
    </div>

</body>
</html>