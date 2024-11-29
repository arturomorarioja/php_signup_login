<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
</head>
<body>
    <header>
        <h1>Home</h1>
    </header>
    <main>
        <section>
            <?php if (isset($_SESSION['user_id'])): ?>
                <p><?=$_SESSION['username']; ?>, you are logged in.</p>
                <p><a href="logout.php" title="Log out">Log out</a></p>
            <?php else: ?>
                <p>
                    <a href="login.php" title="Log in">Log in</a> 
                    or <a href="signup.html" title="Sign up">sign up</a>
                </p>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>