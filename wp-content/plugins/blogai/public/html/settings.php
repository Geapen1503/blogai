<?php


echo '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../css/style.css">
    <title>Blog AI</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Arial", sans-serif;
        }

        body {
            background-color: #f4f4f9;
            color: #333;
            font-size: 1rem;
        }

        #HeaderHolder {
            margin-left: -2vw;
        }

        header {
            display: flex;
            width: 100%;
            padding: 1rem 2rem;
            align-items: center;
            background-color: #B6DEB9;
            color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .blogAiLogo {
            width: 3.5vw;
            height: 3.5vw;
            margin-left: 1vw;
            margin-right: 1rem;
        }

        h1 {
            margin-left: 1vw;
            font-size: 1.5rem;
            color: white; 
        }

        .navbarShadowBox {
            width: 100%;
            padding: 1rem 2rem;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .navbar {
            display: flex;
            align-items: center;
            justify-content: flex-start;
        }

        .navbarButton {
            font-size: 1rem;
            margin: 0 1rem;
            color: #343a40;
            text-decoration: none;
            font-weight: bold;
            background: none;
            border: none;
            cursor: pointer;
            transition: color 0.3s;
        }

        .navbarButton:hover {
            color: #F1C419;
        }

        #MainBox {
            display: flex;
            flex-direction: column;
            width: 50%;
            margin: 0 auto;
            padding: 2rem;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        select, textarea, input[type="text"] {
            width: 100%;
            padding: 0.5rem;
            margin: 0.5rem 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .inputBoxes, .checkBoxes {
            margin: 1rem 0;
        }

        label {
            font-weight: 600;
            font-size: 1rem;
            color: #333;
            margin-bottom: 0.5rem;
            display: block;
        }

        .submitBox {
            display: flex;
            justify-content: center;
            margin-top: 2rem;
        }

        .submitButton {
            background-color: #28a745;
            color: /* Mr */ white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .submitButton:hover {
            background-color: #218838;
        }

        .labelSpan {
            color: red;
        }

        .tooltip {
            position: relative;
            display: flex;
            flex-direction: row;
            cursor: help;
            color: #007bff;
            text-decoration: none;
        }

        .tooltip .tooltiptext {
            visibility: hidden;
            width: 200px;
            background-color: #555;
            color: #fff;
            text-align: center;
            border-radius: 6px;
            padding: 0.5rem;
            position: absolute;
            z-index: 1;
            bottom: 125%;
            left: 50%;
            margin-left: -22vw;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .tooltip .tooltiptext::after {
            content: "";
            position: absolute;
            top: 100%;
            left: 50%;
            margin-left: -5px;
            border-width: 5px;
            border-style: solid;
            border-color: #555 transparent transparent transparent;
        }

        .tooltip:hover .tooltiptext {
            visibility: visible;
            opacity: 1;
        }
    </style>
</head>
<body>

    <div id="HeaderHolder">
        <header>
            <img class="blogAiLogo" alt="Blog AI Logo" src="/wp-content/plugins/blogai/public/img/BlogAILogo.png">
            <h1>Blog AI</h1>
        </header>
    
        <div class="navbarShadowBox">
            <div class="navbar">
                <a class="navbarButton" href="#">General</a>
            </div>
        </div>
    </div>

    <div id="MainBox">
        <form method="post">
            <div class="inputBoxes">
                <div class="tooltip">
                    <label><span class="labelSpan">* </span>The interval:</label>
                    <span class="tooltiptext">Choose how often you want to post</span>
                    &#9432;
                </div>
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
                <div class="tooltip">
                    <label><span class="labelSpan">* </span>The description:</label>
                    <span class="tooltiptext">Here you need to describe your subject as much as you can, and we will generate article with this description</span>
                    &#9432;
                </div>
                <textarea name="description" placeholder="Description of the post..." required></textarea>
            </div>
            <div class="inputBoxes">
                <div class="tooltip">
                    <label><span class="labelSpan">* </span>API Key:</label>
                    <span class="tooltiptext">Put your BlogAI API Key here</span>
                    &#9432;
                </div>
                <input type="text" name="api_key" placeholder="Enter your API key" required>
            </div>
            <div class="checkBoxes">
                <div class="tooltip">
                    <label>Set post state to publish:</label>
                    <span class="tooltiptext">Select to automatically publish the post</span>
                    &#9432;
                </div>
                <input name="sketch_input" type="checkbox" class="checkButton" checked>
            </div>
            <div class="checkBoxes">
                <div class="tooltip">
                    <label>Make a post with images:</label>
                    <span class="tooltiptext">Include images in the post</span>
                    &#9432;
                </div>
                <input type="checkbox" class="checkButton" name="withImage">
            </div>
            <div class="checkBoxes">
                <div class="tooltip">
                    <label>Generate the post now:</label>
                    <span class="tooltiptext">Generate the post immediately after saving</span>
                    &#9432;
                </div>
                <input type="checkbox" class="checkButton" name="generateNow">
            </div>

            

            <div class="submitBox">
                <input type="submit" class="submitButton" name="submit" value="Save Config">
            </div>
        </form>
    </div>

    <footer></footer>
</body>
</html>
';


global $frequency_input, $description_input, $sketch_input, $w_img_input, $gen_now_input, $user_api_key;

if (isset($_POST['submit'])) {
    $frequency_input = $_POST['frequency'];
    $description_input = $_POST['description'];
    $sketch_input = isset($_POST['sketch_input']) ? true : false;
    $w_img_input = isset($_POST['withImage']) ? true : false;
    $gen_now_input = isset($_POST['generateNow']) ? true : false;
    $user_api_key = $_POST['api_key'];
}



//global $register_username, $register_password, $login_username, $login_password;

/*if (isset($_POST['register'])) {
    $register_username = $_POST['register_username'];
    $register_password = $_POST['register_password'];

    register_api($register_username, $register_password);
}

if (isset($_POST['login'])) {
    $login_username = $_POST['login_username'];
    $login_password = $_POST['login_password'];

    login_api($login_username, $login_password);
}
*/

?>
