<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php';

$message = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

// Handle add category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'add') {
    $name = trim($_POST['name']);
    if ($name !== '') {
        $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $stmt->close();
        $_SESSION['message'] = "Category added!";
    }
    header("Location: manage_category.php");
    exit;
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM categories WHERE id = $id");
    $_SESSION['message'] = "Category deleted!";
    header("Location: manage_category.php");
    exit;
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'edit') {
    $id = intval($_POST['id']);
    $newName = trim($_POST['new_name']);
    if ($newName !== '') {
        $stmt = $conn->prepare("UPDATE categories SET name = ? WHERE id = ?");
        $stmt->bind_param("si", $newName, $id);
        $stmt->execute();
        $stmt->close();
        $_SESSION['message'] = "Category updated!";
    }
    header("Location: manage_category.php");
    exit;
}

$categories = $conn->query("SELECT * FROM categories ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Manage Categories</title>
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
        font-weight: 600;
        transition: background-color 0.3s ease;
    }
    .button:hover {
        background: #45a049;
    }
    a {
        color: #0066cc;
        text-decoration: none;
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
    table.categories-table {
        width: 100%;
        border-collapse: collapse;
        box-shadow: 0 0 8px rgba(0,0,0,0.1);
        background: #fff;
        border-radius: 6px;
        overflow: hidden;
        margin-bottom: 20px;
    }
    table.categories-table thead {
        background-color: #4CAF50;
        color: white;
    }
    table.categories-table th,
    table.categories-table td {
        padding: 12px 15px;
        border-bottom: 1px solid #ddd;
        vertical-align: middle;
        text-align: left;
        font-size: 0.95rem;
    }
    table.categories-table tbody tr:hover {
        background-color: #f0f7ff;
    }
    .btn-edit, .btn-delete {
        padding: 7px 14px;
        border-radius: 5px;
        font-weight: 600;
        cursor: pointer;
        border: none;
        transition: background-color 0.3s ease;
        text-decoration: none;
        display: inline-block;
        font-size: 0.9rem;
    }
    .btn-edit {
        background-color: #007bff;
        color: white;
        margin-right: 8px;
    }
    .btn-edit:hover {
        background-color: #0056b3;
    }
    .btn-delete {
        background-color: #d32f2f;
        color: white;
    }
    .btn-delete:hover {
        background-color: #9a1b1b;
        color: white;
        text-decoration: none;
    }
    .back-link {
        display: inline-block;
        color: #0066cc;
        font-weight: 600;
        text-decoration: none;
    }
    .back-link:hover {
        text-decoration: underline;
    }
    form.add-category {
        max-width: 600px;
        margin: 0 auto 30px auto;
        display: flex;
        gap: 10px;
    }
    form.add-category input[type="text"] {
        flex-grow: 1;
        padding: 10px;
        border-radius: 5px;
        border: 1px solid #ccc;
        font-size: 1rem;
    }
    @media (max-width: 720px) {
        table.categories-table th,
        table.categories-table td {
            font-size: 0.85rem;
            padding: 8px 10px;
        }
        form.add-category {
            flex-direction: column;
        }
        form.add-category input[type="text"],
        form.add-category button {
            width: 100%;
        }
        .btn-edit, .btn-delete {
            width: 100%;
            margin-bottom: 6px;
            text-align: center;
        }
    }

    /* Modal styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0,0,0,0.5);
        align-items: center;
        justify-content: center;
        padding: 15px;
    }
    .modal-content {
        background-color: #fff;
        margin: auto;
        padding: 20px;
        border-radius: 8px;
        max-width: 400px;
        width: 100%;
        box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        position: relative;
        animation: fadeInScale 0.3s ease forwards;
    }
    @keyframes fadeInScale {
        from {opacity: 0; transform: scale(0.8);}
        to {opacity: 1; transform: scale(1);}
    }
    .modal-close {
        position: absolute;
        right: 15px;
        top: 15px;
        font-size: 1.5rem;
        font-weight: bold;
        color: #333;
        cursor: pointer;
        border: none;
        background: none;
    }
    .modal-close:hover {
        color: #007bff;
    }
    .modal h3 {
        margin-top: 0;
        margin-bottom: 15px;
        text-align: center;
    }
    .modal form input[type="text"] {
        width: 100%;
        padding: 10px;
        font-size: 1rem;
        border-radius: 5px;
        border: 1px solid #ccc;
        margin-bottom: 20px;
        box-sizing: border-box;
    }
    .modal form button {
        background-color: #007bff;
        border: none;
        color: white;
        padding: 10px 16px;
        border-radius: 5px;
        cursor: pointer;
        width: 100%;
        font-weight: 600;
        font-size: 1rem;
        transition: background-color 0.3s ease;
    }
    .modal form button:hover {
        background-color: #0056b3;
    }
</style>
</head>
<body>

<h2>Manage Categories</h2>

<?php if ($message): ?>
    <div class="message"><?= htmlspecialchars($message) ?></div>
    <script>setTimeout(() => document.querySelector('.message')?.remove(), 3000);</script>
<?php endif; ?>

<form method="POST" class="add-category" autocomplete="off">
    <input type="text" name="name" placeholder="New category name" required>
    <input type="hidden" name="action" value="add">
    <button type="submit" class="button">Add</button>
</form>

<table class="categories-table">
    <thead>
        <tr>
            <th style="width: 60px;">ID</th>
            <th>Category Name</th>
            <th style="width: 180px;">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $categories->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td>
                <button class="btn-edit" data-id="<?= $row['id'] ?>" data-name="<?= htmlspecialchars($row['name'], ENT_QUOTES) ?>">Edit</button>
                <a href="?delete=<?= $row['id'] ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this category?')">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<a href="add-song.php" class="back-link">&larr; Go to Songs</a>

<!-- Modal -->
<div id="editModal" class="modal" aria-hidden="true" role="dialog" aria-labelledby="modalTitle" aria-modal="true">
    <div class="modal-content">
        <button class="modal-close" aria-label="Close modal">&times;</button>
        <h3 id="modalTitle">Edit Category</h3>
        <form id="editForm" method="POST" onsubmit="return validateModalCategoryName()">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="modalCategoryId">
            <input type="text" name="new_name" id="modalCategoryName" required autocomplete="off" placeholder="Category name">
            <button type="submit">Update</button>
        </form>
    </div>
</div>

<script>
// Modal functionality
const modal = document.getElementById('editModal');
const modalCloseBtn = modal.querySelector('.modal-close');
const modalCategoryId = document.getElementById('modalCategoryId');
const modalCategoryName = document.getElementById('modalCategoryName');
const editForm = document.getElementById('editForm');

document.querySelectorAll('.btn-edit').forEach(button => {
    button.addEventListener('click', () => {
        const id = button.getAttribute('data-id');
        const name = button.getAttribute('data-name');
        modalCategoryId.value = id;
        modalCategoryName.value = name;
        modal.style.display = 'flex';
        modalCategoryName.focus();
        modal.setAttribute('aria-hidden', 'false');
    });
});

modalCloseBtn.addEventListener('click', () => {
    closeModal();
});

window.addEventListener('click', e => {
    if (e.target === modal) {
        closeModal();
    }
});

window.addEventListener('keydown', e => {
    if (e.key === 'Escape' && modal.style.display === 'flex') {
        closeModal();
    }
});

function closeModal() {
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
    modalCategoryId.value = '';
    modalCategoryName.value = '';
}

function validateModalCategoryName() {
    const name = modalCategoryName.value.trim();
    if (!name) {
        alert('Category name cannot be empty.');
        modalCategoryName.focus();
        return false;
    }
    return true;
}
</script>

</body>
</html>
