<?php use App\Enums\TicketStatus; ?>
<h1 class="text-xl font-bold mb-4">Dashboard และรายงาน</h1>

<div class="grid grid-cols-2 md:grid-cols-6 gap-3 mb-6">
    <div class="bg-white border rounded-lg p-4">
        <div class="text-xs text-slate-500">ทั้งหมด</div>
        <div class="text-2xl font-bold"><?= (int) $stats['total'] ?></div>
    </div>
    <?php foreach (TicketStatus::cases() as $s): ?>
        <div class="bg-white border rounded-lg p-4">
            <div class="text-xs text-slate-500"><?= e($s->label()) ?></div>
            <div class="text-2xl font-bold"><?= (int) $stats['byStatus'][$s->value] ?></div>
        </div>
    <?php endforeach; ?>
</div>

<div class="grid md:grid-cols-3 gap-6">
    <div class="bg-white border rounded-lg p-5">
        <h2 class="font-semibold mb-2">เวลาแก้ไขเฉลี่ย</h2>
        <p class="text-3xl font-bold text-indigo-700"><?= e($stats['avgResolutionHours']) ?> <span class="text-base font-normal text-slate-500">ชั่วโมง</span></p>
    </div>

    <div class="bg-white border rounded-lg p-5">
        <h2 class="font-semibold mb-3">ช่างที่ปิดงานมากที่สุด</h2>
        <?php
        $max = max(1, ...array_map(fn ($r) => (int) $r['n'], $stats['topTechnicians'] ?: [['n' => 1]]));
        foreach ($stats['topTechnicians'] as $r): ?>
            <div class="mb-2 text-sm">
                <div class="flex justify-between"><span><?= e($r['name']) ?></span><b><?= (int) $r['n'] ?></b></div>
                <div class="h-2 bg-slate-100 rounded"><div class="h-2 bg-indigo-500 rounded" style="width: <?= round($r['n'] / $max * 100) ?>%"></div></div>
            </div>
        <?php endforeach; ?>
        <?php if (!$stats['topTechnicians']): ?><p class="text-sm text-slate-400">ยังไม่มีข้อมูล</p><?php endif; ?>
    </div>

    <div class="bg-white border rounded-lg p-5">
        <h2 class="font-semibold mb-3">คะแนนความพึงพอใจรายเดือน</h2>
        <?php foreach ($stats['monthlyRatings'] as $r): ?>
            <div class="flex justify-between text-sm py-1 border-b last:border-0">
                <span><?= e($r['month']) ?></span>
                <span><b><?= e($r['avg_score']) ?></b> / 5 <span class="text-slate-400">(<?= (int) $r['n'] ?> รีวิว)</span></span>
            </div>
        <?php endforeach; ?>
        <?php if (!$stats['monthlyRatings']): ?><p class="text-sm text-slate-400">ยังไม่มีข้อมูล</p><?php endif; ?>
    </div>
</div>
