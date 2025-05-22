<?php
include 'includes/db.php';
session_start();

// Get all categories
$categories = $conn->query("SELECT * FROM categories");

// Filter by category if selected
$where = "WHERE is_public = 1";
if (isset($_GET['category_id']) && $_GET['category_id'] !== '') {
    $cat_id = intval($_GET['category_id']);
    $where .= " AND category_id = $cat_id";
} else {
    $cat_id = '';
}

// Get public songs
$songs = $conn->query("SELECT songs.*, categories.name as category_name FROM songs 
    JOIN categories ON songs.category_id = categories.id 
    $where ORDER BY songs.id DESC");

// Extract YouTube Video ID from a URL
function getYouTubeID($url) {
    if (preg_match('/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/|v\/))([^\&\?\/]+)/', $url, $matches)) {
        return $matches[1];
    }
    return '';
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>🎶 Song Lyrics and Video Collection</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background: #f5f7fa;
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        .header h1 {
            margin: 0;
            font-size: 26px;
        }

        .auth {
            font-size: 14px;
        }

        .auth a {
            text-decoration: none;
            color: #007bff;
            font-weight: bold;
        }

        .filter {
            margin-bottom: 20px;
        }

        select {
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
            min-width: 200px;
        }

        .songs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }

        .song-card {
            background: #fafafa;
            border: 1px solid #e1e1e1;
            padding: 20px;
            border-radius: 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .song-card h2 {
            margin: 10px 0;
            font-size: 20px;
            text-align: center;
        }

        .cover-photo {
            width: 100%;
            max-width: 250px;
            height: auto;
            margin-bottom: 15px;
            border-radius: 6px;
        }

        .video-thumb {
            width: 100%;
            max-width: 250px;
            margin-top: 10px;
            border-radius: 8px;
            transition: 0.3s ease;
        }

        .video-thumb:hover {
            opacity: 0.85;
            transform: scale(1.01);
        }

        pre {
            white-space: pre-wrap;
            background: #f1f1f1;
            padding: 10px;
            border-radius: 6px;
            font-size: 15px;
            margin-top: 10px;
            width: 100%;
        }

        .no-songs {
            text-align: center;
            font-size: 18px;
            color: #777;
        }
    </style>
</head>
<body>

<div class="container">

    <div class="header">
        <h1>🎵 Song Lyrics and Video Collection</h1>
        <div class="auth">
            <?php if (isset($_SESSION['admin'])): ?>
                Welcome, <strong><?= htmlspecialchars($_SESSION['admin']) ?></strong> |
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">🔐 Admin Login</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="filter">
        <form method="GET">
            <label for="category_id"><strong>Filter by Category:</strong></label>
            <select name="category_id" id="category_id" onchange="this.form.submit()">
                <option value="">-- All Categories --</option>
                <?php while ($row = $categories->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>" <?= ($cat_id == $row['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($row['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </form>
    </div>

    <?php if ($songs->num_rows > 0): ?>
        <div class="songs-grid">
            <?php while ($song = $songs->fetch_assoc()): ?>
                <div class="song-card">
                    <?php if ($song['cover_photo']): ?>
                        <img src="uploads/<?= htmlspecialchars($song['cover_photo']) ?>" alt="Cover Photo" class="cover-photo">
                    <?php endif; ?>

                    <h2><?= htmlspecialchars($song['title']) ?></h2>
                    <p><strong>Category:</strong> <?= htmlspecialchars($song['category_name']) ?></p>

                    <pre><?= htmlspecialchars($song['lyrics']) ?></pre>

                    <?php if ($song['video_link']):
                        $videoID = getYouTubeID($song['video_link']);
                        if ($videoID): ?>
                            <a href="https://www.youtube.com/watch?v=<?= $videoID ?>" target="_blank">
                                <img src="https://img.youtube.com/vi/<?= $videoID ?>/0.jpg" class="video-thumb" alt="Video Thumbnail">
                            </a>
                        <?php endif;
                    endif; ?>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p class="no-songs">No public songs found.</p>
    <?php endif; ?>

</div>

</body>
</html>
