<?php

/**
 * Created by PhpStorm.
 * User: franckzhang
 * Date: 0426//2017
 * Time: 16:55
 */

class User
{
    public $email;
    public $password;
    private $connection;

    public function __construct()
    {
        try {
            $dsn = 'mysql:host=localhost:8889;dbname=mti_db';
            $user = 'zhang_f';
            $password = 'password';
            $this->connection = new PDO($dsn, $user, $password);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function getUser($email)
    {
        try
        {
            $prep = $this->connection->prepare('SELECT * FROM t_user WHERE email = :email;');
            $prep->bindParam(':email', $email, PDO::PARAM_STR);
            $prep->execute();
            while ($user = $prep->fetch(PDO::FETCH_OBJ)) {
                $this->email = $user->email;
                $this->password = $user->password;

            }
        }
        catch (Exception $e)
        {
            echo $e->getMessage();
        }
    }

    public function addUser($email, $password)
    {
        try
        {
            $password = password_hash($password, PASSWORD_DEFAULT);
            $prep = $this->connection->prepare('INSERT INTO t_user(email, `password`) VALUES(:email, :pass)');
            $prep->bindParam(':email', $email, PDO::PARAM_STR);
            $prep->bindParam(':pass', $password, PDO::PARAM_STR);
            if ($prep->execute())
            {
                $_SESSION['email'] = $email;
            }
            return $prep->execute();
        }
        catch (Exception $e)
        {
            return 0;
        }
    }

    public function addImage($email, $extension, $dominant)
    {
        try
        {
            $prep = $this->connection->prepare('INSERT INTO t_image(email, extension, dominant) VALUES(:email, :extension, :dominant)');
            $prep->bindParam(':email', $email, PDO::PARAM_STR);
            $prep->bindParam(':extension', $extension, PDO::PARAM_STR);
            $prep->bindParam(':dominant', $dominant, PDO::PARAM_STR);
            if ($prep->execute())
                return $this->connection->lastInsertId();
            else
                return -1;
        }
        catch (Exception $e)
        {
            echo $e->getMessage();
            return -1;
        }
    }

    public function getImageList($email)
    {
        $image_list = array();
        try
        {
            $prep = $this->connection->prepare('
                    SELECT id, extension
                    FROM t_image
                    JOIN t_user ON t_user.email = t_image.email
                    WHERE t_user.email = :email');
            $prep->bindParam(':email', $email, PDO::PARAM_STR);
            $prep->execute();
            while ($image = $prep->fetch(PDO::FETCH_OBJ)) {
                array_push($image_list, $image->id . "." . $image->extension);
            }
        }
        catch (Exception $e)
        {
            echo $e->getMessage();
        }
        return $image_list;
    }

    public function deleteImage($email, $img)
    {
        try
        {
            $filename = pathinfo($img)['filename'];
            $prep = $this->connection->prepare('
                    DELETE FROM t_image
                    WHERE email = :email AND id = :id ');
            $prep->bindParam(':email', $email, PDO::PARAM_STR);
            $prep->bindParam(':id', $filename, PDO::PARAM_STR);
            $prep->execute();
        }
        catch (Exception $e)
        {
            echo $e->getMessage();
        }
    }
}