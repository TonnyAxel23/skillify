<?php 
require_once '../config/database.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('../login.php');
}

// Get user data
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Skillify</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: #f5f7fb;
        }
        
        .navbar {
            background: white;
            padding: 20px 40px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
        }
        
        .user-menu {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .user-name {
            font-weight: 500;
            color: #333;
        }
        
        .logout-btn {
            padding: 8px 16px;
            background: #f0f0f0;
            color: #333;
            text-decoration: none;
            border-radius: 8px;
            transition: background 0.3s;
        }
        
        .logout-btn:hover {
            background: #e0e0e0;
        }
        
        .dashboard-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .welcome-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            border-radius: 20px;
            margin-bottom: 40px;
        }
        
        .welcome-section h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .welcome-section p {
            font-size: 18px;
            opacity: 0.9;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            text-align: center;
        }
        
        .stat-card h3 {
            color: #667eea;
            font-size: 36px;
            margin-bottom: 10px;
        }
        
        .stat-card p {
            color: #666;
            font-size: 16px;
        }
        
        .courses-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
        }
        
        .course-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .course-card h3 {
            color: #333;
            margin-bottom: 15px;
            font-size: 20px;
        }
        
        .course-card p {
            color: #666;
            margin-bottom: 20px;
            line-height: 1.6;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #5a67d8;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo">Skillify</div>
        <div class="user-menu">
            <span class="user-name">Welcome, <?php echo htmlspecialchars($user['full_name']); ?>!</span>
            <a href="../logout.php" class="logout-btn">Logout</a>
        </div>
    </nav>
    
    <div class="dashboard-container">
        <div class="welcome-section">
            <h1>Welcome back, <?php echo htmlspecialchars($user['full_name']); ?>! 👋</h1>
            <p>Continue your learning journey with Skillify</p>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>0</h3>
                <p>Courses Completed</p>
            </div>
            <div class="stat-card">
                <h3>0</h3>
                <p>Certificates Earned</p>
            </div>
            <div class="stat-card">
                <h3>2</h3>
                <p>Available Courses</p>
            </div>
        </div>
        
        <h2 style="margin-bottom: 20px; color: #333;">Continue Learning</h2>
        
        <div class="courses-grid">
            <div class="course-card">
                <h3>Digital Literacy Fundamentals</h3>
                <p>Learn essential computer skills, internet navigation, and digital productivity tools.</p>
                <a href="#" class="btn">Start Course</a>
            </div>
            
            <div class="course-card">
                <h3>Introduction to AI</h3>
                <p>Understand artificial intelligence basics and learn to use AI tools effectively.</p>
                <a href="#" class="btn">Start Course</a>
            </div>
        </div>
    </div>
</body>
</html>
