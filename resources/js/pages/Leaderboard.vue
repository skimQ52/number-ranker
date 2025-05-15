<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

const numbers = ref();

async function fetchLeaderboard() {
    try {
        const res = await fetch('http://localhost:8000/api/rankings');
        if (!res.ok) throw new Error('Network response was not ok');
        numbers.value = await res.json();
    } catch (error) {
        console.error('Failed to fetch user:', error);
    }
}

onMounted(() => {
    fetchLeaderboard();
});

</script>

<template>
    <Head title="Leaderboard">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    <div class="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] lg:justify-center lg:p-8 dark:bg-[#0a0a0a]">
        <header class="flex justify-start not-has-[nav]:hidden mb-6 w-full h-12 text-sm fixed left-0 top-0 bg-[rgba(0,0,0,0.5)]">
            <nav class="max-w-screen-xl h-full flex items-center justify-end px-4 sm:px-6 lg:px-8 gap-4">
                <Link
                    :href="route('home')"
                    class="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"
                >
                    Home
                </Link>
            </nav>
        </header>
        <div class="w-2/3 lg:w-3xl mx-auto py-8 flex-col text-center">
            <h1 class="text-3xl font-bold mb-6 text-amber-50">Leaderboard</h1>

            <ul class="divide-y divide-gray-200 bg-white rounded shadow">
                <li
                    v-for="(number, index) in numbers"
                    :key="number.id"
                    class="flex items-center justify-between p-4"
                >
                    <span>{{index + 1}}.</span>
                    <div class="flex items-center gap-4">
                        <span class="text-3xl">{{ number.number }}</span>
                    </div>
                    <div>
                        <span class="text-lg font-mono text-green-500">{{ number.wins }}</span>
                        <span class="text-lg font-mono">-</span>
                        <span class="text-lg font-mono text-red-500">{{ number.losses }}</span>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</template>
