<script setup lang="ts">
    import { Head, Link } from '@inertiajs/vue3';
    import { onMounted, ref } from 'vue';
    import { canVoteToday, recordVote } from '@/Utils/voteHandler';

    const leftNum = ref(0);
    const rightNum = ref(0);
    const canVote = ref(true);
    const votesToday = ref(0);
    const showDialog = ref(false)

    const title = 'Pick The Better Number!';
    const titleChars = title.split('');

    async function fetchNumbers() {
        try {
            const res = await fetch('/api/numbers')
            if (res.status === 429) {
                canVote.value = false;
            }
            else if (!res.ok) throw new Error('Network response was not ok')
            const data = await res.json()
            leftNum.value = data.left;
            rightNum.value = data.right;
            votesToday.value = data.votes;
        } catch (error) {
            console.error('Failed to fetch numbers:', error)
        }
    }

    onMounted(() => {
        fetchNumbers()
        canVote.value = canVoteToday();
    })

    function vote(winner:number) {

        if (!canVote.value) {
            return;
        }

        recordVote();
        canVote.value = canVoteToday();

        const loser = winner === rightNum.value ? leftNum.value : rightNum.value;

        fetch('/api/numbers', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                winner: winner,
                loser: loser
            })
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(responseData => {
                console.log('Success:', responseData);
                fetchNumbers();
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }
</script>

<template>
    <Head title="Pick">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    <div class="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] dark:bg-[#0a0a0a] lg:justify-center lg:p-8">
        <header class="flex justify-start not-has-[nav]:hidden mb-6 w-full h-12 text-sm fixed left-0 top-0 z-10 bg-[rgba(0,0,0,0.5)]">
            <nav class="max-w-screen-xl h-full flex items-center justify-end px-4 sm:px-6 lg:px-8 gap-4">
                <Link
                    :href="route('leaderboard')"
                    class="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"
                >
                    Leaderboard
                </Link>
            </nav>
        </header>
        <div class="flex flex-col mt-10 gap-2 mb-2">
            <div class="select-none flex gap-4">
                <h1 class="whitespace-pre text-3xl sm:text-4xl md:text-5xl text-gray-500 font-bold text-center">
                    <span
                          v-for="(char, index) in titleChars"
                          :key="index"
                          class="font-extrabold inline-block transition-transform duration-200 ease-in-out hover:scale-120"
                      >
                        {{ char }}
                    </span>
                </h1>
                <div>
                    <button
                        @click="showDialog = true"
                        class="bg-gray-700 text-white rounded-full w-10 h-10 text-2xl font-bold flex items-center justify-center shadow-lg cursor-pointer hover:bg-gray-500"
                    >
                        ?
                    </button>

                    <div
                        v-if="showDialog"
                        @click.self="showDialog = false"
                        class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
                    >
                        <div class="bg-white rounded-lg p-6 shadow-xl max-w-md w-full relative">
                            <button
                                @click="showDialog = false"
                                class="absolute top-2 right-2 text-gray-400 hover:text-gray-600 text-xl"
                            >
                                X
                            </button>
                            <h2 class="text-xl font-bold mb-4">Need Help?</h2>
                            <p class="text-gray-700">
                                Pick the number YOU think is better.<br><br>There’s no right or wrong — go with your gut!
                                Whether it’s lucky, mathematical, or just your favorite athlete’s number, your opinion matters!
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-full text-center">
                <h3 class="text-2xl text-gray-500">
                    Votes today: <span>{{ votesToday }}</span>
                </h3>
            </div>
        </div>
        <div v-if="canVote" class="flex flex-col w-1/2 h-120 md:h-220 justify-between lg:flex-row lg:w-full lg:h-150">
            <button @click="vote(leftNum)" class="h-1/2 lg:w-1/2 lg:h-full flex justify-center items-center hover:bg-gray-800 hover:outline-1 hover:outline-amber-50 transition duration-300 cursor-pointer">
                <span class="text-9xl text-amber-50">{{leftNum}}</span>
            </button>
            <button @click="vote(rightNum)" class="h-1/2 lg:w-1/2 lg:h-full flex justify-center items-center hover:bg-gray-800 hover:outline-1 hover:outline-amber-50 transition duration-300 cursor-pointer">
                <span class="text-9xl text-amber-50 ">{{rightNum}}</span>
            </button>
        </div>
        <div v-else class="flex flex-col w-1/2 h-140 md:h-250 justify-between lg:flex-row lg:w-full lg:h-150">
            <div class="h-full w-full flex flex-col gap-8 items-center text-center justify-center">
                <span class="text-3xl font-bold text-gray-600">Thank You!</span>
                <span class="text-2xl text-gray-600">You've made all of your votes for today</span>
                <Link
                    :href="route('leaderboard')"
                    class="bg-gray-900 inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"
                >
                    See the Leaderboard
                </Link>
            </div>
        </div>
    </div>
</template>
