<?php
session_start();
include '../includes/auth.php';
include '../includes/db.php';

// Handle Add Song
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_song'])) {
    $title = $_POST['title'];
    $composer = $_POST['composer'];
    $lyrics = $_POST['lyrics'];
    $category_id = $_POST['category_id'];
    $is_public = isset($_POST['is_public']) ? 1 : 0;
    $video_link = $_POST['video_link'];
    $cover_photo = '';

    if ($_FILES['cover_photo']['name']) {
        $target_dir = "../uploads/";
        $filename = basename($_FILES['cover_photo']['name']);
        $target_file = $target_dir . time() . "_" . $filename;
        move_uploaded_file($_FILES['cover_photo']['tmp_name'], $target_file);
        $cover_photo = $target_file;
    }

    $stmt = $conn->prepare("INSERT INTO songs (title, composer, lyrics, cover_photo, video_link, category_id, is_public) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssis", $title, $composer, $lyrics, $cover_photo, $video_link, $category_id, $is_public);
    $stmt->execute();
    $_SESSION['message'] = "Song added!";
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Handle Edit Song
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_song'])) {
    $id = $_POST['song_id'];
    $title = $_POST['title'];
    $composer = $_POST['composer'];
    $lyrics = $_POST['lyrics'];
    $category_id = $_POST['category_id'];
    $is_public = isset($_POST['is_public']) ? 1 : 0;
    $video_link = $_POST['video_link'];
    $cover_photo = $_POST['existing_cover_photo'];

    if ($_FILES['cover_photo']['name']) {
        $target_dir = "../uploads/";
        $filename = basename($_FILES['cover_photo']['name']);
        $target_file = $target_dir . time() . "_" . $filename;
        move_uploaded_file($_FILES['cover_photo']['tmp_name'], $target_file);
        $cover_photo = $target_file;
    }

    $stmt = $conn->prepare("UPDATE songs SET title=?, composer=?, lyrics=?, cover_photo=?, video_link=?, category_id=?, is_public=? WHERE id=?");
    $stmt->bind_param("sssssisi", $title, $composer, $lyrics, $cover_photo, $video_link, $category_id, $is_public, $id);
    $stmt->execute();
    $_SESSION['message'] = "Song updated!";
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Handle Delete Song
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM songs WHERE id=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $_SESSION['message'] = "Song deleted!";
    } else {
        $_SESSION['message'] = "Error deleting song.";
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

$categories = $conn->query("SELECT * FROM categories");
$songs_result = $conn->query("SELECT s.*, c.name AS category_name FROM songs s LEFT JOIN categories c ON s.category_id = c.id ORDER BY s.id DESC");
$songs = [];
while($row = $songs_result->fetch_assoc()) {
    $songs[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Manage Songs</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f5f5f5;
            color: #222;
        }
        h2 {
            text-align: center;
            margin-bottom: 1rem;
        }
        .button {
            background: #4CAF50;
            color: white;
            padding: 10px 16px;
            border: none;
            cursor: pointer;
            margin-bottom: 20px;
            border-radius: 5px;
            font-size: 1rem;
        }
        .button:hover {
            background: #45a049;
        }
        a {
            color: #0066cc;
            text-decoration: none;
            margin-left: 20px;
            font-weight: 600;
        }
        a:hover {
            text-decoration: underline;
        }

        .message {
            background: #dff0d8;
            padding: 12px;
            border: 1px solid #3c763d;
            color: #3c763d;
            margin-bottom: 20px;
            border-radius: 6px;
            text-align: center;
            font-weight: 600;
        }

        table.songs-table {
            width: 100%;
            border-collapse: collapse;
            box-shadow: 0 0 8px rgba(0,0,0,0.1);
            background: #fff;
            border-radius: 6px;
            overflow: hidden;
        }

        table.songs-table thead {
            background-color: #4CAF50;
            color: white;
        }

        table.songs-table th,
        table.songs-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #ddd;
            vertical-align: middle;
            text-align: left;
            font-size: 0.95rem;
        }

        table.songs-table tbody tr:hover {
            background-color: #f0f7ff;
        }

        .cover-cell img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 6px;
        }

        .no-cover {
            color: #888;
            font-style: italic;
            font-size: 0.9rem;
        }

        .lyrics-snippet {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .public-label {
            color: #2e7d32;
            font-weight: 600;
        }

        .private-label {
            color: #c62828;
            font-weight: 600;
        }

        .btn-edit, .btn-delete {
            background-color: #007bff;
            border: none;
            color: white;
            padding: 6px 12px;
            margin: 0 2px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: background-color 0.3s ease;
        }

        .btn-delete {
            background-color: #d32f2f;
        }

        .btn-edit:hover {
            background-color: #0056b3;
        }

        .btn-delete:hover {
            background-color: #9a1b1b;
        }

        @media (max-width: 720px) {
            table.songs-table th, table.songs-table td {
                font-size: 0.85rem;
                padding: 8px 10px;
            }
            .lyrics-snippet {
                max-width: 140px;
            }
        }

        /* Modal Styles (keep your existing style or tweak) */
        .modal {
            display: none;
            position: fixed;
            z-index: 10;
            left: 0; top: 0;
            width: 100%; height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background: #fff;
            margin: 5% auto;
            padding: 20px;
            width: 90%;
            max-width: 600px;
            position: relative;
            border-radius: 6px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .close {
            position: absolute;
            top: 10px; right: 20px;
            font-size: 28px;
            cursor: pointer;
            font-weight: bold;
            color: #666;
            transition: color 0.3s ease;
        }
        .close:hover {
            color: #000;
        }
        input, textarea, select {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 1rem;
            box-sizing: border-box;
            margin-top: 8px;
            margin-bottom: 15px;
        }
        .checkbox-label {
            display: inline-flex;
            align-items: center;
            font-size: 1rem;
            gap: 6px;
            cursor: pointer;
            margin-top: 0;
        }
        .checkbox-label input[type="checkbox"] {
            margin: 0;
            width: 18px;
            height: 18px;
        }
        .logout-button {
    background-color: #d32f2f;
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 8px 14px;
    font-size: 1rem;
    border-radius: 5px;
    border: none;
    cursor: pointer;
    color: white;
    box-shadow: 0 2px 6px rgba(211, 47, 47, 0.5);
    transition: background-color 0.3s ease;
}

.logout-button:hover {
    background-color: #9a1b1b;
}

    </style>
</head>
<body>

<?php if (isset($_SESSION['message'])): ?>
    <div class="message"><?= $_SESSION['message'] ?></div>
    <script>setTimeout(() => document.querySelector('.message')?.remove(), 3000);</script>
    <?php unset($_SESSION['message']); ?>
<?php endif; ?>

<h2>Manage Songs</h2>

<button class="button" onclick="openAddModal()">+ Add New Song</button>
<a href="manage_category.php">Go to Categories</a>

<table class="songs-table">
    <thead>
        <tr>
            <th>Cover</th>
            <th>Title</th>
            <th>Composer</th>
            <th>Category</th>
            <!-- <th>Lyrics Snippet</th> -->
            <!-- <th>Video</th> -->
            <th>Visibility</th>
            <th>Uploaded On</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($songs as $song): ?>
        <tr>
            <td class="cover-cell">
                <?php if ($song['cover_photo']): ?>
                    <img src="<?= htmlspecialchars($song['cover_photo']) ?>" alt="Cover for <?= htmlspecialchars($song['title']) ?>" />
                <?php else: ?>
                    <span class="no-cover">No Image</span>
                <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($song['title']) ?></td>
            <td><?= htmlspecialchars($song['composer']) ?></td>
            <td><?= htmlspecialchars($song['category_name']) ?></td>
            <!-- <td class="lyrics-snippet"><?= htmlspecialchars(mb_substr($song['lyrics'], 0, 60)) ?><?= mb_strlen($song['lyrics']) > 60 ? '…' : '' ?></td> -->
            
            <td>
                <?= $song['is_public'] ? '<span class="public-label">Public</span>' : '<span class="private-label">Private</span>' ?>
            </td>
            <td><?= date('Y-m-d', strtotime($song['uploaded_at'])) ?></td>
            <td>
                <button class="btn-edit" data-song='<?= htmlspecialchars(json_encode($song), ENT_QUOTES, 'UTF-8') ?>' onclick="openEditModalFromAttr(this)">Edit</button>
                <a href="?delete=<?= $song['id'] ?>" class="btn-delete" onclick="return confirmDelete()">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<button class="button logout-button" onclick="window.location.href='../logout.php'">Logout</button>


<!-- ADD MODAL -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('addModal')">&times;</span>
        <h3>Add Song</h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="add_song" value="1">
            <input type="text" name="title" placeholder="Title" required>
            <input type="text" name="composer" placeholder="Composer" required>
            <textarea name="lyrics" rows="5" placeholder="Lyrics" required></textarea>
            <input type="file" name="cover_photo" accept="image/*">
            <input type="text" name="video_link" placeholder="Video Link">
            <select name="category_id" required>
                <option value="">-- Select Category --</option>
                <?php $categories->data_seek(0); while ($cat = $categories->fetch_assoc()): ?>
                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                <?php endwhile; ?>
            </select>
            <label class="checkbox-label">
                <input type="checkbox" name="is_public" checked> <span>Make Public</span>
            </label>
            <button class="button" type="submit">Submit</button>
        </form>
    </div>
</div>

<!-- EDIT MODAL -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('editModal')">&times;</span>
        <h3>Edit Song</h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="edit_song" value="1">
            <input type="hidden" name="song_id" id="edit_id">
            <input type="hidden" name="existing_cover_photo" id="edit_existing_cover">
            <input type="text" name="title" id="edit_title" required>
            <input type="text" name="composer" id="edit_composer" required>
            <textarea name="lyrics" id="edit_lyrics" rows="5" required></textarea>
            <input type="file" name="cover_photo" accept="image/*">
            <input type="text" name="video_link" id="edit_video">
            <select name="category_id" id="edit_category" required>
                <option value="">-- Select Category --</option>
                <?php $categories->data_seek(0); while ($cat = $categories->fetch_assoc()): ?>
                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                <?php endwhile; ?>
            </select>
            <label class="checkbox-label">
                <input type="checkbox" name="is_public" id="edit_is_public"> <span>Make Public</span>
            </label>
            <button class="button" type="submit">Update</button>
        </form>
        
    </div>
    
</div>

<script>
function openAddModal() {
    document.getElementById('addModal').style.display = 'block';
}
function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}
window.onclick = function(event) {
    ['addModal', 'editModal'].forEach(id => {
        let modal = document.getElementById(id);
        if (event.target === modal) modal.style.display = 'none';
    });
}

function openEditModalFromAttr(button) {
    let song = JSON.parse(button.getAttribute('data-song'));
    document.getElementById('edit_id').value = song.id;
    document.getElementById('edit_title').value = song.title;
    document.getElementById('edit_composer').value = song.composer;
    document.getElementById('edit_lyrics').value = song.lyrics;
    document.getElementById('edit_existing_cover').value = song.cover_photo;
    document.getElementById('edit_video').value = song.video_link || '';
    document.getElementById('edit_category').value = song.category_id;
    document.getElementById('edit_is_public').checked = song.is_public == 1;
    document.getElementById('editModal').style.display = 'block';
}

function confirmDelete() {
    return confirm('Are you sure you want to delete this song?');
}
</script>

</body>
</html>
