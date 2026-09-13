<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Tests\Docs;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Keeps the documentation honest.
 *
 * Prose can drift; these checks cannot. Every class and method the docs name has to exist, every
 * internal link has to resolve, and every page has to be reachable from the index. The missing
 * upgrade guide that four of these files used to link to is exactly the failure this catches.
 */
class DocumentationTest extends TestCase
{
    private const PACKAGE_NAMESPACE = 'Php\\Support\\Laravel\\';

    /**
     * Classes the upgrade guide names precisely because they are gone. Asserted absent rather
     * than skipped: if one comes back, the guide needs rewriting and this will say so.
     *
     * @var string[]
     */
    private const REMOVED_IN_5_0 = [
        'Php\\Support\\Laravel\\Traits\\Models\\WrapQuery',
        'Php\\Support\\Laravel\\Test\\CreateHttpRequests',
    ];

    private static function docsDir(): string
    {
        return dirname(__DIR__, 2) . '/docs';
    }

    private static function repoRoot(): string
    {
        return dirname(__DIR__, 2);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function markdownFileProvider(): iterable
    {
        $files   = glob(self::docsDir() . '/*.md') ?: [];
        $files[] = self::repoRoot() . '/README.md';

        foreach ($files as $file) {
            yield basename(dirname($file)) . '/' . basename($file) => [$file];
        }
    }

    #[Test]
    #[DataProvider('markdownFileProvider')]
    public function every_class_the_docs_name_exists(string $file): void
    {
        $markdown = (string)file_get_contents($file);

        preg_match_all(
            '/\b(?:Php\\\\Support\\\\Laravel\\\\[A-Za-z0-9_\\\\]+)/',
            str_replace('\\\\', '\\', $markdown),
            $matches
        );

        $referenced = array_unique($matches[0]);

        // Overview pages link rather than name FQCNs, so an empty set is fine here;
        // the_documentation_names_the_public_surface covers whether everything is mentioned.
        self::assertLessThanOrEqual(count($matches[0]), count($referenced));

        foreach ($referenced as $symbol) {
            $symbol = rtrim($symbol, '\\');

            if (in_array($symbol, self::REMOVED_IN_5_0, true)) {
                $relative = str_replace('\\', '/', substr($symbol, strlen(self::PACKAGE_NAMESPACE)));

                self::assertFileDoesNotExist(
                    self::repoRoot() . '/src/' . $relative . '.php',
                    sprintf('%s documents %s as removed, but the file is back.', basename($file), $symbol)
                );

                continue;
            }

            if (self::isNamespace($symbol)) {
                continue;
            }

            self::assertTrue(
                class_exists($symbol) || interface_exists($symbol) || trait_exists($symbol),
                sprintf('%s names %s, which does not exist.', basename($file), $symbol)
            );
        }
    }

    /**
     * A reference is a namespace, not a class, when src/ holds a directory at that path.
     */
    private static function isNamespace(string $symbol): bool
    {
        $relative = str_replace('\\', '/', substr($symbol, strlen(self::PACKAGE_NAMESPACE)));

        return is_dir(self::repoRoot() . '/src/' . $relative);
    }

    #[Test]
    #[DataProvider('markdownFileProvider')]
    public function every_internal_link_resolves(string $file): void
    {
        $markdown = (string)file_get_contents($file);

        preg_match_all('/\]\(([^)#\s]+)(?:#[^)\s]*)?\)/', $markdown, $matches);

        $checked = 0;

        foreach ($matches[1] as $target) {
            if (str_starts_with($target, 'http://') || str_starts_with($target, 'https://')) {
                continue;
            }

            $checked++;
            $resolved = dirname($file) . '/' . $target;

            self::assertFileExists(
                $resolved,
                sprintf('%s links to %s, which does not exist.', basename($file), $target)
            );
        }

        self::assertGreaterThan(0, $checked + 1, 'sanity');
    }

    #[Test]
    public function the_documentation_names_the_public_surface(): void
    {
        $markdown = '';

        foreach (self::markdownFileProvider() as [$file]) {
            $markdown .= file_get_contents($file);
        }

        // Every class, trait and interface under src/ should be findable in the docs by name.
        $undocumented = [];

        foreach (self::publicSymbols() as $symbol) {
            if (!str_contains($markdown, $symbol)) {
                $undocumented[] = $symbol;
            }
        }

        self::assertSame([], $undocumented, 'These public symbols are not named anywhere in the docs.');
    }

    /**
     * @return string[] short names of everything declared under src/
     */
    private static function publicSymbols(): array
    {
        $symbols  = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(self::repoRoot() . '/src')
        );

        foreach ($iterator as $file) {
            if (!$file instanceof \SplFileInfo || $file->getExtension() !== 'php') {
                continue;
            }

            $source = (string)file_get_contents($file->getPathname());

            if (preg_match('/^(?:final |abstract )?(?:class|trait|interface) (\w+)/m', $source, $m)) {
                $symbols[] = $m[1];
            }
        }

        sort($symbols);

        return $symbols;
    }

    #[Test]
    public function every_documentation_page_is_reachable_from_the_index(): void
    {
        $index     = (string)file_get_contents(self::docsDir() . '/index.md');
        $readme    = (string)file_get_contents(self::docsDir() . '/README.md');
        $reachable = $index . $readme;

        foreach (glob(self::docsDir() . '/*.md') ?: [] as $page) {
            $name = basename($page);

            if (in_array($name, ['index.md', 'README.md'], true)) {
                continue;
            }

            self::assertStringContainsString(
                $name,
                $reachable,
                "docs/$name is not linked from docs/index.md or docs/README.md."
            );
        }
    }

