<?php

it('does not bootstrap with cached configuration so PHPUnit env applies', function (): void {
    expect(app()->configurationIsCached())->toBeFalse();

    $broadcastDriver = config('broadcasting.default');
    expect(in_array($broadcastDriver, [null, 'null'], true))->toBeTrue();
});
