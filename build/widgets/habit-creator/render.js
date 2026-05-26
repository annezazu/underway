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

// widgets/habit-creator/render.tsx
var import_i18n = __toESM(require_i18n());

// widgets/habit-creator/render.scss
if (typeof document !== "undefined" && true && !document.head.querySelector("style[data-wp-hash='7d91690693']")) {
  const style = document.createElement("style");
  style.setAttribute("data-wp-hash", "7d91690693");
  style.appendChild(document.createTextNode(".underway-widget{display:flex;flex-direction:column;gap:12px}.underway-widget__lede{font-weight:500;margin:0}.underway-widget__hint{color:var(--wp--preset--color--contrast-2,#50575e);font-size:12px;margin:0}.underway-widget .components-button.is-primary{align-self:flex-start}"));
  document.head.appendChild(style);
}

// widgets/habit-creator/render.tsx
var import_jsx_runtime = __toESM(require_jsx_runtime());
function HabitCreatorWidget(_props) {
  return /* @__PURE__ */ (0, import_jsx_runtime.jsxs)("div", { className: "underway-widget", children: [
    /* @__PURE__ */ (0, import_jsx_runtime.jsx)("p", { className: "underway-widget__lede", children: (0, import_i18n.__)(
      "Spot patterns in what you write and lean into a writing rhythm.",
      "underway"
    ) }),
    /* @__PURE__ */ (0, import_jsx_runtime.jsx)("p", { className: "underway-widget__hint", children: (0, import_i18n.__)(
      "Demo placeholder running in the experimental Dashboard. The full Habit Creator UI is being ported.",
      "underway"
    ) }),
    /* @__PURE__ */ (0, import_jsx_runtime.jsx)("a", { className: "components-button is-primary", href: "post-new.php", children: (0, import_i18n.__)("Start a new post", "underway") })
  ] });
}
export {
  HabitCreatorWidget as default
};
