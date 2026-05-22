# Underway

A bundle of four small WordPress dashboard widgets that, together, support the full pre-publish lifecycle of writing: capture, schedule, surface, and habituate.

| Widget | What it does | AI? |
|---|---|---|
| **Draft Sweeper** | Surfaces abandoned drafts, scored by completeness, recency, and topical relevance. | ✅ optional |
| **Future Drafts** | Capture an experience now as a tiny draft; the widget brings it back on a date you choose. | — |
| **Ideas Inbox** | A per-user ideas inbox on the dashboard. One click converts an idea into a draft. | — |
| **Habit Creator** | Detects recurring patterns in your archive and nudges you to write the next installment. | ✅ optional |

## Installation

1. Copy this folder to `wp-content/plugins/underway/`.
2. Activate via the Plugins screen — you'll be redirected to a one-screen onboarding wizard.
3. Pick which widgets to enable. Done.

To change settings later: **Settings → Underway**.

## AI

Two of the four widgets can be enhanced when an [AI provider is connected via the WordPress AI Client](https://make.wordpress.org/core/2025/ai-client/) (WordPress 7.0+). Without AI, both widgets still work fully — AI is purely additive (a one-line summary on Draft Sweeper; starter questions on Habit Creator).

If no provider is connected, the Underway settings page shows an inline notice with a link to provider setup, and the **AI enhanced** badge next to each AI-capable widget is greyed out.

## Architecture

```
underway/
├── underway.php              Main plugin file (header + bootstrap)
├── uninstall.php             Removes Underway options + cron on uninstall
├── readme.txt                wp.org-style readme
├── LICENSE                   GPL-2.0 full text
├── src/
│   ├── Plugin.php
│   ├── Activation.php
│   ├── ModuleRegistry.php
│   ├── Module/               Adapter classes for each bundled widget
│   ├── Ai/                   ProviderResolver + AiClient wrapper
│   └── Admin/                SettingsPage, Onboarding, Notices
├── modules/                  Vendored copies of each widget
│   ├── draft-sweeper/        (original namespace: DraftSweeper)
│   ├── future-drafts/        (original namespace: FutureDrafts, with build/)
│   ├── ideas-inbox/          (procedural, ideas_inbox_* prefix)
│   └── habit-creator/        (original namespace: HabitCreator)
├── assets/                   Bundle-level admin CSS
└── languages/                .pot / translations
```

Each module is loaded by a small adapter in `src/Module/`. The adapter:

1. Detects whether the matching standalone plugin is active and bails if so (no duplicate widgets).
2. Registers a local autoloader scoped to that module's namespace.
3. Calls into the module's existing bootstrap with `UNDERWAY_BUNDLED` defined, which the module reads to skip its own settings page and read AI preferences from Underway.

This keeps each module's internals intact — easy to pull upstream fixes back in.

## Credits

* **Draft Sweeper**, **Future Drafts**, **Habit Creator** — Anne McCarthy
* **Ideas Inbox** — Kelly Hoffman

## License

[GPL-2.0-or-later](LICENSE).
