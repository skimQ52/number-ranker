<script setup lang="ts">
    import { Head, Link } from '@inertiajs/vue3';
    import { onMounted, ref } from 'vue';
    import { getMyVotes } from '@/Utils/voteHandler';

    const numbers = ref();
    const myVotes = ref(0);

    async function fetchLeaderboard() {
        try {
            const res = await fetch('/api/rankings');
            if (!res.ok) throw new Error('Network response was not ok');
            numbers.value = await res.json();
        } catch (error) {
            console.error('Failed to fetch user:', error);
        }
    }

    function getBackgroundClass(index: number): string {
        if (index === 0) return 'bg-gradient-to-r from-yellow-300/70 via-yellow-500/60 to-amber-500/60';
        if (index === 1) return 'bg-gradient-to-r from-gray-100/70 via-gray-300/60 to-gray-400/60';
        if (index === 2) return 'bg-gradient-to-r from-orange-300/70 via-amber-500/50 to-orange-600/60';
        if (index < 10) return 'bg-cyan-200';
        if (index < 30) return 'bg-green-200';
        if (index < 60) return 'bg-yellow-200/70';
        return 'bg-red-300/70';
    }

    onMounted(() => {
        fetchLeaderboard();
        myVotes.value = getMyVotes();
    });

</script>

<template>
    <Head title="Leaderboard">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    <div class="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] lg:justify-center lg:p-8 dark:bg-[#0a0a0a]">
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
                <h1 class="text-md text-gray-400">You've made <span class="font-bold text-blue-500">{{ myVotes }}</span> lifetime picks!</h1>
            </div>
        </header>
        <div class="w-9/10 md:w-2/3 lg:w-3xl mx-auto py-8 flex-col text-center">
            <h1 class="text-3xl font-bold mb-6 text-amber-50">Leaderboard</h1>

            <ul class="divide-y divide-gray-300 bg-white rounded-xl shadow overflow-hidden">
                <li
                    v-for="(number, index) in numbers"
                    :key="number.id"
                    :class="[
                        'flex items-center justify-between p-4',
                        getBackgroundClass(index)
                    ]"
                >
                    <span class="w-1/3 text-left">{{index + 1}}.</span>
                    <div class="w-1/3 flex justify-center gap-4">
                        <span class="text-3xl">{{ number.number }}</span>
                    </div>
                    <div class="w-1/3 text-right">
                        <span class="text-lg font-mono text-green-500">{{ number.wins }}</span>
                        <span class="text-lg font-mono">-</span>
                        <span class="text-lg font-mono text-red-500">{{ number.losses }}</span>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</template>
