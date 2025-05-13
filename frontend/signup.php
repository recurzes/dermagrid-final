<?php
// Initialize variables to store form data and errors
$first_name = $last_name = $email = $mobile = $password = $role = $username = "";
$first_name_err = $last_name_err = $email_err = $mobile_err = $password_err = $terms_err = $signup_err = $role_err = $username_err = "";

// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate first name
    if (empty(trim($_POST["first_name"]))) {
        $first_name_err = "Please enter your first name.";
    } else {
        $first_name = trim($_POST["first_name"]);
    }

    // Validate last name
    if (empty(trim($_POST["last_name"]))) {
        $last_name_err = "Please enter your last name.";
    } else {
        $last_name = trim($_POST["last_name"]);
    }

    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email.";
    } else {
        $email = trim($_POST["email"]);
        // Check if email is valid
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email_err = "Please enter a valid email address.";
        }
    }

    // Validate mobile number
    if (empty(trim($_POST["mobile"]))) {
        $mobile_err = "Please enter your mobile number.";
    } else {
        $mobile = trim($_POST["mobile"]);
    }

    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";
    } elseif (strlen(trim($_POST["password"])) < 8) {
        $password_err = "Password must have at least 8 characters.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validate role
    if (empty(trim($_POST["role"]))) {
        $role_err = "Please select a role.";
    } else {
        $role = trim($_POST["role"]);
    }

    // Validate username
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter a username.";
    } else {
        $username = trim($_POST["username"]);
    }

    // Validate terms checkbox
    if (!isset($_POST["terms"])) {
        $terms_err = "You must agree to the terms and conditions.";
    }

    // Check input errors before processing the signup
    if (empty($first_name_err) && empty($last_name_err) && empty($email_err) && empty($mobile_err) && empty($password_err) && empty($role_err) && empty($username_err) && empty($terms_err)) {
        // This is where you would typically save the user to a database
        // For now, we'll just set up the structure

        // PLACEHOLDER: Database connection and query would go here
        // For example:
        // $sql = "INSERT INTO users (first_name, last_name, email, mobile, password) VALUES (?, ?, ?, ?, ?)";

        // For demonstration, let's assume registration is successful
        // Redirect to login page with success message
        header("location: login.php?registered=true");
        exit;
    }
}
?>

<?php
if (!empty($signup_err)) {
    echo '<div style="color: red; margin-bottom: 15px;">' . $signup_err . '</div>';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DermaGrid - Sign Up</title>
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
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .signup-container {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-grow: 1;
            padding: 20px;
        }

        .signup-card {
            background-color: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            width: 100%;
            max-width: 500px;
            text-align: center;
        }

        h1 {
            font-size: 28px;
            color: #333;
            margin-bottom: 30px;
        }

        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
            flex: 1;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-size: 14px;
        }

        input[type="text"],
        input[type="email"],
        input[type="tel"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: #f9f9f9;
            font-size: 14px;
        }

        input[type="text"]::placeholder,
        input[type="email"]::placeholder,
        input[type="tel"]::placeholder,
        input[type="password"]::placeholder {
            color: #aaa;
        }

        .password-field {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #777;
        }

        .terms-checkbox {
            display: flex;
            align-items: flex-start;
            margin-bottom: 20px;
            text-align: left;
        }

        .terms-checkbox input {
            margin-right: 10px;
            margin-top: 3px;
        }

        .terms-checkbox label {
            font-size: 14px;
            color: #555;
            line-height: 1.4;
        }

        .signup-btn {
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

        .signup-btn:hover {
            background-color: #333;
        }

        .login-link {
            color: #0066cc;
            text-decoration: none;
        }

        .login-link:hover {
            text-decoration: underline;
        }

        .mobile-dropdown {
            position: relative;
        }

        .dropdown-icon {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            color: #777;
        }

        .error-text {
            color: red;
            font-size: 12px;
            margin-top: 5px;
        }
    </style>
</head>

<body>
    <header class="header">
        <div>Sign Up</div>
    </header>

    <div class="signup-container">
        <div class="signup-card">
            <h1>New? Sign Up</h1>

            <form action="/backend/operations/signup_process.php" method="post">
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" placeholder="Placeholder" value="<?php echo $first_name; ?>" class="<?php echo (!empty($first_name_err)) ? 'is-invalid' : ''; ?>">
                        <?php if (!empty($first_name_err)) {
                            echo '<div class="error-text">' . $first_name_err . '</div>';
                        } ?>
                    </div>

                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" placeholder="Placeholder" value="<?php echo $last_name; ?>" class="<?php echo (!empty($last_name_err)) ? 'is-invalid' : ''; ?>">
                        <?php if (!empty($last_name_err)) {
                            echo '<div class="error-text">' . $last_name_err . '</div>';
                        } ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Placeholder" value="<?php echo $email; ?>" class="<?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>">
                    <?php if (!empty($email_err)) {
                        echo '<div class="error-text">' . $email_err . '</div>';
                    } ?>
                </div>

                <div class="form-group">
                    <label for="role">Role</label>
                    <select id="role" name="role" class="<?php echo (!empty($role_err)) ? 'is-invalid' : ''; ?>">
                        <option value="" disabled selected>Select Role</option>
                        <option value="doctor" <?php if(isset($role) && $role == 'doctor') echo 'selected'; ?>>Doctor</option>
                        <option value="nurse" <?php if(isset($role) && $role == 'nurse') echo 'selected'; ?>>Nurse</option>
                        <option value="receptionist" <?php if(isset($role) && $role == 'receptionist') echo 'selected'; ?>>Receptionist</option>
                    </select>
                    <?php if (!empty($role_err)) {
                        echo '<div class="error-text">' . $role_err . '</div>';
                    } ?>
                </div>

                <div class="form-group">
                    <label for="mobile">Mobile Number</label>
                    <div class="mobile-dropdown">
                        <input type="tel" id="mobile" name="mobile" placeholder="Placeholder" value="<?php echo $mobile; ?>" class="<?php echo (!empty($mobile_err)) ? 'is-invalid' : ''; ?>">
                        <div class="dropdown-icon">▼</div>
                    </div>
                    <?php if (!empty($mobile_err)) {
                        echo '<div class="error-text">' . $mobile_err . '</div>';
                    } ?>
                </div>

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Choose a username" value="<?php echo isset($username) ? $username : ''; ?>" class="<?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>">
                    <?php if (!empty($username_err)) {
                        echo '<div class="error-text">' . $username_err . '</div>';
                    } ?>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-field">
                        <input type="password" id="password" name="password" placeholder="Placeholder" class="<?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                        <div class="password-toggle" onclick="togglePassword()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0z" />
                                <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8zm8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z" />
                            </svg>
                        </div>
                    </div>
                    <?php if (!empty($password_err)) {
                        echo '<div class="error-text">' . $password_err . '</div>';
                    } ?>
                </div>

                <div class="terms-checkbox">
                    <input type="checkbox" id="terms" name="terms" <?php if (isset($_POST["terms"])) echo "checked"; ?>>
                    <label for="terms">Agree to Terms and Conditions</label>
                    <?php if (!empty($terms_err)) {
                        echo '<div class="error-text">' . $terms_err . '</div>';
                    } ?>
                </div>

                <button type="submit" class="signup-btn">Button Text</button>

                <div>
                    <a href="login.php" class="login-link">Already have an account?</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
        }
    </script>
</body>

</html>