<?php
include 'includes/db.php';
session_start();

$categories = $conn->query("SELECT * FROM categories");

$where = "WHERE is_public = 1";
if (isset($_GET['category_id']) && $_GET['category_id'] !== '') {
    $cat_id = intval($_GET['category_id']);
    $where .= " AND category_id = $cat_id";
} else {
    $cat_id = '';
}

$songs = $conn->query("SELECT songs.*, categories.name as category_name FROM songs 
    JOIN categories ON songs.category_id = categories.id 
    $where ORDER BY songs.id DESC");

function getYouTubeID($url) {
    if (preg_match('/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/|v\/))([^\&\?\/]+)/', $url, $matches)) {
        return $matches[1];
    }
    return '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>🎵 Ultimate Song Vault</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #f0f4ff, #e3e9ff);
      color: #2c2f42;
    }

    header {
      background:  #4CAF50;
      color: white;
      padding: 30px 0;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
      position: sticky;
      top: 0;
      z-index: 1000;
    }

    .header-content {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
    }

    .header-content h1 {
      font-size: 28px;
      font-weight: 700;
    }

    .auth a {
      color: #fff;
      text-decoration: none;
      font-weight: 600;
    }

    main {
      max-width: 1200px;
      margin: 40px auto;
      padding: 0 20px;
    }

    .filter-bar {
      text-align: right;
      margin-bottom: 30px;
    }

    select {
      padding: 12px 14px;
      border-radius: 10px;
      border: none;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      font-size: 14px;
    }

    .songs-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 30px;
    }

    .song-card {
      backdrop-filter: blur(12px);
      background: rgba(255, 255, 255, 0.8);
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.07);
      transition: transform 0.2s ease, box-shadow 0.3s ease;
    }

    .song-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12);
    }

    .song-card img,
    .song-card iframe {
      width: 100%;
      height: 180px;
      object-fit: cover;
      border: none;
      border-radius: 0;
    }

    .song-content {
      padding: 20px;
    }

    .song-content h2 {
      font-size: 20px;
      font-weight: 600;
      margin-bottom: 10px;
      color: #1a1c2b;
    }

    .category-tag {
      background: #4CAF50;
      color: white;
      padding: 5px 12px;
      font-size: 12px;
      border-radius: 8px;
      display: inline-block;
      margin-bottom: 12px;
    }

    pre {
      background: #f9faff;
     
      padding: 14px;
      border-radius: 10px;
      font-size: 13px;
      max-height: 180px;
      overflow-y: auto;
      white-space: pre-wrap;
      line-height: 1.6;
    }

    .no-songs {
      text-align: center;
      font-size: 18px;
      color: #777;
      margin-top: 50px;
    }

    .video-wrapper {
      position: relative;
    }

    .video-thumbnail {
      cursor: pointer;
      display: block;
    }

    @media (max-width: 600px) {
      .header-content {
        flex-direction: column;
        gap: 10px;
        align-items: flex-start;
      }

      .filter-bar {
        text-align: center;
      }
    }
    .login-button {
  display: inline-block;
  padding: 10px 20px;
  background-color: #2f8f2f; /* a darker green */
  color: white;
  font-weight: 600;
  text-decoration: none;
  border-radius: 8px;
  transition: background-color 0.3s ease;
}

.login-button:hover {
  background-color: #246b24;
}

  </style>
</head>
<body>

<header>
  <div class="header-content">
    <h1>🎧 Ultimate Song Vault</h1>
    <div class="auth">
      <?php if (isset($_SESSION['admin'])): ?>
        Hello, <strong><?= htmlspecialchars($_SESSION['admin']) ?></strong> | 
        <a href="logout.php">Logout</a>
      <?php else: ?>
        <a href="login.php" class="login-button">Admin Login</a>

      <?php endif; ?>
    </div>
  </div>
</header>

<main>
  <div class="filter-bar">
    <form method="GET">
      <select name="category_id" onchange="this.form.submit()">
        <option value="">🎼 All Categories</option>
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
          <?php if ($song['video_link']):
            $videoID = getYouTubeID($song['video_link']);
            if ($videoID): ?>
              <div class="video-wrapper" data-video-id="<?= $videoID ?>">
                <img src="https://img.youtube.com/vi/<?= $videoID ?>/hqdefault.jpg" alt="Video Thumbnail" class="video-thumbnail">
              </div>
            <?php endif;
          endif; ?>

          <div class="song-content">
            <span class="category-tag"><?= htmlspecialchars($song['category_name']) ?></span>
            <h2><?= htmlspecialchars($song['title']) ?></h2>
            <pre><?= htmlspecialchars($song['lyrics']) ?></pre>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  <?php else: ?>
    <p class="no-songs">No public songs found. Try another category.</p>
  <?php endif; ?>
</main>

<script>
  document.querySelectorAll('.video-thumbnail').forEach(thumbnail => {
    thumbnail.addEventListener('click', function () {
      const wrapper = this.parentElement;
      const videoID = wrapper.getAttribute('data-video-id');
      wrapper.innerHTML = `
        <iframe width="100%" height="180" src="https://www.youtube.com/embed/${videoID}?autoplay=1" 
          frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
      `;
    });
  });
</script>

</body>
</html>
