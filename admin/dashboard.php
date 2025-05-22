<?php
include '../includes/auth.php';
include '../includes/db.php';

// Fetch counts
$totalSongs = $conn->query("SELECT COUNT(*) as total FROM songs")->fetch_assoc()['total'];
$totalCategories = $conn->query("SELECT COUNT(*) as total FROM categories")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 30px;
            background-color: #f8f9fa;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
        }

        .dashboard-cards {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            padding: 20px;
            width: 200px;
            text-align: center;
        }

        .card h3 {
            margin: 0;
            font-size: 22px;
            color: #333;
        }

        .card p {
            font-size: 28px;
            margin: 10px 0 0;
            color: #007bff;
        }

        .links {
            margin-top: 40px;
            text-align: center;
        }

        .links a {
            margin: 0 10px;
            text-decoration: none;
            color: #007bff;
            font-weight: bold;
        }

        .links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<h2>📊 Admin Dashboard</h2>

<div class="dashboard-cards">
    <div class="card">
        
        <a href="add-song.php"><h3>Total Songs</h3>
        <p><?= $totalSongs ?></p></a>
    </div>
    <div class="card">
        
        <a href="manage_category.php"><h3>Total Categories</h3>
        <p><?= $totalCategories ?></p></a>
    </div>
</div>

<div class="links">
    <a href="add-song.php">➕ Add Song</a>
    <a href="manage_category.php">📂 Manage Categories</a>
    <a href="../logout.php">🔓 Logout</a>
</div>

</body>
</html>
