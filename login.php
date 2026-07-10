<?php

require_once "includes/config.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    $sql = "SELECT * FROM users WHERE username = ?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "s", $username);

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) == 1) {

        $user = mysqli_fetch_assoc($result);

        // Plain password (abhi)
        if ($password == $user["password"]) {

            $_SESSION["user_id"] = $user["id"];
            $_SESSION["username"] = $user["username"];
            $_SESSION["role"] = $user["role"];

            header("Location: pages/dashboard.php");
            exit();
        } else {

            $error = "Invalid Username or Password.";
        }
    } else {

        $error = "Invalid Username or Password.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login | Billing Inventory System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">

</head>

<body class="login-page">
    <div class="login-container">

        <div class="login-card">

            <h2>Billing Inventory</h2>

            <p class="text-muted mb-4">
                Login to continue
            </p>

            <?php if (!empty($error)) { ?>

                <div class="alert alert-danger">

                    <?php echo $error; ?>

                </div>

            <?php } ?>

            <form action="" method="POST">

                <div class="mb-3">

                    <label class="form-label">
                        Username
                    </label>

                    <input
                        type="text"
                        name="username"
                        class="form-control"
                        placeholder="Enter Username"
                        required>

                </div>

                <div class="mb-4">

                    <label class="form-label">
                        Password
                    </label>

                    <div class="input-group">

                        <input
                            type="password"
                            name="password"
                            id="password"
                            class="form-control"
                            placeholder="Enter Password"
                            required>

                        <button
                            class="btn btn-outline-secondary"
                            type="button"
                            id="togglePassword">

                            <i class="bi bi-eye"></i>

                        </button>

                    </div>

                </div>

                <button class="btn btn-primary w-100">

                    Login

                </button>

            </form>

        </div>

    </div>

    <script>
        const password = document.getElementById("password");

        const toggle = document.getElementById("togglePassword");

        toggle.addEventListener("click", () => {

            if (password.type === "password") {

                password.type = "text";

                toggle.innerHTML = '<i class="bi bi-eye-slash"></i>';

            } else {

                password.type = "password";

                toggle.innerHTML = '<i class="bi bi-eye"></i>';

            }

        });
    </script>

</body>

</html>