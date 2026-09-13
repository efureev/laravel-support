# Contributing

## The gate

```bash
composer test         # PHPCS + PHPStan + PHPUnit
composer test-cover   # the same, with coverage
composer cs-fix       # apply the PHPCS fixes that can be applied automatically
```

Everything has to be green before a commit: PHPCS clean, PHPStan clean at the level pinned in
`phpstan.neon` (no `ignoreErrors`, no `@phpstan-ignore`), and the whole suite passing.

## Services

The functional tests run against real services, because the bugs they cover were invisible to
anything less — a Postgres array operator behaves differently from a mock, and the Redis cacher
bug was specific to one client library.

```bash
composer test:docker   # PostgreSQL 18 + Redis 8 in containers; no local services needed
```

Running locally instead, point the suite at your own servers:

```bash
DB_HOST=localhost DB_PORT=5432 DB_DATABASE=forge DB_USERNAME=forge DB_PASSWORD=forge \
REDIS_HOST=localhost REDIS_PORT=6379 \
composer test
```

Redis tests skip themselves when no server answers. CI runs them against both phpredis and
predis (`REDIS_CLIENT=predis`) — the two libraries disagree about `eval`'s argument order, which
is how the cacher stayed broken for so long.

## Writing tests

A test that cannot fail is worse than no test. Before keeping one, break the code it covers —
remove the clause, invert the condition, drop the escaping — and check that it goes red. Line
coverage says a line ran, not that a contract is protected.

Never let a test helper reimplement the logic under test: mutating production code will not move
a helper that computes the expected value the same way.

## Documentation

`docs/` is the source of truth and is gated by `tests/Docs/DocumentationTest.php`:

* every `Php\Support\Laravel\…` symbol the docs name must exist;
* the signatures printed in the reference must match reflection;
* every internal link must resolve, and every page must be reachable from the index;
* everything under `src/` must be named somewhere in the docs.

Rename something without updating the docs and the build fails.

## The pre-commit hook

`.git-hooks/pre-commit` runs PHPCS over the staged PHP files. Enable it once per clone:

```bash
git config core.hooksPath .git-hooks
```

## Packaging

Composer installs the GitHub zipball, so `.gitattributes` — not `archive.exclude` — decides what
reaches a consumer's `vendor/`. After adding a top-level file, check what ships:

```bash
git archive --format=tar HEAD | tar -t | awk -F/ '{print $1}' | sort -u
```

Only `src/`, `resources/`, `composer.json`, `README.md`, `CHANGELOG.md` and `LICENSE` should
appear.
