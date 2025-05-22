<?php
include '../includes/auth.php';
include '../includes/db.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Optionally delete the cover photo
    $result = $conn->query("SELECT cover_photo FROM songs WHERE id = $id");
    if ($result && $row = $result->fetch_assoc()) {
        if ($row['cover_photo'] && file_exists($row['cover_photo'])) {
            unlink($row['cover_photo']);
        }
    }

    // Delete the song
    $conn->query("DELETE FROM songs WHERE id = $id");

    header("Location: manage_songs.php?deleted=1");
    exit;
} else {
    header("Location: manage_songs.php?error=1");
    exit;
}
