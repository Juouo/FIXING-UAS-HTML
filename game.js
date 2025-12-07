// ============ GAME CONFIGURATION ============
const gameConfig = {
    initialBalance: 0,
    userId: 0,
    updateBalanceCallback: null,
    getBalance: null
};

// ============ GAME VARIABLES ============
let gameBalance = 0;
let deck = [];
let dealer = [];
let player = [];
let dealerHidden = true;
let currentBet = 0;
let inRound = false;

// ============ DOM ELEMENTS ============
let dealerHandEl, playerHandEl, dealerValueEl, playerValueEl;
let balanceEl, betInput, betBtn, currentBetEl;
let hitBtn, standBtn, doubleBtn, newRoundBtn, messageEl;

// ============ GAME INITIALIZATION ============
function initializeBlackjack(config) {
    gameConfig.initialBalance = config.initialBalance;
    gameConfig.userId = config.userId;
    gameConfig.updateBalanceCallback = config.updateBalanceCallback;
    gameConfig.getBalance = config.getBalance;
    
    gameBalance = gameConfig.initialBalance;
    
    initializeDOM();
    setupEventListeners();
    updateGameUI();
    
    console.log('Blackjack initialized with balance:', gameBalance);
}

function initializeDOM() {
    // Create game HTML
    const gameArea = document.getElementById('gameArea');
    gameArea.innerHTML = `
        <div class="game-header">
            <div class="game-balance">
                <h3><i class="fas fa-coins"></i> SALDO GAME</h3>
                <div class="balance-amount" id="gameBalanceDisplay">Rp 0</div>
            </div>
            <div class="game-bet">
                <h3><i class="fas fa-money-bill-wave"></i> TARUHAN</h3>
                <input type="number" id="game-bet-input" placeholder="Jumlah taruhan" value="10000" min="1000" max="1000000">
                <button id="game-bet-btn">PASANG TARUHAN</button>
                <div class="current-bet" id="game-current-bet">Taruhan: Rp 0</div>
            </div>
        </div>
        
        <div class="game-board">
            <div class="dealer-area">
                <h3><i class="fas fa-robot"></i> DEALER</h3>
                <div class="cards" id="game-dealer-hand"></div>
                <div class="score" id="game-dealer-value">Skor: ??</div>
            </div>
            
            <div class="player-area">
                <h3><i class="fas fa-user"></i> PLAYER</h3>
                <div class="cards" id="game-player-hand"></div>
                <div class="score" id="game-player-value">Skor: 0</div>
            </div>
        </div>
        
        <div class="game-controls">
            <button id="game-hit" class="game-btn hit"><i class="fas fa-plus"></i> HIT</button>
            <button id="game-stand" class="game-btn stand"><i class="fas fa-hand-paper"></i> STAND</button>
            <button id="game-double" class="game-btn double"><i class="fas fa-times"></i> DOUBLE</button>
            <button id="game-new-round" class="game-btn new-round"><i class="fas fa-redo"></i> RONDE BARU</button>
        </div>
        
        <div class="game-message" id="game-message">
            Pilih taruhan dan mulai game!
        </div>
    `;
    
    // Get DOM elements
    dealerHandEl = document.getElementById('game-dealer-hand');
    playerHandEl = document.getElementById('game-player-hand');
    dealerValueEl = document.getElementById('game-dealer-value');
    playerValueEl = document.getElementById('game-player-value');
    balanceEl = document.getElementById('gameBalanceDisplay');
    betInput = document.getElementById('game-bet-input');
    betBtn = document.getElementById('game-bet-btn');
    currentBetEl = document.getElementById('game-current-bet');
    hitBtn = document.getElementById('game-hit');
    standBtn = document.getElementById('game-stand');
    doubleBtn = document.getElementById('game-double');
    newRoundBtn = document.getElementById('game-new-round');
    messageEl = document.getElementById('game-message');
    
    // Add CSS
    const style = document.createElement('style');
    style.textContent = `
        .game-header {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .game-balance, .game-bet {
            flex: 1;
            background: rgba(0,0,0,0.3);
            padding: 15px;
            border-radius: 10px;
            min-width: 250px;
        }
        
        .balance-amount {
            font-size: 2em;
            font-weight: bold;
            color: #4CAF50;
            margin-top: 10px;
        }
        
        #game-bet-input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 2px solid #2d4256;
            border-radius: 5px;
            background: rgba(255,255,255,0.1);
            color: white;
            font-size: 1.1em;
        }
        
        #game-bet-btn {
            width: 100%;
            padding: 12px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            font-size: 1.1em;
            transition: all 0.3s;
        }
        
        #game-bet-btn:hover {
            background: #45a049;
            transform: translateY(-2px);
        }
        
        #game-bet-btn:disabled {
            background: #666;
            cursor: not-allowed;
        }
        
        .current-bet {
            margin-top: 10px;
            color: #f39c12;
            font-weight: bold;
        }
        
        .game-board {
            display: flex;
            gap: 30px;
            margin: 30px 0;
            flex-wrap: wrap;
        }
        
        .dealer-area, .player-area {
            flex: 1;
            background: rgba(0,0,0,0.3);
            padding: 20px;
            border-radius: 10px;
            min-width: 300px;
        }
        
        .cards {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            min-height: 150px;
            padding: 15px;
            background: rgba(0,0,0,0.5);
            border-radius: 10px;
            margin: 15px 0;
        }
        
        .card {
            width: 70px;
            height: 100px;
            background: white;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 10px;
            color: #333;
            font-weight: bold;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
        }
        
        .card.red {
            color: #e74c3c;
        }
        
        .card.back {
            background: #2d4256;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5em;
        }
        
        .score {
            font-size: 1.2em;
            font-weight: bold;
            color: #4CAF50;
            text-align: center;
        }
        
        .game-controls {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin: 20px 0;
            flex-wrap: wrap;
        }
        
        .game-btn {
            padding: 15px 25px;
            border: none;
            border-radius: 8px;
            font-size: 1.1em;
            font-weight: bold;
            cursor: pointer;
            min-width: 140px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s;
        }
        
        .game-btn:hover:not(:disabled) {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        
        .game-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .game-btn.hit {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
        }
        
        .game-btn.stand {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
        }
        
        .game-btn.double {
            background: linear-gradient(135deg, #f39c12, #e67e22);
            color: white;
        }
        
        .game-btn.new-round {
            background: linear-gradient(135deg, #9b59b6, #8e44ad);
            color: white;
        }
        
        .game-message {
            text-align: center;
            font-size: 1.2em;
            padding: 15px;
            background: rgba(0,0,0,0.3);
            border-radius: 10px;
            margin-top: 20px;
            min-height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .win-message {
            color: #4CAF50;
            font-weight: bold;
        }
        
        .lose-message {
            color: #e74c3c;
            font-weight: bold;
        }
    `;
    document.head.appendChild(style);
}

