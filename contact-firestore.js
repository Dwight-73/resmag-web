import { addDoc, collection, doc, getDoc, getDocs, serverTimestamp, setDoc } from "https://www.gstatic.com/firebasejs/10.12.5/firebase-firestore.js";
import { db, createClientId, safeText } from "./firebase-client.js";

function setText(target, value) {
  if (!target) return;
  const next = safeText(value);
  if (!next) return;
  target.textContent = next;
}

function setHref(target, value, prefix = "") {
  if (!target) return;
  const next = safeText(value);
  if (!next) return;
  target.href = prefix ? `${prefix}${next}` : next;
}

async function loadGeneralConfig() {
  try {
    const snapshot = await getDoc(doc(db, "configuracion", "general"));
    if (!snapshot.exists()) return;

    const data = snapshot.data() || {};
    const address = safeText(data.direccion);
    const email = safeText(data.correoPrincipal);
    const phone = safeText(data.telefonoPrincipal);
    const schedule = safeText(data.horario);

    if (address) {
      document.querySelectorAll("[data-config-address]").forEach((node) => setText(node, address));
    }

    if (email) {
      document.querySelectorAll(".email-link").forEach((node) => {
        setText(node, email);
        setHref(node, email, "mailto:");
      });
    }

    if (schedule) {
      document.querySelectorAll("[data-config-schedule]").forEach((node) => setText(node, schedule));
    }

    if (phone) {
      const phoneNode = document.querySelector("[data-config-phone]");
      setText(phoneNode, phone);
      const whatsapp = document.querySelector(".whatsapp-float");
      if (whatsapp) {
        const cleanPhone = phone.replace(/[^\d]/g, "");
        whatsapp.href = `https://wa.me/${cleanPhone}?text=Hola%20RESMAG%2C%20quisiera%20una%20cotizaci%C3%B3n.`;
      }
    }
  } catch {}
}

async function loadServices() {
  const select = document.getElementById("serviceSelect");
  if (!select) return;

  try {
    const snapshot = await getDocs(collection(db, "servicios"));
    const services = snapshot.docs
      .map((item) => ({ id: item.id, ...(item.data() || {}) }))
      .filter((item) => item.activo !== false)
      .sort((a, b) => Number(a.orden || 0) - Number(b.orden || 0));

    if (!services.length) return;

    const placeholder = select.querySelector('option[value=""]');
    select.innerHTML = "";
    if (placeholder) select.append(placeholder);

    services.forEach((service) => {
      const option = document.createElement("option");
      option.value = safeText(service.nombre || service.id);
      option.textContent = safeText(service.nombre || service.id);
      select.append(option);
    });

    const requestedService = safeText(new URLSearchParams(window.location.search).get("servicio"));
    if (requestedService) {
      const exists = Array.from(select.options).some((option) => option.value === requestedService);
      if (exists) select.value = requestedService;
    }
  } catch {}
}

async function saveQuoteToFirestore(quote) {
  const nombre = safeText(quote?.nombre);
  const empresa = safeText(quote?.empresa);
  const email = safeText(quote?.email).toLowerCase();
  const telefono = safeText(quote?.telefono);
  const servicio = safeText(quote?.servicio);
  const urgencia = safeText(quote?.urgencia).toLowerCase() === "urgente" ? "urgente" : "normal";
  const mensaje = safeText(quote?.mensaje);
  const estado = safeText(quote?.estado) || "nuevo";
  const clientId = createClientId(email) || `cliente-${Date.now()}`;

  await setDoc(doc(db, "clientes", clientId), {
    clienteId: clientId,
    nombreEmpresa: empresa,
    contactoNombre: nombre,
    correo: email,
    telefono,
    estado: "activo",
    origen: "web",
    updatedAt: serverTimestamp(),
    createdAt: serverTimestamp()
  }, { merge: true });

  const quoteRef = await addDoc(collection(db, "cotizaciones"), {
    createdAt: serverTimestamp(),
    nombre,
    empresa,
    email,
    telefono,
    servicio,
    urgencia,
    mensaje,
    estado,
    canal: "web",
    origen: "landing",
    clienteId
  });

  await addDoc(collection(db, "actividad"), {
    usuarioId: clientId,
    correo: email,
    accion: "crear_cotizacion",
    detalle: `Nueva cotizacion para ${servicio || "servicio no especificado"} (${urgencia})`,
    modulo: "contacto",
    fecha: serverTimestamp(),
    resultado: "exitoso",
    referenciaId: quoteRef.id
  });

  return { clientId, quoteId: quoteRef.id };
}

window.resmagSaveQuoteToFirestore = saveQuoteToFirestore;

window.addEventListener("DOMContentLoaded", async () => {
  await Promise.all([loadGeneralConfig(), loadServices()]);
});
