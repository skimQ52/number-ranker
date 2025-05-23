const MAX_VOTES_PER_DAY = 30;
const VOTE_KEY = 'user_votes';
const VOTE_TOTALS_KEY = 'users_total_votes';
const VOTE_DATE_KEY = 'vote_date';
const NUMBER_LEFT_KEY = 'number_left';
const NUMBER_RIGHT_KEY = 'number_right';

export function canVoteToday(): boolean {
    const today = new Date().toDateString();
    const storedDate = localStorage.getItem(VOTE_DATE_KEY);
    const voteCount = parseInt(localStorage.getItem(VOTE_KEY) || '0', 10);

    if (storedDate !== today) {
        localStorage.setItem(VOTE_DATE_KEY, today);
        localStorage.setItem(VOTE_KEY, '0');
        return true;
    }

    return voteCount < MAX_VOTES_PER_DAY;
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

export function recordVote(): void {
    const today = new Date().toDateString();
    let voteCount = parseInt(localStorage.getItem(VOTE_KEY) || '0', 10);
    let userTotalVotes = parseInt(localStorage.getItem(VOTE_TOTALS_KEY) || '0', 10);

    if (localStorage.getItem(VOTE_DATE_KEY) !== today) {
        localStorage.setItem(VOTE_DATE_KEY, today);
        voteCount = 0;
    }

    voteCount += 1;
    userTotalVotes += 1;
    localStorage.setItem(VOTE_KEY, voteCount.toString());
    localStorage.setItem(VOTE_TOTALS_KEY, userTotalVotes.toString());
}

export function getMyVotes(): number {
    return parseInt(localStorage.getItem(VOTE_TOTALS_KEY) || '0', 10);
}
