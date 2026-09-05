# GitHub Actions workflow flow

This document describes the workflows currently defined in
`.github/workflows/`. All jobs run on GitHub-hosted `ubuntu-latest` runners.
PHP-related commands run through the repository Docker Compose setup
(`tools/ci/docker-compose`).

## Overall event flow

```mermaid
flowchart TD
    native[GitHub Secret Scanning and Push Protection] --> Alerts[GitHub security alerts or blocked push]

    push[Push to master or develop] --> PostMerge[CI post-merge]
    push --> CodeQL[CodeQL]
    push --> Audit{Changed files under .github?}
    Audit -->|yes| WorkflowAudit[Workflow audit]

    pr[PR opened / edited / synchronized / reopened] --> CI[CI]
    pr --> CodeQL
    pr --> Commitlint[Commitlint]
    pr --> Semantic[Semantic PR Title]
    pr --> PathLabel[PR Labeler]
    pr --> Audit

    tag[Push tag v*.*.*] --> Release[Release]

    weekly[Weekly schedules] --> CodeQL
    weekly --> LinkCheck[Link check]
    weekly --> Scorecard[OpenSSF Scorecard]
    weekly --> WorkflowAudit

    daily[Daily schedule] --> Stale[Stale]

    manual[workflow_dispatch] --> LinkCheck
    manual --> Scorecard
    manual --> Stale
    manual --> WorkflowAudit
```

## Pull request flow

```mermaid
flowchart TD
    PR[PR activity] --> CI[CI quality gate]
    PR --> Native[GitHub Secret Scanning]
    PR --> CL[Validate commit messages]
    PR --> SPT[Validate PR title]
    PR --> PL[Apply path labels]
    PR --> CQL[Analyze GitHub Actions with CodeQL]
    PR --> WA{Workflow files changed?}
    WA -->|yes| AL[Actionlint]
    WA -->|yes| ZM[Zizmor]

    CI --> V[Validate]
    V --> L[Lint matrix]
    L --> T[Test matrix]
    V --> DS[Dependency security]
    V --> SS[Secret scan]
    V --> SA[SAST]
    T --> CU[Coverage gate and uploads]
    DS --> CU
    SS --> CU
    SA --> CU
```

The CI and PR-policy workflows are independent: a label or title check does
not wait for CI, and CI does not wait for those checks.

## CI (`ci.yml`)

**Triggers:** pull request targeting `master` or `develop`.
Concurrent runs for the same Git ref cancel older in-progress runs.

```mermaid
flowchart LR
    V[Validate] --> L[Lint matrix] --> T[Test matrix] --> C[Coverage upload]
    V --> D[Dependency security]
    V --> S[Secret scan]
    V --> A[SAST]
    D --> C
    S --> C
    A --> C

    V --- V1[Checkout, build PHP image]
    V --- V2[Restore/install Composer dependencies]
    V --- V3[composer validate --strict]

    L --- L1[Pint, PHPCS, PHPStan, PHPMD, PHP-CS-Fixer]
    T --- T1[Laravel 12 + coverage artifact]
    T --- T2[Laravel 13 smoke tests]
    D --- D1[Composer audit + OSV Scanner + PR Dependency Review]
    S --- S1[Gitleaks OSS CLI in pinned Docker image]
    A --- A1[Semgrep OSS]
    C --- C1[Download Laravel 12 coverage artifact]
    C --- C2[Enforce 85% Clover floor]
    C --- C3[Upload to Codecov and SonarQube]
```

The test matrix pins `illuminate/*` and `orchestra/testbench` per Laravel major
(`^12` / testbench `^10`, `^13` / testbench `^11`). Coverage uploads come from
the Laravel 12 leg only.

## CI post-merge (`ci-post-merge.yml`)

**Triggers:** push to `master` or `develop`. Lighter sanity after merge:
validate, Laravel 12/13 tests (coverage from Laravel 12), Codecov, and SonarQube.

## Release flow (`release.yml`)

**Trigger:** push of a tag matching `v*.*.*`. Runs are not cancelled.

```mermaid
flowchart TD
    Tag[Push v*.*.* tag] --> Checkout[Checkout full history]
    Checkout --> Master{Tag commit is reachable from origin/master?}
    Master -->|no| Stop[Fail release]
    Master -->|yes| Setup[Build PHP image, install dependencies]
    Setup --> Quality[Composer validate, lint, PHPUnit coverage]
    Quality --> Trivy[Scan filesystem and PHP Docker image with Trivy]
    Trivy --> SARIF[Upload filesystem SARIF]
    SARIF --> SBOM[Generate SPDX JSON SBOM]
    SBOM --> GHRelease[Create GitHub Release with generated notes and SBOM]
```

The workflow fails if the tag is not on `origin/master`. It is therefore the
publication path — the tag itself is the release trigger.

## Other workflows

| Workflow | Trigger | Flow / result |
| --- | --- | --- |
| `codeql.yml` | Push/PR on `master` or `develop`; Monday 06:00 UTC | Checkout → initialize CodeQL for GitHub Actions only → analyze and publish security results. |
| `commitlint.yml` | PR opened, edited, synchronized, reopened | Checkout full history → validate every PR commit against `.github/commitlint.config.mjs`. |
| `semantic-pr.yml` | PR opened, edited, synchronized | Validate PR title type and require an uppercase first subject character. |
| `pr-labeler.yml` | PR opened, synchronized, reopened | Checkout → apply labels from `.github/labeler.yml` based on changed paths. |
| `link-check.yml` | Monday 04:00 UTC; manual | Checkout → Lychee checks Markdown links, excluding `vendor`, Packagist, Codecov, and mail links. |
| `scorecard.yml` | Push to `master`; Monday 00:00 UTC; manual | Checkout full history → OpenSSF Scorecard → upload SARIF. |
| `stale.yml` | Daily 01:00 UTC; manual | Mark issues/PRs stale after 60 inactive days; close 14 days later, except pinned/security/dependencies. |
| `workflow-audit.yml` | `.github/**` changes on push/PR; Monday 03:00 UTC; manual | Runs independent jobs: Actionlint checks workflow syntax and Zizmor scans workflow security, then uploads Zizmor SARIF when produced. |

## Scheduled maintenance timeline

All cron expressions use UTC, not the runner's local timezone.

```mermaid
gantt
    title Scheduled workflows (UTC)
    dateFormat  HH:mm
    axisFormat  %H:%M
    section Monday
    OpenSSF Scorecard      :milestone, 00:00, 0m
    Stale (also daily)     :milestone, 01:00, 0m
    Workflow audit         :milestone, 03:00, 0m
    Link check             :milestone, 04:00, 0m
    CodeQL                 :milestone, 06:00, 0m
```

## Notes

- All jobs use GitHub-hosted `ubuntu-latest`. There is no self-hosted runner pool
  and no local `.github/actions/self-hosted-prepare` composite.
- Secret scanning has two layers: GitHub Secret Scanning and Push Protection
  detect or block supported secrets at GitHub, while CI scans the checked-out
  Git history with the MIT-licensed Gitleaks OSS CLI. GitHub Secret Scanning
  and Push Protection are enabled in the repository security settings; they
  are not controlled by a workflow file.
- The coverage job uploads the Laravel 12 Clover report to Codecov and
  SonarQube. It normalizes a scheme-less `SONAR_HOST_URL` to `https://…`
  before scanning.
- Release is tag-driven, but its `origin/master` ancestry gate prevents a tag
  from publishing a commit that is not on the production branch.
