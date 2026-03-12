<?php
require_once '../config/database.php';

if (!isLoggedIn()) {
    redirect('../login.php');
}

$user_id = $_SESSION['user_id'];
$course_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$current_lesson_id = isset($_GET['lesson']) ? (int)$_GET['lesson'] : 0;

// Get course details
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ? AND is_published = 1");
$stmt->execute([$course_id]);
$course = $stmt->fetch();

if (!$course) {
    redirect('courses.php');
}

// Check if user is enrolled
$stmt = $pdo->prepare("SELECT * FROM enrollments WHERE user_id = ? AND course_id = ?");
$stmt->execute([$user_id, $course_id]);
$enrollment = $stmt->fetch();

if (!$enrollment) {
    redirect('courses.php');
}

// Get all lessons for this course
$stmt = $pdo->prepare("SELECT * FROM lessons WHERE course_id = ? ORDER BY lesson_order");
$stmt->execute([$course_id]);
$lessons = $stmt->fetchAll();

// If no lesson specified, get the first one
if ($current_lesson_id == 0 && count($lessons) > 0) {
    $current_lesson_id = $lessons[0]['id'];
}

// Get current lesson details
$current_lesson = null;
foreach ($lessons as $lesson) {
    if ($lesson['id'] == $current_lesson_id) {
        $current_lesson = $lesson;
        break;
    }
}

// Mark lesson as completed if requested
if (isset($_GET['complete']) && $current_lesson) {
    // Check if already completed
    $stmt = $pdo->prepare("SELECT id FROM user_progress WHERE user_id = ? AND course_id = ? AND lesson_id = ?");
    $stmt->execute([$user_id, $course_id, $current_lesson_id]);
    
    if (!$stmt->fetch()) {
        // Mark as completed
        $stmt = $pdo->prepare("INSERT INTO user_progress (user_id, course_id, lesson_id, completed, completed_at) VALUES (?, ?, ?, 1, NOW())");
        $stmt->execute([$user_id, $course_id, $current_lesson_id]);
        
        // Update enrollment progress
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM lessons WHERE course_id = ?");
        $stmt->execute([$course_id]);
        $total_lessons = $stmt->fetch()['total'];
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as completed FROM user_progress WHERE user_id = ? AND course_id = ? AND completed = 1");
        $stmt->execute([$user_id, $course_id]);
        $completed_lessons = $stmt->fetch()['completed'];
        
        $progress = round(($completed_lessons / $total_lessons) * 100);
        
        $stmt = $pdo->prepare("UPDATE enrollments SET progress_percentage = ? WHERE user_id = ? AND course_id = ?");
        $stmt->execute([$progress, $user_id, $course_id]);
        
        // Check if course is completed
        if ($progress == 100) {
            $stmt = $pdo->prepare("UPDATE enrollments SET completed_at = NOW() WHERE user_id = ? AND course_id = ?");
            $stmt->execute([$user_id, $course_id]);
        }
    }
    
    // Find next lesson
    $next_lesson = null;
    $found = false;
    foreach ($lessons as $lesson) {
        if ($found) {
            $next_lesson = $lesson;
            break;
        }
        if ($lesson['id'] == $current_lesson_id) {
            $found = true;
        }
    }
    
    if ($next_lesson) {
        redirect("course_view.php?id=$course_id&lesson=" . $next_lesson['id']);
    } else {
        redirect("course_view.php?id=$course_id&completed=1");
    }
}

