
// =========================
// SET MESSAGE
// =========================
function setMessage(text, status) {
  if (messageEl) {
    messageEl.textContent = text;
    messageEl.className = status;
  }
}

// =========================
// KONSTANTA KARTU
// =========================
const SUITS = ['♠', '♥', '♦', '♣'];
const RANKS = ['A', '2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K'];

// =========================
// DOM ELEMENTS
// =========================
const dealerHandEl = document.getElementById('dealer-hand');
const playerHandEl = document.getElementById('player-hand');
const dealerValueEl = document.getElementById('dealer-value');
const playerValueEl = document.getElementById('player-value');
const balanceEl = document.getElementById('balance');
const betInput = document.getElementById('bet-input');
const betBtn = document.getElementById('bet-btn');
const currentBetEl = document.getElementById('current-bet');
const hitBtn = document.getElementById('hit');
const standBtn = document.getElementById('stand');
const doubleBtn = document.getElementById('double');
const newRoundBtn = document.getElementById('new-round');
const messageEl = document.getElementById('message');

// TOPUP DOM
const topupBtn = document.getElementById('topup-btn');
const topupModal = document.getElementById('topup-modal');
const topupForm = document.getElementById('topup-form');
const topupMessage = document.getElementById('topup-message');
const modalAmount = document.getElementById('modal-amount');

// =========================
// VARIABEL GAME
// =========================
let deck = [];
let dealer = [];
let player = [];
let dealerHidden = true;
let balance = 0;
let currentBet = 0;
let inRound = false;

// =========================================
// AMBIL VALUE DARI PHP (HIDDEN INPUT)
// =========================================
let gameBalance = parseInt(document.getElementById('php-balance').value);
let userId = parseInt(document.getElementById('php-userid').value);

// =========================
// UPDATE BALANCE DISPLAY
// =========================
function updateBalanceDisplay() {
  if (balanceEl) balanceEl.textContent = gameBalance.toLocaleString('id-ID');
  const modalBalance = document.getElementById('modal-balance');
  if (modalBalance) modalBalance.textContent = gameBalance.toLocaleString('id-ID');
}

// =========================
// SYNC KE SERVER
// =========================
async function syncBalanceToServer() {
  try {
    const response = await fetch('update_balance.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ userId, balance: gameBalance })
    });

    const data = await response.json();
    return data.success;
  } catch (err) {
    console.error("Error:", err);
    return false;
  }
}

// =========================
// UPDATE SESSION PHP
// =========================
async function updateSessionBalance() {
  try {
    await fetch('update_session.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ balance: gameBalance })
    });
  } catch (err) {
    console.error("Error:", err);
  }
}

// =========================
// UPDATE BALANCE LOGIC
// =========================
function updateGameBalance(amount, type) {
  if (type === 'win') gameBalance += amount;
  else if (type === 'loss') gameBalance -= amount;
  else if (type === 'bet') gameBalance -= amount;
  else if (type === 'refund') gameBalance += amount;

  updateBalanceDisplay();
  syncBalanceToServer();
// =========================
// SYNC KE SERVER (pakai update_balance.php)
async function syncBalanceToServer() {
  try {
    const response = await fetch('update_balance.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ balance: gameBalance })
    });

    const data = await response.json();
    return data.success === true;
  } catch (err) {
    console.error("Error:", err);
    return false;
  }
}

  updateSessionBalance();
}

window.updateGameBalance = updateGameBalance;
window.gameBalance = gameBalance;
window.userId = userId;

// =========================
// FUNGSI KARTU
// =========================
function makeDeck() {
  deck = [];
  for (const s of SUITS) {
    for (const r of RANKS) deck.push({ suit: s, rank: r });
  }
}

function shuffle() {
  for (let i = deck.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [deck[i], deck[j]] = [deck[j], deck[i]];
  }
}

function cardValue(card) {
  if (card.rank === 'A') return [1, 11];
  if (['J', 'Q', 'K'].includes(card.rank)) return [10];
  return [parseInt(card.rank)];
}

function handValues(hand) {
  let totals = [0];

  for (const card of hand) {
    const cv = cardValue(card);
    const newTotals = [];

    totals.forEach(t => cv.forEach(v => newTotals.push(t + v)));

    totals = [...new Set(newTotals)];
  }

  const valid = totals.filter(t => t <= 21);
  return valid.length ? Math.max(...valid) : Math.min(...totals);
}

function renderHand(el, hand, hideFirst = false) {
  el.innerHTML = '';

  hand.forEach((c, i) => {
    const div = document.createElement('div');

    if (hideFirst && i === 0) {
      div.className = 'card back';
      div.textContent = 'HIDE';
    } else {
      div.className = 'card' + ((c.suit === '♥' || c.suit === '♦') ? ' red' : '');
      const top = document.createElement('div');
      const bot = document.createElement('div');

      top.textContent = c.rank + ' ' + c.suit;
      bot.textContent = c.rank + ' ' + c.suit;
      bot.style.alignSelf = 'flex-end';

      div.appendChild(top);
      div.appendChild(bot);
    }

    el.appendChild(div);
  });
}

