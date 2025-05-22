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
$songs = $conn->query("SELECT s.*, c.name AS category FROM songs s LEFT JOIN categories c ON s.category_id = c.id ORDER BY s.id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Songs</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f5f5f5;
        }
        h2 {
            text-align: center;
        }
        .button {
            background: #4CAF50;
            color: white;
            padding: 8px 14px;
            border: none;
            cursor: pointer;
            margin: 5px;
            border-radius: 4px;
        }
        .button.edit { background: #2196F3; }
        .button.delete { background: #f44336; }
        table {
            width: 100%;
            background: white;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
        }
        th {
            background: #eee;
        }
        img { width: 60px; }
        .message {
            background: #dff0d8;
            padding: 10px;
            border: 1px solid #3c763d;
            color: #3c763d;
            margin-bottom: 15px;
            border-radius: 5px;
        }

        /* Modal */
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
        }
        .close {
            position: absolute;
            top: 10px; right: 20px;
            font-size: 24px;
            cursor: pointer;
        }
        input, textarea, select {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 16px;
            box-sizing: border-box;
            margin-top: 6px;
          }
         .checkbox-label {
            display: inline-flex;
            align-items: center;
            font-size: 16px;
            margin-top: 10px;
            cursor: pointer;
            gap: 2px; /* reduce this to make them closer */
        }

        .checkbox-label input[type="checkbox"] {
            margin: 0;
        }



    </style>
</head>
<body>

<?php if (isset($_SESSION['message'])): ?>
    <div class="message"><?= $_SESSION['message'] ?></div>
    <script>
        setTimeout(() => document.querySelector('.message')?.remove(), 3000);
    </script>
    <?php unset($_SESSION['message']); ?>
<?php endif; ?>

<h2>Add Songs</h2>

<button class="button" onclick="openAddModal()">+ Add New Song</button>
<p><a href="dashboard.php">← Back to Dashboard</a></p>

<table>
    <thead>
        <tr>
            <th>Title</th><th>Composer</th><th>Category</th><th>Cover</th><th>Video</th><th>Public</th><th>Date uploaded</th><th>Actions</th> 
        </tr>
    </thead>
    <tbody>
        <?php while ($song = $songs->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($song['title']) ?></td>
            <td><?= htmlspecialchars($song['composer']) ?></td>
            <td><?= htmlspecialchars($song['category']) ?></td>
            <td><?php if ($song['cover_photo']): ?><img src="<?= $song['cover_photo'] ?>" /><?php endif; ?></td>
            <td><a href="<?= htmlspecialchars($song['video_link']) ?>" target="_blank">Link</a></td>
            <td><?= $song['is_public'] ? 'Yes' : 'No' ?></td>
            <td><?= date('Y-m-d H:i', strtotime($song['uploaded_at'])) ?></td>
            <td>
                <button class="button edit" data-song='<?= htmlspecialchars(json_encode($song), ENT_QUOTES, 'UTF-8') ?>' onclick="openEditModalFromAttr(this)">Edit</button>

                <a href="?delete=<?= $song['id'] ?>" class="button delete" onclick="return confirmDelete()">Delete</a>
            </td>

        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

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



            <button class="button">Submit</button>
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
    <input type="checkbox" id="edit_public" name="is_public"> <span>Make Public</span>
</label>

            <button class="button">Update</button>
        </form>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('addModal').style.display = 'block';
}
function openEditModal(data) {
    document.getElementById('edit_id').value = data.id;
    document.getElementById('edit_title').value = data.title;
    document.getElementById('edit_composer').value = data.composer;
    document.getElementById('edit_lyrics').value = data.lyrics;
    document.getElementById('edit_video').value = data.video_link;
    document.getElementById('edit_existing_cover').value = data.cover_photo;
    document.getElementById('edit_category').value = data.category_id;
    document.getElementById('edit_public').checked = data.is_public == 1;

    document.getElementById('editModal').style.display = 'block';
}
function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}
window.onclick = function(event) {
    ['addModal', 'editModal'].forEach(id => {
        const modal = document.getElementById(id);
        if (event.target == modal) modal.style.display = "none";
    });
}
function confirmDelete() {
    return confirm('Are you sure you want to delete this song?');
}
function openEditModalFromAttr(button) {
    const data = JSON.parse(button.getAttribute('data-song'));
    openEditModal(data);
}

</script>

</body>
</html>
