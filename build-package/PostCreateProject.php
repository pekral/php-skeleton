<?php

declare(strict_types=1);

namespace Pekral\BuildPackage;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class PostCreateProject
{
    private const EXCLUDED_DIRECTORIES = [
        'vendor',
        '.git',
        'node_modules',
        '.idea',
        '.vscode',
        'build-package',
    ];

    private const BINARY_EXTENSIONS = [
        'png', 'jpg', 'jpeg', 'gif', 'ico', 'webp', 'svg',
        'woff', 'woff2', 'ttf', 'eot', 'otf',
        'zip', 'tar', 'gz', 'rar',
        'pdf', 'doc', 'docx',
        'exe', 'dll', 'so', 'dylib',
        'phar',
    ];

    private const FILES_TO_PROCESS = [
        'composer.json',
        'README.md',
        'CHANGELOG.md',
        'CONTRIBUTING.md',
        'phpstan.neon',
        'phpunit.xml',
        'pint.json',
        'rector.php',
        'ruleset.xml',
        'LICENSE',
    ];

    private const DIRECTORIES_TO_PROCESS = [
        'src',
        'tests',
    ];

    private const EXAMPLE_FILES_TO_DELETE = [
        'src/Example.php',
        'tests/Unit/Example.php',
    ];

    private const BUILD_PACKAGE_DIRECTORY = 'build-package';

    private string $projectPath;
    private string $oldPackageName = 'pekral/php-skeleton';
    private string $oldNamespace = 'Pekral\\Example';
    private string $oldTestNamespace = 'Pekral\\Test';
    private string $oldDisplayName = 'PHP Skeleton';
    private string $oldGithubUrl = 'https://github.com/pekral/php-skeleton';

    private string $newPackageName = '';
    private string $newNamespace = '';
    private string $newTestNamespace = '';
    private string $newDisplayName = '';
    private string $newGithubUrl = '';
    private string $newVendor = '';
    private string $newPackage = '';

    /**
     * @var resource|null
     */
    private $inputStream;

    /**
     * @var resource|null
     */
    private $outputStream;

    /**
     * @param resource|null $inputStream
     * @param resource|null $outputStream
     */
    public function __construct(
        ?string $projectPath = null,
        $inputStream = null,
        $outputStream = null,
    ) {
        $this->projectPath = $projectPath ?? (getcwd() ?: dirname(__DIR__));
        $this->inputStream = $inputStream;
        $this->outputStream = $outputStream;
    }

    public function run(): void
    {
        $this->printBanner();

        if (!$this->isInteractive()) {
            $this->writeLine('⚠️  Non-interactive mode detected. Skipping configuration.');
            $this->writeLine('   Run this script manually to configure your package:');
            $this->writeLine('   php post-create-project.php');

            return;
        }

        $this->gatherInput();
        $this->confirmChanges();
        $this->performReplacements();
        $this->deleteExampleFiles();
        $this->removeGitDirectory();
        $this->initializeGitRepository();
        $this->cleanupComposerJson();
        $this->deleteBuildPackageDirectory();
        $this->runComposerDumpAutoload();
        $this->deleteSelf();

        $this->printSuccess();
    }

    public function configure(
        string $packageName,
        string $namespace,
        string $testNamespace,
        string $displayName,
        string $githubUrl,
    ): void {
        $parts = explode('/', $packageName);
        if (count($parts) !== 2) {
            throw new \InvalidArgumentException('Invalid package name format. Expected: vendor/package');
        }

        $this->newPackageName = $packageName;
        $this->newVendor = $parts[0];
        $this->newPackage = $parts[1];
        $this->newNamespace = $namespace;
        $this->newTestNamespace = $testNamespace;
        $this->newDisplayName = $displayName;
        $this->newGithubUrl = $githubUrl;
    }

    public function runWithoutInteraction(): void
    {
        $this->performReplacements();
        $this->deleteExampleFiles();
        $this->removeGitDirectory();
        $this->cleanupComposerJson();
        $this->deleteBuildPackageDirectory();
    }

    public function toStudlyCase(string $value): string
    {
        $value = str_replace(['-', '_'], ' ', $value);
        $value = ucwords($value);

        return str_replace(' ', '', $value);
    }

    public function toDisplayName(string $value): string
    {
        $value = str_replace(['-', '_'], ' ', $value);

        return ucwords($value);
    }

