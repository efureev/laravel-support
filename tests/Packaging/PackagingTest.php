<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Tests\Packaging;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Guards what a consumer actually receives.
 *
 * Composer installs the GitHub zipball, so `.gitattributes` — not `archive.exclude` — decides
 * the contents of someone's `vendor/`. Before 5.0 the whole repository shipped: tests, docs, CI
 * configs and Docker files. These checks keep that from creeping back, and keep the `require`
 * list honest about what `src/` really needs.
 */
class PackagingTest extends TestCase
{
    /** Exactly what a consumer should receive. */
    private const SHIPPED = [
        'CHANGELOG.md',
        'LICENSE',
        'README.md',
        'composer.json',
        'resources',
        'src',
    ];

    private static function repoRoot(): string
    {
        return dirname(__DIR__, 2);
    }

    /**
     * @return array<string, mixed>
     */
    private static function composer(): array
    {
        /** @var array<string, mixed> $decoded */
        $decoded = json_decode(
            (string)file_get_contents(self::repoRoot() . '/composer.json'),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        return $decoded;
    }

    /**
     * Everything at the top level that must never reach a consumer.
     *
     * @return iterable<string, array{string}>
     */
    public static function developmentOnlyPathProvider(): iterable
    {
        $paths = [
            '.changelog',
            '.docker',
            '.dockerignore',
            '.git-hooks',
            '.github',
            '.gitattributes',
            '.gitignore',
            '.idea',
            '.phpcs.xml',
            'CONTRIBUTING.md',
            'SUMMARY.md',
            'docker-compose.yml',
            'docs',
            'phpstan.neon',
            'phpunit.xml.dist',
            'tests',
        ];

        foreach ($paths as $path) {
            yield $path => [$path];
        }
    }

    #[Test]
    #[DataProvider('developmentOnlyPathProvider')]
    public function development_only_paths_are_export_ignored(string $path): void
    {
        $attributes = (string)file_get_contents(self::repoRoot() . '/.gitattributes');

        self::assertMatchesRegularExpression(
            '#^/' . preg_quote($path, '#') . '\s+export-ignore$#m',
            $attributes,
            "$path would ship to a consumer's vendor/ directory."
        );
    }

    #[Test]
    public function every_top_level_entry_is_either_shipped_on_purpose_or_export_ignored(): void
    {
        // A new top-level file has to be a deliberate decision, not an oversight.
        $attributes  = (string)file_get_contents(self::repoRoot() . '/.gitattributes');
        $unaccounted = [];

        foreach ((array)scandir(self::repoRoot()) as $entry) {
            if (!is_string($entry) || in_array($entry, ['.', '..', '.git', 'vendor', 'storage'], true)) {
                continue;
            }

            if (in_array($entry, self::SHIPPED, true)) {
                continue;
            }

            if (str_contains($attributes, "/$entry ")) {
                continue;
            }

            // Untracked scratch files are not part of the package either way.
            $command = sprintf(
                'git -C %s ls-files --error-unmatch %s 2>/dev/null',
                escapeshellarg(self::repoRoot()),
                escapeshellarg($entry)
            );

            exec($command, $out, $status);

            if ($status !== 0) {
                continue;
            }

            $unaccounted[] = $entry;
        }

        self::assertSame(
            [],
            $unaccounted,
            'These tracked top-level entries are neither in the shipped set nor export-ignored.'
        );
    }

    #[Test]
    public function the_shipped_set_matches_what_git_archive_produces(): void
    {
        exec(
            'git -C ' . escapeshellarg(self::repoRoot()) . ' archive --format=tar HEAD 2>/dev/null | tar -t',
            $entries,
            $status
        );

        if ($status !== 0 || $entries === []) {
            self::markTestSkipped('git archive is unavailable here.');
        }

        $roots = array_map(static fn(string $line): string => explode('/', $line)[0], $entries);
        $top   = array_values(array_unique($roots));
        sort($top);

        self::assertSame(self::SHIPPED, $top);
    }

    /**
     * Every external symbol `src/` imports has to be satisfiable by the declared `require` set.
     *
     * The exceptions are the two Redis connection classes, which appear in a docblock only —
     * `illuminate/redis` stays a `suggest` so a consumer who never uses a Redis cache store does
     * not pay for it. {@see self::the_redis_connection_classes_are_documentation_only()} pins
     * that they are never touched at runtime.
     */
    #[Test]
    public function every_external_symbol_used_by_src_is_a_declared_dependency(): void
    {
        $suggestedOnly = [
            'Illuminate\\Redis\\Connections\\PhpRedisConnection',
            'Illuminate\\Redis\\Connections\\PredisConnection',
        ];

        $missing = [];

        foreach (self::externalImports() as $symbol) {
            if (in_array($symbol, $suggestedOnly, true)) {
                continue;
            }

            if (!class_exists($symbol) && !interface_exists($symbol) && !trait_exists($symbol)) {
                $missing[] = $symbol;
            }
        }

        sort($missing);

        self::assertSame([], $missing, 'src/ imports symbols the require list does not cover.');
    }

    #[Test]
    public function the_redis_connection_classes_are_documentation_only(): void
    {
        $source = (string)file_get_contents(
            self::repoRoot() . '/src/Traits/Models/Cachers/RedisCacher.php'
        );

        foreach (['PhpRedisConnection', 'PredisConnection'] as $class) {
            foreach (explode("\n", $source) as $line) {
                $line = trim($line);

                if (!str_contains($line, $class)) {
                    continue;
                }

                $isImport  = str_starts_with($line, 'use ');
                $isComment = str_starts_with($line, '*')
                    || str_starts_with($line, '//')
                    || str_starts_with($line, '/*');

                self::assertTrue(
                    $isImport || $isComment,
                    "$class is referenced in executable code: $line"
                );
            }
        }
    }

    #[Test]
    public function illuminate_redis_is_suggested_rather_than_required(): void
    {
        $composer = self::composer();

        self::assertArrayNotHasKey('illuminate/redis', $composer['require']);
        self::assertArrayHasKey('illuminate/redis', $composer['suggest']);
    }

    #[Test]
    public function the_dropped_dependency_is_not_referenced_anywhere(): void
    {
        $composer = self::composer();

        self::assertArrayNotHasKey('efureev/support', $composer['require']);
        self::assertArrayNotHasKey('efureev/support', $composer['require-dev']);

        foreach (self::externalImports() as $symbol) {
            self::assertStringStartsNotWith(
                'Php\\Support\\Exceptions',
                $symbol,
                'src/ still imports from the dropped package.'
            );
            self::assertStringStartsNotWith(
                'Php\\Support\\Helpers',
                $symbol,
                'src/ still imports from the dropped package.'
            );
        }
    }

    /**
     * Top-of-file imports in `src/`, excluding the package's own namespace.
     *
     * Only lines that are genuine `use X;` imports count — `use SomeTrait;` inside a class body
     * is indented, so the anchor on column zero keeps trait usage out.
     *
     * @return string[]
     */
    private static function externalImports(): array
    {
        $imports  = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(self::repoRoot() . '/src')
        );

        foreach ($iterator as $file) {
            if (!$file instanceof SplFileInfo || $file->getExtension() !== 'php') {
                continue;
            }

            $source = (string)file_get_contents($file->getPathname());

            if (preg_match_all('/^use\s+([A-Z][A-Za-z0-9_\\\\]+)\s*;/m', $source, $matches)) {
                foreach ($matches[1] as $symbol) {
                    if (!str_starts_with($symbol, 'Php\\Support\\Laravel\\')) {
                        $imports[$symbol] = true;
                    }
                }
            }
        }

        $names = array_keys($imports);
        sort($names);

        return $names;
    }
}
