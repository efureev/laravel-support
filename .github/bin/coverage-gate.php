<?php

declare(strict_types=1);

/**
 * Fail the build when line coverage drops below a floor.
 *
 * Usage: php .github/bin/coverage-gate.php clover.xml 90
 */

$file = $argv[1] ?? 'clover.xml';
$floor = (float)($argv[2] ?? 90);

if (!is_file($file)) {
    fwrite(STDERR, "Coverage report not found: $file\n");
    exit(1);
}

$xml = simplexml_load_file($file);

if ($xml === false) {
    fwrite(STDERR, "Coverage report is not valid XML: $file\n");
    exit(1);
}

$metrics = $xml->project->metrics ?? null;

if ($metrics === null) {
    fwrite(STDERR, "Coverage report has no project metrics: $file\n");
    exit(1);
}

$statements = (int)$metrics['statements'];
$covered = (int)$metrics['coveredstatements'];

if ($statements === 0) {
    fwrite(STDERR, "Coverage report counted zero statements — the run produced nothing.\n");
    exit(1);
}

$percent = $covered / $statements * 100;

printf("Line coverage: %.2f%% (%d/%d), floor %.2f%%\n", $percent, $covered, $statements, $floor);

if ($percent + 0.005 < $floor) {
    fwrite(STDERR, sprintf("Coverage %.2f%% is below the %.2f%% floor.\n", $percent, $floor));
    exit(1);
}
