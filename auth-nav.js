import { getApp, getApps, initializeApp } from "https://www.gstatic.com/firebasejs/10.12.5/firebase-app.js";
import { getAuth, onAuthStateChanged, signOut } from "https://www.gstatic.com/firebasejs/10.12.5/firebase-auth.js";
import { doc, getDoc, getFirestore } from "https://www.gstatic.com/firebasejs/10.12.5/firebase-firestore.js";

const firebaseConfig = {
  apiKey: "AIzaSyAA2ahN95a83qbe4uFSYvHCQWUiDRI9aWE",
  authDomain: "res-log-man.firebaseapp.com",
  databaseURL: "https://res-log-man-default-rtdb.firebaseio.com",
  projectId: "res-log-man",
  storageBucket: "res-log-man.firebasestorage.app",
  messagingSenderId: "822449215130",
  appId: "1:822449215130:web:af7144c5bdfccd97a53123",
  measurementId: "G-JXRX3VBWMX"
};

const app = getApps().length ? getApp() : initializeApp(firebaseConfig);
const auth = getAuth(app);
const db = getFirestore(app);

function norm(value) {
  return (value ?? "").toString().trim().toLowerCase();
}

function isAdminRole(role) {
  return ["admin", "administrador", "administrator", "superadmin", "root"].includes(norm(role));
}

function getUserLabel(user) {
  const displayName = (user?.displayName || "").trim();
  if (displayName) return displayName;
  const email = (user?.email || "").trim();
  if (!email) return "Mi cuenta";
  return email.split("@")[0] || "Mi cuenta";
}

function createUserMenu(user, options = {}) {
  const wrapper = document.createElement("div");
  wrapper.className = "nav-user-menu";

  const trigger = document.createElement("button");
  trigger.type = "button";
  trigger.className = "user-chip nav-user-trigger";
  trigger.setAttribute("aria-haspopup", "menu");
  trigger.setAttribute("aria-expanded", "false");

  const icon = document.createElement("span");
  icon.className = "nav-user-icon";
  icon.setAttribute("aria-hidden", "true");
  icon.innerHTML = '<svg viewBox="0 0 24 24" focusable="false"><path d="M12 12c2.761 0 5-2.462 5-5.5S14.761 1 12 1 7 3.462 7 6.5 9.239 12 12 12zm0 2c-4.418 0-8 2.91-8 6.5 0 .828.672 1.5 1.5 1.5h13c.828 0 1.5-.672 1.5-1.5 0-3.59-3.582-6.5-8-6.5z"/></svg>';

  const label = document.createElement("span");
  label.className = "nav-user-name";
  label.textContent = getUserLabel(user);

  trigger.append(icon, label);

  const dropdown = document.createElement("div");
  dropdown.className = "nav-user-dropdown";
  dropdown.setAttribute("role", "menu");

  const emailRow = document.createElement("div");
  emailRow.className = "nav-user-email";
  emailRow.textContent = (user?.email || "").trim() || "Sin correo";

  const dashboardLink = document.createElement("a");
  dashboardLink.className = "logout-btn nav-user-dashboard";
  dashboardLink.href = "admin.html";
  dashboardLink.textContent = "Dashboard";
  dashboardLink.setAttribute("role", "menuitem");

  const logoutButton = document.createElement("button");
  logoutButton.type = "button";
  logoutButton.className = "logout-btn nav-user-logout";
  logoutButton.textContent = "Logout";
  logoutButton.setAttribute("role", "menuitem");

  dropdown.append(emailRow, dashboardLink, logoutButton);
  wrapper.append(trigger, dropdown);

  const closeMenu = () => {
    wrapper.classList.remove("is-open");
    trigger.setAttribute("aria-expanded", "false");
  };

  const openMenu = () => {
    wrapper.classList.add("is-open");
    trigger.setAttribute("aria-expanded", "true");
  };

  trigger.addEventListener("click", (event) => {
    event.stopPropagation();
    if (wrapper.classList.contains("is-open")) {
      closeMenu();
      return;
    }
    document.querySelectorAll(".nav-user-menu.is-open").forEach((node) => {
      if (node !== wrapper) node.classList.remove("is-open");
    });
    openMenu();
  });

  logoutButton.addEventListener("click", async () => {
    try {
      await signOut(auth);
      closeMenu();
      if (/auth\.html$/i.test(window.location.pathname)) {
        window.location.reload();
      }
    } catch {}
  });

  document.addEventListener("click", (event) => {
    if (!(event.target instanceof Node)) return;
    if (!wrapper.contains(event.target)) closeMenu();
  });

  window.addEventListener("keydown", (event) => {
    if (event.key === "Escape") closeMenu();
  });

  return wrapper;
}

function mountAuthNav(user, options = {}) {
  const loginLinks = Array.from(document.querySelectorAll(".menu .login-btn"));
  if (!loginLinks.length) return;

  loginLinks.forEach((loginLink) => {
    const existingMenu = loginLink.parentElement?.querySelector(".nav-user-menu");
    if (existingMenu) existingMenu.remove();

    if (!user) {
      loginLink.style.display = "";
      return;
    }

    loginLink.style.display = "none";
    const menu = createUserMenu(user, options);
    loginLink.insertAdjacentElement("afterend", menu);
  });
}

onAuthStateChanged(auth, async (user) => {
  if (!user) {
    mountAuthNav(null);
    return;
  }

  let isAdmin = false;
  try {
    const snapshot = await getDoc(doc(db, "usuarios", user.uid));
    if (snapshot.exists()) {
      const data = snapshot.data() || {};
      isAdmin = isAdminRole(data.rol);
    }
  } catch {}

  mountAuthNav(user, { isAdmin });
});
