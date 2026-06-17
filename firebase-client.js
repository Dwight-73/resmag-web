import { getApp, getApps, initializeApp } from "https://www.gstatic.com/firebasejs/10.12.5/firebase-app.js";
import { getAnalytics } from "https://www.gstatic.com/firebasejs/10.12.5/firebase-analytics.js";
import { getAuth } from "https://www.gstatic.com/firebasejs/10.12.5/firebase-auth.js";
import { getFirestore } from "https://www.gstatic.com/firebasejs/10.12.5/firebase-firestore.js";

export const firebaseConfig = {
  apiKey: "AIzaSyAA2ahN95a83qbe4uFSYvHCQWUiDRI9aWE",
  authDomain: "res-log-man.firebaseapp.com",
  databaseURL: "https://res-log-man-default-rtdb.firebaseio.com",
  projectId: "res-log-man",
  storageBucket: "res-log-man.firebasestorage.app",
  messagingSenderId: "822449215130",
  appId: "1:822449215130:web:af7144c5bdfccd97a53123",
  measurementId: "G-JXRX3VBWMX"
};

export const app = getApps().length ? getApp() : initializeApp(firebaseConfig);
export const auth = getAuth(app);
export const db = getFirestore(app);

try {
  getAnalytics(app);
} catch {}

export function safeText(value) {
  return (value ?? "").toString().trim();
}

export function createClientId(value) {
  const normalized = safeText(value).toLowerCase();
  if (!normalized) return "";
  return normalized.replace(/[^a-z0-9]+/g, "-").replace(/^-+|-+$/g, "");
}
