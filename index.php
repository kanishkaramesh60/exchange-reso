<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Book Exchange Platform</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Full page setup */
        body, html {
            height: 100%;
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
        }

        /* Center content */
        .center-container {
            display: flex;
            flex-direction: column;
            justify-content: center; /* vertical center */
            align-items: center;     /* horizontal center */
            height: 100%;
            text-align: center;
        }

        h1 {
            color: #333;
            margin-bottom: 20px;
        }

        p {
            font-size: 18px;
            color: #555;
            margin-bottom: 40px;
        }

        .button {
            display: inline-block;
            padding: 15px 30px;
            margin: 10px;
            font-size: 18px;
            cursor: pointer;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome to Book/Resource Exchange Platform</h1>
        <p>Please select your login type:</p>
        <a class="button" href="login.php">User Login</a>
        <a class="button" href="admin_login.php">Admin Login</a>
    </div>
</body>
</html>
