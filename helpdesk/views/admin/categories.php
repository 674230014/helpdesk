<h1 class="text-xl font-bold mb-4">จัดการหมวดหมู่</h1>
<div class="grid md:grid-cols-3 gap-6">
    <div class="md:col-span-2 bg-white border rounded-lg overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-100 text-left text-slate-600"><tr><th class="px-3 py-2">ชื่อ</th><th class="px-3 py-2">คำอธิบาย</th></tr></thead>
            <tbody>
            <?php foreach ($categories as $c): ?>
                <tr class="border-t"><td class="px-3 py-2"><?= e($c['name']) ?></td><td class="px-3 py-2 text-slate-500"><?= e($c['description']) ?></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <form class="json-form bg-white border rounded-lg p-5 space-y-3 h-fit" action="/admin/categories">
        <h2 class="font-semibold">เพิ่มหมวดหมู่</h2>
        <input name="name" placeholder="ชื่อหมวดหมู่" required class="w-full border rounded px-3 py-2 text-sm">
        <textarea name="description" rows="2" placeholder="คำอธิบาย" class="w-full border rounded px-3 py-2 text-sm"></textarea>
        <button class="w-full bg-indigo-600 text-white rounded py-2 text-sm">บันทึก</button>
    </form>
</div>
