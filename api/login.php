<?php
    header("Content-Type: application/json");

    include("./db_connection.php");

    session_start();

    $email = $password = $id = "";

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = getInput($_POST["email"]);
        $password = getInput($_POST["password"]);

        $stmt = $conn->prepare("SELECT * FROM accounts WHERE email = ?;");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        $foundAccount = false;

        while ($row = $result->fetch_assoc()) {
            if (password_verify($password, $row['password'])) {
                $_SESSION['account']['id'] = $row['id'];
                $_SESSION['account']['email'] = $row['email'];
                $_SESSION['account']['name'] = $row['name'];
                $_SESSION['account']['surname'] = $row['surname'];

                if (isset($row['userimage'])) {
                    $_SESSION['account']['userimage'] = $row['userimage'];
                }
                else {
                    $_SESSION['account']['userimage'] = null;
                }

                $_SESSION['account']['description'] = $row['description'];
                $_SESSION['account']['username'] = $row['username'];
                $_SESSION['account']['public'] = $row['public'];

                $foundAccount = true;
                break;
            }
        }

        $stmt->close();

        if (!$foundAccount) {
            echo json_encode(null);
            $conn->close();
            exit;
        }
    }
    else {
        if (!isset($_GET['account']) || !is_numeric($_GET['account'])) {
            echo json_encode(null);
            exit;
        }

        $id = $_GET['account'];

        $stmt = $conn->prepare("SELECT * FROM accounts WHERE id = ?;");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        $row = $result->fetch_assoc();

        if (!$row) {
            $stmt->close();
            echo json_encode(null);
            $conn->close();
            exit;
        }

        $_SESSION['account']['id'] = $row['id'];
        $_SESSION['account']['email'] = $row['email'];
        $_SESSION['account']['name'] = $row['name'];
        $_SESSION['account']['surname'] = $row['surname'];
        $_SESSION['account']['userimage'] = $row['userimage'];
        $_SESSION['account']['description'] = $row['description'];
        $_SESSION['account']['username'] = $row['username'];
        $_SESSION['account']['public'] = $row['public'];

        $stmt->close();
    }

    /* Notifications loading */
    $id = $_SESSION['account']['id'];

    $stmt = $conn->prepare("SELECT * FROM notifications WHERE accountid = ?;");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    $notifications = [];

    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }

    $stmt->close();

    $_SESSION['account']['notifications'] = $notifications;

    echo json_encode($_SESSION['account']);
    $conn->close();
    exit;

    function getInput($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);

        return $data;
    }

    exit;
?>