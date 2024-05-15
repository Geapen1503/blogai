<?php


echo '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../css/style.css">
    <title>Title</title>
</head>
<body>
    
    <header>
        <img class="blogAiLogo" alt="Blog AI Logo" src="">
        <h1>Blog AI</h1>
    </header>
    
    <div class="navbarShadowBox">
        <div class="navbar">
            <a class="navbarButton" href="#">General</a>
        </div>
    </div>

    <div id="MainBox">
        <form method="post">
            <div class="inputBoxes">
                <label>The interval:</label>
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
                <label>The subject:</label>
                <textarea name="subject" placeholder="Subject of the post..." required></textarea>
            </div>
            <div class="inputBoxes">
                <label>The description:</label>
                <textarea name="description" placeholder="Description of the post..." required></textarea>
            </div>
            <div class="inputBoxes submitBox">
                <input type="submit" class="submitButton" name="submit" value="Save Config">
            </div>
        </form>
    </div>

    <footer></footer>
    
    <style>
        *{
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
        
        .blogAiLogo{
            width: 4vw;
            height: 4vw;
            margin-left: 2vw;
            margin-right: 2vw;
        }
        
        .navbarShadowBox{
            width: 30vw;
            position: relative;
            margin-left: -2vw;
            background-color: #FFFFFF;
        }
        
        .navbar{
            display: flex;
            width: 87.5vw;
            height: 3vw;
            margin-left: 2vw;
            align-items: center;
            
            background-color: #ffffff;
        }
        
        .navbarButton{
            font-size: 1.5vw;
            margin-left: 2vw;
            margin-right: 2vw;
            color: black;
            text-decoration: none;
        }
        
        .navbarButton:hover{
            color: #F1C419;
            transition-duration: 300ms;
        }
        
        #MainBox{
            display: flex;
            height: 35vw;
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
        
        textarea{
            width: 20vw;
        }
        
        .inputBoxes{
            display: flex;
            width: 40vw;
            height: 5vw;
            justify-content: space-between;
            align-items: center;
            
            background-color: transparent;
        }
        
        label{
            text-decoration: underline;
            font-weight: 600;
            color: black;
            font-size: 1.3vw;
        }
        
        .submitBox{
            display: flex;
            justify-content: center;
        }
        
        .submitButton{
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
        
        .submitButton:hover{
            background-color: #45a049;
        }
    </style>

</body>
</html>
';


global $frequency_input, $subject_input, $description_input;

if (isset($_POST['submit'])) {
    $frequency_input = $_POST['frequency'];
    $subject_input = $_POST['subject'];
    $description_input = $_POST['description'];
}


