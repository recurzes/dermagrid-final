<?php
// Initialize the session
session_start();

// Check if the user is already logged in, if yes then redirect to dashboard
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: dashboard.php");
    exit;
}

// Define variables and initialize with empty values
$email = $password = "";
$email_err = $password_err = $login_err = "";

// Check if there are any error messages in the session
if(isset($_SESSION['login_err']) && !empty($_SESSION['login_err'])){
    $login_err = $_SESSION['login_err'];
    unset($_SESSION['login_err']);
}

if(isset($_SESSION['email_err']) && !empty($_SESSION['email_err'])){
    $email_err = $_SESSION['email_err'];
    unset($_SESSION['email_err']);
}

if(isset($_SESSION['password_err']) && !empty($_SESSION['password_err'])){
    $password_err = $_SESSION['password_err'];
    unset($_SESSION['password_err']);
}

// Prefill email if available in session
if(isset($_SESSION['email'])){
    $email = $_SESSION['email'];
    unset($_SESSION['email']);
}

// Registration success message
$registration_success = false;
if(isset($_GET['registered']) && $_GET['registered'] == 'true') {
    $registration_success = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DermaGrid - Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f5f5;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .header {
            background-color: #fff;
            padding: 10px 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-grow: 1;
            padding: 20px;
        }

        .login-card {
            background-color: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            width: 100%;
            max-width: 450px;
            text-align: center;
        }

        h1 {
            font-size: 28px;
            color: #333;
            margin-bottom: 10px;
        }

        p {
            color: #666;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-size: 14px;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: #f9f9f9;
            font-size: 14px;
        }

        input[type="email"]::placeholder,
        input[type="password"]::placeholder {
            color: #aaa;
        }

        .password-hint {
            font-size: 12px;
            color: #777;
            margin-top: 5px;
        }

        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .remember-me {
            display: flex;
            align-items: center;
        }

        .remember-me input {
            margin-right: 8px;
        }

        .forgot-password {
            color: #0066cc;
            text-decoration: none;
            font-size: 14px;
        }

        .forgot-password:hover {
            text-decoration: underline;
        }

        .login-btn {
            width: 100%;
            padding: 12px;
            background-color: #000;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-bottom: 20px;
        }

        .login-btn:hover {
            background-color: #333;
        }
        
        .signup-link {
            color: #0066cc;
            text-decoration: none;
        }

        .signup-link:hover {
            text-decoration: underline;
        }
        
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: left;
        }
    </style>
</head>
<body>
    <header class="header">
        <div>Log In</div>
    </header>

    <div class="login-container">
        <div class="login-card">
            <h1>Welcome Back</h1>
            <p>Please log in to continue</p>

            <?php if ($registration_success): ?>
                <div class="success-message">Registration successful! Please log in with your credentials.</div>
            <?php endif; ?>

            <?php if (!empty($login_err)): ?>
                <div style="color: red; margin-bottom: 15px;"><?php echo $login_err; ?></div>
            <?php endif; ?>

            <form action="/backend/operations/login_process.php" method="post">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="Placeholder" value="<?php echo $email; ?>" class="<?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>">
                    <?php if (!empty($email_err)): ?>
                        <span style="color: red; font-size: 12px;"><?php echo $email_err; ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Placeholder" class="<?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                    <?php if (!empty($password_err)): ?>
                        <span style="color: red; font-size: 12px;"><?php echo $password_err; ?></span>
                    <?php endif; ?>
                    <div class="password-hint">It must be a combination of minimum 8 letters, numbers, and symbols.</div>
                </div>

                <div class="remember-forgot">
                    <div class="remember-me">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Remember me</label>
                    </div>
                    <a href="forgot-password.php" class="forgot-password">Forgot Password?</a>
                </div>

                <button type="submit" class="login-btn">Log In</button>
                
                <div>
                    <a href="signup.php" class="signup-link">New? Sign Up</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