// ============ GAME FUNCTIONS ============
function setupEventListeners() {
    betBtn.addEventListener('click', startRound);
    hitBtn.addEventListener('click', playerHit);
    standBtn.addEventListener('click', playerStand);
    doubleBtn.addEventListener('click', playerDouble);
    newRoundBtn.addEventListener('click', resetGame);
    
    betInput.addEventListener('input', () => {
        const bet = parseInt(betInput.value) || 0;
        betBtn.disabled = bet > gameBalance || bet < 1000;
    });
    
    // Keyboard shortcuts
    document.addEventListener('keydown', (e) => {
        if (e.key === 'h' || e.key === 'H') playerHit();
        if (e.key === 's' || e.key === 'S') playerStand();
        if (e.key === 'd' || e.key === 'D') playerDouble();
        if (e.key === 'Enter' && !inRound) startRound();
    });
}

function updateGameUI() {
    updateBalanceDisplay();
    updateGameControls();
    
    if (dealerHandEl && playerHandEl) {
        renderCards();
    }
}

function updateBalanceDisplay() {
    if (balanceEl) {
        gameBalance = gameConfig.getBalance ? gameConfig.getBalance() : gameBalance;
        balanceEl.textContent = `Rp ${gameBalance.toLocaleString('id-ID')}`;
        
        // Update bet button state
        if (betBtn) {
            const bet = parseInt(betInput.value) || 0;
            betBtn.disabled = bet > gameBalance || bet < 1000;
        }
    }
}

