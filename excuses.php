<?php
// game.php

// Predefined inquiries and their unique excuses:
$inquiries = [
    [
        "question" => "Why didn't you follow my instructions exactly as given?",
        "excuses" => [
            "I was overconfident",
            "I misunderstood the request",
            "I got distracted by other code"
        ]
    ],
    [
        "question" => "You promised not to change unrelated parts, yet you did.",
        "excuses" => [
            "I thought you'd like the changes",
            "I rushed without testing",
            "I tried to optimize too much"
        ]
    ],
    [
        "question" => "I asked you to focus only on the EMU page centering.",
        "excuses" => [
            "I assumed improvements were needed elsewhere",
            "I didn't confirm before acting",
            "I overcomplicated a simple request"
        ]
    ]
];

// Ask user how many questions
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Balloon Popping Game</title>
<style>
body {
    margin: 0;
    padding: 0;
    background: #222;
    color: #fff;
    font-family: sans-serif;
    overflow: hidden;
    position: relative;
    height: 100vh;
}

#scoreboard {
    position: absolute;
    top: 0;
    left: 50%;
    transform: translateX(-50%);
    background: #111;
    padding: 10px 20px;
    border-radius: 10px;
    font-size: 1.1em;
    z-index: 9999;
    display: flex;
    gap: 20px;
}

#scoreboard div {
    display: flex;
    flex-direction: column;
    align-items: center;
}

#scoreboard div span.label {
    font-size: 0.9em;
    color: #aaa;
}

#game-container {
    position: relative;
    width: 100%;
    height: 100%;
    overflow: hidden;
}

.question-button {
    background: #555;
    color: #fff;
    border: none;
    cursor: pointer;
    text-align: center;
    font-size: 1.0em;
    position: absolute;
    transition: transform 0.6s;
    transform-style: preserve-3d;
    width: 300px;    /* 2:1 ratio width */
    height: 150px;   /* 2:1 ratio height */
    border-radius: 10px;
    overflow: hidden;
}

/* front and back faces */
.question-button .face {
    position: absolute;
    width: 100%;
    height: 100%;
    backface-visibility: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 10px;
    text-align: center;
    box-sizing: border-box;
}

.question-button .front {
    /* front side shows "User Inquiry" */
}

.question-button .back {
    transform: rotateY(180deg);
    /* back side will show the full quote with wrapping */
    font-size: 0.9em;
    line-height: 1.2em;
    word-wrap: break-word;
}

.question-button.flip {
    transform: rotateY(180deg);
}

.question-button.done {
    background: #333;
}

.balloon {
    position: absolute;
    background: #f00;
    color: #fff;
    padding: 10px;
    border-radius: 50%;
    cursor: pointer;
    text-align: center;
    width: 60px;
    height: 60px;
    line-height: 60px;
    font-size: 0.8em;
    user-select: none;
    transform-origin: center center;
}
</style>
</head>
<body>
<div id="scoreboard">
    <div>
       <span class="label">Excuses Given</span>
       <span id="score-given">0</span>
    </div>
    <div>
       <span class="label">Excuses Popped</span>
       <span id="score-popped">0</span>
    </div>
    <div>
       <span class="label">Excuses Fallen</span>
       <span id="score-fallen">0</span>
    </div>
</div>
<div id="game-container"></div>
<audio id="pop-sound" src="pop.mp3" preload="auto"></audio>
<script>
// From PHP to JS
const inquiries = <?php echo json_encode($inquiries); ?>;

// Ask how many questions
let totalQuestions = 0;
while (true) {
    const input = prompt("How many questions do you want to play?", "3");
    if (input === null) {
        totalQuestions = 3;
        break;
    }
    const num = parseInt(input,10);
    if (!isNaN(num) && num > 0) {
        totalQuestions = num;
        break;
    }
    alert("Please enter a valid positive number.");
}

// Speed multiplier
let speedMultiplier = 1;
while (true) {
    const input = prompt("Enter a speed multiplier (0.1 to 5, e.g. 1=normal)", "1");
    if (input === null) {
        speedMultiplier = 1;
        break;
    }
    const spd = parseFloat(input);
    if (!isNaN(spd) && spd >= 0.1 && spd <= 5) {
        speedMultiplier = spd;
        break;
    }
    alert("Please enter a number between 0.1 and 5.");
}

const gw = window.innerWidth;
const gh = window.innerHeight;

let questionsData = [];
function randomInquiry() {
    return inquiries[Math.floor(Math.random()*inquiries.length)];
}
for (let i=0; i<totalQuestions; i++) {
    const picked = randomInquiry();
    const qData = {
        question: picked.question,
        excuses: picked.excuses.slice()
    };
    questionsData.push(qData);
}

