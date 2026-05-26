var __create = Object.create;
var __defProp = Object.defineProperty;
var __getOwnPropDesc = Object.getOwnPropertyDescriptor;
var __getOwnPropNames = Object.getOwnPropertyNames;
var __getProtoOf = Object.getPrototypeOf;
var __hasOwnProp = Object.prototype.hasOwnProperty;
var __commonJS = (cb, mod) => function __require() {
  return mod || (0, cb[__getOwnPropNames(cb)[0]])((mod = { exports: {} }).exports, mod), mod.exports;
};
var __copyProps = (to, from, except, desc) => {
  if (from && typeof from === "object" || typeof from === "function") {
    for (let key of __getOwnPropNames(from))
      if (!__hasOwnProp.call(to, key) && key !== except)
        __defProp(to, key, { get: () => from[key], enumerable: !(desc = __getOwnPropDesc(from, key)) || desc.enumerable });
  }
  return to;
};
var __toESM = (mod, isNodeMode, target) => (target = mod != null ? __create(__getProtoOf(mod)) : {}, __copyProps(
  // If the importer is in node compatibility mode or this is not an ESM
  // file that has been converted to a CommonJS file using a Babel-
  // compatible transform (i.e. "__esModule" has not been set), then set
  // "default" to the CommonJS "module.exports" for node compatibility.
  isNodeMode || !mod || !mod.__esModule ? __defProp(target, "default", { value: mod, enumerable: true }) : target,
  mod
));

// vendor-external:react
var require_react = __commonJS({
  "vendor-external:react"(exports, module) {
    module.exports = window.React;
  }
});

// package-external:@wordpress/i18n
var require_i18n = __commonJS({
  "package-external:@wordpress/i18n"(exports, module) {
    module.exports = window.wp.i18n;
  }
});

// vendor-external:react/jsx-runtime
var require_jsx_runtime = __commonJS({
  "vendor-external:react/jsx-runtime"(exports, module) {
    module.exports = window.ReactJSXRuntime;
  }
});

// widgets/_shared/widget-html.tsx
var import_react = __toESM(require_react());
var import_i18n = __toESM(require_i18n());
var import_jsx_runtime = __toESM(require_jsx_runtime());
function WidgetHtml({ slug }) {
  const hostRef = (0, import_react.useRef)(null);
  const [status, setStatus] = (0, import_react.useState)("loading");
  (0, import_react.useEffect)(() => {
    let cancelled = false;
    if (!wp?.apiFetch) {
      setStatus("error");
      return;
    }
    wp.apiFetch({ path: `/underway/v1/widgets/${slug}/html` }).then((res) => {
      if (cancelled) {
        return;
      }
      if (hostRef.current && typeof res?.html === "string") {
        hostRef.current.innerHTML = res.html;
        setStatus("ok");
      } else {
        setStatus("error");
      }
    }).catch(() => {
      if (!cancelled) {
        setStatus("error");
      }
    });
    return () => {
      cancelled = true;
    };
  }, [slug]);
  return /* @__PURE__ */ (0, import_jsx_runtime.jsxs)(import_jsx_runtime.Fragment, { children: [
    status === "loading" && /* @__PURE__ */ (0, import_jsx_runtime.jsx)("p", { style: { margin: 0, color: "#50575e" }, children: (0, import_i18n.__)("Loading\xE2\xA6", "underway") }),
    status === "error" && /* @__PURE__ */ (0, import_jsx_runtime.jsx)("p", { style: { margin: 0, color: "#b32d2e" }, children: (0, import_i18n.__)("Could not load this widget.", "underway") }),
    /* @__PURE__ */ (0, import_jsx_runtime.jsx)("div", { ref: hostRef })
  ] });
}

// widgets/ideas-inbox/render.tsx
var import_jsx_runtime2 = __toESM(require_jsx_runtime());
function IdeasInboxWidget() {
  return /* @__PURE__ */ (0, import_jsx_runtime2.jsx)(WidgetHtml, { slug: "ideas-inbox" });
}
export {
  IdeasInboxWidget as default
};
