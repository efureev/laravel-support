<?php

declare(strict_types=1);

namespace Php\Support\Laravel\Tests\Release;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Covers the script the release job uses to turn CHANGELOG.md into release notes.
 *
 * The first 5.0.0 release shipped the whole changelog — every version back to 1.x — because the
 * workflow handed the file straight to the release action. These checks keep the extractor
 * honest, and keep a tagged version from ever publishing empty notes.
 */
class ChangelogSectionTest extends TestCase
{
    private static function repoRoot(): string
    {
        return dirname(__DIR__, 2);
    }

    /**
     * @return array{int, string, string} exit code, stdout, stderr
     */
    private static function extract(string $changelog, string $version, string ...$flags): array
    {
        $command = sprintf(
            'php %s %s %s %s',
            escapeshellarg(self::repoRoot() . '/.github/bin/changelog-section.php'),
            escapeshellarg($changelog),
            escapeshellarg($version),
            implode(' ', array_map(escapeshellarg(...), $flags))
        );

        $descriptors = [
            1 => [
                'pipe',
                'w',
            ],
            2 => [
                'pipe',
                'w',
            ],
        ];
        $process     = proc_open($command, $descriptors, $pipes);

        self::assertIsResource($process);

        $stdout = (string)stream_get_contents($pipes[1]);
        $stderr = (string)stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        return [
            proc_close($process),
            $stdout,
            $stderr,
        ];
    }

    private static function changelog(): string
    {
        return self::repoRoot() . '/CHANGELOG.md';
    }

    #[Test]
    public function it_extracts_only_the_requested_version(): void
    {
        [
            $status,
            $notes,
        ] = self::extract(self::changelog(), 'v5.0.0');

        self::assertSame(0, $status);
        self::assertStringContainsString('Require PHP >= 8.5', $notes);
        self::assertStringNotContainsString('## [4.0.0]', $notes);
        self::assertStringNotContainsString('## [1.', $notes);
    }

    #[Test]
    public function the_notes_are_a_fraction_of_the_whole_file(): void
    {
        [,
            $notes,
        ] = self::extract(self::changelog(), 'v5.0.0');

        $whole   = substr_count((string)file_get_contents(self::changelog()), "\n");
        $section = substr_count($notes, "\n");

        self::assertLessThan(
            $whole / 2,
            $section,
            'The extractor is returning most of the file — it is not extracting a section.'
        );
    }

    #[Test]
    public function the_file_header_is_not_part_of_the_notes(): void
    {
        [,
            $notes,
        ] = self::extract(self::changelog(), 'v5.0.0');

        self::assertStringNotContainsString('BEGIN HEADER', $notes);
        self::assertStringNotContainsString('All notable changes to this project', $notes);
    }

    #[Test]
    public function the_trailing_separator_is_trimmed(): void
    {
        [,
            $notes,
        ] = self::extract(self::changelog(), 'v5.0.0');

        self::assertStringEndsNotWith("---\n", $notes);
        self::assertSame(trim($notes), trim($notes));
    }

    #[Test]
    public function the_leading_v_is_optional(): void
    {
        [
            $withV,
            $a,
        ] = self::extract(self::changelog(), 'v5.0.0');
        [
            $without,
            $b,
        ] = self::extract(self::changelog(), '5.0.0');

        self::assertSame(0, $withV);
        self::assertSame(0, $without);
        self::assertSame($a, $b);
    }

    /**
     * Versions whose section actually carries content.
     *
     * Most of this changelog's history is heading-only: conventional-changelog emitted a heading
     * for every tag, including the 68 whose commits were all of ignored types. Those are a fact
     * of the file, not something to assert notes for — {@see self::an_empty_section_fails()}
     * covers what happens when one is tagged.
     *
     * @return iterable<string, array{string}>
     */
    public static function documentedVersionProvider(): iterable
    {
        foreach (self::sections() as $version => $body) {
            if (trim(str_replace('---', '', $body)) !== '') {
                yield $version => [$version];
            }
        }
    }

    /**
     * @return array<string, string> version => section body
     */
    private static function sections(): array
    {
        $changelog = (string)file_get_contents(self::repoRoot() . '/CHANGELOG.md');
        $blocks    = preg_split('/^##\s+\[/m', $changelog) ?: [];
        $sections  = [];

        foreach (array_slice($blocks, 1) as $block) {
            $version = strstr($block, ']', true);

            if (!is_string($version) || preg_match('/^\d+\.\d+\.\d+$/', $version) !== 1) {
                continue;
            }

            $sections[$version] = (string)strstr($block, "\n");
        }

        return $sections;
    }

