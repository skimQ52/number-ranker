<script lang="ts" setup>
import { onMounted, ref } from 'vue';
import { usePage, router, Link, Head } from '@inertiajs/vue3';
import { getMyVotes } from '@/Utils/voteHandler';

const myVotes = ref(0);

interface Vote {
    id: number;
    created_at: string;
    winner: { number: number };
    loser: { number: number };
}

const numberId: any = usePage().props.numberId;
const votes = ref<Vote[]>([]);
const currentView = ref<'all' | 'wins' | 'losses'>('all');
const nextPageUrl = ref<string | null>(null);

async function fetchVotes(view: 'all' | 'wins' | 'losses', url: string | null = null) {

    if (view !== currentView.value) {
        votes.value = [];
        nextPageUrl.value = null;
    }

    currentView.value = view;

    let fetchUrl = url;
    if (!fetchUrl) {
        fetchUrl = `/api/votes/${numberId}`;
        if (view === 'wins') fetchUrl = `/api/wins/${numberId}`;
        if (view === 'losses') fetchUrl = `/api/losses/${numberId}`;
    }


    console.log('numberId:', numberId);

    const response = await fetch(fetchUrl);
    const data = await response.json();

    votes.value = [...votes.value, ...data.data];
    nextPageUrl.value = data.next_page_url;
}

function getBackgroundClass(winner: number): string {
    if (winner == numberId) return 'bg-green-200';
    return 'bg-red-200';
}

onMounted(() => {
    fetchVotes('all');
    myVotes.value = getMyVotes();
});
</script>

<template>
    <Head title="Pick History">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    <div class="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] lg:justify-start lg:p-8 dark:bg-[#0a0a0a]">
        <header class="flex justify-between not-has-[nav]:hidden mb-6 w-full h-12 text-sm fixed left-0 top-0 bg-[rgba(0,0,0,0.5)]">
            <nav class="max-w-screen-xl h-full flex items-center justify-end px-4 sm:px-6 lg:px-8 gap-4">
                <Link
                    :href="route('home')"
                    class="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"
                >
                    Home
                </Link>
            </nav>
            <div class="flex h-full text-center items-center mr-4">
                <h1 class="text-gray-400">You've made <span class="font-bold text-blue-500">{{ myVotes }}</span> lifetime picks!</h1>
            </div>
        </header>
        <div class="w-8/9 md:w-1/2 py-10">
            <button
                @click="router.visit('/leaderboard')"
                class="top-15 left-10 absolute text-white mb-4 rounded bg-gray-700 px-4 py-2 hover:bg-gray-800 cursor-pointer"
            >
                ← Back
            </button>

            <h1 class="mt-12 mb-4 md:mt-0 text-center text-3xl text-white">Pick History for <span class="font-bold">{{ numberId }}</span></h1>

            <div class="mb-6 flex justify-center gap-4">
                <button @click="fetchVotes('all')" :class="['rounded-lg px-4 py-2', currentView === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-200 cursor-pointer']">
                    All
                </button>
                <button @click="fetchVotes('wins')" :class="['rounded-lg px-4 py-2', currentView === 'wins' ? 'bg-green-600 text-white' : 'bg-gray-200 cursor-pointer']">
                    Wins
                </button>
                <button @click="fetchVotes('losses')" :class="['rounded-lg px-4 py-2', currentView === 'losses' ? 'bg-red-600 text-white' : 'bg-gray-200 cursor-pointer']">
                    Losses
                </button>
            </div>

            <ul class="divide-y divide-black/10 rounded-xl bg-white shadow overflow-hidden">
                <li v-for="vote in votes" :key="vote.id"
                    :class="[
                        'flex items-center justify-between p-3',
                        getBackgroundClass(vote.winner.number)
                    ]"
                >
                    <span class="text-lg text-gray-600">{{ numberId == vote.winner.number ? 'Win' : 'Loss' }}</span>

                    <span class="text-3xl">
                        <span class="text-sm text-gray-400">
                            vs
                        </span>
                        {{ numberId == vote.loser.number ? vote.winner.number : vote.loser.number }}
                    </span>
                    <span class="text-xs text-gray-500">
                        {{ new Date(vote.created_at).toLocaleString(undefined, {
                            dateStyle: 'short',
                            timeStyle: 'short'
                        }) }}
                    </span>
                </li>
            </ul>
            <div class="mt-4 flex justify-center" v-if="nextPageUrl">
                <button
                    @click="fetchVotes(currentView, nextPageUrl)"
                    class="rounded-lg bg-gray-700 text-white px-4 py-2 hover:bg-gray-800 hover:text-gray-300 cursor-pointer"
                >
                    Load More
                </button>
            </div>
        </div>
    </div>
</template>
