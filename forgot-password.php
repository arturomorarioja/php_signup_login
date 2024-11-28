<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
</head>
<body>
    <header>
        <h1>Forgot Password</h1>
    </header>
    <main>
        <form method="POST" action="send-password-reset.php">
            <div>
                <label for="txtEmail">Email</label>
                <input type="email" id="txtEmail" name="email">
            </div>
            <div>
                <input type="submit" value="Send">
            </div>
        </form>
    </main>
</body>
</html>