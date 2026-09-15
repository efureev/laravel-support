<?php

declare(strict_types=1);

/**
 * Print one version's section of a Keep a Changelog file.
 *
 * Release notes should describe the release, not repeat every version ever shipped. Passing
 * CHANGELOG.md straight to the release action put all 40-odd versions into the v5.0.0 notes.
 *
 * Usage: php .github/bin/changelog-section.php CHANGELOG.md v5.0.0 [--raw]
 *
 * Paragraphs and list items are unwrapped into single lines. CHANGELOG.md is hard-wrapped for
 * readability as a file, and GitHub renders a file's single newlines as spaces — but in *release
 * notes* it renders them as <br>, so the wrapping leaks through and breaks sentences mid-way.
 * Pass --raw to keep the source line breaks.
 *
 * Exits non-zero when the version has no section, so a release never publishes empty notes.
 */

$arguments = array_values(array_filter(
    array_slice($argv, 1),
    static fn(string $argument): bool => $argument !== '--raw'
));

$raw = in_array('--raw', $argv, true);
$file = $arguments[0] ?? 'CHANGELOG.md';
$version = ltrim($arguments[1] ?? '', 'v');

if ($version === '') {
    fwrite(STDERR, "Usage: changelog-section.php <changelog> <version>\n");
    exit(2);
}

if (!is_file($file)) {
    fwrite(STDERR, "Changelog not found: $file\n");
    exit(1);
}

$lines = file($file, FILE_IGNORE_NEW_LINES);

if ($lines === false) {
    fwrite(STDERR, "Cannot read: $file\n");
    exit(1);
}

$heading = '/^##\s+\[?' . preg_quote($version, '/') . '\]?/';
$anyHeading = '/^##\s+\[?\d+\.\d+\.\d+/';

$section = [];
$inside = false;

foreach ($lines as $line) {
    if (preg_match($heading, $line) === 1) {
        $inside = true;

        continue;
    }

    if ($inside && preg_match($anyHeading, $line) === 1) {
        break;
    }

    if ($inside) {
        $section[] = $line;
    }
}

if (!$inside) {
    fwrite(STDERR, "No section for version $version in $file\n");
    exit(1);
}

// Drop the leading blank lines and the trailing `---` separator with its surrounding blanks.
while ($section !== [] && trim($section[0]) === '') {
    array_shift($section);
}

while ($section !== [] && (trim((string)end($section)) === '' || trim((string)end($section)) === '---')) {
    array_pop($section);
}

if ($section === []) {
    fwrite(STDERR, "Section for version $version in $file is empty\n");
    exit(1);
}

echo implode("\n", $raw ? $section : unwrap($section)), "\n";

/**
 * Join the continuation lines of each paragraph and list item back onto one line.
 *
 * A line starts a new block when it is blank, a heading, a list item, a blockquote, a table row,
 * a rule, or a fence; anything else continues the block above it. Fenced code is copied through
 * untouched.
 *
 * @param string[] $lines
 *
 * @return string[]
 */
function unwrap(array $lines): array
{
    $result = [];
    $buffer = '';
    $inFence = false;

    $flush = static function () use (&$result, &$buffer): void {
        if ($buffer !== '') {
            $result[] = $buffer;
            $buffer = '';
        }
    };

    foreach ($lines as $line) {
        if (preg_match('/^\s*```/', $line) === 1) {
            $flush();
            $result[] = $line;
            $inFence = !$inFence;

            continue;
        }

        if ($inFence) {
            $result[] = $line;

            continue;
        }

        if (trim($line) === '') {
            $flush();
            $result[] = '';

            continue;
        }

        $startsBlock = preg_match('/^\s*(#{1,6}\s|[*+-]\s|\d+[.)]\s|>|\||-{3,}$|={3,}$)/', $line) === 1;

        if ($startsBlock) {
            $flush();
            $buffer = rtrim($line);

            continue;
        }

        $buffer = $buffer === '' ? rtrim($line) : $buffer . ' ' . trim($line);
    }

    $flush();

    return $result;
}