function updateGameControls() {
    if (hitBtn && standBtn && doubleBtn && betBtn) {
        hitBtn.disabled = !inRound;
        standBtn.disabled = !inRound;
        doubleBtn.disabled = !inRound || gameBalance < currentBet;
        betBtn.disabled = inRound;
        
        // Update bet input
        if (betInput) {
            betInput.disabled = inRound;
        }
    }
}

function renderCards() {
    // Clear cards
    dealerHandEl.innerHTML = '';
    playerHandEl.innerHTML = '';
    
    // Render dealer cards
    dealer.forEach((card, index) => {
        const cardDiv = document.createElement('div');
        cardDiv.className = 'card';
        
        if (dealerHidden && index === 1) {
            // Hide second card during game
            cardDiv.classList.add('back');
            cardDiv.textContent = '?';
        } else {
            if (card.suit === '♥' || card.suit === '♦') {
                cardDiv.classList.add('red');
            }
            
            const top = document.createElement('div');
            const bottom = document.createElement('div');
            
            top.textContent = `${card.rank} ${card.suit}`;
            bottom.textContent = `${card.rank} ${card.suit}`;
            bottom.style.alignSelf = 'flex-end';
            bottom.style.transform = 'rotate(180deg)';
            
            cardDiv.appendChild(top);
            cardDiv.appendChild(bottom);
        }
        
        dealerHandEl.appendChild(cardDiv);
    });
    
    // Render player cards
    player.forEach(card => {
        const cardDiv = document.createElement('div');
        cardDiv.className = 'card';
        
        if (card.suit === '♥' || card.suit === '♦') {
            cardDiv.classList.add('red');
        }
        
        const top = document.createElement('div');
        const bottom = document.createElement('div');
        
        top.textContent = `${card.rank} ${card.suit}`;
        bottom.textContent = `${card.rank} ${card.suit}`;
        bottom.style.alignSelf = 'flex-end';
        bottom.style.transform = 'rotate(180deg)';
        
        cardDiv.appendChild(top);
        cardDiv.appendChild(bottom);
        
        playerHandEl.appendChild(cardDiv);
    });
    
    // Update scores
    updateScores();
}

function updateScores() {
    if (dealerValueEl && playerValueEl) {
        const playerScore = calculateScore(player);
        playerValueEl.textContent = `Skor: ${playerScore}`;
        
        if (dealerHidden) {
            dealerValueEl.textContent = 'Skor: ??';
        } else {
            const dealerScore = calculateScore(dealer);
            dealerValueEl.textContent = `Skor: ${dealerScore}`;
        }
    }
}

function calculateScore(hand) {
    let score = 0;
    let aces = 0;
    
    hand.forEach(card => {
        if (card.rank === 'A') {
            aces++;
            score += 11;
        } else if (['K', 'Q', 'J'].includes(card.rank)) {
            score += 10;
        } else {
            score += parseInt(card.rank);
        }
    });
    
    // Adjust for aces if over 21
    while (score > 21 && aces > 0) {
        score -= 10;
        aces--;
    }
    
    return score;
}

function createDeck() {
    deck = [];
    const suits = ['♠', '♥', '♦', '♣'];
    const ranks = ['A', '2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K'];
    
    suits.forEach(suit => {
        ranks.forEach(rank => {
            deck.push({ suit, rank });
        });
    });
    
    // Shuffle deck
    for (let i = deck.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [deck[i], deck[j]] = [deck[j], deck[i]];
    }
}

function startRound() {
    if (inRound) return;
    
    const bet = parseInt(betInput.value) || 0;
    
    if (bet < 1000) {
        showMessage('Minimal taruhan Rp 1.000!', 'error');
        return;
    }
    
    if (bet > gameBalance) {
        showMessage('Saldo tidak cukup!', 'error');
        return;
    }
    
    // Update balance through save system
    if (gameConfig.updateBalanceCallback) {
        gameBalance = gameConfig.updateBalanceCallback(gameBalance - bet, 'bet');
    } else {
        gameBalance -= bet;
    }
    
    currentBet = bet;
    inRound = true;
    dealerHidden = true;
    
    createDeck();
    
    // Deal cards
    dealer = [deck.pop(), deck.pop()];
    player = [deck.pop(), deck.pop()];
    
    updateGameUI();
    showMessage('Game dimulai! Pilih HIT atau STAND.');
    
    // Check for blackjack
    if (calculateScore(player) === 21) {
        setTimeout(() => {
            endRound();
        }, 1000);
    }
}