// Get completed lessons
$stmt = $pdo->prepare("SELECT lesson_id FROM user_progress WHERE user_id = ? AND course_id = ? AND completed = 1");
$stmt->execute([$user_id, $course_id]);
$completed_lessons = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($course['title']); ?> - Skillify</title>
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
        
        .nav-links a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            margin-left: 30px;
        }
        
        .course-container {
            display: flex;
            min-height: calc(100vh - 80px);
        }
        
        .sidebar {
            width: 350px;
            background: white;
            border-right: 1px solid #e1e1e1;
            padding: 30px;
            overflow-y: auto;
        }
        
        .sidebar h2 {
            font-size: 20px;
            margin-bottom: 20px;
            color: #333;
        }
        
        .progress-bar {
            width: 100%;
            height: 8px;
            background: #e1e1e1;
            border-radius: 4px;
            margin: 20px 0;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: #48bb78;
            transition: width 0.3s;
        }
        
        .lesson-list {
            list-style: none;
        }
        
        .lesson-item {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: background 0.3s;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .lesson-item:hover {
            background: #f5f7fb;
        }
        
        .lesson-item.active {
            background: #e0e7ff;
            border-left: 4px solid #667eea;
        }
        
        .lesson-item.completed {
            color: #48bb78;
        }
        
        .lesson-number {
            width: 25px;
            height: 25px;
            background: #e1e1e1;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }
        
        .lesson-item.completed .lesson-number {
            background: #48bb78;
            color: white;
        }
        
        .main-content {
            flex: 1;
            padding: 40px;
            overflow-y: auto;
        }
        
        .lesson-header {
            margin-bottom: 30px;
        }
        
        .lesson-header h1 {
            font-size: 32px;
            margin-bottom: 10px;
            color: #333;
        }
        
        .lesson-meta {
            color: #666;
            display: flex;
            gap: 20px;
        }
        
        .lesson-content {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            line-height: 1.8;
            margin-bottom: 30px;
        }
        
        .lesson-content h3 {
            margin: 30px 0 15px;
            color: #333;
        }
        
        .lesson-content h3:first-child {
            margin-top: 0;
        }
        
        .lesson-content p {
            margin-bottom: 20px;
            color: #444;
        }
        
        .lesson-content ul, .lesson-content ol {
            margin: 20px 0;
            padding-left: 30px;
        }
        
        .lesson-content li {
            margin-bottom: 10px;
        }
        
        .complete-btn {
            display: inline-block;
            padding: 15px 40px;
            background: #48bb78;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            transition: background 0.3s;
        }
        
        .complete-btn:hover {
            background: #38a169;
        }
        
        .complete-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        
        .completion-message {
            text-align: center;
            padding: 60px;
            background: white;
            border-radius: 15px;
        }
        
        .completion-message h2 {
            font-size: 36px;
            color: #48bb78;
            margin-bottom: 20px;
        }
        
        .completion-message p {
            font-size: 18px;
            color: #666;
            margin-bottom: 30px;
        }
        
        .certificate-btn {
            display: inline-block;
            padding: 15px 40px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo">Skillify</div>
        <div class="nav-links">
            <a href="user_dashboard.php">Dashboard</a>
            <a href="courses.php">Courses</a>
            <a href="ai_tutor.php">AI Tutor</a>
            <a href="../logout.php" style="color: #666;">Logout</a>
        </div>
    </nav>
    
    <div class="course-container">
        <!-- Sidebar with lessons -->
        <div class="sidebar">
            <h2><?php echo htmlspecialchars($course['title']); ?></h2>
            
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?php echo $enrollment['progress_percentage']; ?>%"></div>
            </div>
            <p style="color: #666; margin-bottom: 20px;"><?php echo $enrollment['progress_percentage']; ?>% Complete</p>
            
            <ul class="lesson-list">
                <?php foreach ($lessons as $index => $lesson): ?>
                    <a href="?id=<?php echo $course_id; ?>&lesson=<?php echo $lesson['id']; ?>" style="text-decoration: none; color: inherit;">
                        <li class="lesson-item <?php 
                            echo $lesson['id'] == $current_lesson_id ? 'active' : '';
                            echo in_array($lesson['id'], $completed_lessons) ? ' completed' : '';
                        ?>">
                            <span class="lesson-number"><?php echo $index + 1; ?></span>
                            <div style="flex: 1;">
                                <strong><?php echo htmlspecialchars($lesson['title']); ?></strong>
                                <div style="font-size: 12px; color: #999; margin-top: 5px;">
                                    <?php echo $lesson['duration_minutes']; ?> min
                                </div>
                            </div>
                            <?php if (in_array($lesson['id'], $completed_lessons)): ?>
                                <span style="color: #48bb78;">✓</span>
                            <?php endif; ?>
                        </li>
                    </a>
                <?php endforeach; ?>
            </ul>
        </div>
        
        <!-- Main content area -->
        <div class="main-content">
            <?php if (isset($_GET['completed'])): ?>
                <div class="completion-message">
                    <h2>🎉 Congratulations!</h2>
                    <p>You've completed the <?php echo htmlspecialchars($course['title']); ?> course!</p>
                    <p>Your certificate is ready for download.</p>
                    <a href="certificate.php?course_id=<?php echo $course_id; ?>" class="certificate-btn">Download Certificate</a>
                </div>
            <?php elseif ($current_lesson): ?>
                <div class="lesson-header">
                    <h1><?php echo htmlspecialchars($current_lesson['title']); ?></h1>
                    <div class="lesson-meta">
                        <span>⏱️ <?php echo $current_lesson['duration_minutes']; ?> minutes</span>
                        <?php if (in_array($current_lesson_id, $completed_lessons)): ?>
                            <span style="color: #48bb78;">✓ Completed</span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="lesson-content">
                    <?php echo $current_lesson['content']; ?>
                    
                    <?php if ($current_lesson['video_url']): ?>
                        <div style="margin-top: 30px;">
                            <h3>Video Lesson</h3>
                            <video controls style="width: 100%; border-radius: 10px;">
                                <source src="<?php echo htmlspecialchars($current_lesson['video_url']); ?>" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if (!in_array($current_lesson_id, $completed_lessons)): ?>
                    <a href="?id=<?php echo $course_id; ?>&lesson=<?php echo $current_lesson_id; ?>&complete=1" class="complete-btn">
                        Mark as Completed
                    </a>
                <?php else: ?>
                    <button class="complete-btn" disabled>✓ Lesson Completed</button>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>