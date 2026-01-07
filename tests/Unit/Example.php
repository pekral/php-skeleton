<?php

declare(strict_types = 1);

use Pekral\Example\Example;

it('foo', function (): void {
    $example = new Example();

    expect($example->foo())->toBeTrue();
});
