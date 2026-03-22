<?php
/**
 * Fix Password Script
 * Run this to reset passwords to 'password123'
 */

require_once 'config/database.php';

echo "<h2>Password Fix Tool</h2>";

// Generate correct hash for password123
$correctHash = password_hash('password123', PASSWORD_BCRYPT);
echo "<p>Generated hash for 'password123': <code>$correctHash</code></p>";

// Update all demo users
$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email IN (?, ?, ?)");
$stmt->execute([$correctHash, 'admin@techlearn.com', 'john@example.com', 'sarah@example.com']);

echo "<p style='color: green;'>✅ Passwords updated successfully!</p>";
echo "<p>You can now login with:</p>";
echo "<ul>";
echo "<li><strong>Admin:</strong> admin@techlearn.com / password123</li>";
echo "<li><strong>Student:</strong> john@example.com / password123</li>";
echo "<li><strong>Student:</strong> sarah@example.com / password123</li>";
echo "</ul>";
echo "<p><a href='login.php' style='padding: 10px 20px; background: #6366f1; color: white; text-decoration: none; border-radius: 8px;'>Go to Login</a></p>";

// Verify the update
$stmt = $pdo->query("SELECT email, password FROM users WHERE email IN ('admin@techlearn.com', 'john@example.com', 'sarah@example.com')");
echo "<h3>Verification:</h3>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Email</th><th>Password Verified</th></tr>";
while ($user = $stmt->fetch()) {
    $verified = password_verify('password123', $user['password']) ? '✅ YES' : '❌ NO';
    echo "<tr><td>{$user['email']}</td><td>$verified</td></tr>";
}
echo "</table>";
?>