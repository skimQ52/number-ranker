const MAX_VOTES_PER_DAY = 5;
const VOTE_KEY = 'user_votes';
const VOTE_DATE_KEY = 'vote_date';

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

export function recordVote(): void {
    const today = new Date().toDateString();
    let voteCount = parseInt(localStorage.getItem(VOTE_KEY) || '0', 10);

    if (localStorage.getItem(VOTE_DATE_KEY) !== today) {
        localStorage.setItem(VOTE_DATE_KEY, today);
        voteCount = 0;
    }

    voteCount += 1;
    localStorage.setItem(VOTE_KEY, voteCount.toString());
}
