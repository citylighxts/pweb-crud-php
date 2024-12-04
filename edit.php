<?php
require_once 'database.php';

$formData = [
    'nrp' => '',
    'name' => '',
    'gender' => '',
    'email' => '',
    'phoneNumber' => ''
];
$errors = [];
$success = false;

if (!isset($_GET['nrp'])) {
    die("No NRP provided");
}

$nrp = $_GET['nrp'];

$stmt = $conn->prepare("SELECT * FROM registrants WHERE nrp = ?");
$stmt->bind_param("s", $nrp);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Registrant not found");
}

$existingData = $result->fetch_assoc();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $formData = [
        'nrp' => trim($_POST['nrp'] ?? ''),
        'name' => trim($_POST['name'] ?? ''),
        'gender' => trim($_POST['gender'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'phoneNumber' => trim($_POST['phoneNumber'] ?? '')
    ];

    if (empty($formData['name']) || strlen($formData['name']) < 2) {
        $errors[] = "Name is required and must be at least 2 characters.";
    }

    if (!in_array($formData['gender'], ['Male', 'Female'])) {
        $errors[] = "Please select a valid gender.";
    }

    if (empty($formData['email']) || !filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email address.";
    }

    if (empty($formData['phoneNumber']) || !preg_match('/^\d{10,12}$/', $formData['phoneNumber'])) {
        $errors[] = "Phone number must be 10-12 digits.";
    }

    if (empty($errors)) {
        $checkDuplicate = $conn->prepare("SELECT * FROM registrants WHERE email = ? AND nrp != ?");
        $checkDuplicate->bind_param("ss", $formData['email'], $nrp);
        $checkDuplicate->execute();
        $duplicateResult = $checkDuplicate->get_result();
        
        if ($duplicateResult->num_rows > 0) {
            $errors[] = "Email already exists in the system.";
        }
        $checkDuplicate->close();
    }

    if (empty($errors)) {
        $updateStmt = $conn->prepare("UPDATE registrants SET name = ?, gender = ?, email = ?, phoneNumber = ? WHERE nrp = ?");
        $updateStmt->bind_param("sssss", 
            $formData['name'], 
            $formData['gender'], 
            $formData['email'], 
            $formData['phoneNumber'],
            $nrp
        );

        try {
            if ($updateStmt->execute()) {
                $success = true;
                $existingData = $formData;
            } else {
                $errors[] = "Update failed. Please try again.";
            }
            $updateStmt->close();
        } catch (Exception $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Registrant</title>
    <style>
        body {
            font-family: 'Poppins';
            max-width: 500px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f8;
        }
        .error {
            color: #d32f2f;
            background-color: #ffebee;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        h2 {
            color: #a7097a;
            text-align: center;
            margin-bottom: 25px;
            font-weight: 300;
        }
        .form-group {
            margin-bottom: 18px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: bold;
        }
        input, select {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
            border: 1.5px solid #ccc;
            border-radius: 6px;
            transition: border-color 0.3s ease;
        }
        input:focus, select:focus {
            border-color: #a7097a;
            outline: none;
            box-shadow: 0 0 0 2px rgba(167, 9, 122, 0.1);
        }
        .submit-btn {
            background-color: #a7097a;
            color: white;
            padding: 12px 15px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            font-weight: bold;
            letter-spacing: 0.5px;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }
        .submit-btn:hover {
            background-color: #85076c;
            transform: translateY(-2px);
        }
        .back-link {
            display: block;
            margin-top: 20px;
            text-align: center;
            color: #a7097a;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        .success {
            display: block;
            margin-top: 20px;
            text-align: center;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <h2>Edit Registrant</h2>
    
    <?php if ($success): ?>
        <div class="success">
            Registrant updated successfully!
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!$success): ?>
        <form action="edit.php?nrp=<?php echo urlencode($nrp); ?>" method="post">
            <div class="form-group">
                <label for="nrp">NRP (cannot be changed):</label>
                <input type="text" id="nrp" name="nrp" 
                       value="<?php echo htmlspecialchars($existingData['nrp']); ?>" 
                       readonly>
            </div>
            
            <div class="form-group">
                <label for="name">Full Name:</label>
                <input type="text" id="name" name="name" 
                       value="<?php echo htmlspecialchars($existingData['name']); ?>" 
                       required minlength="2">
            </div>
            
            <div class="form-group">
                <label for="gender">Gender:</label>
                <select id="gender" name="gender" required>
                    <option value="Male" <?php echo ($existingData['gender'] == 'Male' ? 'selected' : ''); ?>>Male</option>
                    <option value="Female" <?php echo ($existingData['gender'] == 'Female' ? 'selected' : ''); ?>>Female</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" 
                       value="<?php echo htmlspecialchars($existingData['email']); ?>" 
                       required>
            </div>
            
            <div class="form-group">
                <label for="phoneNumber">Phone Number (10-12 digits):</label>
                <input type="tel" id="phoneNumber" name="phoneNumber" 
                       value="<?php echo htmlspecialchars($existingData['phoneNumber']); ?>" 
                       required pattern="\d{10,12}">
            </div>
            
            <button type="submit" class="submit-btn">Update Registrant</button>
        </form>
    <?php endif; ?>

    <a href="registrants.php" class="back-link">Back to Registrants List</a>
</body>
</html>
<?php
$conn->close();
?>