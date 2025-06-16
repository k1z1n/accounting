<div class="grid grid-cols-1 md:grid-cols-2 gap-6 w-full">

    <!-- Статистика за недели -->
    <div class="bg-gradient-to-br from-[#1f2937] to-[#111827] rounded-2xl p-6 shadow-xl transform transition hover:scale-[1.02] duration-300">
        <h2 class="text-lg font-semibold mb-2 text-gray-300">Доход за недели</h2>
        <p class="text-2xl font-bold text-cyan-400 mb-4">$12,500</p>
        <canvas id="weeklyChart" class="rounded-lg bg-[#0f172a] p-2"></canvas>
    </div>

    <!-- Статистика за дни -->
    <div class="bg-gradient-to-br from-[#1f2937] to-[#111827] rounded-2xl p-6 shadow-xl transform transition hover:scale-[1.02] duration-300">
        <h2 class="text-lg font-semibold mb-2 text-gray-300">Доход за дни</h2>
        <p class="text-2xl font-bold text-purple-400 mb-4">$3,000</p>
        <canvas id="dailyChart" class="rounded-lg bg-[#0f172a] p-2"></canvas>
    </div>

</div>
