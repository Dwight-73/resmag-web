import { collection, doc, getDoc, getDocs } from "https://www.gstatic.com/firebasejs/10.12.5/firebase-firestore.js";
import { db, safeText } from "./firebase-client.js";

function setText(target, value) {
  if (!target) return;
  const next = safeText(value);
  if (!next) return;
  target.textContent = next;
}

function serviceHref(name) {
  return `contactanos.html?servicio=${encodeURIComponent(name)}#cotizacion`;
}

function renderServiceCard(service) {
  const card = document.createElement("div");
  card.className = "card-servicio";

  const icon = document.createElement("div");
  icon.className = "icono-servicio";
  icon.textContent = safeText(service.icono) || "⚓";

  const title = document.createElement("h3");
  title.textContent = safeText(service.nombre) || "Servicio";

  const description = document.createElement("p");
  description.textContent = safeText(service.descripcion) || "Servicio especializado para operaciones RESMAG.";

  card.append(icon, title, description);
  return card;
}

async function loadHomeConfig() {
  const title = document.getElementById("hero-title");
  const description = document.getElementById("hero-description");
  const primaryCta = document.getElementById("hero-cta-primary");
  const secondaryCta = document.getElementById("hero-cta-secondary");

  try {
    const snapshot = await getDoc(doc(db, "configuracion", "home"));
    if (!snapshot.exists()) return;

    const data = snapshot.data() || {};
    setText(title, data.heroTitulo);
    setText(description, data.heroDescripcion);
    setText(primaryCta, data.ctaPrincipal);
    setText(secondaryCta, data.ctaSecundario);
  } catch {}
}

async function loadFeaturedServices() {
  const grid = document.getElementById("featured-services-grid");
  if (!grid) return;

  try {
    const snapshot = await getDocs(collection(db, "servicios"));
    const services = snapshot.docs
      .map((item) => ({ id: item.id, ...(item.data() || {}) }))
      .filter((item) => item.activo !== false)
      .sort((a, b) => Number(a.orden || 0) - Number(b.orden || 0))
      .slice(0, 6);

    if (!services.length) return;

    grid.innerHTML = "";
    services.forEach((service) => {
      const card = renderServiceCard(service);
      const actions = document.createElement("div");
      actions.className = "card-servicio-actions";

      const link = document.createElement("a");
      link.className = "btn-cotizar";
      link.href = serviceHref(service.nombre || service.id);
      link.textContent = "Solicitar";
      actions.append(link);
      card.append(actions);
      grid.append(card);
    });
  } catch {}
}

window.addEventListener("DOMContentLoaded", async () => {
  await Promise.all([loadHomeConfig(), loadFeaturedServices()]);
});