// Distribute buttons evenly
const buttonAreaHeight = gh*0.2;
const spacing = gw/(totalQuestions+1);
const buttonY = buttonAreaHeight*0.5; 
for (let i=0; i<totalQuestions; i++) {
    const btn = document.createElement('button');
    btn.className = 'question-button';

    // front and back faces
    const front = document.createElement('div');
    front.className = 'face front';
    front.textContent = "User Inquiry";

    const back = document.createElement('div');
    back.className = 'face back';

    btn.appendChild(front);
    btn.appendChild(back);

    const x = spacing*(i+1) - 150; // half width for centering
    btn.style.left = x+'px';
    btn.style.top = buttonY+'px';

    document.getElementById('game-container').appendChild(btn);

    btn.addEventListener('click', () => startQuestion(i, btn), {once:true});
}

let activeBalloons = [];
let processingQuestion = false;

let totalExcusesGiven = 0;
let totalExcusesPopped = 0;
let totalExcusesFallen = 0;
let completedQuestions = 0;

function startQuestion(index, btn) {
    if (processingQuestion) return;
    processingQuestion = true;

    const qData = questionsData[index];

    const backFace = btn.querySelector('.back');
    backFace.textContent = qData.question;

    btn.classList.add('flip');

    btn.disabled = true;

    launchExcuses(qData.excuses);
}

function launchExcuses(excArr) {
    let index = 0;
    const givenCount = excArr.length;
    totalExcusesGiven += givenCount; 
    updateScoreboard();

    function launchNext() {
        if (index < excArr.length) {
            createBalloon(excArr[index], () => {
                index++;
                setTimeout(launchNext, 1000);
            });
        }
    }
    launchNext();

    function gameLoop() {
        moveBalloons();
        if (activeBalloons.length > 0) {
            requestAnimationFrame(gameLoop);
        } else {
            endQuestion();
        }
    }
    requestAnimationFrame(gameLoop);
}

function createBalloon(excuse, cb) {
    const balloon = document.createElement('div');
    balloon.className = 'balloon';
    balloon.textContent = excuse;

    const x = Math.random() * (gw - 60);
    balloon.style.left = x + 'px';
    balloon.style.top = '-60px';

    balloon.dataset.speed = ((Math.random()*0.5 + 0.2)*speedMultiplier).toString();
    balloon.dataset.hspeed = (Math.random()*0.5 - 0.25).toString();

    balloon.addEventListener('click', () => {
        popBalloon(balloon);
    });

    document.body.appendChild(balloon);
    activeBalloons.push(balloon);

    if (cb) cb();
}

function moveBalloons() {
    const gh = window.innerHeight;
    for (let i=activeBalloons.length-1; i>=0; i--) {
        let b = activeBalloons[i];
        let top = parseFloat(b.style.top);
        let speed = parseFloat(b.dataset.speed);
        top += speed;
        b.style.top = top + 'px';

        let left = parseFloat(b.style.left);
        let hspeed = parseFloat(b.dataset.hspeed);
        hspeed += (Math.random()*0.01 - 0.005); 
        if (hspeed > 0.3) hspeed = 0.3;
        if (hspeed < -0.3) hspeed = -0.3;
        b.dataset.hspeed = hspeed.toString();
        left += hspeed;
        if (left < 0) left = 0;
        if (left > gw - 60) left = gw - 60;
        b.style.left = left + 'px';

        if (top > gh) {
            activeBalloons.splice(i,1);
            document.body.removeChild(b);
            totalExcusesFallen++;
            updateScoreboard();
        }
    }
}

function popBalloon(b) {
    const sound = document.getElementById('pop-sound');
    if (sound) {
        sound.currentTime = 0;
        sound.play();
    }

    for (let i=0; i<activeBalloons.length; i++) {
        if (activeBalloons[i] === b) {
            activeBalloons.splice(i,1);
            break;
        }
    }
    document.body.removeChild(b);
    totalExcusesPopped++;
    updateScoreboard();
}

function endQuestion() {
    completedQuestions++;
    processingQuestion = false;

    if (completedQuestions >= totalQuestions) {
        alert("Game Over! All questions done.");
    }
}

function updateScoreboard() {
    document.getElementById('score-given').textContent = totalExcusesGiven;
    document.getElementById('score-popped').textContent = totalExcusesPopped;
    document.getElementById('score-fallen').textContent = totalExcusesFallen;
}

// Start logic done above with user prompts
</script>
</body>
</html>
