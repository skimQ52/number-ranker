<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

const leftNum = ref(0);
const rightNum = ref(0);

async function fetchNumbers() {
    try {
        const res = await fetch('http://localhost:8000/api/numbers')
        if (!res.ok) throw new Error('Network response was not ok')
        const data = await res.json()
        leftNum.value = data.left;
        rightNum.value = data.right;
    } catch (error) {
        console.error('Failed to fetch user:', error)
    }
}

onMounted(() => {
    fetchNumbers()
})

function vote(winner:number) {

    const loser = winner === rightNum.value ? leftNum.value : rightNum.value;

    fetch('http://localhost:8000/api/numbers', {
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
        })
        .catch(error => {
            console.error('Error:', error);
        });
}


</script>

<template>
    <Head title="Welcome">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    <div class="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] dark:bg-[#0a0a0a] lg:justify-center lg:p-8">
<!--        <header class="not-has-[nav]:hidden mb-6 w-full max-w-[335px] text-sm lg:max-w-4xl">-->
<!--            <nav class="flex items-center justify-end gap-4">-->
<!--                <Link-->
<!--                    v-if="$page.props.auth.user"-->
<!--                    :href="route('dashboard')"-->
<!--                    class="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"-->
<!--                >-->
<!--                    Dashboard-->
<!--                </Link>-->
<!--                <template v-else>-->
<!--                    <Link-->
<!--                        :href="route('login')"-->
<!--                        class="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"-->
<!--                    >-->
<!--                        Log in-->
<!--                    </Link>-->
<!--                    <Link-->
<!--                        :href="route('register')"-->
<!--                        class="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"-->
<!--                    >-->
<!--                        Register-->
<!--                    </Link>-->
<!--                </template>-->
<!--            </nav>-->
<!--        </header>-->
<!--        <div class="h-14.5 hidden lg:block"></div>-->
        <div>
            <h1 class="text-5xl text-gray-500 font-bold m-6">
                Pick The Better Number
            </h1>
        </div>
        <div class="flex columns-2 justify-between w-full h-180">
            <button @click="vote(leftNum)" class="w-1/2 flex justify-center items-center hover:bg-gray-800 hover:outline-1 hover:outline-amber-50 transition duration-300 cursor-pointer">
                <span class="text-9xl text-amber-50">{{leftNum}}</span>
            </button>
            <button @click="vote(rightNum)" class="w-1/2 flex justify-center items-center hover:bg-gray-800 hover:outline-1 hover:outline-amber-50 transition duration-300 cursor-pointer">
                <span class="text-9xl text-amber-50 ">{{rightNum}}</span>
            </button>
        </div>
    </div>
</template>
