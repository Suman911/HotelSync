<?php
$showAlert = "";
$emailError = "";
$passwordError = "";
$email = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    include 'connt_db.php';
    $email = $_POST["email"];
    $password = $_POST["password"];

    $sql = "Select * from `userinfo` where email='$email'";
    $result = mysqli_query($conn, $sql);
    $num = mysqli_num_rows($result);
    if ($num === 1) {
        $row = mysqli_fetch_assoc($result);
        if (password_verify($password, $row['password'])) {

            include 'start_session.php';

            header("location: profile.php");
        } else {
            $passwordError = "Wrong Password";
        }
    } else {
        $emailError = "Invalid Credentials";
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title></title>
    <meta content="" name="description">
    <meta content="" name="keywords">
    <link href="style.css" rel="stylesheet">
    <link href="login.css" rel="stylesheet">
</head>

<body>

    <?php
    if ($showAlert)
        echo '<div class="alart">', $showAlert, '</div>';
    ?>

    <main>
        <div id="login">
            <div class="padding20_top_bottm section-title">
                <h3>Log In</h3>
            </div>
            <form id="login_form" method="post" action="next.php">
                <div class="login_inputs padding20_top_bottm">
                    <b><label for="email">Email:</label></b>
                    <?php
                    if ($emailError)
                        echo '<span class="error_msg">' , $emailError , '</span>';
                    ?>
                    <input type="email" id=" email" placeholder="Enter Email" value="<?= $email ?>"
                        name="email" required="">
                </div>

                <div class="login_inputs padding20_top_bottm">
                    <b> <label for="password">Password:</label></b>
                    <?php
                    if ($passwordError)
                        echo '<span class="error_msg">' , $passwordError , '</span>';
                    ?>
                    <input type="password" id="password" placeholder="Enter password" name="password" minlength="1"
                        required="">
                </div>

                <div class="login_inputs padding20_top_bottm">
                    <button class="btn" type="submit">Log In</button>
                </div>

                <div class="txt_center">
                    <p class="padding20_top_bottm"> Dont have an account <a href="register.php"> Create New </a>
                    </p>
                </div>
            </form>
        </div>
    </main>


</body>

</html>