<?php
    require 'vendor/autoload.php';
    require 'filter/Sepia.php';
    require 'filter/Edge.php';

    use Imagine\Image\Box;
    use Imagine\Image\Point;

    session_start();

    function find_dominant_color($path, $ext)
    {
        if ($ext == "jpg" || $ext == "jpeg")
            $i = imagecreatefromjpeg($path);
        else if ($ext == "png")
            $i = imagecreatefrompng($path);
        else
            echo "Error file is not png or jpg format";

        if (isset($i)) {
          $rTotal = 0;
          $gTotal = 0;
          $bTotal = 0;

          for ($x = 0; $x < imagesx($i); $x++) {
            for ($y = 0; $y < imagesy($i); $y++) {
              $rgb = imagecolorat($i, $x, $y);
              $rTotal += ($rgb >> 16) & 0xFF;
              $gTotal += ($rgb >> 8) & 0xFF;
              $bTotal += $rgb & 0xFF;
            }
          }
          $max = max($rTotal, $gTotal, $bTotal);
          if ($max == $rTotal)
            return "red";
          else if ($max == $gTotal)
            return "green";
          else
            return "blue";
        }
    }

    function apply_sepia($img, $img_dir, $png)
    {
        imagefilter($img,IMG_FILTER_GRAYSCALE);
        imagefilter($img,IMG_FILTER_BRIGHTNESS,-30);
        imagefilter($img,IMG_FILTER_COLORIZE, 90, 55, 30);
        if ($png)
            imagepng($img, $img_dir);
        else
            imagejpeg($img, $img_dir);
        imagedestroy($img);
    }

    function apply_filter($img, $filter, $options = null)
    {
        $extension = pathinfo($img, PATHINFO_EXTENSION);
        $img_dir = "photos" . DIRECTORY_SEPARATOR . $_SESSION['email'] . DIRECTORY_SEPARATOR . $img ;
        if (strcmp($extension, "jpg") == 0 || strcmp($extension, "jpeg") == 0)
        {
            $img = imagecreatefromjpeg($img_dir);
            if ($filter === -1)
                apply_sepia($img, $img_dir, false);
            elseif($img && imagefilter($img, $filter, $options))
            {
                imagejpeg($img, $img_dir);
                imagedestroy($img);
            }
        }
        else if (strcmp($extension, "png" ) == 0)
        {
            $img = imagecreatefrompng($img_dir);
            if ($filter === -1)
                apply_sepia($img, $img_dir, true);
            elseif($img && imagefilter($img, $filter, $options))
            {
                imagepng($img, $img_dir, $options);
                imagedestroy($img);
            }
        }
    }

    function print_image ($email, $delete)
    {
        $user = new User();
        $imageList = $user->getImageList($email);
        $userImage = "photos" . DIRECTORY_SEPARATOR . $email;

        foreach ($imageList as $image)
        {
            $image_path = $userImage . DIRECTORY_SEPARATOR . $image;
            echo '<div>';
            echo "<img src= $image_path>";
            if ($delete)
            {
                echo "<form class=\"m-2\" method=\"post\" action=\"index.php\">";
                echo "<div class=\"btn-group d-block text-center btn-group-sm\">";
                echo "<button method=\"post\" name=\"img\" value=$image type=\"submit\" class=\"btn btn-danger\">Delete $image</button>";
                echo "</div>";
                echo "<div class=\"btn-group d-block text-center btn-group-sm\">";
                echo "<button method=\"post\" name=\"contrast_minus\" value=$image type=\"submit\" class=\"btn btn-secondary\">Contrast -</button>";
                echo "<button method=\"post\" name=\"contrast_plus\" value=$image type=\"submit\" class=\"btn btn-secondary\">Contrast +</button>";
                echo "<button method=\"post\" name=\"luminosity_plus\" value=$image type=\"submit\" class=\"btn btn-secondary\">Luminosity -</button>";
                echo "<button method=\"post\" name=\"luminosity_minus\" value=$image type=\"submit\" class=\"btn btn-secondary\">Luminosity +</button>";
                echo "</div>";
                echo "<div class=\"btn-group d-block text-center btn-group-sm\">";
                echo "<button method=\"post\" name=\"edge\" value=$image type=\"submit\" class=\"btn btn-secondary\">Edge</button>";
                echo "<button method=\"post\" name=\"blur\" value=$image type=\"submit\" class=\"btn btn-secondary\">Blur</button>";
                echo "<button method=\"post\" name=\"grayscale\" value=$image type=\"submit\" class=\"btn btn-secondary\">Grayscale</button>";
                echo "<button method=\"post\" name=\"sepia\" value=$image type=\"submit\" class=\"btn btn-secondary\">Sepia</button>";
                echo "</div>";
                echo "</form>";
            }
            echo '</div>';
        }
    }

    spl_autoload_register(function ($class) {
        require_once ('classes/' . $class . '.php');
    });


    if (!empty($_POST))
    {
        if (isset($_POST["delete_session"]))
        {
            session_destroy();
            session_start();
        }
        else if(isset($_POST['img']))
        {
            $user = new User();
            $user->deleteImage($_SESSION['email'], $_POST['img']);
            $img_dir = "photos" . DIRECTORY_SEPARATOR . $_SESSION['email'] . DIRECTORY_SEPARATOR . $_POST['img'];
            unlink($img_dir);
        }
        else if (isset($_POST['edge']))
            apply_filter($_POST['edge'], IMG_FILTER_EDGEDETECT);
        else if (isset($_POST['blur']))
            apply_filter($_POST['blur'], IMG_FILTER_GAUSSIAN_BLUR);
        else if (isset($_POST['grayscale']))
            apply_filter($_POST['grayscale'], IMG_FILTER_GRAYSCALE);
        else if (isset($_POST['sepia']))
            apply_filter($_POST['sepia'], -1);
        else if (isset($_POST['contrast_minus']))
            apply_filter($_POST['contrast_minus'], IMG_FILTER_CONTRAST, -10);
        else if (isset($_POST['contrast_plus']))
            apply_filter($_POST['contrast_plus'], IMG_FILTER_CONTRAST, 10);
        else if (isset($_POST['luminosity_plus']))
            apply_filter($_POST['luminosity_plus'], IMG_FILTER_CONTRAST, 17);
        else if (isset($_POST['luminosity_minus']))
            apply_filter($_POST['luminosity_minus'], IMG_FILTER_CONTRAST, -17);

    }


    if (!empty($_FILES))
    {
        $upload_dir = "photos";
        $user_dir = "photos/" . $_SESSION['email'];
        if (!is_dir($upload_dir))
            mkdir("photos", 0755, true);
        if (!is_dir($user_dir))
            mkdir($user_dir, 0755, true);

        $user = new User();

        $extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
        if (!empty($extension))
        {
          $image_index = $user->addImage($_SESSION['email'], $extension, find_dominant_color($_FILES['file']['tmp_name'], $extension));
          $upload_file = $user_dir . '/' . $image_index . '.' . $extension;
          if (move_uploaded_file($_FILES['file']['tmp_name'], $upload_file)) {
            $message = "success";
            $imagine = new Imagine\Gd\Imagine();
            try {
              $image = $imagine->open($upload_file);
              $image->resize(new Box(420, 420));
              $image->save($upload_file);
            }
            catch (Exception $e) {
                echo "Error file is not png or jpg format";
            }
          }
          else
          {
            echo "Error cannot upload your file";
          }
        }
        else
        {
            echo "Error file is empty";
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>420px</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" integrity="sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUwEyJ" crossorigin="anonymous">
    <style>

        .flex-container {
            padding: 0;
            margin: 0;
            list-style: none;
            display: -webkit-box;
            display: -moz-box;
            display: -ms-flexbox;
            display: -webkit-flex;
            display: flex;
            -webkit-flex-flow: row wrap;
            justify-content: space-around;
        }

        .flex-container > div {
            position: relative;
            display: flex;
            flex-flow: column;
            align-items: center;
            /*padding: 10px;*/
        }

        .flex-container > div > img {
            max-width: 200px;
        }

    </style>

</head>
<body>

<nav class="navbar navbar-toggleable-md navbar-light bg-faded">
    <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarTogglerDemo02" aria-controls="navbarTogglerDemo02" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <a class="navbar-brand" href="index.php">Navbar</a>
    <div class="collapse navbar-collapse justify-content-end">
        <ul class="justify-content-end navbar-nav">
            <?php if(empty($_SESSION)): ?>
            <li class="nav-item">
                <a class="nav-link" href="login.php">Log in<span class="sr-only">(current)</span></a>
            </li>
            <li class="nav-item pull-xs-right">
                <a class="nav-link" href="signup.php">Sign up</a>
            </li>
            <?php else: ?>
                <form method="post" action="index.php">
                    <li class="nav-item">
                        <input type="submit" name="delete_session" value="Delete session">
                    </li>

                </form>

            <? endif; ?>
        </ul>
    </div>
</nav>
<div id="pictures" class="flex-container">
    <!--
    <?php if (isset($message) && $message === "success") : ?>
        Success
    <? endif; ?>
    -->
    <?php if (isset($_GET['user'])): print_image($_GET['user'], false); ?>
    <?php elseif(!empty($_SESSION)) : print_image($_SESSION['email'], true);  ?>
    <form enctype="multipart/form-data" action="index.php" method="POST">
        <input type="hidden" name="MAX_FILE_SIZE" value="512000" />
        <input name="file" type="file" />
        <input type="submit" value="Send File" />
    </form>

    <? endif; ?>
</div>
<!-- jQuery library -->
<script src="https://code.jquery.com/jquery-3.1.1.slim.min.js" integrity="sha384-A7FZj7v+d/sdmMqp/nOQwliLvUsJfDHW+k9Omg/a/EheAdgtzNs3hpfag6Ed950n" crossorigin="anonymous"></script>

<!-- Tether -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js" integrity="sha384-DztdAPBWPRXSA/3eYEEUWrWCy7G5KFbe8fFjk5JAIxUYHKkDx6Qin1DkWx51bBrb" crossorigin="anonymous"></script>
<!-- Bootstrap 4 Alpha JS -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js" integrity="sha384-vBWWzlZJ8ea9aCX4pEW3rVHjgjt7zpkNpZk+02D9phzyeVkE+jo0ieGizqPLForn" crossorigin="anonymous"></script>


</body>
</html>