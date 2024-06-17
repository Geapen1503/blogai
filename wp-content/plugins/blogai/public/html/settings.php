<?php


echo '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../css/style.css">
    <title>Title</title>
    <style>
        * {
            margin: 0;
            padding: 0;
        }

        header {
            display: flex;
            width: 87.5vw;
            height: 10vw;
            flex-direction: row;
            align-items: center;
            background-color: ;
        }

        .blogAiLogo {
            width: 4vw;
            height: 4vw;
            margin-left: 2vw;
            margin-right: 2vw;
        }

        .navbarShadowBox {
            width: 30vw;
            position: relative;
            margin-left: -2vw;
            background-color: #FFFFFF;
        }

        .navbar {
            display: flex;
            width: 87.5vw;
            height: 3vw;
            margin-left: 2vw;
            align-items: center;
            background-color: #ffffff;
        }

        .navbarButton {
            font-size: 1.5vw;
            margin-left: 2vw;
            margin-right: 2vw;
            color: black;
            text-decoration: none;
            background: none;
            border: none;
        }

        .navbarButton:hover {
            color: #F1C419;
            transition-duration: 300ms;
        }

        .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #f9f9f9;
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
        }

        .dropdown-content form {
            display: flex;
            flex-direction: column;
            padding: 12px 16px;
        }

        .dropdown-content input[type="text"], .dropdown-content input[type="password"] {
            width: 100%;
            padding: 8px;
            margin: 5px 0;
            box-sizing: border-box;
        }

        .dropdown-content input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
        }

        .dropdown-content input[type="submit"]:hover {
            background-color: #45a049;
        }

        .dropdown:hover .dropdown-content {
            display: block;
        }

        #MainBox {
            display: flex;
            height: 45vw;
            width: 45vw;
            margin-left: 2vw;
            flex-direction: column;
            justify-content: space-evenly;
            background-color: transparent;
        }

        select {
            width: 20vw;
            text-align: center;
        }

        textarea {
            width: 20vw;
        }

        .inputBoxes {
            display: flex;
            width: 40vw;
            height: 5vw;
            justify-content: space-between;
            align-items: center;
            background-color: transparent;
        }

        .checkBoxes {
            display: flex;
            width: 40vw;
            height: 3vw;
            justify-content: space-between;
            align-items: center;
            background-color: transparent;
        }

        .firstCheckBox {
            margin-top: 2vw;
        }

        label {
            text-decoration: underline;
            font-weight: 600;
            color: black;
            font-size: 1.3vw;
        }

        input[name="sketch_input"] {
            display: flex;
            margin-right: 25%;
        }

        input[name="withImage"] {
            display: flex;
            margin-right: 25%;
        }

        input[name="generateNow"] {
            display: flex;
            margin-right: 25%;
        }

        .submitBox {
            display: flex;
            justify-content: center;
            margin-top: 2vw;
        }

        .submitButton {
            display: flex;
            background-color: #4CAF50;
            color: white;
            padding: 12px 20px;
            align-self: center;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .submitButton:hover {
            background-color: #45a049;
        }

        .labelSpan {
            color: red;
            text-decoration: none;
        }
    </style>
</head>
<body>

    <header>
        <img class="blogAiLogo" alt="Blog AI Logo" src="">
        <h1>Blog AI</h1>
    </header>

    <div class="navbarShadowBox">
        <div class="navbar">
            <a class="navbarButton" href="#">General</a>
            <div class="dropdown">
                <button class="navbarButton">Register</button>
                <div class="dropdown-content">
                    <form method="post">
                        <input type="text" name="register_username" placeholder="Username" required>
                        <input type="password" name="register_password" placeholder="Password" required>
                        <input type="submit" name="register" value="Register">
                    </form>
                </div>
            </div>
            <div class="dropdown">
                <button class="navbarButton">Login</button>
                <div class="dropdown-content">
                    <form method="post">
                        <input type="text" name="login_username" placeholder="Username" required>
                        <input type="password" name="login_password" placeholder="Password" required>
                        <input type="submit" name="login" value="Login">
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div id="MainBox">
        <form method="post">
            <div class="inputBoxes">
                <label><span class="labelSpan">* </span>The interval:</label>
                <select name="frequency" required>
                    <option value="" selected disabled hidden>Choose a frequency</option>
                    <option value="1d">1d (1 day)</option>
                    <option value="3d">3d (3 days)</option>
                    <option value="1w">1w (1 week)</option>
                    <option value="2w">2w (2 weeks)</option>
                    <option value="1m">1m (1 month)</option>
                    <option value="3m">3m (3 months)</option>
                </select>
            </div>
            <div class="inputBoxes">
                <label><span class="labelSpan">* </span>The description:</label>
                <textarea name="description" placeholder="Description of the post..." required></textarea>
            </div>
            <div class="inputBoxes checkBoxes firstCheckBox">
                <label>Set post state to publish:</label>
                <input name="sketch_input" type="checkbox" class="checkButton" checked>
            </div>
            <div class="inputBoxes checkBoxes">
                <label>Make a post with images:</label>
                <input type="checkbox" class="checkButton" name="withImage">
            </div>
            <div class="inputBoxes checkBoxes">
            <label>Generate the post now:</label>
                <input type="checkbox" class="checkButton" name="generateNow">
            </div>

            <div class="inputBoxes submitBox">
                <input type="submit" class="submitButton" name="submit" value="Save Config">
            </div>
        </form>
    </div>

    <footer></footer>
</body>
</html>
';


global $frequency_input, $description_input, $sketch_input, $w_img_input, $gen_now_input;

if (isset($_POST['submit'])) {
    $frequency_input = $_POST['frequency'];
    $description_input = $_POST['description'];
    $sketch_input = isset($_POST['sketch_input']) ? true : false;
    $w_img_input = isset($_POST['withImage']) ? true : false;
    $gen_now_input = isset($_POST['generateNow']) ? true : false;
}



//global $register_username, $register_password, $login_username, $login_password;

if (isset($_POST['register'])) {
    $register_username = $_POST['register_username'];
    $register_password = $_POST['register_password'];

    register_api($register_username, $register_password);
}

if (isset($_POST['login'])) {
    $login_username = $_POST['login_username'];
    $login_password = $_POST['login_password'];

    login_api($login_username, $login_password);
}


?>