// =========================
// UPDATE UI
// =========================
function updateUI() {
  renderHand(dealerHandEl, dealer, dealerHidden);
  renderHand(playerHandEl, player);

  dealerValueEl.textContent = dealerHidden ? '??' : 'Nilai: ' + handValues(dealer);
  playerValueEl.textContent = 'Nilai: ' + handValues(player);
  updateBalanceDisplay();

  if (currentBetEl) currentBetEl.textContent = currentBet.toLocaleString('id-ID');
}

// =========================
// START ROUND
// =========================
function startRound() {
  if (inRound) return;

  const bet = Number(betInput.value);
  if (bet <= 0) return alert("Masukkan jumlah taruhan!");
  if (bet > gameBalance) return alert("BANK TIDAK CUKUP!");

  currentBet = bet;
  updateGameBalance(bet, 'bet');
  inRound = true;
  dealerHidden = true;
  setMessage("", "");

  makeDeck();
  shuffle();

  dealer = [deck.pop(), deck.pop()];
  player = [deck.pop(), deck.pop()];
  updateUI();

  // NATURAL BLACKJACK
  if (handValues(player) === 21) {
    dealerHidden = false;
    updateUI();

    if (handValues(dealer) === 21) {
      updateGameBalance(currentBet, 'refund');
      setMessage("Tie!", "");
    } else {
      updateGameBalance(Math.floor(currentBet * 2.5), 'win');
      setMessage("Blackjack! You win!", "win");
    }

    inRound = false;
    currentBet = 0;
  }
}

// =========================
// HIT
// =========================
function playerHit() {
  if (!inRound) return;

  player.push(deck.pop());
  updateUI();

  if (handValues(player) > 21) {
    dealerHidden = false;
    setMessage("Bust! You Lose!", "lose");
    inRound = false;
    currentBet = 0;
  }
}

// =========================
// STAND
// =========================
function playerStand() {
  if (!inRound) return;

  dealerHidden = false;

  while (handValues(dealer) < 17) dealer.push(deck.pop());

  updateUI();

  const pv = handValues(player);
  const dv = handValues(dealer);

  if (dv > 21 || pv > dv) {
    updateGameBalance(currentBet * 2, 'win');
    setMessage("You Win!", "win");
  } else if (pv === dv) {
    updateGameBalance(currentBet, 'refund');
    setMessage("Tie!", "");
  } else {
    setMessage("You Lose!", "lose");
  }

  inRound = false;
  currentBet = 0;
}

// =========================
// DOUBLE
// =========================
function playerDouble() {
  if (!inRound) return;
  if (gameBalance < currentBet) return alert("Bank tidak cukup untuk double.");

  updateGameBalance(currentBet, 'bet');
  currentBet *= 2;

  player.push(deck.pop());
  updateUI();

  if (handValues(player) > 21) {
    dealerHidden = false;
    setMessage("Bust! You Lose!", "lose");
    inRound = false;
    currentBet = 0;
    return;
  }

  playerStand();
}

// =========================
// TOP UP MODAL
// =========================
topupBtn.addEventListener('click', () => {
  topupModal.style.display = 'flex';
  topupForm.reset();
  topupMessage.style.display = 'none';
  updateBalanceDisplay();
});

function closeTopUpModal() {
  topupModal.style.display = 'none';
  topupMessage.style.display = 'none';
}

window.addEventListener('click', e => {
  if (e.target === topupModal) closeTopUpModal();
});

function setModalAmount(amount) {
  modalAmount.value = amount;
}

function showTopUpMessage(msg, type) {
  topupMessage.textContent = msg;
  topupMessage.className = 'topup-message ' + type;
  topupMessage.style.display = 'block';
}

topupForm.addEventListener('submit', e => {
  e.preventDefault();

  const bankMethod = document.querySelector('input[name="bank_method"]:checked');
  const amount = parseInt(modalAmount.value);

  if (!bankMethod) return showTopUpMessage("Pilih metode pembayaran!", "error");
  if (amount <= 0 || amount > 1000000) return showTopUpMessage("Jumlah harus 1–1.000.000", "error");

  const formData = new FormData(topupForm);
  formData.append('userId', userId);

  fetch('process_topup.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        gameBalance = data.new_balance;
        updateBalanceDisplay();
        showTopUpMessage("✓ Top up berhasil!", "success");
        setTimeout(closeTopUpModal, 2000);
      } else {
        showTopUpMessage(data.message, "error");
      }
    })
    .catch(() => showTopUpMessage("Terjadi kesalahan.", "error"));
});

// =========================
// EVENT LISTENERS
// =========================
betBtn.addEventListener('click', startRound);
hitBtn.addEventListener('click', playerHit);
standBtn.addEventListener('click', playerStand);
doubleBtn.addEventListener('click', playerDouble);

newRoundBtn.addEventListener('click', () => {
  if (inRound && !confirm("Masih dalam ronde. Reset?")) return;

  dealer = [];
  player = [];
  deck = [];
  inRound = false;
  dealerHidden = true;
  currentBet = 0;

  setMessage("Game di-reset.", "");
  updateUI();
});

window.addEventListener('keydown', e => {
  if (e.key === 'h') playerHit();
  if (e.key === 's') playerStand();
  if (e.key === 'd') playerDouble();
  if (e.key === 'Enter') startRound();
});

// =========================
// INIT
// =========================
document.addEventListener('DOMContentLoaded', () => {
  updateBalanceDisplay();
});