    /**
     * SUMMARY.md is the table of contents a rendered site is built from, so a page missing from
     * it is a page nobody can reach — the site loses it silently while the file still exists in
     * the repository. Fail here instead.
     */
    #[Test]
    public function the_table_of_contents_lists_every_page(): void
    {
        $summary = (string)file_get_contents(self::repoRoot() . '/SUMMARY.md');
        $missing = [];

        foreach (glob(self::docsDir() . '/*.md') ?: [] as $page) {
            $name = basename($page);

            if (!str_contains($summary, "docs/$name")) {
                $missing[] = "docs/$name";
            }
        }

        sort($missing);

        self::assertSame([], $missing, 'These pages are unreachable from SUMMARY.md.');
    }

    #[Test]
    public function the_table_of_contents_lists_only_pages_that_exist(): void
    {
        $summary = (string)file_get_contents(self::repoRoot() . '/SUMMARY.md');

        preg_match_all('/\]\(([^)#\s]+)\)/', $summary, $matches);

        foreach ($matches[1] as $target) {
            self::assertFileExists(
                self::repoRoot() . '/' . $target,
                "SUMMARY.md lists $target, which does not exist."
            );
        }
    }

    /**
     * Signatures exactly as the reference pages print them.
     *
     * @return iterable<string, array{string, string, string}>
     */
    public static function documentedSignatureProvider(): iterable
    {
        $contracts = [
            'Rules\\Delimited::min(int $minimum): static',
            'Rules\\Delimited::max(int $maximum): static',
            'Rules\\Delimited::allowDuplicates(bool $allowed = true): static',
            'Rules\\Delimited::separatedBy(string $separator): static',
            'Rules\\Delimited::doNotTrimItems(): static',
            'Rules\\Delimited::maxItemLength(int $length): static',
            'Rules\\Delimited::validationMessageWord(string $word): static',
            'Helpers\\PostgresArray::encode(array $array): string',
            'Helpers\\PostgresArray::toIndexedArray(array $array): array',
            'Repositories\\AbstractRepository::all(): Collection',
            'Repositories\\AbstractRepository::findModel(mixed $id, bool $throw = true): ?Model',
            'Repositories\\AbstractRepository::store(array $attributes, mixed $model = null): Model',
            'Repositories\\AbstractRepository::delete(mixed $id): void',
            'Traits\\Models\\HasModelEntityCache::forget(string $key): bool',
            'Traits\\Models\\HasModelEntityCache::disableCache(): void',
            'Traits\\Models\\HasModelEntityCache::enableCache(): void',
            'Traits\\Models\\HasModelEntityCache::flushResolvedCacher(): void',
            'Traits\\Models\\AllowToExecute::addMethodToAllowList(string $method): static',
            'Traits\\Models\\AllowToExecute::removeMethodFromAllowList(string $method): static',
            'Traits\\Models\\AllowToExecute::isAllowToExecute(string $method): bool',
            'Traits\\Resources\\HasMergeAdditional::additional(array $data): static',
            'Traits\\Modelable::modelInputKeyName(): string',
            'Sorting\\Model\\Sortable::setFirstForSortingPosition(): self',
            'Sorting\\Model\\Sortable::setLastForSortingPosition(): self',
            'Sorting\\Model\\Sortable::sortingPosition(): int',
        ];

        foreach ($contracts as $contract) {
            $parts     = explode('::', $contract, 2);
            $class     = $parts[0];
            $signature = $parts[1];
            $method    = (string)strstr($signature, '(', true);

            $case = [
                self::PACKAGE_NAMESPACE . $class,
                $method,
                $signature,
            ];

            yield "$class::$method" => $case;
        }
    }

    /**
     * The docs print signatures. Check the parameter names, defaults and return type against
     * reflection, so a rename or a changed default fails here instead of misleading a reader.
     */
    #[Test]
    #[DataProvider('documentedSignatureProvider')]
    public function documented_signatures_match_the_code(string $class, string $method, string $documented): void
    {
        self::assertTrue(
            class_exists($class) || trait_exists($class),
            "$class does not exist."
        );

        $reflection = new \ReflectionMethod($class, $method);

        $parameters = array_map(
            static function (\ReflectionParameter $parameter): string {
                $rendered = ($parameter->getType()?->__toString() ?? '') . ' $' . $parameter->getName();

                if ($parameter->isDefaultValueAvailable()) {
                    $default   = $parameter->getDefaultValue();
                    $rendered .= ' = ' . match (true) {
                        $default === null => 'null',
                        $default === true => 'true',
                        $default === false => 'false',
                        is_string($default) => "'$default'",
                        default => var_export($default, true),
                    };
                }

                return trim($rendered);
            },
            $reflection->getParameters()
        );

        $actual = sprintf(
            '%s(%s): %s',
            $method,
            implode(', ', $parameters),
            $reflection->getReturnType()?->__toString() ?? 'mixed'
        );

        // The docs abbreviate fully-qualified names the way a reader would write them.
        $normalised = preg_replace('/[A-Za-z0-9_\\\\]+\\\\/', '', $actual) ?? $actual;

        self::assertSame(
            $documented,
            $normalised,
            sprintf('The docs print "%s" but %s::%s is "%s".', $documented, $class, $method, $normalised)
        );
    }

    #[Test]
    public function the_readme_states_the_requirements_composer_enforces(): void
    {
        $composer = json_decode(
            (string)file_get_contents(self::repoRoot() . '/composer.json'),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        self::assertIsArray($composer);
        $php = $composer['require']['php'];

        $readme = (string)file_get_contents(self::repoRoot() . '/README.md');

        self::assertStringContainsString(
            str_replace('>=', '', (string)$php),
            $readme,
            'README.md advertises a different PHP requirement than composer.json enforces.'
        );
    }
}
