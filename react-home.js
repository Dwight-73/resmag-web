import React, { useEffect, useMemo, useState } from "https://esm.sh/react@18.3.1";
import { createRoot } from "https://esm.sh/react-dom@18.3.1/client";

const e = React.createElement;

const copy = {
  es: {
    badge: "Modulo interactivo con React",
    title: "Centro digital de atencion",
    subtitle: "Integramos React en el Home para mostrar una vista interactiva del proceso comercial y operativo sin alterar la estructura actual del sitio.",
    stats: [
      { value: "24/7", label: "Disponibilidad de respuesta" },
      { value: "< 24h", label: "Tiempo referencial de respuesta" },
      { value: "3", label: "Frentes de servicio principales" }
    ],
    tabsLabel: "Explora por linea de servicio",
    cta: "Solicitar cotizacion",
    services: [
      {
        id: "naval",
        tab: "Mantenimiento naval",
        title: "Mantenimiento naval planificado",
        description: "Coordinamos diagnostico, propuesta, ejecucion y cierre con trazabilidad para mantener continuidad operativa.",
        bullets: ["Incluye coordinacion tecnica y seguimiento por hitos.", "Entrega evidencias y control de calidad del servicio.", "Ideal para atenciones preventivas y correctivas."],
        meta: ["Tiempo tipico: segun alcance", "Entregable: reporte y evidencias", "Cobertura: Callao y Lima"]
      },
      {
        id: "logistica",
        tab: "Logistica maritima",
        title: "Logistica con visibilidad del requerimiento",
        description: "Centralizamos la solicitud, el estado y la comunicacion para reducir re-trabajo y acelerar la atencion inicial.",
        bullets: ["Registro estructurado del requerimiento del cliente.", "Coordinacion con responsable y estado visible.", "Soporte para operaciones portuarias y de carga."],
        meta: ["Seguimiento: en tiempo real", "Control: estados y observaciones", "Objetivo: respuesta mas ordenada"]
      },
      {
        id: "buceo",
        tab: "Trabajo de buceo",
        title: "Intervenciones subacuaticas con enfoque en seguridad",
        description: "Organizamos el requerimiento y el alcance para que el equipo operativo intervenga con informacion clara y confirmada.",
        bullets: ["Planificacion previa y coordinacion del servicio.", "Enfoque en seguridad y cumplimiento.", "Trazabilidad desde la solicitud hasta el cierre."],
        meta: ["Prioridad: seguridad", "Coordinacion: alcance validado", "Resultado: servicio controlado"]
      }
    ]
  },
  en: {
    badge: "Interactive module with React",
    title: "Digital service hub",
    subtitle: "We integrated React into the Home page to show an interactive view of the commercial and operational flow without changing the current site structure.",
    stats: [
      { value: "24/7", label: "Response availability" },
      { value: "< 24h", label: "Reference response time" },
      { value: "3", label: "Main service lines" }
    ],
    tabsLabel: "Browse by service line",
    cta: "Request a quote",
    services: [
      {
        id: "naval",
        tab: "Naval maintenance",
        title: "Planned naval maintenance",
        description: "We coordinate assessment, proposal, execution, and close-out with traceability to maintain operational continuity.",
        bullets: ["Includes technical coordination and milestone tracking.", "Delivers evidence and service quality control.", "Suitable for preventive and corrective work."],
        meta: ["Typical timing: depends on scope", "Deliverable: report and evidence", "Coverage: Callao and Lima"]
      },
      {
        id: "logistica",
        tab: "Maritime logistics",
        title: "Logistics with request visibility",
        description: "We centralize the request, status, and communication to reduce rework and speed up initial response.",
        bullets: ["Structured intake of the client request.", "Coordination with owner and visible status.", "Support for port and cargo operations."],
        meta: ["Tracking: real-time", "Control: statuses and notes", "Goal: a more orderly response"]
      },
      {
        id: "buceo",
        tab: "Diving work",
        title: "Underwater interventions with a safety-first focus",
        description: "We organize the request and scope so the operational team can act with clear and confirmed information.",
        bullets: ["Pre-planning and service coordination.", "Safety and compliance oriented.", "Traceability from request to close-out."],
        meta: ["Priority: safety", "Coordination: validated scope", "Outcome: controlled service"]
      }
    ]
  },
  pt: {
    badge: "Modulo interativo com React",
    title: "Centro digital de atendimento",
    subtitle: "Integramos React na Home para mostrar uma visao interativa do fluxo comercial e operacional sem alterar a estrutura atual do site.",
    stats: [
      { value: "24/7", label: "Disponibilidade de resposta" },
      { value: "< 24h", label: "Tempo referencial de resposta" },
      { value: "3", label: "Principais linhas de servico" }
    ],
    tabsLabel: "Explore por linha de servico",
    cta: "Solicitar orcamento",
    services: [
      {
        id: "naval",
        tab: "Manutencao naval",
        title: "Manutencao naval planejada",
        description: "Coordenamos diagnostico, proposta, execucao e encerramento com rastreabilidade para manter a continuidade operacional.",
        bullets: ["Inclui coordenacao tecnica e acompanhamento por marcos.", "Entrega evidencias e controle de qualidade do servico.", "Ideal para atendimentos preventivos e corretivos."],
        meta: ["Prazo tipico: conforme escopo", "Entregavel: relatorio e evidencias", "Cobertura: Callao e Lima"]
      },
      {
        id: "logistica",
        tab: "Logistica maritima",
        title: "Logistica com visibilidade da solicitacao",
        description: "Centralizamos a solicitacao, o status e a comunicacao para reduzir retrabalho e acelerar o atendimento inicial.",
        bullets: ["Registro estruturado da necessidade do cliente.", "Coordenacao com responsavel e status visivel.", "Suporte para operacoes portuarias e de carga."],
        meta: ["Acompanhamento: em tempo real", "Controle: status e observacoes", "Objetivo: resposta mais organizada"]
      },
      {
        id: "buceo",
        tab: "Trabalho de mergulho",
        title: "Intervencoes subaquaticas com foco em seguranca",
        description: "Organizamos a solicitacao e o escopo para que a equipe operacional atue com informacoes claras e confirmadas.",
        bullets: ["Planejamento previo e coordenacao do servico.", "Foco em seguranca e conformidade.", "Rastreabilidade da solicitacao ao encerramento."],
        meta: ["Prioridade: seguranca", "Coordenacao: escopo validado", "Resultado: servico controlado"]
      }
    ]
  }
};

