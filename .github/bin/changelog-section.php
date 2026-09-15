<?php

declare(strict_types=1);

/**
 * Print one version's section of a Keep a Changelog file.
 *
 * Release notes should describe the release, not repeat every version ever shipped. Passing
 * CHANGELOG.md straight to the release action put all 40-odd versions into the v5.0.0 notes.
 *
 * Usage: php .github/bin/changelog-section.php CHANGELOG.md v5.0.0
 *
 * Exits non-zero when the version has no section, so a release never publishes empty notes.
 */

$file = $argv[1] ?? 'CHANGELOG.md';
$version = ltrim($argv[2] ?? '', 'v');

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

echo implode("\n", $section), "\n";
