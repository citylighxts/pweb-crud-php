<?php
require_once 'database.php';

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['nrp'])) {
    $nrpToDelete = $_GET['nrp'];
    
    $deleteStmt = $conn->prepare("DELETE FROM registrants WHERE nrp = ?");
    $deleteStmt->bind_param("s", $nrpToDelete);
    
    if ($deleteStmt->execute()) {
        $deleteMessage = "Registrant deleted successfully.";
    } else {
        $deleteError = "Error deleting registrant: " . $conn->error;
    }
    $deleteStmt->close();
}

$resultsPerPage = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $resultsPerPage;

$totalRegistrantsQuery = "SELECT COUNT(*) as total FROM registrants";
$totalResult = $conn->query($totalRegistrantsQuery);
$totalRegistrants = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalRegistrants / $resultsPerPage);

$query = "SELECT * FROM registrants LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $resultsPerPage, $offset);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrants List</title>
    <style>
        body {
            font-family: 'Poppins';
            font-size: 16px;
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f8;
        }
        h1 {
            color: #a7097a;
            text-align: center;
            font-weight: 300;
            margin-bottom: 25px;
        }
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
        }
        th, td {
            border-bottom: 1px solid #e0e0e0;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #a7097a;
            color: white;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        .btn {
            padding: 6px 12px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 0.9em;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }
        .btn-edit {
            background-color: #a7097a;
            color: white;
        }
        .btn-delete {
            background-color: #d32f2f;
            color: white;
        }
        .btn:hover {
            opacity: 0.85;
            transform: translateY(-2px);
        }
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        .pagination a {
            color: #a7097a;
            padding: 8px 16px;
            text-decoration: none;
            border: 1px solid #a7097a;
            margin: 0 4px;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }
        .pagination a.active, .pagination a:hover {
            background-color: #a7097a;
            color: white;
        }
        .back-link {
            display: block;
            margin-top: 20px;
            text-align: center;
            color: #a7097a;
            text-decoration: none;
        }
    </style>
    <script>
        function confirmDelete(nrp, name) {
            if (confirm(`Are you sure you want to delete the registrant ${name} (NRP: ${nrp})?`)) {
                window.location.href = `registrants.php?action=delete&nrp=${nrp}`;
            }
        }
    </script>
</head>
<body>
    <h1>Registrants List</h1>

    <?php if (isset($deleteMessage)): ?>
        <div class="message success-message">
            <?php echo htmlspecialchars($deleteMessage); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($deleteError)): ?>
        <div class="message error-message">
            <?php echo htmlspecialchars($deleteError); ?>
        </div>
    <?php endif; ?>

    <?php if ($totalRegistrants > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>NRP</th>
                    <th>Name</th>
                    <th>Gender</th>
                    <th>Email</th>
                    <th>Phone Number</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['nrp']); ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['gender']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['phoneNumber']); ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="edit.php?nrp=<?php echo urlencode($row['nrp']); ?>" class="btn btn-edit">Edit</a>
                                <button onclick="confirmDelete('<?php echo htmlspecialchars($row['nrp']); ?>', '<?php echo htmlspecialchars($row['name']); ?>')" class="btn btn-delete">Delete</button>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?php echo $i; ?>" 
                   class="<?php echo ($page == $i ? 'active' : ''); ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php else: ?>
        <p style="text-align: center;">No registrants found.</p>
    <?php endif; ?>

    <div style="text-align: center; margin-top: 20px;">
        <a href="registerForm.php" style="background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Add New Registrant</a>
    </div>

    <a href="index.php" class="back-link">Back to Home</a>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>