    public function isBinaryFile(string $filePath): bool
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        return in_array($extension, self::BINARY_EXTENSIONS, true);
    }

    public function isExcludedPath(string $filePath): bool
    {
        foreach (self::EXCLUDED_DIRECTORIES as $excluded) {
            if (str_contains($filePath, "/{$excluded}/")) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, string>
     */
    public function buildReplacements(): array
    {
        return [
            $this->oldPackageName => $this->newPackageName,
            str_replace('\\', '\\\\', $this->oldNamespace) => str_replace('\\', '\\\\', $this->newNamespace),
            str_replace('\\', '\\\\', $this->oldTestNamespace) => str_replace('\\', '\\\\', $this->newTestNamespace),
            $this->oldNamespace => $this->newNamespace,
            $this->oldTestNamespace => $this->newTestNamespace,
            $this->oldGithubUrl => $this->newGithubUrl,
            $this->oldDisplayName => $this->newDisplayName,
            'pekral' => $this->newVendor,
            'Pekral' => $this->toStudlyCase($this->newVendor),
            'Petr Král' => 'Your Name',
            'kral.petr.88@gmail.com' => 'your.email@example.com',
            '@pekral' => '@' . $this->newVendor,
        ];
    }

    /**
     * @param array<string, string> $replacements
     */
    public function processFileContent(string $content, array $replacements): string
    {
        foreach ($replacements as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }

        return $content;
    }

    /**
     * @return array<string>
     */
    public function getFilesToProcess(): array
    {
        return self::FILES_TO_PROCESS;
    }

    /**
     * @return array<string>
     */
    public function getDirectoriesToProcess(): array
    {
        return self::DIRECTORIES_TO_PROCESS;
    }

    /**
     * @return array<string>
     */
    public function getExampleFilesToDelete(): array
    {
        return self::EXAMPLE_FILES_TO_DELETE;
    }

    /**
     * @return array<string>
     */
    public function getBinaryExtensions(): array
    {
        return self::BINARY_EXTENSIONS;
    }

    /**
     * @return array<string>
     */
    public function getExcludedDirectories(): array
    {
        return self::EXCLUDED_DIRECTORIES;
    }

    public function getProjectPath(): string
    {
        return $this->projectPath;
    }

    private function printBanner(): void
    {
        $this->writeLine('');
        $this->writeLine('╔══════════════════════════════════════════════════════════════╗');
        $this->writeLine('║                                                              ║');
        $this->writeLine('║   🚀 PHP Skeleton - Project Configuration                    ║');
        $this->writeLine('║                                                              ║');
        $this->writeLine('╚══════════════════════════════════════════════════════════════╝');
        $this->writeLine('');
    }

    private function isInteractive(): bool
    {
        if ($this->inputStream !== null) {
            return true;
        }

        return defined('STDIN') && posix_isatty(STDIN);
    }

    private function gatherInput(): void
    {
        $this->writeLine('Please provide the following information for your new package:');
        $this->writeLine('');

        $this->newPackageName = $this->ask(
            'Package name (vendor/package)',
            'my-vendor/my-package'
        );

        $parts = explode('/', $this->newPackageName);
        if (count($parts) !== 2) {
            $this->writeLine('❌ Invalid package name format. Expected: vendor/package');
            exit(1);
        }

        $this->newVendor = $parts[0];
        $this->newPackage = $parts[1];

        $defaultNamespace = $this->toStudlyCase($this->newVendor) . '\\' . $this->toStudlyCase($this->newPackage);
        $this->newNamespace = $this->ask(
            'Root PSR-4 namespace',
            $defaultNamespace
        );

        $namespaceParts = explode('\\', $this->newNamespace);
        $defaultTestNamespace = $namespaceParts[0] . '\\Test';
        $this->newTestNamespace = $this->ask(
            'Test namespace',
            $defaultTestNamespace
        );

        $defaultDisplayName = $this->toDisplayName($this->newPackage);
        $this->newDisplayName = $this->ask(
            'Project display name',
            $defaultDisplayName
        );

        $defaultGithubUrl = "https://github.com/{$this->newPackageName}";
        $this->newGithubUrl = $this->ask(
            'GitHub repository URL',
            $defaultGithubUrl
        );
    }

    private function confirmChanges(): void
    {
        $this->writeLine('');
        $this->writeLine('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->writeLine('Configuration Summary:');
        $this->writeLine('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->writeLine("  Package name:    {$this->newPackageName}");
        $this->writeLine("  Namespace:       {$this->newNamespace}");
        $this->writeLine("  Test namespace:  {$this->newTestNamespace}");
        $this->writeLine("  Display name:    {$this->newDisplayName}");
        $this->writeLine("  GitHub URL:      {$this->newGithubUrl}");
        $this->writeLine('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->writeLine('');

        $confirm = $this->ask('Proceed with these settings?', 'yes');

        if (!in_array(strtolower($confirm), ['yes', 'y', ''], true)) {
            $this->writeLine('❌ Configuration cancelled.');
            exit(0);
        }
    }

    private function performReplacements(): void
    {
        $this->writeLine('');
        $this->writeLine('🔄 Performing replacements...');

        $replacements = $this->buildReplacements();

        foreach (self::FILES_TO_PROCESS as $file) {
            $filePath = $this->projectPath . '/' . $file;
            if (file_exists($filePath)) {
                $this->processFile($filePath, $replacements);
            }
        }

        foreach (self::DIRECTORIES_TO_PROCESS as $directory) {
            $directoryPath = $this->projectPath . '/' . $directory;
            if (is_dir($directoryPath)) {
                $this->processDirectory($directoryPath, $replacements);
            }
        }

        $this->writeLine('   ✓ Replacements completed');
    }

    /**
     * @param array<string, string> $replacements
     */
    private function processFile(string $filePath, array $replacements): void
    {
        if ($this->isBinaryFile($filePath)) {
            return;
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            return;
        }

        $originalContent = $content;
        $content = $this->processFileContent($content, $replacements);

        if ($content !== $originalContent) {
            file_put_contents($filePath, $content);
            $relativePath = str_replace($this->projectPath . '/', '', $filePath);
            $this->writeLine("   ✓ Updated: {$relativePath}");
        }
    }

    /**
     * @param array<string, string> $replacements
     */
    private function processDirectory(string $directory, array $replacements): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            if (!$file instanceof SplFileInfo) {
                continue;
            }

            $filePath = $file->getPathname();

            if ($this->isExcludedPath($filePath)) {
                continue;
            }

            if ($file->isFile()) {
                $this->processFile($filePath, $replacements);
            }
        }
    }

    private function deleteExampleFiles(): void
    {
        $this->writeLine('');
        $this->writeLine('🗑️  Deleting example files...');

        foreach (self::EXAMPLE_FILES_TO_DELETE as $file) {
            $filePath = $this->projectPath . '/' . $file;
            if (file_exists($filePath)) {
                unlink($filePath);
                $this->writeLine("   ✓ Deleted: {$file}");
            }
        }
    }

    private function removeGitDirectory(): void
    {
        $this->writeLine('');
        $this->writeLine('🗑️  Removing .git directory...');

        $gitPath = $this->projectPath . '/.git';
        if (is_dir($gitPath)) {
            $this->deleteDirectory($gitPath);
            $this->writeLine('   ✓ Removed .git directory');
        } else {
            $this->writeLine('   ⚠️  No .git directory found');
        }
    }

    private function cleanupComposerJson(): void
    {
        $this->writeLine('');
        $this->writeLine('🧹 Cleaning up composer.json...');

        $composerPath = $this->projectPath . '/composer.json';
        if (!file_exists($composerPath)) {
            return;
        }

        $content = file_get_contents($composerPath);
        if ($content === false) {
            return;
        }

        /** @var array<string, mixed>|null $composer */
        $composer = json_decode($content, true);
        if (!is_array($composer)) {
            return;
        }

        $modified = false;

        if (
            isset($composer['scripts'])
            && is_array($composer['scripts'])
            && array_key_exists('post-create-project-cmd', $composer['scripts'])
        ) {
            unset($composer['scripts']['post-create-project-cmd']);
            $modified = true;
            $this->writeLine('   ✓ Removed post-create-project-cmd script');
        }

        if (
            isset($composer['autoload-dev'])
            && is_array($composer['autoload-dev'])
            && isset($composer['autoload-dev']['psr-4'])
            && is_array($composer['autoload-dev']['psr-4'])
        ) {
            $buildPackageKey = $this->toStudlyCase($this->newVendor) . '\\BuildPackage\\';
            if (array_key_exists($buildPackageKey, $composer['autoload-dev']['psr-4'])) {
                unset($composer['autoload-dev']['psr-4'][$buildPackageKey]);
                $modified = true;
                $this->writeLine('   ✓ Removed build-package autoload entry');
            }
        }

        if ($modified) {
            $json = json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            if ($json !== false) {
                file_put_contents($composerPath, $json . "\n");
            }
        }
    }

    private function deleteBuildPackageDirectory(): void
    {
        $this->writeLine('');
        $this->writeLine('🗑️  Removing build-package directory...');

        $buildPackagePath = $this->projectPath . '/' . self::BUILD_PACKAGE_DIRECTORY;
        if (is_dir($buildPackagePath)) {
            $this->deleteDirectory($buildPackagePath);
            $this->writeLine('   ✓ Removed build-package directory');
        }
    }

    private function deleteDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($items as $item) {
            if (!$item instanceof SplFileInfo) {
                continue;
            }

            if ($item->isDir()) {
                rmdir($item->getPathname());
            } else {
                unlink($item->getPathname());
            }
        }

        rmdir($directory);
    }

    private function initializeGitRepository(): void
    {
        $this->writeLine('');
        $initGit = $this->ask('Initialize a new git repository?', 'yes');

        if (in_array(strtolower($initGit), ['yes', 'y', ''], true)) {
            $this->writeLine('');
            $this->writeLine('📦 Initializing new git repository...');

            $currentDir = getcwd();
            chdir($this->projectPath);

            exec('git init 2>&1', $output, $returnCode);

            if ($returnCode === 0) {
                $this->writeLine('   ✓ Git repository initialized');

                $createCommit = $this->ask('Create initial commit?', 'yes');
                if (in_array(strtolower($createCommit), ['yes', 'y', ''], true)) {
                    exec('git add -A 2>&1');
                    exec('git commit -m "Initial commit" 2>&1', $commitOutput, $commitReturnCode);

                    if ($commitReturnCode === 0) {
                        $this->writeLine('   ✓ Initial commit created');
                    }
                }
            } else {
                $this->writeLine('   ⚠️  Failed to initialize git repository');
            }

            if ($currentDir !== false) {
                chdir($currentDir);
            }
        }
    }

    private function runComposerDumpAutoload(): void
    {
        $this->writeLine('');
        $this->writeLine('🔄 Running composer dump-autoload...');

        $currentDir = getcwd();
        chdir($this->projectPath);

        exec('composer dump-autoload 2>&1', $output, $returnCode);

        if ($returnCode === 0) {
            $this->writeLine('   ✓ Autoload files regenerated');
        } else {
            $this->writeLine('   ⚠️  Failed to regenerate autoload files');
        }

        if ($currentDir !== false) {
            chdir($currentDir);
        }
    }

    private function deleteSelf(): void
    {
        $scriptPath = $this->projectPath . '/post-create-project.php';
        if (file_exists($scriptPath)) {
            unlink($scriptPath);
            $this->writeLine('   ✓ Removed post-create-project.php');
        }
    }

    private function printSuccess(): void
    {
        $this->writeLine('');
        $this->writeLine('╔══════════════════════════════════════════════════════════════╗');
        $this->writeLine('║                                                              ║');
        $this->writeLine('║   ✅ Project configured successfully!                        ║');
        $this->writeLine('║                                                              ║');
        $this->writeLine('╚══════════════════════════════════════════════════════════════╝');
        $this->writeLine('');
        $this->writeLine('Next steps:');
        $this->writeLine("  1. cd {$this->newPackage}");
        $this->writeLine('  2. Update author information in composer.json');
        $this->writeLine('  3. Start building your package in src/');
        $this->writeLine('  4. Run `composer check` to verify setup');
        $this->writeLine('');
        $this->writeLine('Happy coding! 🎉');
        $this->writeLine('');
    }

    private function ask(string $question, string $default = ''): string
    {
        $defaultHint = $default !== '' ? " [{$default}]" : '';
        $this->write("  {$question}{$defaultHint}: ");

        $handle = $this->inputStream ?? fopen('php://stdin', 'r');
        if ($handle === false) {
            return $default;
        }

        $input = fgets($handle);

        if ($this->inputStream === null) {
            fclose($handle);
        }

        if ($input === false) {
            return $default;
        }

        $input = trim($input);

        return $input !== '' ? $input : $default;
    }

    private function write(string $message): void
    {
        if ($this->outputStream !== null) {
            fwrite($this->outputStream, $message);

            return;
        }

        echo $message;
    }

    private function writeLine(string $message): void
    {
        $this->write($message . PHP_EOL);
    }
}
