<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import {
    canVoteNow,
    clearHandshake,
    clearNumberLeft,
    clearNumberRight,
    getHandshake,
    getMyVotes,
    getNumberLeft,
    getNumberRight,
    recordVote,
    setHandshake,
    setNumberLeft,
    setNumberRight,
} from '@/Utils/voteHandler';

const leftNum = ref(0);
const rightNum = ref(0);
const canVote = ref(true);
const votesToday = ref(0);
const totalVotes = ref(0);
const myVotes = ref(0);
const showDialog = ref(false);
const handshake = ref('');

const title = 'Pick The Better Number!';
const titleChars = title.split('');

function hasVoteWaiting() {
    return getNumberLeft() !== 0 && getNumberRight() !== 0 && getHandshake() !== '';
}

async function fetchVoteStats() {
    const res = await fetch('/api/numbers?meta_only=1');
    if (!res.ok) {
        throw new Error('Failed to fetch vote stats');
    }
    const data = await res.json();
    votesToday.value = data.votes;
    totalVotes.value = data.total;
}

async function fetchNumbers() {
    try {
        const res = await fetch('/api/numbers');
        if (res.status === 429) {
            canVote.value = false;
        } else if (!res.ok) throw new Error('Network response was not ok');
        const data = await res.json();
        setNumberLeft(data.left);
        setNumberRight(data.right);
        setHandshake(data.handshake);

        leftNum.value = data.left;
        rightNum.value = data.right;
        handshake.value = data.handshake;

        votesToday.value = data.votes;
        totalVotes.value = data.total;
    } catch (error) {
        console.error('Failed to fetch numbers:', error);
    }
}

onMounted(() => {
    if (hasVoteWaiting()) {
        leftNum.value = getNumberLeft();
        rightNum.value = getNumberRight();
        handshake.value = getHandshake();
        fetchVoteStats();
    }
    else {
        fetchNumbers();
    }
    canVote.value = canVoteNow();
    myVotes.value = getMyVotes();
});

function vote(winner: number) {
    if (!canVote.value) {
        return;
    }

    const loser = winner === rightNum.value ? leftNum.value : rightNum.value;

    if (!getHandshake()) {
        console.error('No handshake found');
        return;
    }

    recordVote();
    canVote.value = canVoteNow();
    myVotes.value = getMyVotes();

    fetch('/api/numbers', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            winner: winner,
            loser: loser,
            handshake: handshake.value,
        }),
    })
        .then((response) => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then((responseData) => {
            console.log('Success:', responseData);
            leftNum.value = responseData.left;
            rightNum.value = responseData.right;
            handshake.value = responseData.handshake;

            setNumberLeft(responseData.left);
            setNumberRight(responseData.right);
            setHandshake(responseData.handshake);

            votesToday.value = responseData.votes;
            totalVotes.value = responseData.total;
        })
        .catch((error) => {
            console.error('Error:', error);
            clearNumberLeft();
            clearNumberRight();
            clearHandshake();
        });
}
</script>

<template>
    <Head title="Pick">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    <div class="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] lg:justify-center lg:p-8 dark:bg-[#0a0a0a]">
        <header class="fixed top-0 left-0 z-10 mb-6 flex h-12 w-full justify-between bg-[rgba(0,0,0,0.5)] text-sm not-has-[nav]:hidden">
            <nav class="flex h-full max-w-screen-xl items-center justify-end gap-4 px-4 sm:px-6 lg:px-8">
                <Link
                    :href="route('leaderboard')"
                    class="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"
                >
                    Leaderboard
                </Link>
            </nav>
            <div class="mr-4 flex h-full items-center text-center">
                <h1 class="text-md text-gray-400">
                    You've made <span class="font-bold text-blue-500">{{ myVotes }}</span> lifetime picks!
                </h1>
            </div>
        </header>
        <div class="mt-10 mb-2 flex flex-col gap-2">
            <div class="flex items-center gap-4 select-none">
                <h1 class="text-center text-2xl font-bold whitespace-pre text-blue-400 sm:text-3xl md:text-4xl">
                    <span
                        v-for="(char, index) in titleChars"
                        :key="index"
                        class="inline-block font-extrabold transition-transform duration-200 ease-in-out hover:scale-120"
                    >
                        {{ char }}
                    </span>
                </h1>
                <div>
                    <button
                        @click="showDialog = true"
                        class="flex h-10 w-10 cursor-pointer items-center justify-center rounded-full bg-gray-700 text-2xl font-bold text-white shadow-lg hover:bg-gray-500"
                    >
                        ?
                    </button>

                    <div v-if="showDialog" @click.self="showDialog = false" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                        <div class="relative w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
                            <button @click="showDialog = false" class="absolute top-2 right-2 text-xl text-gray-400 hover:text-gray-600">X</button>
                            <h2 class="mb-4 text-xl font-bold">Confused?</h2>
                            <p class="text-gray-700">
                                Pick the number YOU think is better. It's that simple.<br /><br />There’s no right or wrong — go with your gut!
                                Whether it’s lucky, mathematical, or just your favorite athlete’s number, your opinion matters! <br /><br />
                                Check the leaderboards to see which numbers are winning, and which numbers are losing.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex w-full justify-around text-center">
                <h3 class="text:xl text-gray-400 sm:text-2xl">
                    <span class="font-bold text-blue-500">{{ votesToday }}</span> Picks Today
                </h3>
                <h3 class="text:xl text-gray-400 sm:text-2xl">
                    <span class="font-bold text-blue-500">{{ totalVotes }}</span> Total Picks
                </h3>
            </div>
        </div>
        <div v-if="canVote" class="flex h-120 w-1/2 flex-col justify-between md:h-220 lg:h-150 lg:w-full lg:flex-row">
            <button
                @click="vote(leftNum)"
                class="flex h-1/2 cursor-pointer items-center justify-center transition duration-300 active:scale-95 active:bg-gray-700 lg:h-full lg:w-1/2 [@media(hover:hover)]:hover:bg-gray-800 [@media(hover:hover)]:hover:outline-1 [@media(hover:hover)]:hover:outline-amber-50"
            >
                <span class="text-9xl text-amber-50">{{ leftNum }}</span>
            </button>
            <button
                @click="vote(rightNum)"
                class="flex h-1/2 cursor-pointer items-center justify-center transition duration-300 active:scale-95 active:bg-gray-700 lg:h-full lg:w-1/2 [@media(hover:hover)]:hover:bg-gray-800 [@media(hover:hover)]:hover:outline-1 [@media(hover:hover)]:hover:outline-amber-50"
            >
                <span class="text-9xl text-amber-50">{{ rightNum }}</span>
            </button>
        </div>
        <div v-else class="flex h-140 w-2/3 flex-col justify-between md:h-250 md:w-1/2 lg:h-150 lg:w-full lg:flex-row">
            <div class="flex h-full w-full flex-col items-center justify-center gap-8 text-center">
                <span class="text-3xl font-bold text-gray-500 sm:text-4xl">Thank You</span>
                <span class="text-xl text-gray-600 sm:text-2xl">Come back later to make more picks!</span>
                <Link
                    :href="route('leaderboard')"
                    class="inline-block rounded-sm border border-transparent bg-gray-900 px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"
                >
                    See the Leaderboard
                </Link>
            </div>
        </div>
    </div>
</template>