function getLanguage() {
  const selector = document.getElementById("selector-idioma");
  const value = selector?.value || document.documentElement.lang || "es";
  return copy[value] ? value : "es";
}

function useSiteLanguage() {
  const [language, setLanguage] = useState(getLanguage());

  useEffect(() => {
    const selector = document.getElementById("selector-idioma");
    const update = () => setLanguage(getLanguage());

    selector?.addEventListener("change", update);
    window.addEventListener("resize", update);

    const observer = new MutationObserver(update);
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ["lang"] });

    return () => {
      selector?.removeEventListener("change", update);
      window.removeEventListener("resize", update);
      observer.disconnect();
    };
  }, []);

  return language;
}

function ReactHomeShowcase() {
  const language = useSiteLanguage();
  const content = useMemo(() => copy[language] || copy.es, [language]);
  const [active, setActive] = useState(content.services[0].id);

  useEffect(() => {
    setActive(content.services[0].id);
  }, [content]);

  const selected = content.services.find((item) => item.id === active) || content.services[0];

  return e("section", { className: "react-showcase scroll-reveal is-visible" }, [
    e("div", { className: "react-header", key: "header" }, [
      e("span", { className: "react-badge", key: "badge" }, content.badge),
      e("h2", { key: "title" }, content.title),
      e("p", { key: "subtitle" }, content.subtitle)
    ]),
    e("div", { className: "react-grid", key: "grid" }, [
      e("div", { className: "react-stats", key: "stats" },
        content.stats.map((stat) =>
          e("article", { className: "react-stat-card", key: stat.label }, [
            e("strong", { key: "value" }, stat.value),
            e("span", { key: "label" }, stat.label)
          ])
        )
      ),
      e("div", { className: "react-panel", key: "panel" }, [
        e("p", { className: "react-panel-label", key: "label" }, content.tabsLabel),
        e("div", { className: "react-tabs", key: "tabs" },
          content.services.map((service) =>
            e("button", {
              key: service.id,
              type: "button",
              className: service.id === active ? "react-tab is-active" : "react-tab",
              onClick: () => setActive(service.id)
            }, service.tab)
          )
        ),
        e("div", { className: "react-panel-body", key: "body" }, [
          e("h3", { key: "title" }, selected.title),
          e("p", { key: "description" }, selected.description),
          e("ul", { className: "react-list", key: "bullets" },
            selected.bullets.map((bullet) => e("li", { key: bullet }, bullet))
          ),
          e("div", { className: "react-meta", key: "meta" },
            selected.meta.map((item) => e("span", { className: "react-pill", key: item }, item))
          ),
          e("a", { className: "btn-cotizar", href: "contactanos.html#cotizacion", key: "cta" }, content.cta)
        ])
      ])
    ])
  ]);
}

const container = document.getElementById("react-home-root");
if (container) {
  createRoot(container).render(e(ReactHomeShowcase));
}
