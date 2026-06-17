<?php
declare(strict_types=1);
header('Content-Type: text/html; charset=utf-8');
session_start();

function isAdminRole(string $role): bool
{
  $r = strtolower(trim($role));
  return in_array($r, ['admin', 'administrador', 'administrator', 'superadmin', 'root'], true);
}

if (!isAdminRole((string)($_SESSION['rol'] ?? ''))) {
  http_response_code(403);
  echo 'Acceso denegado. Solo administradores.';
  exit;
}

$nombre = htmlspecialchars((string)($_SESSION['nombre'] ?? 'Admin'), ENT_QUOTES, 'UTF-8');
$correo = htmlspecialchars((string)($_SESSION['correo'] ?? ''), ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Panel de Administración – RESMAG</title>
  <style>
    :root{--bg:#0f141b;--card:#1b2028;--muted:#c0c5ce;--accent:#00b4db;--accent2:#0083b0}
    *{box-sizing:border-box} body{margin:0;background:var(--bg);font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Cantarell,Noto Sans,sans-serif;color:#fff}
    .topbar{display:flex;align-items:center;justify-content:space-between;padding:14px 20px;background:#0b1016;border-bottom:1px solid rgba(255,255,255,.06)}
    .brand{display:flex;align-items:center;gap:10px;font-weight:700}
    .brand .pill{padding:4px 8px;border-radius:999px;background:linear-gradient(135deg,var(--accent),var(--accent2));font-size:.78rem}
    .user{color:var(--muted);font-size:.95rem}
    .wrapper{max-width:1100px;margin:24px auto;padding:0 16px;display:grid;grid-template-columns:240px 1fr;gap:20px}
    .sidebar{background:var(--card);border:1px solid rgba(255,255,255,.06);border-radius:12px;padding:16px}
    .sidebar h3{margin:0 0 10px;font-size:1rem}
    .nav a{display:block;color:#fff;text-decoration:none;padding:10px;border-radius:8px}
    .nav a.active,.nav a:hover{background:#0b3642}
    .content{display:grid;gap:20px}
    .card{background:var(--card);border:1px solid rgba(255,255,255,.06);border-radius:12px;padding:16px}
    .grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}
    .stat{padding:14px;background:#151a22;border:1px solid rgba(255,255,255,.06);border-radius:10px}
    .stat h4{margin:0 0 6px;font-size:.95rem;color:var(--muted)}
    .stat .n{font-size:1.6rem;font-weight:800}
    .btn{display:inline-block;padding:10px 14px;border-radius:8px;border:none;background:linear-gradient(135deg,var(--accent),var(--accent2));color:#fff;text-decoration:none;font-weight:700;cursor:pointer}
    .muted{color:var(--muted)}
    table{width:100%;border-collapse:collapse}
    th,td{padding:10px;border-bottom:1px solid rgba(255,255,255,.06);text-align:left}
    .footer{max-width:1100px;margin:0 auto 30px;padding:0 16px;color:var(--muted);font-size:.9rem}
  </style>
</head>
<body>
  <div class="topbar">
    <div class="brand">
      <span>RESMAG</span>
      <span class="pill">ADMIN</span>
    </div>
    <div class="user">
      <?php echo $nombre; ?> &lt;<?php echo $correo; ?>&gt; · <a class="btn" href="logout.php">Cerrar sesión</a>
    </div>
  </div>

  <div class="wrapper">
    <aside class="sidebar">
      <h3>Menú</h3>
      <nav class="nav">
        <a href="#" class="active">Inicio</a>
        <a href="#usuarios">Usuarios</a>
        <a href="#servicios">Servicios</a>
      </nav>
    </aside>

    <main class="content">
      <section class="card">
        <h2 style="margin:0 0 6px;">Bienvenido, <?php echo $nombre; ?></h2>
        <p class="muted" style="margin:0">Panel de control general</p>
        <div class="grid" style="margin-top:14px">
          <div class="stat"><h4>Órdenes activas</h4><div class="n" id="kpi1">—</div></div>
          <div class="stat"><h4>Servicios publicados</h4><div class="n" id="kpi2">—</div></div>
          <div class="stat"><h4>Usuarios registrados</h4><div class="n" id="kpi3">—</div></div>
        </div>
      </section>

      <section id="servicios" class="card">
        <div style="display:flex;justify-content:space-between;align-items:center">
          <h3 style="margin:0">Servicios</h3>
          <button class="btn" id="btn-refresh">Actualizar</button>
        </div>
        <div id="services-wrap" class="muted" style="margin-top:10px">Cargando…</div>
      </section>

      <section id="usuarios" class="card">
        <h3 style="margin:0 0 10px">Usuarios (demo)</h3>
        <p class="muted" style="margin:0 0 10px">Esta sección muestra una tabla de ejemplo. Podemos conectarla a tu base real cuando lo indiques.</p>
        <div style="overflow:auto">
          <table>
            <thead><tr><th>ID</th><th>Nombre</th><th>Ciudad</th><th>Distrito</th><th>Geo</th><th>Creado</th></tr></thead>
            <tbody id="tabla-usuarios"><tr><td colspan="6" class="muted">Cargando…</td></tr></tbody>
          </table>
        </div>
      </section>
    </main>
  </div>

  <div class="footer">
    <span>&copy; <?php echo date('Y'); ?> RESMAG</span>
  </div>

  <script>
    async function loadServicios() {
      const wrap = document.getElementById('services-wrap');
      try {
        const resp = await fetch('listar_usuarios.php', { method: 'GET', credentials: 'include' });
        const text = await resp.text();
        let count = 0;
        try {
          const json = JSON.parse(text);
          if (json && json.usuarios) count = json.usuarios.length;
        } catch {}
        document.getElementById('kpi1').textContent = '—';
        document.getElementById('kpi2').textContent = '5';
        document.getElementById('kpi3').textContent = String(count || '—');
        wrap.textContent = 'OK';
      } catch (e) {
        wrap.textContent = 'No disponible';
      }
    }
    async function loadUsuarios() {
      const body = document.getElementById('tabla-usuarios');
      try {
        const resp = await fetch('listar_usuarios.php', { credentials: 'include' });
        const data = await resp.json();
        if (!data.ok) throw new Error('error');
        body.innerHTML = '';
        data.usuarios.slice(0, 10).forEach(u => {
          const tr = document.createElement('tr');
          tr.innerHTML = `<td>${u.id}</td><td>${u.nombre}</td><td>${u.ciudad}</td><td>${u.distrito}</td><td>${u.latitud}, ${u.longitud}</td><td>${u.created_at}</td>`;
          body.appendChild(tr);
        });
        if (data.usuarios.length === 0) body.innerHTML = '<tr><td colspan="6" class="muted">Sin datos</td></tr>';
      } catch (e) {
        body.innerHTML = '<tr><td colspan="6" class="muted">No disponible</td></tr>';
      }
    }
    document.getElementById('btn-refresh').addEventListener('click', () => { loadServicios(); loadUsuarios(); });
    loadServicios(); loadUsuarios();
  </script>
</body>
</html>

