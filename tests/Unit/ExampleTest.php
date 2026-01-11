<?php

declare(strict_types = 1);

use Pekral\Example\Example;

describe(Example::class, function (): void {
    it('returns true from foo method', function (): void {
        $example = new Example();

        expect($example->foo())->toBeTrue();
    });
});
