<?php
session_start();
if(!isset($_SESSION['username'])){
  header("Location: login.html");
  exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Dashboard — Scutter Casino</title>
  <style>
    body {
      font-family: Arial;
      background: #081428;
      color: #fff;
      text-align: center;
      padding-top: 80px;
    }
    a {
      display: inline-block;
      margin-top: 20px;
      color: #f59e0b;
      text-decoration: none;
      font-weight: bold;
      cursor: pointer;
    }

    /* Loading overlay styles */
    #loadingScreen {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(8, 20, 40, 0.95);
      display: none;
      align-items: center;
      justify-content: center;
      flex-direction: column;
      z-index: 9999;
    }

    #loadingBarContainer {
      width: 60%;
      max-width: 400px;
      height: 20px;
      background: #1a2745;
      border-radius: 10px;
      overflow: hidden;
      margin-top: 20px;
    }

    #loadingBar {
      width: 0%;
      height: 100%;
      background: linear-gradient(90deg, #f59e0b, #ffd54f);
      transition: width 0.1s ease-in-out;
    }

    #loadingText {
      margin-top: 15px;
      font-size: 18px;
      color: #fcd34d;
    }
  </style>
</head>
<body>
  <h1>Welcome, <?php echo $_SESSION['username']; ?>!</h1>
  <p>Your current credits: ₱<?php echo $_SESSION['credits']; ?></p>

  <a id="enterCasino">🎰 Enter the Casino Game</a><br>
  <a href="logout.php">Logout</a>

  <!-- Loading Screen -->
  <div id="loadingScreen">
    <h2>Loading Scutter Casino...</h2>
    <div id="loadingBarContainer">
      <div id="loadingBar"></div>
    </div>
    <p id="loadingText">0%</p>
  </div>

  <script>
    const enterCasino = document.getElementById('enterCasino');
    const loadingScreen = document.getElementById('loadingScreen');
    const loadingBar = document.getElementById('loadingBar');
    const loadingText = document.getElementById('loadingText');

    enterCasino.addEventListener('click', () => {
      loadingScreen.style.display = 'flex';
      let progress = 0;
      const interval = setInterval(() => {
        progress += Math.random() * 10; // simulate loading speed
        if (progress >= 100) {
          progress = 100;
          clearInterval(interval);
          loadingText.textContent = "100%";
          setTimeout(() => {
            window.location.href = "index.php";
          }, 400);
        }
        loadingBar.style.width = progress + "%";
        loadingText.textContent = Math.floor(progress) + "%";
      }, 150);
    });
  </script>
</body>
</html>
