<?php
declare(strict_types=1);
header('Content-Type: application/javascript; charset=utf-8');
?>
(async function(){
  try{
    const res = await fetch('http://localhost/res-log-man/api/session.php', { credentials: 'include' });
    const nav = document.querySelector('.barra-superior .menu');
    const loginBtn = nav ? nav.querySelector('.login-btn') : null;
    if(!res || !res.ok){
      if (loginBtn){ loginBtn.style.display = 'none'; }
      return;
    }
    const s = await res.json();
    const logged = s && (s.logged === true || s.ok === true || s.status === 'ok' || s.email || s.correo || (s.user && (s.user.email || s.usuario)));
    if (!nav){
      return;
    }
    if (!logged){
      if (loginBtn){ loginBtn.style.display = 'none'; }
      return;
    }
    if (loginBtn){
      loginBtn.href = 'http://localhost/res-log-man/logout.php';
      loginBtn.textContent = 'Salir';
    } else {
      const a = document.createElement('a');
      a.className = 'login-btn';
      a.href = 'http://localhost/res-log-man/logout.php';
      a.textContent = 'Salir';
      nav.appendChild(a);
    }
    const rol = (s.rol || (s.user && s.user.role) || '').toString();
    const hasAdmin = !!nav.querySelector('a[href="admin.html"]');
    if (rol === 'admin' && !hasAdmin){
      const adminLink = document.createElement('a');
      adminLink.href = 'admin.html';
      adminLink.textContent = 'Admin';
      const spacer = nav.querySelector('.spacer');
      if (spacer) nav.insertBefore(adminLink, spacer);
      else nav.appendChild(adminLink);
    }
  }catch(e){
    // silencio: en caso de error no bloqueamos la UI
  }
})(); 