    /**
     * Every version whose changelog section has content must extract that content.
     */
    #[Test]
    #[DataProvider('documentedVersionProvider')]
    public function a_version_with_content_yields_notes(string $version): void
    {
        [
            $status,
            $notes,
        ] = self::extract(self::changelog(), $version);

        self::assertSame(0, $status, "Extraction failed for $version.");
        self::assertNotSame('', trim($notes), "Notes for $version are empty.");
    }

    #[Test]
    public function an_empty_section_fails_rather_than_publishing_blank_notes(): void
    {
        // 1.18.1 is heading-only in this changelog. Tagging such a version must stop the release
        // job, not publish an empty body.
        $sections = self::sections();

        self::assertArrayHasKey('1.18.1', $sections);
        self::assertSame('', trim(str_replace('---', '', $sections['1.18.1'])));

        [
            $status,
            $notes,
            $stderr,
        ] = self::extract(self::changelog(), '1.18.1');

        self::assertSame(1, $status);
        self::assertSame('', trim($notes));
        self::assertStringContainsString('is empty', $stderr);
    }

    /**
     * GitHub renders a single newline as <br> in release notes, though not in a rendered file.
     * CHANGELOG.md is hard-wrapped for readability, so without unwrapping the notes break
     * sentences mid-way — which is exactly how the first v5.0.0 notes looked.
     */
    #[Test]
    public function paragraphs_and_list_items_are_unwrapped(): void
    {
        [,
            $unwrapped,
        ] = self::extract(self::changelog(), 'v5.0.0');
        [,
            $verbatim,
        ] = self::extract(self::changelog(), 'v5.0.0', '--raw');

        self::assertNotSame($verbatim, $unwrapped, 'Nothing was unwrapped.');
        self::assertLessThan(
            substr_count($verbatim, "\n"),
            substr_count($unwrapped, "\n"),
            'Unwrapping must reduce the line count.'
        );

        // A bullet that is hard-wrapped in the file has to arrive as one line.
        $bullet = 'Drop the `efureev/support` dependency.';
        $line   = '';

        foreach (explode("\n", $unwrapped) as $candidate) {
            if (str_contains($candidate, $bullet)) {
                $line = $candidate;

                break;
            }
        }

        self::assertNotSame('', $line, "Could not find the bullet starting: $bullet");
        self::assertStringContainsString('catches on the old FQCNs are not.', $line);
    }

    #[Test]
    public function unwrapping_keeps_every_list_item_separate(): void
    {
        [,
            $unwrapped,
        ] = self::extract(self::changelog(), 'v5.0.0');
        [,
            $verbatim,
        ] = self::extract(self::changelog(), 'v5.0.0', '--raw');

        $count = static fn(string $text): int => preg_match_all('/^\* /m', $text) ?: 0;

        self::assertSame(
            $count($verbatim),
            $count($unwrapped),
            'Unwrapping must not merge or drop list items.'
        );
    }

    #[Test]
    public function unwrapping_keeps_headings_and_blank_lines(): void
    {
        [,
            $unwrapped,
        ] = self::extract(self::changelog(), 'v5.0.0');

        self::assertStringContainsString('### ⚠ BREAKING CHANGES', $unwrapped);
        self::assertStringContainsString('### Bug Fixes', $unwrapped);
        self::assertStringContainsString("\n\n", $unwrapped, 'Paragraph breaks must survive.');
    }

    #[Test]
    public function an_unknown_version_fails_instead_of_printing_nothing(): void
    {
        [
            $status,
            $notes,
            $stderr,
        ] = self::extract(self::changelog(), 'v9.9.9');

        self::assertSame(1, $status);
        self::assertSame('', trim($notes));
        self::assertStringContainsString('No section for version', $stderr);
    }

    #[Test]
    public function a_missing_changelog_fails(): void
    {
        [$status] = self::extract('/no/such/CHANGELOG.md', 'v5.0.0');

        self::assertSame(1, $status);
    }

    #[Test]
    public function the_current_tag_has_a_section(): void
    {
        // The release job runs this for github.ref_name; if the newest changelog entry and the
        // tag ever drift apart, the release would publish nothing.
        preg_match(
            '/^##\s+\[(\d+\.\d+\.\d+)\]/m',
            (string)file_get_contents(self::changelog()),
            $matches
        );

        self::assertNotEmpty($matches, 'The changelog has no version sections at all.');

        [$status] = self::extract(self::changelog(), 'v' . $matches[1]);

        self::assertSame(0, $status);
    }
}
