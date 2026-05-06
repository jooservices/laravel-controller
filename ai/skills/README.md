# AI Skills Map

This repository keeps one agent-facing repository policy and exposes it through a small set of checked-in adapter files.

See also:

- [AI Skills Usage Guide](./USAGE.md)

## Canonical repository skills

The source of truth lives under `.github/skills/`:

- `.github/skills/repo-quality-foundation/SKILL.md`
- `.github/skills/code-style-and-conventions/SKILL.md`
- `.github/skills/architecture-and-design-principles/SKILL.md`
- `.github/skills/docs-and-readme-sync/SKILL.md`
- `.github/skills/ci-and-release/SKILL.md`

## Adapter layers currently present

- `AGENTS.md`: shared always-on repository policy
- `CLAUDE.md`: Claude Code adapter guidance
- `.github/copilot-instructions.md`: GitHub Copilot baseline instructions
- `ai/skills/README.md` and `ai/skills/USAGE.md`: teammate-facing onboarding and task guidance

## Intent

All checked-in adapters should reflect the same repository truth:

- package architecture and module ownership
- code style and naming beyond formatter output
- docs and README synchronization rules
- CI and release expectations
- required Composer command map
- Laravel Resource as the presentation boundary
- `develop` as the current integration branch

## Current difference from `jooservices/dto`

This package intentionally keeps a narrower AI surface.

It does not currently ship additional adapter directories such as `.cursor/rules/`, `.claude/commands/`, `.github/instructions/`, `.github/prompts/`, `jetbrains/prompts/`, or `antigravity/prompts/`.

If those layers are added later, they should still derive from the canonical `.github/skills/` files first.
