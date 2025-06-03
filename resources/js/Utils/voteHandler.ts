const MAX_VOTES_PER_PERIOD = 30;
const COOLDOWN_HOURS = 8;
const COOLDOWN_MS = COOLDOWN_HOURS * 60 * 60 * 1000;
const VOTE_COUNT_KEY = 'vote_count';
const VOTE_TOTALS_KEY = 'users_total_votes';
const VOTE_WINDOW_KEY = 'vote_window_start';
const NUMBER_LEFT_KEY = 'number_left';
const NUMBER_RIGHT_KEY = 'number_right';
const HANDSHAKE_KEY = 'vote_handshake';

export function canVoteNow(): boolean {
    const now = Date.now();
    const windowStart = parseInt(localStorage.getItem(VOTE_WINDOW_KEY) || '0', 10);
    const voteCount = parseInt(localStorage.getItem(VOTE_COUNT_KEY) || '0', 10);

    if (!windowStart || now - windowStart > COOLDOWN_MS) {
        localStorage.setItem(VOTE_WINDOW_KEY, now.toString());
        localStorage.setItem(VOTE_COUNT_KEY, '0');
        return true;
    }

    return voteCount < MAX_VOTES_PER_PERIOD;
}

export function getNumberLeft(): number {
    return parseInt(localStorage.getItem(NUMBER_LEFT_KEY) || '0');
}

export function getNumberRight(): number {
    return parseInt(localStorage.getItem(NUMBER_RIGHT_KEY) || '0');
}

export function setNumberLeft(value: number) {
    localStorage.setItem(NUMBER_LEFT_KEY, value.toString());
}

export function setNumberRight(value: number) {
    localStorage.setItem(NUMBER_RIGHT_KEY, value.toString());
}

export function clearNumberLeft(): void {
    localStorage.removeItem(NUMBER_LEFT_KEY);
}

export function clearNumberRight(): void {
    localStorage.removeItem(NUMBER_RIGHT_KEY);
}

export function setHandshake(value: string) {
    localStorage.setItem(HANDSHAKE_KEY, value);
}

export function getHandshake(): string {
    return localStorage.getItem(HANDSHAKE_KEY) || '';
}

export function clearHandshake(): void {
    localStorage.removeItem(HANDSHAKE_KEY);
}

export function recordVote(): void {
    const now = Date.now();
    let windowStart = parseInt(localStorage.getItem(VOTE_WINDOW_KEY) || '0', 10);
    let voteCount = parseInt(localStorage.getItem(VOTE_COUNT_KEY) || '0', 10);
    let totalVotes = parseInt(localStorage.getItem(VOTE_TOTALS_KEY) || '0', 10);

    if (!windowStart || now - windowStart > COOLDOWN_MS) {
        windowStart = now;
        voteCount = 0;
        localStorage.setItem(VOTE_WINDOW_KEY, windowStart.toString());
    }

    voteCount += 1;
    totalVotes += 1;

    localStorage.setItem(VOTE_COUNT_KEY, voteCount.toString());
    localStorage.setItem(VOTE_TOTALS_KEY, totalVotes.toString());
}

export function getMyVotes(): number {
    return parseInt(localStorage.getItem(VOTE_TOTALS_KEY) || '0', 10);
}
