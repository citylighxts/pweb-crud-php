<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration System</title>
    <style>
        body {
            font-family: 'Poppins';
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            text-align: center;
            background-color: #f4f4f8;
        }
        .button-container {
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
        }
        .button {
            padding: 12px 25px;
            background-color: #a7097a;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            letter-spacing: 0.5px;
            transition: background-color 0.3s ease, transform 0.2s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .button:hover {
            background-color: #85076c;
            transform: translateY(-2px);
        }
        h1 {
            color: #a7097a;
            font-weight: 300;
        }
    </style>
</head>
<body>
    <h1>Student Registration System</h1>
    <div class="button-container">
        <a href="registerForm.php" class="button">Register New Student</a>
        <a href="registrants.php" class="button">View Registrants</a>
    </div>
</body>
</html>