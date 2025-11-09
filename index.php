<?php
session_start();
if(!isset($_SESSION['credits'])) $_SESSION['credits'] = 10; // default starting credits
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Scutter Casino — 9-Reel Fruit Machine</title>
  <style>
    :root{--bg:#0b1020;--card:#0f1724;--accent:#f59e0b;--muted:#93c5fd}
    *{box-sizing:border-box;font-family:Inter,system-ui,Segoe UI,Roboto,Arial}
    body{margin:0;background:linear-gradient(180deg,#031026 0%, #071428 100%);color:#e6eef8;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}
    .app{width:100%;max-width:1100px;background:linear-gradient(180deg,rgba(255,255,255,0.02),transparent);border-radius:12px;padding:18px;box-shadow:0 10px 30px rgba(2,6,23,0.6)}
    header{display:flex;justify-content:space-between;align-items:center;margin-bottom:14px}
    h1{font-size:18px;margin:0}
    .top-controls{display:flex;gap:10px;align-items:center}
    .chip{background:rgba(255,255,255,0.03);padding:8px 12px;border-radius:8px;font-weight:600}
    .slot-area{display:grid;grid-template-columns:1fr 320px;gap:18px}
    .machine{background:var(--card);padding:18px;border-radius:10px;display:flex;flex-direction:column;align-items:center}
    .grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;background:linear-gradient(180deg,#071427,#041428);padding:14px;border-radius:10px}
    .cell{width:100px;height:100px;background:linear-gradient(180deg,#0b1830,#08122a);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:48px;transition:transform .3s}
    .winCell{animation:winFlash 1s ease-in-out infinite alternate}
    .controls{margin-top:12px;display:flex;gap:8px;align-items:center;flex-wrap:wrap;justify-content:center}
    button{background:var(--accent);border:none;padding:12px 16px;border-radius:8px;font-weight:700;cursor:pointer}
    button:disabled{opacity:.5;cursor:not-allowed}
    .side{background:linear-gradient(180deg,#071428,#041428);padding:14px;border-radius:10px;display:flex;flex-direction:column;gap:12px}
    .balance{font-size:20px;font-weight:800}
    .bet-row{display:flex;gap:8px;align-items:center}
    .small{background:rgba(255,255,255,0.03);padding:8px;border-radius:8px}
    .message{min-height:28px;text-align:center}
    footer{margin-top:12px;text-align:center;color:rgba(230,238,248,.6);font-size:13px}
    @keyframes winFlash{from{transform:scale(1)}to{transform:scale(1.15)}}
    @media (max-width:880px){
      .slot-area{grid-template-columns:1fr;}
      .cell{width:80px;height:80px;font-size:36px}
    }
  </style>
</head>
<body>
  <div class="app">
    <header>
      <h1>Scatter Casino — 9-Reel Fruit Machine</h1>
      <div class="top-controls">
        <div class="chip">Credits: <span id="creditsLabel">0</span></div>
        <div class="chip">Bet: <span id="betLabel">10</span></div>
      </div>
    </header>

    <div class="slot-area">
      <div class="machine">
        <div class="grid" id="grid"></div>
        <div class="controls">
          <button id="spinBtn">SPIN</button>
          <button id="autoBtn">AUTO</button>
          <div class="small">Win: <strong id="lastWin">0</strong></div>
        </div>
        <div class="message" id="message"></div>
      </div>

      <aside class="side">
        <div>
          <div class="balance">Balance
            <div id="balance">0</div>
          </div>
        </div>
        <div>
          <div class="bet-row">
            <button id="betDown">-</button>
            <div class="small">Bet: <strong id="bet">10</strong></div>
            <button id="betUp">+</button>
          </div>
        </div>
        <div>
          <h4>Controls</h4>
          <div style="display:flex;gap:8px;flex-wrap:wrap">
            <button id="maxBet">Max Bet</button>
          </div>
        </div>
      </aside>
    </div>
  </div>

<script>
const SYMBOLS = ["🍒","🍋","🍇","🍉","🍊","🍎","🍓","🥝","💎","7️⃣","🍍","🥭","🥥"];
let credits = <?php echo $_SESSION['credits']; ?>; // load from PHP session
let bet = 10, auto = false, spinning = false;
let spinCount = 0; 
const symbolWins = {};

const grid = document.getElementById('grid');
const message = document.getElementById('message');
const creditsLabel = document.getElementById('creditsLabel');
const balanceEl = document.getElementById('balance');
const betEl = document.getElementById('bet');
const betLabel = document.getElementById('betLabel');
const lastWin = document.getElementById('lastWin');
const spinBtn = document.getElementById('spinBtn');
const autoBtn = document.getElementById('autoBtn');

// audio variables
let spinAudioCtx = null;
let spinOsc = null;

// initialize grid
for(let i=0;i<9;i++){
  const div=document.createElement('div');
  div.className='cell';
  div.textContent=SYMBOLS[Math.floor(Math.random()*SYMBOLS.length)];
  grid.appendChild(div);
}

function updateUI(){
  creditsLabel.textContent=credits;
  balanceEl.textContent=credits;
  betEl.textContent=bet;
  betLabel.textContent=bet;
  saveCreditsToServer(); // save every time UI updates
}

function saveCreditsToServer() {
  fetch('update_credits.php', {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded'},
    body: 'credits=' + credits
  });
}


// 🔊 Start spin sound
function startSpinSound(){
  spinAudioCtx = new (window.AudioContext || window.webkitAudioContext)();
  spinOsc = spinAudioCtx.createOscillator();
  const gain = spinAudioCtx.createGain();
  spinOsc.type = 'sawtooth';
  spinOsc.frequency.value = 320;
  gain.gain.setValueAtTime(0.05, spinAudioCtx.currentTime);
  spinOsc.connect(gain).connect(spinAudioCtx.destination);
  spinOsc.start();
  const sweep = setInterval(() => {
    if(!spinning){clearInterval(sweep);return;}
    spinOsc.frequency.value = 300 + Math.random()*100;
  }, 100);
}

// 🔇 Stop spin sound
function stopSpinSound(){
  if(spinOsc && spinAudioCtx){
    spinOsc.stop();
    spinAudioCtx.close();
    spinOsc = null;
    spinAudioCtx = null;
  }
}
 
function spin(){
  if(spinning) return;
  if(credits<bet){message.textContent="Not enough credits!";return;}
  spinning=true;
  credits-=bet;
  spinCount++;
  updateUI();
  message.textContent="Spinning...";
  const cells=document.querySelectorAll('.cell');
  cells.forEach(c=>c.classList.remove('winCell'));
  startSpinSound();

  let steps=15;
  const interval=setInterval(()=>{
    cells.forEach(c=>c.textContent=SYMBOLS[Math.floor(Math.random()*SYMBOLS.length)]);
    steps--;
    if(steps<=0){
      clearInterval(interval);
      stopSpinSound();

      const gridSym=[[],[],[]];
      for(let r=0;r<3;r++){
        for(let c=0;c<3;c++){
          gridSym[r][c]=SYMBOLS[Math.floor(Math.random()*SYMBOLS.length)];
        }
      }

      if(spinCount % 1 === 0){
        const winLines=[[0,0,0,1,0,2],[1,0,1,1,1,2],[2,0,2,1,2,2],[0,0,1,0,2,0],[0,1,1,1,2,1],[0,2,1,2,2,2],[0,0,1,1,2,2],[0,2,1,1,2,0]];
        const chosenLine = winLines[Math.floor(Math.random()*winLines.length)];
        const sym = SYMBOLS[Math.floor(Math.random()*SYMBOLS.length)];
        for(let i=0;i<3;i++){
          gridSym[chosenLine[i*2]][chosenLine[i*2+1]]=sym;
        }
      }

      for(let r=0;r<3;r++){
        for(let c=0;c<3;c++){
          cells[r*3+c].textContent = gridSym[r][c];
        }
      }

      evaluate();
      spinning=false;
    }
  },100);
}

function evaluate(){
  const cells=[...document.querySelectorAll('.cell')];
  const gridSym=[];
  for(let r=0;r<3;r++){
    gridSym[r]=[];
    for(let c=0;c<3;c++){
      gridSym[r][c]=cells[r*3+c].textContent;
    }
  }

  const winLines=[
    [[0,0],[0,1],[0,2]],
    [[1,0],[1,1],[1,2]],
    [[2,0],[2,1],[2,2]],
    [[0,0],[1,0],[2,0]],
    [[0,1],[1,1],[2,1]],
    [[0,2],[1,2],[2,2]],
    [[0,0],[1,1],[2,2]],
    [[0,2],[1,1],[2,0]]
  ];

  let totalWin=0;

  for(const line of winLines){
    const s0=gridSym[line[0][0]][line[0][1]];
    const s1=gridSym[line[1][0]][line[1][1]];
    const s2=gridSym[line[2][0]][line[2][1]];
    if(s0===s1 && s1===s2){
      const prevWins = symbolWins[s0] || 0;
      const win = bet * (5 + prevWins);
      totalWin += win;
      symbolWins[s0] = prevWins + 1;
      line.forEach(([r,c])=>cells[r*3+c].classList.add('winCell'));
    }
  }

  if(totalWin>0){
    credits += totalWin;
    message.textContent=`🎉 Super Ace! You won ₱${totalWin}!`;
    playSoundWin();
  } else {
    message.textContent="No win — try again.";
  }

  lastWin.textContent=totalWin;
  updateUI();

  if(auto && credits>=bet)setTimeout(spin,700);
  else{auto=false;autoBtn.textContent="AUTO";}
}

function playSoundWin(){
  try{
    const ctx=new (window.AudioContext||window.webkitAudioContext)();
    const o=ctx.createOscillator();
    const g=ctx.createGain();
    o.type='triangle';o.frequency.value=880;g.gain.value=0.07;
    o.connect(g);g.connect(ctx.destination);o.start();
    setTimeout(()=>{o.frequency.value=660},120);
    setTimeout(()=>{o.stop();ctx.close()},400);
  }catch(e){}
}

// Controls
spinBtn.addEventListener('click',()=>spin());
autoBtn.addEventListener('click',()=>{
  auto=!auto;autoBtn.textContent=auto?"STOP":"AUTO";
  if(auto&&!spinning)spin();
});
document.getElementById('betUp').addEventListener('click',()=>{bet=Math.min(credits,bet+1);updateUI();});
document.getElementById('betDown').addEventListener('click',()=>{bet=Math.max(1,bet-1);updateUI();});
document.getElementById('maxBet').addEventListener('click',()=>{bet=Math.min(100,credits);updateUI();});

updateUI();
</script>
</body>
</html>
