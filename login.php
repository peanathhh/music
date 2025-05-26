<?php
session_start();
include 'includes/db.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = hash('sha256', $_POST['password']);

    $sql = "SELECT * FROM users WHERE username='$username' AND password='$password' AND role='admin'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $_SESSION['admin'] = $username;
        header("Location: admin/add-song.php");
        exit;
    } else {
        $error = "Invalid login.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <style>
        /* Reset some default styling */
        * {
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #d4edda, #a8e6a1);
            font-family: 'Poppins', Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .login-card {
            background: white;
            padding: 40px 30px;
            border-radius: 15px;
            box-shadow: 0 12px 30px rgba(72, 157, 76, 0.3);
            width: 350px;
            text-align: center;
            transition: box-shadow 0.3s ease;
        }

        .login-card:hover {
            box-shadow: 0 18px 40px rgba(72, 157, 76, 0.5);
        }

        .login-card h2 {
            color: #2f6f2f;
            margin-bottom: 25px;
            font-weight: 700;
            font-size: 26px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 14px 12px;
            margin-bottom: 20px;
            border: 2px solid #a3d17a;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: #4CAF50;
            outline: none;
        }

        button {
            width: 100%;
            padding: 14px 0;
            background-color: #4CAF50;
            color: white;
            font-weight: 700;
            font-size: 18px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #388e3c;
        }

        .error {
            color: #a94442;
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .back-link {
            display: inline-block;
            margin-top: 25px;
            color: #4CAF50;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: color 0.3s ease;
        }

        .back-link:hover {
            color: #2f6f2f;
            text-decoration: underline;
        }

        @media (max-width: 400px) {
            .login-card {
                width: 90%;
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>

<div class="login-card">
    <h2>Admin Login</h2>
    <?php if (!empty($error)) echo "<div class='error'>$error</div>"; ?>
    <form method="POST" autocomplete="off">
        <input type="text" name="username" required placeholder="Username" autocomplete="username">
        <input type="password" name="password" required placeholder="Password" autocomplete="current-password">
        <button type="submit">Login</button>
    </form>
    <a href="index.php" class="back-link">← Back to Home</a>
</div>

</body>
</html>
