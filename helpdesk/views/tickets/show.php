<?php use App\Enums\TicketStatus; ?>
<div class="mb-4 text-sm"><a href="/tickets" class="text-indigo-700 hover:underline">← กลับรายการ</a></div>

<div class="grid md:grid-cols-3 gap-6" data-ticket-id="<?= (int) $ticket['id'] ?>" id="ticket-root">
    <section class="md:col-span-2 space-y-6">
        <div class="bg-white border border-slate-200 rounded-lg p-6">
            <div class="flex items-start justify-between gap-4">
                <h1 class="text-xl font-bold">#<?= (int) $ticket['id'] ?> · <?= e($ticket['title']) ?></h1>
                <span id="status-badge" class="shrink-0 px-3 py-1 rounded-full text-xs <?= $status->badge() ?>"><?= e($status->label()) ?></span>
            </div>
            <p class="mt-3 whitespace-pre-line text-slate-700"><?= e($ticket['description']) ?></p>
            <dl class="mt-4 grid grid-cols-2 gap-2 text-sm text-slate-600">
                <div>หมวดหมู่: <b><?= e($ticket['category_name']) ?></b></div>
                <div>ความสำคัญ: <span class="px-2 py-0.5 rounded-full text-xs <?= $priority->badge() ?>"><?= e($priority->label()) ?></span></div>
                <div>ผู้แจ้ง: <b><?= e($ticket['requester_name']) ?></b></div>
                <div>ช่างผู้รับผิดชอบ: <b><?= e($ticket['technician_name'] ?? 'ยังไม่มอบหมาย') ?></b></div>
                <div>สร้างเมื่อ: <?= e(thai_date($ticket['created_at'])) ?></div>
                <div>ซ่อมเสร็จ: <?= e(thai_date($ticket['resolved_at'])) ?></div>
            </dl>
            <?php if ($rating): ?>
                <div class="mt-4 p-3 bg-emerald-50 border border-emerald-200 rounded text-sm">
                    คะแนนความพึงพอใจ: <b><?= str_repeat('★', (int) $rating['score']) . str_repeat('☆', 5 - (int) $rating['score']) ?></b>
                    <?php if ($rating['feedback']): ?> — <?= e($rating['feedback']) ?><?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="bg-white border border-slate-200 rounded-lg p-6">
            <h2 class="font-semibold mb-3">ความเห็นและความคืบหน้า</h2>
            <ul id="comment-list" class="space-y-4">
                <?php foreach ($comments as $c): ?>
                    <li class="text-sm">
                        <div class="text-slate-500"><b class="text-slate-800"><?= e($c['author_name']) ?></b> · <?= e(thai_date($c['created_at'])) ?></div>
                        <p class="mt-1 whitespace-pre-line"><?= e($c['body']) ?></p>
                        <?php if ($c['image_path']): ?>
                            <a href="/uploads/<?= e($c['image_path']) ?>" target="_blank">
                                <img src="/uploads/<?= e($c['image_path']) ?>" alt="รูปแนบ" class="mt-2 max-h-48 rounded border">
                            </a>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
                <?php if (!$comments): ?><li class="text-sm text-slate-400">ยังไม่มีความเห็น</li><?php endif; ?>
            </ul>

            <?php if ($canComment): ?>
                <form id="comment-form" class="mt-5 border-t pt-4 space-y-2" enctype="multipart/form-data">
                    <textarea name="body" rows="2" required placeholder="พิมพ์ความเห็น..." class="w-full border rounded px-3 py-2 text-sm"></textarea>
                    <div class="flex items-center gap-3">
                        <input type="file" name="image" accept="image/*" class="text-sm">
                        <button class="ml-auto bg-slate-800 text-white text-sm rounded px-4 py-1.5">ส่งความเห็น</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </section>

    <aside class="space-y-6">
        <?php if ($action): ?>
            <div class="bg-white border-2 border-indigo-200 rounded-lg p-5">
                <h2 class="font-semibold mb-3">ดำเนินการ</h2>

                <?php if ($action === 'assign'): ?>
                    <form class="status-form space-y-3" data-status="Assigned">
                        <label class="block text-sm">เลือกช่างเทคนิค
                            <select name="technician_id" required class="mt-1 w-full border rounded px-2 py-2">
                                <option value="">— เลือก —</option>
                                <?php foreach ($technicians as $t): ?>
                                    <option value="<?= (int) $t['id'] ?>"><?= e($t['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <button class="w-full bg-indigo-600 text-white rounded py-2 text-sm">มอบหมายงาน</button>
                    </form>

                <?php elseif ($action === 'accept'): ?>
                    <form class="status-form space-y-3" data-status="InProgress">
                        <label class="block text-sm">comment ตอนรับงาน (จำเป็น)
                            <textarea name="comment" rows="3" required class="mt-1 w-full border rounded px-3 py-2"></textarea>
                        </label>
                        <button class="w-full bg-amber-500 text-white rounded py-2 text-sm">รับงาน / เริ่มซ่อม</button>
                    </form>

                <?php elseif ($action === 'resolve'): ?>
                    <form class="status-form space-y-3" data-status="Resolved" enctype="multipart/form-data">
                        <label class="block text-sm">รูปผลการซ่อม (จำเป็น)
                            <input type="file" name="image" accept="image/*" required class="mt-1 block text-sm">
                        </label>
                        <label class="block text-sm">สรุปการซ่อม
                            <textarea name="comment" rows="3" class="mt-1 w-full border rounded px-3 py-2"></textarea>
                        </label>
                        <button class="w-full bg-sky-600 text-white rounded py-2 text-sm">แจ้งซ่อมเสร็จ</button>
                    </form>

                <?php elseif ($action === 'confirm'): ?>
                    <form class="status-form space-y-3 pb-4 mb-4 border-b" data-status="Closed">
                        <p class="text-sm font-medium">ยอมรับผลงานและปิดงาน</p>
                        <label class="block text-sm">คะแนน (1-5)
                            <select name="score" required class="mt-1 w-full border rounded px-2 py-2">
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                    <option value="<?= $i ?>"><?= str_repeat('★', $i) ?> (<?= $i ?>)</option>
                                <?php endfor; ?>
                            </select>
                        </label>
                        <textarea name="feedback" rows="2" placeholder="ข้อเสนอแนะ (ไม่บังคับ)" class="w-full border rounded px-3 py-2 text-sm"></textarea>
                        <button class="w-full bg-emerald-600 text-white rounded py-2 text-sm">ยืนยันและให้คะแนน</button>
                    </form>
                    <form class="status-form space-y-3" data-status="InProgress">
                        <p class="text-sm font-medium text-red-700">ยังไม่หาย — ส่งกลับให้แก้ไข</p>
                        <textarea name="reason" rows="2" required placeholder="ระบุเหตุผล (จำเป็น)" class="w-full border rounded px-3 py-2 text-sm"></textarea>
                        <button class="w-full bg-red-600 text-white rounded py-2 text-sm">ไม่ยอมรับผล</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="bg-white border border-slate-200 rounded-lg p-5">
            <h2 class="font-semibold mb-3">ประวัติสถานะ (Audit Log)</h2>
            <ol class="space-y-3 text-sm border-l-2 border-slate-200 pl-4">
                <?php foreach ($logs as $l): ?>
                    <li>
                        <div class="font-medium">
                            <?= e($l['from_status'] ? TicketStatus::from($l['from_status'])->label() . ' → ' : '') ?><?= e(TicketStatus::from($l['to_status'])->label()) ?>
                        </div>
                        <div class="text-slate-500"><?= e($l['actor_name']) ?> · <?= e(thai_date($l['created_at'])) ?></div>
                        <?php if ($l['note']): ?><div class="text-slate-600"><?= e(mb_strimwidth($l['note'], 0, 120, '…')) ?></div><?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>
    </aside>
</div>
