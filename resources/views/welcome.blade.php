<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] flex p-6 lg:p-8 items-center lg:justify-center min-h-screen flex-col">
        <svg class="hidden" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <symbol id="icon-arrow-up-right" viewBox="0 0 10 11" fill="none">
                    <path d="M7.70833 6.95834V2.79167H3.54167M2.5 8L7.5 3.00001" stroke="currentColor" stroke-linecap="square" />
                </symbol>
            </defs>
        </svg>

        <div class="flex items-center justify-center w-full transition-opacity opacity-100 duration-750 lg:grow starting:opacity-0">
            <main class="flex max-w-[335px] w-full flex-col-reverse lg:max-w-4xl lg:flex-row">
                <div class="text-[13px] leading-[20px] flex-1 p-6 pb-6 lg:p-12 lg:pb-8 bg-white dark:bg-[#161615] dark:text-[#EDEDEC] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.12)] dark:shadow-[inset_0px_0px_0px_1px_rgba(30,46,122,0.4)] rounded-bl-lg rounded-br-lg lg:rounded-tl-lg lg:rounded-br-none">
                    <h1 class="mb-1 font-medium">One Piece Cards API</h1>
                    <p class="mb-2 text-[#706f6c] dark:text-[#A1A09A]">REST API for One Piece TCG card data — packs, cards, and filters.</p>
                    <ul class="flex flex-col mb-4 lg:mb-6">
                        <li class="flex items-center gap-4 py-2 relative before:border-l before:border-[#e3e3e0] dark:before:border-[#3E3E3A] before:top-1/2 before:bottom-0 before:left-[0.4rem] before:absolute">
                            <span class="relative py-1 bg-white dark:bg-[#161615]">
                                <span class="flex items-center justify-center rounded-full bg-[#FDFDFC] dark:bg-[#161615] shadow-[0px_0px_1px_0px_rgba(0,0,0,0.03),0px_1px_2px_0px_rgba(0,0,0,0.06)] w-3.5 h-3.5 border dark:border-[#3E3E3A] border-[#e3e3e0]">
                                    <span class="rounded-full bg-[#dbdbd7] dark:bg-[#3E3E3A] w-1.5 h-1.5"></span>
                                </span>
                            </span>
                            <span class="flex flex-col gap-1">
                                <span class="text-[#706f6c] dark:text-[#A1A09A]">Authenticate with a Bearer token</span>
                                <code class="font-mono text-[11px] bg-[#f5f5f0] dark:bg-[#1C1C1A] px-2 py-0.5 rounded text-[#1b1b18] dark:text-[#EDEDEC]">Authorization: Bearer &lt;your-api-key&gt;</code>
                            </span>
                        </li>
                        <li class="flex items-center gap-4 py-2 relative before:border-l before:border-[#e3e3e0] dark:before:border-[#3E3E3A] before:bottom-1/2 before:top-0 before:left-[0.4rem] before:absolute">
                            <span class="relative py-1 bg-white dark:bg-[#161615]">
                                <span class="flex items-center justify-center rounded-full bg-[#FDFDFC] dark:bg-[#161615] shadow-[0px_0px_1px_0px_rgba(0,0,0,0.03),0px_1px_2px_0px_rgba(0,0,0,0.06)] w-3.5 h-3.5 border dark:border-[#3E3E3A] border-[#e3e3e0]">
                                    <span class="rounded-full bg-[#dbdbd7] dark:bg-[#3E3E3A] w-1.5 h-1.5"></span>
                                </span>
                            </span>
                            <span>
                                Read the
                                <a href="/docs/api" class="inline-flex items-center space-x-1 font-medium underline underline-offset-4 text-[#1E2E7A] dark:text-[#F0C830] ml-1">
                                    <span>Documentation</span>
                                    <svg class="w-2.5 h-2.5"><use href="#icon-arrow-up-right" /></svg>
                                </a>
                            </span>
                        </li>
                    </ul>

                    <p class="mt-6 lg:mt-10 text-[#706f6c] dark:text-[#A1A09A]">
                        MCP Server available at
                        <a href="/mcp" class="inline-flex items-center space-x-1 font-medium underline underline-offset-4 text-[#1E2E7A] dark:text-[#F0C830] ml-1">
                            <span>/mcp</span>
                            <svg class="w-2.5 h-2.5"><use href="#icon-arrow-up-right" /></svg>
                        </a>
                    </p>
                </div>
                <div class="bg-white dark:bg-[#111D52] relative lg:-ml-px -mb-px lg:mb-0 rounded-t-lg lg:rounded-t-none lg:rounded-r-lg aspect-[335/364] lg:aspect-auto w-full lg:w-[438px] shrink-0 overflow-hidden">
                    <svg class="w-full text-[#1E2E7A] dark:text-[#F0C830] transition-all translate-y-0 opacity-100 max-w-none delay-300 duration-750 starting:opacity-0 motion-safe:starting:translate-y-6" viewBox="0 0 438 104" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <text x="219" y="64" text-anchor="middle" font-size="56" font-weight="600" font-family="Instrument Sans, ui-sans-serif, sans-serif" fill="currentColor">One Piece TCG</text>
                    </svg>

                    <svg class="w-[438px] max-w-none relative -mt-[6.6rem] -ml-8 lg:ml-0" viewBox="0 0 440 392" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g class="transition-all delay-300 opacity-100 duration-750 starting:opacity-0 text-[#1E2E7A] dark:text-[#F0C830]">
                            <text x="-10" y="372" font-size="270" font-weight="700" font-family="Instrument Sans, ui-sans-serif, sans-serif" fill="currentColor">API</text>
                        </g>
                        <g class="mix-blend-multiply dark:mix-blend-screen transition-all delay-400 opacity-100 duration-750 starting:opacity-0 motion-safe:starting:-translate-x-[26px] motion-safe:starting:translate-y-[22px] text-[#C89800] dark:text-[#060B1E]">
                            <text x="16" y="350" font-size="270" font-weight="700" font-family="Instrument Sans, ui-sans-serif, sans-serif" fill="currentColor">API</text>
                        </g>
                        <g class="mix-blend-multiply dark:mix-blend-screen transition-all delay-400 opacity-100 duration-750 starting:opacity-0 motion-safe:starting:-translate-x-[52px] motion-safe:starting:translate-y-[44px] text-[#F0C830] dark:text-[#0C1428]">
                            <text x="42" y="328" font-size="270" font-weight="700" font-family="Instrument Sans, ui-sans-serif, sans-serif" fill="currentColor">API</text>
                        </g>
                        <g class="mix-blend-multiply dark:mix-blend-screen transition-all delay-400 opacity-100 duration-750 starting:opacity-0 motion-safe:starting:-translate-x-[78px] motion-safe:starting:translate-y-[66px] text-[#7B9FD4] dark:text-[#140D00]">
                            <text x="68" y="306" font-size="270" font-weight="700" font-family="Instrument Sans, ui-sans-serif, sans-serif" fill="currentColor">API</text>
                        </g>
                        <g class="mix-blend-multiply dark:mix-blend-screen transition-all delay-400 opacity-100 duration-750 starting:opacity-0 motion-safe:starting:-translate-x-[104px] motion-safe:starting:translate-y-[88px] text-[#AABDE8] dark:text-[#080C20]">
                            <text x="94" y="284" font-size="270" font-weight="700" font-family="Instrument Sans, ui-sans-serif, sans-serif" fill="currentColor">API</text>
                        </g>
                    </svg>
                    <div class="absolute inset-0 rounded-t-lg lg:rounded-t-none lg:rounded-r-lg shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.12)] dark:shadow-[inset_0px_0px_0px_1px_rgba(255,255,255,0.15)]"></div>
                </div>
            </main>
        </div>
    </body>
</html>
