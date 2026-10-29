<?php

use App\Models\Investigator;
use Tests\TestCase;

uses(TestCase::class);

test('submitting and corresponding can be combined', function () {
    expect(Investigator::labelsFromFlags(true, true, false))->toBe(['Submitting', 'Corresponding']);
    expect(Investigator::roleSelectionError(true, true, false))->toBeNull();
});

test('contributing is an explicit role and must be selected alone', function () {
    expect(Investigator::labelsFromFlags(false, false, true))->toBe(['Contributing']);
    expect(Investigator::roleSelectionError(false, false, true))->toBeNull();
    expect(Investigator::roleSelectionError(true, false, true))->toBe('Contributing must be selected on its own.');
    expect(Investigator::roleSelectionError(false, true, true))->toBe('Contributing must be selected on its own.');
});

test('a role must be selected', function () {
    expect(Investigator::labelsFromFlags(false, false, false))->toBe([]);
    expect(Investigator::roleSelectionError(false, false, false))->toBe('Select at least one investigator type.');
});
