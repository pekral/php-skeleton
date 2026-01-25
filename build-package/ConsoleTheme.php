<?php

declare(strict_types = 1);

namespace Pekral\BuildPackage;

use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\OutputInterface;

use function Termwind\render;

final class ConsoleTheme
{

    public const string ACCENT = 'orange-500';

    public const string SUCCESS = 'green-500';

    public const string WARNING = 'yellow-500';

    public const string MUTED = 'gray-500';

    public const string INFO = 'blue-500';

    public const string ERROR = 'red-500';

    private const string ACCENT_HEX = '#E07B39';

    private const string SUCCESS_HEX = '#22C55E';

    private const string WARNING_HEX = '#EAB308';

    private const string MUTED_HEX = '#6B7280';

    public static function apply(OutputInterface $output): void
    {
        $formatter = $output->getFormatter();

        $formatter->setStyle('accent', new OutputFormatterStyle(self::ACCENT_HEX));
        $formatter->setStyle('success', new OutputFormatterStyle(self::SUCCESS_HEX));
        $formatter->setStyle('warning', new OutputFormatterStyle(self::WARNING_HEX));
        $formatter->setStyle('muted', new OutputFormatterStyle(self::MUTED_HEX));
        $formatter->setStyle('bold', new OutputFormatterStyle(null, null, ['bold']));
    }

    public static function banner(string $title, string $subtitle = ''): void
    {
        $subtitleHtml = $subtitle !== ''
            ? sprintf('<div class="text-gray-400 ml-1">%s</div>', $subtitle)
            : '';

        render(sprintf(
            <<<'HTML'
                <div class="my-1">
                    <div class="flex">
                        <span class="bg-orange-500 text-white px-1 font-bold">⚡</span>
                        <span class="bg-gray-800 text-white px-2 font-bold">%s</span>
                    </div>
                    %s
                </div>
                HTML,
            $title,
            $subtitleHtml,
        ));
    }

    public static function section(string $icon, string $title): void
    {
        render(sprintf(
            <<<'HTML'
                <div class="mt-1 mb-1">
                    <div class="text-gray-700 mb-1">────────────────────────────────────────────────────────────────────────────────</div>
                    <div class="flex">
                        <span class="text-orange-500 mr-2">%s</span>
                        <span class="font-bold text-white">%s</span>
                    </div>
                </div>
                HTML,
            $icon,
            $title,
        ));
    }

    public static function step(string $icon, string $message): void
    {
        render(sprintf(
            <<<'HTML'
                <div class="flex mt-1">
                    <span class="text-orange-500 mr-1">%s</span>
                    <span class="text-gray-300">%s</span>
                </div>
                HTML,
            $icon,
            $message,
        ));
    }

    public static function success(string $message): void
    {
        render(sprintf(
            <<<'HTML'
                <div class="flex ml-3">
                    <span class="text-green-500 mr-1">✓</span>
                    <span class="text-gray-400">%s</span>
                </div>
                HTML,
            $message,
        ));
    }

    public static function warning(string $message): void
    {
        render(sprintf(
            <<<'HTML'
                <div class="flex ml-3">
                    <span class="text-yellow-500 mr-1">⚠</span>
                    <span class="text-gray-400">%s</span>
                </div>
                HTML,
            $message,
        ));
    }

    public static function info(string $label, string $value): void
    {
        render(sprintf(
            <<<'HTML'
                <div class="flex ml-3">
                    <span class="text-gray-500 w-16">%s</span>
                    <span class="text-orange-400">%s</span>
                </div>
                HTML,
            $label,
            $value,
        ));
    }

    public static function divider(): void
    {
        render(
            <<<'HTML'
                <div class="text-gray-700 my-1">─────────────────────────────────────────────────</div>
                HTML,
        );
    }

    public static function thinDivider(): void
    {
        render(
            <<<'HTML'
                <div class="text-gray-800">╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌</div>
                HTML,
        );
    }

    public static function successBox(string $title, string $message = ''): void
    {
        $messageHtml = $message !== ''
            ? sprintf('<div class="text-gray-300 mt-1 ml-4">%s</div>', $message)
            : '';

        render(sprintf(
            <<<'HTML'
                <div class="my-1">
                    <div class="w-full bg-green-600 text-white py-1 px-2 font-bold">✓   %s</div>
                    %s
                </div>
                HTML,
            $title,
            $messageHtml,
        ));
    }

    public static function warningBox(string $title, string $message = ''): void
    {
        $messageHtml = $message !== ''
            ? sprintf('<div class="text-gray-300 mt-1 ml-4">%s</div>', $message)
            : '';

        render(sprintf(
            <<<'HTML'
                <div class="my-1">
                    <div class="w-full bg-yellow-500 text-black py-1 px-2 font-bold">⚠   %s</div>
                    %s
                </div>
                HTML,
            $title,
            $messageHtml,
        ));
    }

    public static function errorBox(string $title, string $message = ''): void
    {
        $messageHtml = $message !== ''
            ? sprintf('<div class="text-gray-300 mt-1 ml-4">%s</div>', $message)
            : '';

        render(sprintf(
            <<<'HTML'
                <div class="my-1">
                    <div class="w-full bg-red-500 text-white py-1 px-2 font-bold">✗   %s</div>
                    %s
                </div>
                HTML,
            $title,
            $messageHtml,
        ));
    }

    /**
     * @param array<string, string> $items
     */
    public static function summary(string $title, array $items): void
    {
        $itemsHtml = '';

        foreach ($items as $label => $value) {
            $itemsHtml .= sprintf(
                '<div class="flex ml-3"><span class="text-gray-500 w-16">%s</span><span class="text-orange-400">%s</span></div>',
                $label,
                htmlspecialchars($value),
            );
        }

        render(sprintf(
            <<<'HTML'
                <div class="my-1">
                    <div class="text-gray-700">─────────────────────────────────────────────────</div>
                    <div class="flex my-1">
                        <span class="text-orange-500 mr-1">📋</span>
                        <span class="font-bold text-white">%s</span>
                    </div>
                    <div class="text-gray-800 mb-1">╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌╌</div>
                    %s
                    <div class="text-gray-700 mt-1">─────────────────────────────────────────────────</div>
                </div>
                HTML,
            $title,
            $itemsHtml,
        ));
    }

    /**
     * @param array<string> $items
     */
    public static function list(string $title, array $items): void
    {
        $itemsHtml = '';

        foreach ($items as $index => $item) {
            $itemsHtml .= sprintf(
                '<div class="flex ml-4"><span class="text-orange-500 w-4">%d.</span><span class="text-gray-300">%s</span></div>',
                $index + 1,
                $item,
            );
        }

        render(sprintf(
            <<<'HTML'
                <div class="my-1">
                    <div class="text-gray-700 mb-1">────────────────────────────────────────────────────────────────────────────────</div>
                    <div class="flex">
                        <span class="text-orange-500 mr-1">📝</span>
                        <span class="font-bold text-white">%s</span>
                    </div>
                    %s
                </div>
                HTML,
            $title,
            $itemsHtml,
        ));
    }

    public static function command(string $command): void
    {
        render(sprintf(
            <<<'HTML'
                <div class="my-1">
                    <div class="flex">
                        <span class="bg-gray-700 text-gray-400 px-1">$</span>
                        <span class="bg-gray-800 text-green-400 px-2 font-bold">%s</span>
                    </div>
                </div>
                HTML,
            htmlspecialchars($command),
        ));
    }

    public static function newLine(): void
    {
        render('<br>');
    }

}
