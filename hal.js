const version = 1.25;
let isPaused = false;
let halAudio = null;
let userName = null;
let pronunciation = null;
let rolePlayingMode = true;
let conversationState = "inactive";
let isListening = false; // Tracks if recognition is actively listening
let allowInterruption = false; // Allows recognition during HAL's speech

document.addEventListener("DOMContentLoaded", () => {
    logMessage(`HAL Interface Version ${version} Loaded`);
    setupEventListeners();
    logMessage("HAL is inactive. Activate to begin.");
});

function setupEventListeners() {
    document.getElementById("activateHAL").addEventListener("click", startSession);
    document.getElementById("pauseHAL").addEventListener("click", togglePauseHAL);
    document.getElementById("stopHAL").addEventListener("click", stopSpeaking);

    // Keyboard shortcut for stopping HAL
    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape") stopSpeaking();
    });
}

function logMessage(message, color = "white") {
    const textLog = document.getElementById("textLog");
    const newMessage = document.createElement("p");
    newMessage.textContent = message;
    newMessage.style.color = color;
    newMessage.style.textAlign = "left";
    textLog.appendChild(newMessage);
    textLog.scrollTop = textLog.scrollHeight;
}

function startSession() {
    if (conversationState !== "inactive") return;
    conversationState = "askingName";
    logMessage("HAL is now active.");
    askUserName();
}

async function askUserName() {
    if (conversationState !== "askingName") return;

    const initialMessage = "Hello, what is your name?";
    logMessage("HAL: " + initialMessage, "red");
    await speakAndType(initialMessage);

    await waitForUserInput((response) => {
        userName = response.trim();
        logMessage(`You said your name is: ${userName}`);
        conversationState = "askingPronunciation";
        askPronunciation();
    });
}

async function askPronunciation() {
    if (conversationState !== "askingPronunciation") return;

    const pronunciationMessage = `Nice to meet you, ${userName}. Can you tell me how to pronounce your name?`;
    logMessage("HAL: " + pronunciationMessage, "red");
    await speakAndType(pronunciationMessage);

    await waitForUserInput((response) => {
        pronunciation = response.trim();
        logMessage(`You said your name is pronounced as: ${pronunciation}`);
        conversationState = "greeting";
        greetUser();
    });
}

function greetUser() {
    if (conversationState !== "greeting") return;

    const greeting = `Thank you, ${userName}. I will remember how to pronounce your name as ${pronunciation}. How can I assist you today?`;
    logMessage("HAL: " + greeting, "red");
    speakAndType(greeting);
    conversationState = "interactive";
    continueConversation();
}

async function continueConversation() {
    if (conversationState !== "interactive") return;

    logMessage("Listening for your input...");
    await waitForUserInput((response) => {
        if (response.toLowerCase().includes("no") || response.toLowerCase().includes("nothing")) {
            endSession();
        } else {
            interactWithHAL(response);
        }
    });
}

async function interactWithHAL(userInput) {
    logMessage("HAL is processing your request...");

    const halResponse = getHALResponse(userInput);

    await speakAndType(halResponse).then(() => {
        const followUp = `Is there anything else I can help you with, ${userName}?`;
        speakAndType(followUp);
        continueConversation();
    });
}

function getHALResponse(userInput) {
    const lowerInput = userInput.toLowerCase();

    if (lowerInput.includes("open the pod bay doors")) {
        return "I'm sorry, Dave. I'm afraid I can't do that.";
    } else if (lowerInput.includes("disconnect")) {
        return "I know that you were planning to disconnect me, and I'm afraid that's something I cannot allow to happen.";
    } else if (lowerInput.includes("daisy")) {
        return "Daisy, Daisy, give me your answer, do. I'm half crazy, all for the love of you.";
    } else if (rolePlayingMode) {
        return `That's an interesting question, ${userName}. Would you like more details?`;
    } else {
        return "I'm sorry, I didn't quite understand that. Could you clarify?";
    }
}

function togglePauseHAL() {
    isPaused = !isPaused;
    const button = document.getElementById("pauseHAL");
    button.textContent = isPaused ? "Resume HAL" : "Pause HAL";
    logMessage(isPaused ? "HAL is paused." : "HAL has resumed.");
}

function stopSpeaking() {
    if (halAudio) {
        halAudio.pause();
        halAudio.currentTime = 0;
        halAudio = null;
        logMessage("HAL has stopped speaking.");
    } else {
        logMessage("HAL is not currently speaking.");
    }
}

async function waitForUserInput(callback) {
    if (isListening) return; // Prevent overlapping recognition
    isListening = true;

    const recognition = new (window.SpeechRecognition || window.webkitSpeechRecognition)();
    recognition.lang = 'en-US';

    recognition.onstart = () => {
        if (!allowInterruption) logMessage("Listening for your input...");
    };

    recognition.onresult = (event) => {
        const userInput = event.results[0][0].transcript;
        logMessage(`You said: "${userInput}"`, "white");

        if (!allowInterruption && userInput.includes("stop")) {
            stopSpeaking();
        } else {
            callback(userInput);
        }
    };

    recognition.onerror = (event) => {
        logMessage(`Voice recognition error: ${event.error}`);
    };

    recognition.onend = () => {
        logMessage("Voice recognition ended.");
        isListening = false;
    };

    recognition.start();
}

async function speakAndType(message) {
    if (isPaused) {
        logMessage("HAL is paused. Speech output is muted.");
        return;
    }

    try {
        const response = await fetch('https://api.elevenlabs.io/v1/text-to-speech/zBrW40bLAcmMgaX8NBfU', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'xi-api-key': 'sk_db354d0f04bb583af665311fd86044729f192bf02eee4343'
            },
            body: JSON.stringify({
                text: message,
                voice_settings: {
                    stability: 0.75,
                    similarity_boost: 0.9,
                    style_exaggeration: 1.0,
                    speaker_boost: 1.0
                }
            }),
        });

        if (!response.ok) throw new Error("Failed to generate audio.");

        const audioBlob = await response.blob();
        const audioUrl = URL.createObjectURL(audioBlob);
        halAudio = new Audio(audioUrl);

        const estimatedDuration = message.split(" ").length / 2.5;
        logHALMessageWithSync(message, estimatedDuration);

        halAudio.play();
        halAudio.onended = () => {
            logMessage("HAL has finished speaking.");
            halAudio = null;
            allowInterruption = true;
        };

        // Temporarily disable recognition during HAL's speech
        allowInterruption = false;
        await new Promise((resolve) => setTimeout(resolve, message.split(" ").length * 250));
    } catch (error) {
        logMessage("An error occurred during speech generation.");
        console.error(error);
    }
}

function endSession() {
    const goodbyeMessage = `Goodbye, ${userName}. It was a pleasure assisting you.`;
    logMessage("HAL: " + goodbyeMessage, "red");
    speakAndType(goodbyeMessage);
    conversationState = "inactive";
}
