<?php
require_once '../config/database.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('../login.php');
}

$user_id = $_SESSION['user_id'];
$category = isset($_GET['category']) ? $_GET['category'] : 'all';

// Get all published courses
if ($category == 'all') {
    $stmt = $pdo->query("SELECT c.*, 
                         (SELECT COUNT(*) FROM lessons WHERE course_id = c.id) as lesson_count,
                         (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id AND user_id = $user_id) as is_enrolled
                         FROM courses c WHERE is_published = 1");
} else {
    $stmt = $pdo->prepare("SELECT c.*,
                          (SELECT COUNT(*) FROM lessons WHERE course_id = c.id) as lesson_count,
                          (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id AND user_id = $user_id) as is_enrolled
                          FROM courses c WHERE is_published = 1 AND category = ?");
    $stmt->execute([$category]);
}
$courses = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courses - Skillify</title>
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
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
        }
        
        .nav-links {
            display: flex;
            gap: 30px;
            align-items: center;
        }
        
        .nav-links a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .nav-links a:hover {
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
        
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }
        
        .page-header h1 {
            font-size: 32px;
            color: #333;
        }
        
        .category-filters {
            display: flex;
            gap: 15px;
        }
        
        .filter-btn {
            padding: 10px 20px;
            border: 2px solid #e1e1e1;
            background: white;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
            text-decoration: none;
            color: #666;
        }
        
        .filter-btn:hover {
            border-color: #667eea;
            color: #667eea;
        }
        
        .filter-btn.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        
        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 30px;
        }
        
        .course-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .course-thumbnail {
            height: 180px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 48px;
        }
        
        .course-content {
            padding: 25px;
        }
        
        .course-category {
            display: inline-block;
            padding: 4px 12px;
            background: #e0e7ff;
            color: #667eea;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 15px;
        }
        
        .course-title {
            font-size: 20px;
            margin-bottom: 10px;
            color: #333;
        }
        
        .course-description {
            color: #666;
            line-height: 1.6;
            margin-bottom: 20px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .course-meta {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            color: #888;
            font-size: 14px;
        }
        
        .course-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .course-level {
            display: inline-block;
            padding: 4px 12px;
            background: #f0f0f0;
            border-radius: 15px;
            font-size: 12px;
            color: #666;
        }
        
        .btn-enroll {
            display: inline-block;
            padding: 12px 30px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: background 0.3s;
            border: none;
            cursor: pointer;
            width: 100%;
            text-align: center;
        }
        
        .btn-enroll:hover {
            background: #5a67d8;
        }
        
        .btn-continue {
            background: #48bb78;
        }
        
        .btn-continue:hover {
            background: #38a169;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px;
            background: white;
            border-radius: 15px;
            color: #666;
        }
        
        .empty-state h3 {
            font-size: 24px;
            margin-bottom: 15px;
            color: #333;
        }
        
        .enrolled-badge {
            background: #48bb78;
            color: white;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo">Skillify</div>
        <div class="nav-links">
            <a href="user_dashboard.php">Dashboard</a>
            <a href="courses.php" style="color: #667eea;">Courses</a>
            <a href="ai_tutor.php">AI Tutor</a>
        </div>
        <div class="user-menu">
            <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
            <a href="../logout.php" class="logout-btn">Logout</a>
        </div>
    </nav>
    
    <div class="container">
        <div class="page-header">
            <h1>Explore Courses</h1>
            <div class="category-filters">
                <a href="?category=all" class="filter-btn <?php echo $category == 'all' ? 'active' : ''; ?>">All</a>
                <a href="?category=digital_literacy" class="filter-btn <?php echo $category == 'digital_literacy' ? 'active' : ''; ?>">Digital Literacy</a>
                <a href="?category=ai_literacy" class="filter-btn <?php echo $category == 'ai_literacy' ? 'active' : ''; ?>">AI Literacy</a>
            </div>
        </div>
        
        <?php if (count($courses) > 0): ?>
            <div class="courses-grid">
                <?php foreach ($courses as $course): ?>
                    <div class="course-card">
                        <div class="course-thumbnail">
                            <?php if ($course['category'] == 'digital_literacy'): ?>
                                💻
                            <?php else: ?>
                                🤖
                            <?php endif; ?>
                        </div>
                        <div class="course-content">
                            <span class="course-category">
                                <?php echo $course['category'] == 'digital_literacy' ? 'Digital Literacy' : 'AI Literacy'; ?>
                            </span>
                            <h3 class="course-title">
                                <?php echo htmlspecialchars($course['title']); ?>
                                <?php if ($course['is_enrolled'] > 0): ?>
                                    <span class="enrolled-badge">Enrolled</span>
                                <?php endif; ?>
                            </h3>
                            <p class="course-description"><?php echo htmlspecialchars($course['description']); ?></p>
                            <div class="course-meta">
                                <span>📚 <?php echo $course['lesson_count']; ?> lessons</span>
                                <span>⏱️ <?php echo $course['duration_hours']; ?> hours</span>
                            </div>
                            <span class="course-level"><?php echo ucfirst($course['level']); ?></span>
                            
                            <?php if ($course['is_enrolled'] > 0): ?>
                                <a href="course_view.php?id=<?php echo $course['id']; ?>" class="btn-enroll btn-continue" style="margin-top: 20px;">Continue Learning</a>
                            <?php else: ?>
                                <form method="POST" action="enroll.php" style="margin-top: 20px;">
                                    <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                    <button type="submit" class="btn-enroll">Enroll Now</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h3>No courses found</h3>
                <p>Check back later for new courses in this category!</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>