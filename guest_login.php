<?php
session_start();
require_once 'db_connect.php';
include 'session_helper.php';

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nickname = trim($_POST['nickname']);
    
    if (!empty($nickname)) {
        // Check if username already exists
        $check_stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE username = ?");
        $check_stmt->bind_param("s", $nickname);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row['count'] > 0) {
            // Username already exists
            $error_message = "This username is already taken. Please choose a different one.";
        } else {
            // Insert guest into DB
            $stmt = $conn->prepare("INSERT INTO users (username, is_guest) VALUES (?, 1)");
            $stmt->bind_param("s", $nickname);
            
            if ($stmt->execute()) {
                $user_id = $stmt->insert_id;
                
                // Set session
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $nickname;
                $_SESSION['is_guest'] = true;
                
                // Redirect to home.php
                header("Location: home.php");
                exit();
            } else {
                $error_message = "Registration failed. Please try again.";
            }
        }
    } else {
        $error_message = "Username cannot be empty.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Guest Registration</title>
    <style>
        :root {
            --grande-purple: #6a0dad;
            --dark-purple: #4b0082;
            --dark-blue: #00008b;
            --light-purple: #9370db;
            --off-white: #f8f8ff;
            --dark-gray: #1a1a1a;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, var(--dark-blue), var(--dark-purple), var(--dark-gray));
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
        }
        
        @keyframes gradient {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }
        
        .container {
            background-color: rgba(26, 26, 26, 0.8);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
            width: 450px;
            padding: 40px;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(106, 13, 173, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .container:before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(to bottom right, 
                transparent, 
                transparent, 
                transparent, 
                var(--light-purple), 
                transparent, 
                transparent, 
                transparent);
            transform: rotate(45deg);
            animation: shine 3s infinite;
            z-index: -1;
        }
        
        @keyframes shine {
            0% {
                left: -50%;
                top: -50%;
            }
            100% {
                left: 150%;
                top: 150%;
            }
        }
        
        .logo {
            text-align: center;
            margin-bottom: 20px;
            font-size: 28px;
            color: var(--off-white);
        }
        
        .logo span {
            color: var(--grande-purple);
            font-weight: bold;
        }
        
        h1 {
            color: var(--off-white);
            text-align: center;
            margin-bottom: 30px;
            font-size: 24px;
            font-weight: 500;
        }
        
        form {
            display: flex;
            flex-direction: column;
        }
        
        .form-group {
            position: relative;
            margin-bottom: 25px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: var(--off-white);
            font-size: 16px;
            font-weight: 500;
        }
        
        input {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 10px;
            background-color: rgba(248, 248, 255, 0.1);
            color: var(--off-white);
            font-size: 16px;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        input:focus {
            outline: none;
            border-color: var(--grande-purple);
            background-color: rgba(248, 248, 255, 0.15);
        }
        
        input::placeholder {
            color: rgba(248, 248, 255, 0.5);
        }
        
        .form-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        
        .btn {
            background: linear-gradient(45deg, var(--grande-purple), var(--dark-blue));
            color: var(--off-white);
            border: none;
            border-radius: 10px;
            padding: 12px 20px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            flex: 1;
        }
        
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        
        .btn-secondary {
            background: rgba(248, 248, 255, 0.1);
            margin-left: 10px;
            border: 1px solid rgba(248, 248, 255, 0.2);
        }
        
        .btn-secondary:hover {
            background: rgba(248, 248, 255, 0.2);
        }
        
        .emoji-decorations {
            position: absolute;
            color: rgba(248, 248, 255, 0.15);
            font-size: 24px;
            z-index: -1;
        }
        
        .emoji-1 {
            top: 20px;
            left: 30px;
            animation: float 4s ease-in-out infinite;
        }
        
        .emoji-2 {
            bottom: 30px;
            right: 20px;
            animation: float 3.5s ease-in-out infinite;
            animation-delay: 0.5s;
        }
        
        .emoji-3 {
            top: 50%;
            right: 30px;
            animation: float 5s ease-in-out infinite;
            animation-delay: 1s;
        }
        
        .emoji-4 {
            bottom: 50px;
            left: 40px;
            animation: float 4.5s ease-in-out infinite;
            animation-delay: 1.5s;
        }
        
        @keyframes float {
            0% {
                transform: translateY(0) rotate(0deg);
            }
            50% {
                transform: translateY(-10px) rotate(5deg);
            }
            100% {
                transform: translateY(0) rotate(0deg);
            }
        }
        
        .error-message {
            background-color: rgba(255, 0, 0, 0.2);
            border-left: 4px solid #ff3333;
            color: var(--off-white);
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-size: 14px;
        }
        
        p {
            text-align: center;
            color: var(--off-white);
            margin-top: 25px;
            font-size: 15px;
        }
        
        p a {
            color: var(--light-purple);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        p a:hover {
            color: var(--grande-purple);
            text-decoration: underline;
        }
        
        .sparkles {
            position: absolute;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }
        
        .sparkle {
            position: absolute;
            background: rgba(248, 248, 255, 0.5);
            border-radius: 50%;
            width: 3px;
            height: 3px;
            animation: sparkle-fade 2s infinite;
        }
        
        @keyframes sparkle-fade {
            0% {
                opacity: 0;
                transform: scale(0);
            }
            50% {
                opacity: 1;
                transform: scale(1);
            }
            100% {
                opacity: 0;
                transform: scale(0);
            }
        }
        
        .shape {
            position: absolute;
            z-index: -1;
            opacity: 0.1;
        }
        
        .circle {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: var(--grande-purple);
            top: -30px;
            left: -30px;
        }
        
        .rectangle {
            width: 120px;
            height: 40px;
            background: var(--dark-blue);
            bottom: -10px;
            right: -20px;
            transform: rotate(30deg);
        }
        
        .guest-icon {
            font-size: 32px;
            margin-right: 10px;
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="shape circle"></div>
        <div class="shape rectangle"></div>
        
        <div class="emoji-decorations emoji-1">✨</div>
        <div class="emoji-decorations emoji-2">🎮</div>
        <div class="emoji-decorations emoji-3">🎯</div>
        <div class="emoji-decorations emoji-4">🏆</div>
        
        <div class="sparkles">
            <?php for($i = 0; $i < 20; $i++): ?>
                <div class="sparkle" style="top: <?php echo rand(0, 100); ?>%; left: <?php echo rand(0, 100); ?>%; animation-delay: <?php echo $i * 0.1; ?>s;"></div>
            <?php endfor; ?>
        </div>
        
        <div class="logo"><span>Quiz</span>Master</div>
        <h1><span class="guest-icon">👤</span> Guest Registration</h1>
        
        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label for="nickname">Choose a nickname:</label>
                <input type="text" id="nickname" name="nickname" value="<?php echo isset($nickname) ? htmlspecialchars($nickname) : ''; ?>" placeholder="Enter your nickname" required>
            </div>
            
            <div class="form-actions">
                <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                <button type="submit" class="btn">Continue as Guest</button>
            </div>
        </form>
        
        <p>Join as a guest to participate in quizzes!</p>
    </div>
</body>
</html>
