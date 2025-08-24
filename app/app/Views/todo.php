<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Todo App</title>
  <style>
    :root { font-family: system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif; }
    body { margin: 0; background: #0b1320; color: #e7ecf3; }
    .wrap { max-width: 820px; margin: 40px auto; padding: 24px; background: #131b2e; border-radius: 16px; }
    h1 { margin-top: 0; font-size: 24px; }
    form { display: flex; gap: 8px; margin: 16px 0 24px; }
    input[type="text"] { flex: 1; padding: 10px 12px; border-radius: 10px; border: 1px solid #2a375a; background:#0e1526; color:#e7ecf3; }
    button { padding: 10px 14px; border: 0; border-radius: 10px; cursor: pointer; }
    .btn-primary { background: #2f80ed; color: white; }
    .btn-ghost { background: transparent; color: #9fb0d9; border: 1px solid #2a375a; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 10px; border-bottom: 1px solid #2a375a; vertical-align: middle; }
    tr:last-child td { border-bottom: 0; }
    .muted { color: #9fb0d9; }
    .pill { padding: 2px 8px; border-radius: 999px; font-size: 12px; border:1px solid #2a375a; }
    .ok { background:#143f2c; color:#a9e9c2; border-color:#1b764d; }
    .no { background:#402020; color:#f3b3b3; border-color:#6a2a2a; }
    .row-actions { display: flex; gap: 6px; justify-content: flex-end; }
    .empty { text-align:center; padding: 24px; color:#9fb0d9; }
  </style>
</head>
<body>
  <div class="wrap">
    <h1>📝 Todo App</h1>

    <form id="form-add">
      <input id="title" type="text" placeholder="Nueva tarea..." required />
      <button class="btn-primary" type="submit">Agregar</button>
    </form>

    <div id="table-wrap">
      <table>
        <thead>
          <tr>
            <th style="width:60px">ID</th>
            <th>Título</th>
            <th style="width:140px">Estado</th>
            <th style="width:200px">Acciones</th>
          </tr>
        </thead>
        <tbody id="tbody"></tbody>
      </table>
      <div id="empty" class="empty" style="display:none">No hay tareas aún</div>
    </div>
  </div>

  <script>
    const API = '';

    const tbody = document.getElementById('tbody');
    const empty = document.getElementById('empty');
    const form  = document.getElementById('form-add');
    const title = document.getElementById('title');

    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const text = title.value.trim();
      if (!text) return;
      await createTask(text);
      title.value = '';
      await loadTasks();
    });

    async function loadTasks() {
      const res = await fetch(`${API}/tasks`);
      const list = await res.json();
      render(list);
    }

    function render(list) {
      tbody.innerHTML = '';
      if (!list || list.length === 0) {
        empty.style.display = 'block';
        return;
      }
      empty.style.display = 'none';
      for (const t of list) {
        const tr = document.createElement('tr');

        const tdId = document.createElement('td');
        tdId.textContent = t.id;

        const tdTitle = document.createElement('td');
        tdTitle.textContent = t.title;

        const tdState = document.createElement('td');
        const state = document.createElement('span');
        state.className = 'pill ' + (Number(t.completed) ? 'ok' : 'no');
        state.textContent = Number(t.completed) ? 'Completada' : 'Pendiente';
        tdState.appendChild(state);

        const tdActions = document.createElement('td');
        tdActions.className = 'row-actions';

        const btnToggle = document.createElement('button');
        btnToggle.className = 'btn-ghost';
        btnToggle.textContent = Number(t.completed) ? 'Marcar pendiente' : 'Marcar completa';
        btnToggle.onclick = async () => {
          await updateTask(t.id, { completed: Number(!Number(t.completed)) });
          await loadTasks();
        };

        const btnEdit = document.createElement('button');
        btnEdit.className = 'btn-ghost';
        btnEdit.textContent = 'Renombrar';
        btnEdit.onclick = async () => {
          const nuevo = prompt('Nuevo título:', t.title);
          if (nuevo && nuevo.trim()) {
            await updateTask(t.id, { title: nuevo.trim() });
            await loadTasks();
          }
        };

        const btnDel = document.createElement('button');
        btnDel.className = 'btn-ghost';
        btnDel.textContent = 'Eliminar';
        btnDel.onclick = async () => {
          if (confirm(`¿Eliminar tarea #${t.id}?`)) {
            await deleteTask(t.id);
            await loadTasks();
          }
        };

        tdActions.append(btnToggle, btnEdit, btnDel);

        tr.append(tdId, tdTitle, tdState, tdActions);
        tbody.appendChild(tr);
      }
    }

    async function createTask(title) {
      await fetch(`${API}/tasks`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ title, completed: 0 })
      });
    }

    async function updateTask(id, patch) {
      await fetch(`${API}/tasks/${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(patch)
      });
    }

    async function deleteTask(id) {
      await fetch(`${API}/tasks/${id}`, { method: 'DELETE' });
    }

    // Primera carga
    loadTasks().catch(err => {
      tbody.innerHTML = '';
      empty.style.display = 'block';
      empty.textContent = 'Error cargando tareas';
      console.error(err);
    });
  </script>
</body>
</html>
