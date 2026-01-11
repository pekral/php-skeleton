<?php

declare(strict_types = 1);

use Pekral\BuildPackage\ConsoleTheme;
use Symfony\Component\Console\Output\BufferedOutput;

use function Termwind\renderUsing;

describe(ConsoleTheme::class, function (): void {
    describe('apply', function (): void {
        it('applies color styles to output formatter', function (): void {
            $output = new BufferedOutput();

            ConsoleTheme::apply($output);

            $formatter = $output->getFormatter();

            expect($formatter->hasStyle('accent'))->toBeTrue()
                ->and($formatter->hasStyle('success'))->toBeTrue()
                ->and($formatter->hasStyle('warning'))->toBeTrue()
                ->and($formatter->hasStyle('muted'))->toBeTrue()
                ->and($formatter->hasStyle('bold'))->toBeTrue();
        });
    });

    describe('banner', function (): void {
        it('renders banner without subtitle', function (): void {
            $buffer = new BufferedOutput();
            renderUsing($buffer);

            ConsoleTheme::banner('Test Title');

            renderUsing(null);

            expect($buffer->fetch())->toContain('Test Title');
        });

        it('renders banner with subtitle', function (): void {
            $buffer = new BufferedOutput();
            renderUsing($buffer);

            ConsoleTheme::banner('Test Title', 'Test Subtitle');
            $result = $buffer->fetch();

            renderUsing(null);

            expect($result)->toContain('Test Title')
                ->and($result)->toContain('Test Subtitle');
        });
    });

    describe('step', function (): void {
        it('renders step with icon and message', function (): void {
            $buffer = new BufferedOutput();
            renderUsing($buffer);

            ConsoleTheme::step('⚡', 'Processing...');
            $result = $buffer->fetch();

            renderUsing(null);

            expect($result)->toContain('⚡')
                ->and($result)->toContain('Processing...');
        });
    });

    describe('success', function (): void {
        it('renders success message with checkmark', function (): void {
            $buffer = new BufferedOutput();
            renderUsing($buffer);

            ConsoleTheme::success('Operation completed');
            $result = $buffer->fetch();

            renderUsing(null);

            expect($result)->toContain('✓')
                ->and($result)->toContain('Operation completed');
        });
    });

    describe('warning', function (): void {
        it('renders warning message with warning icon', function (): void {
            $buffer = new BufferedOutput();
            renderUsing($buffer);

            ConsoleTheme::warning('Something went wrong');
            $result = $buffer->fetch();

            renderUsing(null);

            expect($result)->toContain('⚠')
                ->and($result)->toContain('Something went wrong');
        });
    });

    describe('info', function (): void {
        it('renders info with label and value', function (): void {
            $buffer = new BufferedOutput();
            renderUsing($buffer);

            ConsoleTheme::info('Status', 'Active');
            $result = $buffer->fetch();

            renderUsing(null);

            expect($result)->toContain('Status')
                ->and($result)->toContain('Active');
        });
    });

    describe('divider', function (): void {
        it('renders horizontal divider', function (): void {
            $buffer = new BufferedOutput();
            renderUsing($buffer);

            ConsoleTheme::divider();
            $result = $buffer->fetch();

            renderUsing(null);

            expect($result)->toContain('────');
        });
    });

    describe('successBox', function (): void {
        it('renders success box without message', function (): void {
            $buffer = new BufferedOutput();
            renderUsing($buffer);

            ConsoleTheme::successBox('Success Title');
            $result = $buffer->fetch();

            renderUsing(null);

            expect($result)->toContain('✓')
                ->and($result)->toContain('Success Title');
        });

        it('renders success box with message', function (): void {
            $buffer = new BufferedOutput();
            renderUsing($buffer);

            ConsoleTheme::successBox('Success Title', 'Additional info');
            $result = $buffer->fetch();

            renderUsing(null);

            expect($result)->toContain('Success Title')
                ->and($result)->toContain('Additional info');
        });
    });

    describe('warningBox', function (): void {
        it('renders warning box without message', function (): void {
            $buffer = new BufferedOutput();
            renderUsing($buffer);

            ConsoleTheme::warningBox('Warning Title');
            $result = $buffer->fetch();

            renderUsing(null);

            expect($result)->toContain('⚠')
                ->and($result)->toContain('Warning Title');
        });

        it('renders warning box with message', function (): void {
            $buffer = new BufferedOutput();
            renderUsing($buffer);

            ConsoleTheme::warningBox('Warning Title', 'Warning details');
            $result = $buffer->fetch();

            renderUsing(null);

            expect($result)->toContain('Warning Title')
                ->and($result)->toContain('Warning details');
        });
    });

    describe('errorBox', function (): void {
        it('renders error box without message', function (): void {
            $buffer = new BufferedOutput();
            renderUsing($buffer);

            ConsoleTheme::errorBox('Error Title');
            $result = $buffer->fetch();

            renderUsing(null);

            expect($result)->toContain('✗')
                ->and($result)->toContain('Error Title');
        });

        it('renders error box with message', function (): void {
            $buffer = new BufferedOutput();
            renderUsing($buffer);

            ConsoleTheme::errorBox('Error Title', 'Error details');
            $result = $buffer->fetch();

            renderUsing(null);

            expect($result)->toContain('Error Title')
                ->and($result)->toContain('Error details');
        });
    });

    describe('summary', function (): void {
        it('renders summary with title and items', function (): void {
            $buffer = new BufferedOutput();
            renderUsing($buffer);

            ConsoleTheme::summary('Configuration', [
                'Name' => 'MyProject',
                'Version' => '1.0.0',
            ]);
            $result = $buffer->fetch();

            renderUsing(null);

            expect($result)->toContain('Configuration')
                ->and($result)->toContain('Name')
                ->and($result)->toContain('MyProject')
                ->and($result)->toContain('Version')
                ->and($result)->toContain('1.0.0');
        });

        it('handles special characters in values', function (): void {
            $buffer = new BufferedOutput();
            renderUsing($buffer);

            ConsoleTheme::summary('Test', [
                'HTML' => '<script>alert("xss")</script>',
            ]);
            $result = $buffer->fetch();

            renderUsing(null);

            expect($result)->toContain('Test')
                ->and($result)->toContain('HTML');
        });
    });

    describe('list', function (): void {
        it('renders numbered list', function (): void {
            $buffer = new BufferedOutput();
            renderUsing($buffer);

            ConsoleTheme::list('Steps', ['First step', 'Second step', 'Third step']);
            $result = $buffer->fetch();

            renderUsing(null);

            expect($result)->toContain('Steps')
                ->and($result)->toContain('1.')
                ->and($result)->toContain('First step')
                ->and($result)->toContain('2.')
                ->and($result)->toContain('Second step')
                ->and($result)->toContain('3.')
                ->and($result)->toContain('Third step');
        });
    });

    describe('command', function (): void {
        it('renders command with dollar sign prompt', function (): void {
            $buffer = new BufferedOutput();
            renderUsing($buffer);

            ConsoleTheme::command('cd /path/to/project && composer install');
            $result = $buffer->fetch();

            renderUsing(null);

            expect($result)->toContain('cd /path/to/project')
                ->and($result)->toContain('$');
        });

        it('handles special characters in command', function (): void {
            $buffer = new BufferedOutput();
            renderUsing($buffer);

            ConsoleTheme::command('echo "<test>"');
            $result = $buffer->fetch();

            renderUsing(null);

            expect($result)->toContain('echo');
        });
    });

    describe('section', function (): void {
        it('renders section with icon and title', function (): void {
            $buffer = new BufferedOutput();
            renderUsing($buffer);

            ConsoleTheme::section('⚡', 'Processing');
            $result = $buffer->fetch();

            renderUsing(null);

            expect($result)->toContain('⚡')
                ->and($result)->toContain('Processing');
        });
    });

    describe('thinDivider', function (): void {
        it('renders thin divider line', function (): void {
            $buffer = new BufferedOutput();
            renderUsing($buffer);

            ConsoleTheme::thinDivider();
            $result = $buffer->fetch();

            renderUsing(null);

            expect($result)->toContain('╌');
        });
    });

    describe('newLine', function (): void {
        it('renders empty line', function (): void {
            $buffer = new BufferedOutput();
            renderUsing($buffer);

            ConsoleTheme::newLine();
            $result = $buffer->fetch();

            renderUsing(null);

            expect($result)->not->toBeEmpty();
        });
    });

    describe('color constants', function (): void {
        it('has correct color values', function (): void {
            expect(ConsoleTheme::ACCENT)->toBe('orange-500')
                ->and(ConsoleTheme::SUCCESS)->toBe('green-500')
                ->and(ConsoleTheme::WARNING)->toBe('yellow-500')
                ->and(ConsoleTheme::MUTED)->toBe('gray-500')
                ->and(ConsoleTheme::INFO)->toBe('blue-500')
                ->and(ConsoleTheme::ERROR)->toBe('red-500');
        });
    });
});
