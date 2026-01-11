<?php

declare(strict_types = 1);

use Pekral\BuildPackage\PostCreateProject;

describe(PostCreateProject::class, function (): void {
    describe('toStudlyCase', function (): void {
        it('converts kebab-case to StudlyCase', function (string $input, string $expected): void {
            $project = new PostCreateProject();

            expect($project->toStudlyCase($input))->toBe($expected);
        })->with([
            ['my-vendor', 'MyVendor'],
            ['my-package', 'MyPackage'],
            ['awesome-lib', 'AwesomeLib'],
            ['simple', 'Simple'],
        ]);

        it('converts snake_case to StudlyCase', function (string $input, string $expected): void {
            $project = new PostCreateProject();

            expect($project->toStudlyCase($input))->toBe($expected);
        })->with([
            ['my_vendor', 'MyVendor'],
            ['my_package', 'MyPackage'],
            ['awesome_lib', 'AwesomeLib'],
        ]);

        it('handles mixed separators', function (): void {
            $project = new PostCreateProject();

            expect($project->toStudlyCase('my-awesome_package'))->toBe('MyAwesomePackage');
        });
    });

    describe('toDisplayName', function (): void {
        it('converts kebab-case to display name', function (string $input, string $expected): void {
            $project = new PostCreateProject();

            expect($project->toDisplayName($input))->toBe($expected);
        })->with([
            ['my-package', 'My Package'],
            ['awesome-lib', 'Awesome Lib'],
            ['simple', 'Simple'],
        ]);

        it('converts snake_case to display name', function (string $input, string $expected): void {
            $project = new PostCreateProject();

            expect($project->toDisplayName($input))->toBe($expected);
        })->with([
            ['my_package', 'My Package'],
            ['awesome_lib', 'Awesome Lib'],
        ]);
    });

    describe('isBinaryFile', function (): void {
        it('returns true for binary file extensions', function (string $extension): void {
            $project = new PostCreateProject();

            expect($project->isBinaryFile('/path/to/file.' . $extension))->toBeTrue();
        })->with([
            'png',
            'jpg',
            'jpeg',
            'gif',
            'ico',
            'webp',
            'svg',
            'woff',
            'woff2',
            'ttf',
            'eot',
            'otf',
            'zip',
            'tar',
            'gz',
            'rar',
            'pdf',
            'doc',
            'docx',
            'exe',
            'dll',
            'so',
            'dylib',
            'phar',
        ]);

        it('returns false for text file extensions', function (string $extension): void {
            $project = new PostCreateProject();

            expect($project->isBinaryFile('/path/to/file.' . $extension))->toBeFalse();
        })->with([
            'php',
            'js',
            'ts',
            'json',
            'xml',
            'md',
            'txt',
            'yml',
            'yaml',
            'neon',
            'html',
            'css',
        ]);

        it('handles uppercase extensions', function (): void {
            $project = new PostCreateProject();

            expect($project->isBinaryFile('/path/to/file.PNG'))->toBeTrue()
                ->and($project->isBinaryFile('/path/to/file.PHP'))->toBeFalse();
        });
    });

    describe('isExcludedPath', function (): void {
        it('returns true for excluded directories', function (string $path): void {
            $project = new PostCreateProject();

            expect($project->isExcludedPath($path))->toBeTrue();
        })->with([
            '/project/vendor/autoload.php',
            '/project/.git/config',
            '/project/node_modules/package/index.js',
            '/project/.idea/workspace.xml',
            '/project/.vscode/settings.json',
            '/project/build-package/PostCreateProject.php',
        ]);

        it('returns false for non-excluded directories', function (string $path): void {
            $project = new PostCreateProject();

            expect($project->isExcludedPath($path))->toBeFalse();
        })->with([
            '/project/src/Example.php',
            '/project/tests/Unit/ExampleTest.php',
            '/project/composer.json',
            '/project/README.md',
        ]);
    });

    describe('configure', function (): void {
        it('sets configuration values correctly', function (): void {
            $project = new PostCreateProject();
            $project->configure(
                'acme/awesome-lib',
                'Acme\\AwesomeLib',
                'Acme\\Test',
                'Awesome Lib',
                'https://github.com/acme/awesome-lib',
            );

            $replacements = $project->buildReplacements();

            expect($replacements)->toHaveKey('pekral/php-skeleton')
                ->and($replacements['pekral/php-skeleton'])->toBe('acme/awesome-lib')
                ->and($replacements)->toHaveKey('Pekral\\Example')
                ->and($replacements['Pekral\\Example'])->toBe('Acme\\AwesomeLib')
                ->and($replacements)->toHaveKey('Pekral\\Test')
                ->and($replacements['Pekral\\Test'])->toBe('Acme\\Test')
                ->and($replacements)->toHaveKey('https://github.com/pekral/php-skeleton')
                ->and($replacements['https://github.com/pekral/php-skeleton'])->toBe('https://github.com/acme/awesome-lib')
                ->and($replacements)->toHaveKey('PHP Skeleton')
                ->and($replacements['PHP Skeleton'])->toBe('Awesome Lib');
        });

        it('throws exception for invalid package name', function (): void {
            $project = new PostCreateProject();

            expect(fn () => $project->configure(
                'invalid-package-name',
                'Invalid\\Package',
                'Invalid\\Test',
                'Invalid Package',
                'https://github.com/invalid',
            ))->toThrow(InvalidArgumentException::class, 'Invalid package name format');
        });
    });

    describe('buildReplacements', function (): void {
        it('includes escaped namespaces for JSON files', function (): void {
            $project = new PostCreateProject();
            $project->configure(
                'acme/lib',
                'Acme\\Lib',
                'Acme\\Test',
                'Lib',
                'https://github.com/acme/lib',
            );

            $replacements = $project->buildReplacements();

            expect($replacements)->toHaveKey('Pekral\\\\Example')
                ->and($replacements['Pekral\\\\Example'])->toBe('Acme\\\\Lib')
                ->and($replacements)->toHaveKey('Pekral\\\\Test')
                ->and($replacements['Pekral\\\\Test'])->toBe('Acme\\\\Test');
        });

        it('replaces author information with placeholders', function (): void {
            $project = new PostCreateProject();
            $project->configure(
                'vendor/package',
                'Vendor\\Package',
                'Vendor\\Test',
                'Package',
                'https://github.com/vendor/package',
            );

            $replacements = $project->buildReplacements();

            expect($replacements)->toHaveKey('Petr Král')
                ->and($replacements['Petr Král'])->toBe('Your Name')
                ->and($replacements)->toHaveKey('kral.petr.88@gmail.com')
                ->and($replacements['kral.petr.88@gmail.com'])->toBe('your.email@example.com');
        });
    });

    describe('processFileContent', function (): void {
        it('replaces all occurrences in content', function (): void {
            $project = new PostCreateProject();
            $project->configure(
                'acme/awesome-lib',
                'Acme\\AwesomeLib',
                'Acme\\Test',
                'Awesome Lib',
                'https://github.com/acme/awesome-lib',
            );

            $content = <<<'PHP'
                <?php

                namespace Pekral\Example;

                // Package: pekral/php-skeleton
                // URL: https://github.com/pekral/php-skeleton

                final class Example {}
                PHP;

            $result = $project->processFileContent($content, $project->buildReplacements());

            expect($result)->toContain('namespace Acme\\AwesomeLib;')
                ->and($result)->toContain('Package: acme/awesome-lib')
                ->and($result)->toContain('https://github.com/acme/awesome-lib')
                ->and($result)->not->toContain('Pekral\\Example')
                ->and($result)->not->toContain('pekral/php-skeleton');
        });

        it('handles JSON escaped namespaces', function (): void {
            $project = new PostCreateProject();
            $project->configure(
                'acme/lib',
                'Acme\\Lib',
                'Acme\\Test',
                'Lib',
                'https://github.com/acme/lib',
            );

            $content = '{"autoload": {"psr-4": {"Pekral\\\\Example\\\\": "src/"}}}';

            $result = $project->processFileContent($content, $project->buildReplacements());

            expect($result)->toBe('{"autoload": {"psr-4": {"Acme\\\\Lib\\\\": "src/"}}}');
        });
    });

    describe('getters', function (): void {
        it('returns files to process', function (): void {
            $project = new PostCreateProject();

            expect($project->getFilesToProcess())->toContain('composer.json')
                ->and($project->getFilesToProcess())->toContain('README.md')
                ->and($project->getFilesToProcess())->toContain('phpstan.neon');
        });

        it('returns directories to process', function (): void {
            $project = new PostCreateProject();

            expect($project->getDirectoriesToProcess())->toBe(['src', 'tests']);
        });

        it('returns example files to delete', function (): void {
            $project = new PostCreateProject();

            expect($project->getExampleFilesToDelete())->toBe([
                'src/Example.php',
                'tests/Unit/ExampleTest.php',
                'tests/Unit/PostCreateProjectTest.php',
            ]);
        });

        it('returns binary extensions', function (): void {
            $project = new PostCreateProject();

            expect($project->getBinaryExtensions())->toContain('png')
                ->and($project->getBinaryExtensions())->toContain('phar');
        });

        it('returns excluded directories', function (): void {
            $project = new PostCreateProject();

            expect($project->getExcludedDirectories())->toContain('vendor')
                ->and($project->getExcludedDirectories())->toContain('.git')
                ->and($project->getExcludedDirectories())->toContain('node_modules');
        });

        it('returns project path', function (): void {
            $project = new PostCreateProject('/custom/path');

            expect($project->getProjectPath())->toBe('/custom/path');
        });
    });

    describe('integration', function (): void {
        it('performs full replacement workflow on temp directory', function (): void {
            $tempDir = sys_get_temp_dir() . '/php-skeleton-test-' . uniqid();
            mkdir($tempDir);
            mkdir($tempDir . '/src');
            mkdir($tempDir . '/tests');
            mkdir($tempDir . '/tests/Unit');
            mkdir($tempDir . '/build-package');

            file_put_contents($tempDir . '/composer.json', json_encode([
                'name' => 'pekral/php-skeleton',
                'autoload' => [
                    'psr-4' => [
                        'Pekral\\Example\\' => 'src/',
                    ],
                ],
                'autoload-dev' => [
                    'psr-4' => [
                        'Pekral\\BuildPackage\\' => 'build-package/',
                        'Pekral\\Test\\' => 'tests/',
                    ],
                ],
                'scripts' => [
                    'post-create-project-cmd' => ['@php post-create-project.php'],
                    'test' => ['vendor/bin/pest'],
                ],
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            file_put_contents($tempDir . '/src/Example.php', <<<'PHP'
                <?php

                namespace Pekral\Example;

                final class Example {}
                PHP);

            file_put_contents($tempDir . '/tests/Unit/ExampleTest.php', <<<'PHP'
                <?php

                use Pekral\Example\Example;

                it('works', fn () => expect(true)->toBeTrue());
                PHP);

            file_put_contents($tempDir . '/tests/Unit/PostCreateProjectTest.php', <<<'PHP'
                <?php

                use Pekral\BuildPackage\PostCreateProject;

                it('tests something', fn () => expect(true)->toBeTrue());
                PHP);

            file_put_contents($tempDir . '/build-package/PostCreateProject.php', <<<'PHP'
                <?php

                namespace Pekral\BuildPackage;

                final class PostCreateProject {}
                PHP);

            $outputStream = fopen('php://memory', 'rw');
            $project = new PostCreateProject($tempDir, null, $outputStream !== false ? $outputStream : null);
            $project->configure(
                'acme/awesome-lib',
                'Acme\\AwesomeLib',
                'Acme\\Test',
                'Awesome Lib',
                'https://github.com/acme/awesome-lib',
            );
            $project->runWithoutInteraction();

            $composerContent = file_get_contents($tempDir . '/composer.json');

            /** @var array{
             * name: string,
             * autoload: array<string, mixed>,
             * autoload-dev: array{psr-4: array<string, string>},
             * scripts: array<string, mixed>
             * } $composerData */
            $composerData = json_decode($composerContent !== false ? $composerContent : '', true);

            expect($composerContent)->toContain('acme/awesome-lib')
                ->and($composerContent)->toContain('Acme\\\\AwesomeLib\\\\')
                ->and(file_exists($tempDir . '/src/Example.php'))->toBeFalse()
                ->and(file_exists($tempDir . '/tests/Unit/ExampleTest.php'))->toBeFalse()
                ->and(file_exists($tempDir . '/tests/Unit/PostCreateProjectTest.php'))->toBeFalse()
                ->and(file_exists($tempDir . '/src/.gitkeep'))->toBeTrue()
                ->and(file_exists($tempDir . '/tests/Unit/.gitkeep'))->toBeTrue()
                ->and(is_dir($tempDir . '/build-package'))->toBeFalse()
                ->and($composerData['scripts'])->not->toHaveKey('post-create-project-cmd')
                ->and($composerData['scripts'])->toHaveKey('test')
                ->and($composerData['autoload-dev']['psr-4'])->not->toHaveKey('Acme\\BuildPackage\\')
                ->and($composerData['autoload-dev']['psr-4'])->toHaveKey('Acme\\Test\\');

            if ($outputStream !== false) {
                fclose($outputStream);
            }

            array_map('unlink', glob($tempDir . '/src/*') ?: []);
            array_map('unlink', glob($tempDir . '/src/.*') ?: []);
            array_map('unlink', glob($tempDir . '/tests/Unit/*') ?: []);
            array_map('unlink', glob($tempDir . '/tests/Unit/.*') ?: []);
            rmdir($tempDir . '/tests/Unit');
            rmdir($tempDir . '/tests');
            rmdir($tempDir . '/src');
            unlink($tempDir . '/composer.json');
            rmdir($tempDir);
        });
    });
});
