<?php

declare(strict_types=1);

test('basic assertion passes', function (): void {
    expect(true)->toBeTrue();
})->group('unit');

test('plugin namespace is correct', function (): void {
    expect(PestPluginWordPress\TestCase::class)->toBeString();
})->group('unit');

test('pest is working', function (): void {
    expect(something())->toBeTrue();
})->group('unit');

