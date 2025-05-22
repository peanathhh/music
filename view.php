<?php
include 'includes/db.php';

if (!isset($_GET['id'])) {
    echo "Song ID not provided.";
    exit;
}

$song_id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT songs.*, categories.name AS category_name FROM songs 
                        JOIN categories ON songs.category_id = categories.id 
                        WHERE songs.id = ? AND is_public = 1");
$stmt->bind_param("i", $song_id);
$stmt->execute();
$result = $stmt->get_result();
$song = $result->fetch_assoc();
$stmt->close();

if (!$song) {
    echo "Song not found.";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($song['title']) ?></title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .cover-photo { max-width: 300px; margin-bottom: 15px; }
        pre { white-space: pre-wrap; }
    </style>
</head>
<body>

<h1><?= htmlspecialchars($song['title']) ?></h1>

<?php if ($song['cover_photo']): ?>
    <img src="<?= htmlspecialchars($song['cover_photo']) ?>" class="cover-photo" alt="Cover">
<?php endif; ?>

<p><strong>Category:</strong> <?= htmlspecialchars($song['category_name']) ?></p>

<pre><?= htmlspecialchars($song['lyrics']) ?></pre>

<?php if ($song['video_link']): ?>
    <p><a href="<?= htmlspecialchars($song['video_link']) ?>" target="_blank">🎬 Watch Video</a></p>
<?php endif; ?>

</body>
</html>
