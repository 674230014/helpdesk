// Vanilla JavaScript + Fetch API — ทุก request ส่ง CSRF token ผ่าน header
(() => {
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

  function toast(msg, ok = true) {
    const el = document.getElementById('toast');
    if (!el) return alert(msg);
    el.textContent = msg;
    el.className = 'fixed bottom-4 right-4 px-4 py-3 rounded-lg shadow-lg text-white text-sm ' + (ok ? 'bg-emerald-600' : 'bg-red-600');
    clearTimeout(el._t);
    el._t = setTimeout(() => el.classList.add('hidden'), 3500);
  }

  async function send(url, formData, method = 'POST') {
    const headers = { 'X-CSRF-Token': csrf, Accept: 'application/json' };
    if (method !== 'POST') headers['X-HTTP-Method-Override'] = method; // PHP ไม่ parse multipart ของ PATCH
    const res = await fetch(url, { method: 'POST', headers, body: formData, credentials: 'same-origin' });
    let data = {};
    try { data = await res.json(); } catch (_) { /* non-JSON */ }
    if (!res.ok || data.ok === false) {
      throw new Error(data.error || `เกิดข้อผิดพลาด (HTTP ${res.status})`);
    }
    return data;
  }

  async function submitGuarded(form, fn) {
    const btn = form.querySelector('button');
    if (btn) btn.disabled = true;
    try { await fn(); } catch (e) { toast(e.message, false); } finally { if (btn) btn.disabled = false; }
  }

  // สร้าง Ticket (Sequence 6.1)
  document.getElementById('ticket-form')?.addEventListener('submit', (ev) => {
    ev.preventDefault();
    const f = ev.target;
    submitGuarded(f, async () => {
      const data = await send(f.action, new FormData(f));
      toast('สร้าง Ticket สำเร็จ');
      setTimeout(() => (location.href = data.redirect), 600);
    });
  });

  // เปลี่ยนสถานะ (Sequence 6.2) — PATCH /api/tickets/{id}/status
  const root = document.getElementById('ticket-root');
  document.querySelectorAll('.status-form').forEach((form) => {
    form.addEventListener('submit', (ev) => {
      ev.preventDefault();
      const fd = new FormData(form);
      fd.set('status', form.dataset.status);
      submitGuarded(form, async () => {
        const data = await send(`/api/tickets/${root.dataset.ticketId}/status`, fd, 'PATCH');
        toast('อัปเดตสถานะเป็น "' + data.label + '"');
        setTimeout(() => location.reload(), 500);
      });
    });
  });

  // เพิ่มความเห็น
  document.getElementById('comment-form')?.addEventListener('submit', (ev) => {
    ev.preventDefault();
    const f = ev.target;
    submitGuarded(f, async () => {
      await send(`/api/tickets/${root.dataset.ticketId}/comments`, new FormData(f));
      location.reload();
    });
  });

  // ฟอร์ม admin (ผู้ใช้/หมวดหมู่)
  document.querySelectorAll('form.json-form').forEach((form) => {
    form.addEventListener('submit', (ev) => {
      ev.preventDefault();
      submitGuarded(form, async () => {
        await send(form.getAttribute('action'), new FormData(form));
        location.reload();
      });
    });
  });
})();
