<?php

session_start();

$headerText = 'Home';
include 'views/header.php';

?>
    <main>
        <section>
            <?php if (isset($_SESSION['user_id'])): ?>
                <p><?=$_SESSION['username']; ?>, you are logged in.</p>
                <p><a href="logout.php" title="Log out">Log out</a></p>
            <?php else: ?>
                <p>
                    <a href="login.php" title="Log in">Log in</a> 
                    or <a href="signup.php" title="Sign up">sign up</a>
                </p>
            <?php endif; ?>
        </section>
    </main>

<?php include 'views/footer.php'; ?>