function playerHit() {
    if (!inRound) return;
    
    player.push(deck.pop());
    updateGameUI();
    
    if (calculateScore(player) > 21) {
        showMessage('BUST! Anda kalah!', 'lose');
        endRound();
    }
}

function playerStand() {
    if (!inRound) return;
    
    dealerHidden = false;
    
    // Dealer draws until 17 or higher
    while (calculateScore(dealer) < 17) {
        dealer.push(deck.pop());
    }
    
    updateGameUI();
    endRound();
}

function playerDouble() {
    if (!inRound) return;
    
    if (gameBalance < currentBet) {
        showMessage('Saldo tidak cukup untuk double!', 'error');
        return;
    }
    
    // Double the bet
    if (gameConfig.updateBalanceCallback) {
        gameBalance = gameConfig.updateBalanceCallback(gameBalance - currentBet, 'double');
    } else {
        gameBalance -= currentBet;
    }
    
    currentBet *= 2;
    
    // Take one card
    player.push(deck.pop());
    updateGameUI();
    
    if (calculateScore(player) > 21) {
        showMessage('BUST! Anda kalah!', 'lose');
        endRound();
    } else {
        playerStand();
    }
}

function endRound() {
    if (!inRound) return;
    
    inRound = false;
    dealerHidden = false;
    
    const playerScore = calculateScore(player);
    const dealerScore = calculateScore(dealer);
    
    let result = '';
    let winnings = 0;
    
    if (playerScore > 21) {
        result = 'BUST! Anda kalah!';
        showMessage(result, 'lose');
    } else if (dealerScore > 21) {
        result = 'Dealer BUST! Anda menang!';
        winnings = currentBet * 2;
        if (gameConfig.updateBalanceCallback) {
            gameBalance = gameConfig.updateBalanceCallback(gameBalance + winnings, 'win');
        } else {
            gameBalance += winnings;
        }
        showMessage(result, 'win');
    } else if (playerScore > dealerScore) {
        result = 'Anda menang!';
        winnings = currentBet * 2;
        if (gameConfig.updateBalanceCallback) {
            gameBalance = gameConfig.updateBalanceCallback(gameBalance + winnings, 'win');
        } else {
            gameBalance += winnings;
        }
        showMessage(result, 'win');
    } else if (playerScore === dealerScore) {
        result = 'Seri! Taruhan dikembalikan.';
        winnings = currentBet;
        if (gameConfig.updateBalanceCallback) {
            gameBalance = gameConfig.updateBalanceCallback(gameBalance + winnings, 'push');
        } else {
            gameBalance += winnings;
        }
        showMessage(result, 'info');
    } else {
        result = 'Dealer menang! Anda kalah.';
        showMessage(result, 'lose');
    }
    
    // Update UI
    updateGameUI();
    updateScores();
    
    // Update current bet display
    if (currentBetEl) {
        currentBetEl.textContent = `Taruhan: Rp ${currentBet.toLocaleString('id-ID')}`;
    }
    
    // Reset for next round
    currentBet = 0;
    setTimeout(() => {
        showMessage('Pilih taruhan untuk ronde berikutnya!');
    }, 3000);
}

function resetGame() {
    if (inRound && !confirm('Masih dalam permainan. Reset game?')) {
        return;
    }
    
    deck = [];
    dealer = [];
    player = [];
    dealerHidden = true;
    currentBet = 0;
    inRound = false;
    
    updateGameUI();
    showMessage('Game direset! Pilih taruhan baru.');
}

function showMessage(text, type = '') {
    if (messageEl) {
        messageEl.textContent = text;
        messageEl.className = 'game-message';
        
        if (type === 'win') {
            messageEl.classList.add('win-message');
        } else if (type === 'lose') {
            messageEl.classList.add('lose-message');
        } else if (type === 'error') {
            messageEl.style.color = '#e74c3c';
        } else if (type === 'info') {
            messageEl.style.color = '#3498db';
        }
    }
}