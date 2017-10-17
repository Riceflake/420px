<?php

spl_autoload_register(function ($class) {
    require_once ('classes/' . $class . '.php');
});


if (!isset($_SESSION))
    session_start();
if (isset($_SESSION['email']))
    header("Location: index.php");

if (!empty($_POST))
{
    if (isset($_POST['email']) && isset($_POST['password']))
    {

        $email = htmlspecialchars($_POST['email']);
        $password = htmlspecialchars($_POST['password']);
        $user = new User();
        $user->getUser($email);
        if (password_verify($password, $user->password) == true)
        {
            $_SESSION['email'] = $email;
            header("Location: index.php");
        }
        else
            header("Location: login.php?message=error");
    }
    else
    {
        header("HTTP/1.1 500 Internal Server Error");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <style>
        .login {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .center {
            text-align : center;
        }
    </style>

    <meta charset="UTF-8">
    <title>420px</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" integrity="sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUwEyJ" crossorigin="anonymous">
</head>
<body>

<nav class="navbar navbar-toggleable-md navbar-light bg-faded">
    <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarTogglerDemo02" aria-controls="navbarTogglerDemo02" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <a class="navbar-brand" href="index.php">Navbar</a>

    <div class="collapse navbar-collapse justify-content-end">
        <ul class="justify-content-end navbar-nav">
            <li class="nav-item">
                <a class="nav-link" href="login.php">Log in<span class="sr-only">(current)</span></a>
            </li>
            <li class="nav-item pull-xs-right">
                <a class="nav-link" href="signup.php">Sign up</a>
            </li>
        </ul>
    </div>
</nav>


<div class="login jumbotron">


    <form method="post" action="login.php">
        <?php if (isset($_GET['message']) && $_GET['message'] == "error"):  ?>
            <div class="alert alert-danger" role="alert">
                <strong>Error : </strong> Login failed
            </div>
        <?php endif ?>
        <h2 class="mb-3 center">Login</h2>
        <input type="email" name="email" class="mb-3 form-control" placeholder="Email address" required autofocus>
        <input type="password" name="password" class="mb-3 form-control" placeholder="Password" required>
        <button class="btn btn-lg btn-primary btn-block" type="submit">Log in</button>
    </form>

</div>

<!-- jQuery library -->
<script src="https://code.jquery.com/jquery-3.1.1.slim.min.js" integrity="sha384-A7FZj7v+d/sdmMqp/nOQwliLvUsJfDHW+k9Omg/a/EheAdgtzNs3hpfag6Ed950n" crossorigin="anonymous"></script>

<!-- Tether -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js" integrity="sha384-DztdAPBWPRXSA/3eYEEUWrWCy7G5KFbe8fFjk5JAIxUYHKkDx6Qin1DkWx51bBrb" crossorigin="anonymous"></script>
<!-- Bootstrap 4 Alpha JS -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js" integrity="sha384-vBWWzlZJ8ea9aCX4pEW3rVHjgjt7zpkNpZk+02D9phzyeVkE+jo0ieGizqPLForn" crossorigin="anonymous"></script>


</body>
</html>