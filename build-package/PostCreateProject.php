<?php

declare(strict_types = 1);

namespace Pekral\BuildPackage;

use FilesystemIterator;
use InvalidArgumentException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StreamableInputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

final class PostCreateProject extends Command
{

    private const array EXCLUDED_DIRECTORIES = [
        'vendor',
        '.git',
        'node_modules',
        '.idea',
        '.vscode',
        'build-package',
    ];

    private const array BINARY_EXTENSIONS = [
        'png', 'jpg', 'jpeg', 'gif', 'ico', 'webp', 'svg',
        'woff', 'woff2', 'ttf', 'eot', 'otf',
        'zip', 'tar', 'gz', 'rar',
        'pdf', 'doc', 'docx',
        'exe', 'dll', 'so', 'dylib',
        'phar',
    ];

    private const array FILES_TO_PROCESS = [
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

    private const array DIRECTORIES_TO_PROCESS = [
        'src',
        'tests',
    ];

    private const array EXAMPLE_FILES_TO_DELETE = [
        'src/Example.php',
        'tests/Unit/ConsoleThemeTest.php',
        'tests/Unit/ExampleTest.php',
        'tests/Unit/PostCreateProjectTest.php',
    ];

    private const string BUILD_PACKAGE_DIRECTORY = 'build-package';

    public string $projectPath {
        get => $this->projectPath;
        set(string $path) {
            $this->projectPath = $path;
        }
    }

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

    private string $specContent = '';

    private string $newVendor = '';

    private string $newPackage = '';

    private SymfonyStyle $symfonyStyle;

    public function __construct(?string $projectPath = null)
    {
        parent::__construct('configure');

        $this->projectPath = $projectPath ?? (getcwd() ?: dirname(__DIR__));
    }

    public function configurePackage(string $packageName, string $namespace, string $testNamespace, string $displayName, string $githubUrl): void
    {
        $parts = explode('/', $packageName);

        if (count($parts) !== 2) {
            throw new InvalidArgumentException('Invalid package name format. Expected: vendor/package');
        }

        $this->newPackageName = $packageName;
        $this->newVendor = $parts[0];
        $this->newPackage = $parts[1];
        $this->newNamespace = $namespace;
        $this->newTestNamespace = $testNamespace;
        $this->newDisplayName = $displayName;
        $this->newGithubUrl = $githubUrl;
    }

    public function runWithoutInteraction(OutputInterface $output): void
    {
        $this->setupStyles($output);
        $this->symfonyStyle = new SymfonyStyle(new ArrayInput([]), $output);
        $this->performReplacements();
        $this->clearDocumentationFiles();
        $this->createSpecFile();
        $this->deleteExampleFiles();
        $this->removeGitDirectory();
        $this->cleanupComposerJson();
        $this->cleanupPhpstanNeon();
        $this->cleanupRectorPhp();
        $this->moveRequireToRequireDev();
        $this->updateDevDependenciesToStable();
        $this->deleteBuildPackageDirectory();
        $this->createExampleFile();
        $this->createExampleTest();
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
            if (str_contains($filePath, sprintf('/%s/', $excluded))) {
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
        $replacements = [
            $this->oldDisplayName => $this->newDisplayName,
            $this->oldGithubUrl => $this->newGithubUrl,
            $this->oldNamespace . '\\' => $this->newNamespace . '\\',
            $this->oldNamespace . ';' => $this->newNamespace . ';',
            $this->oldPackageName => $this->newPackageName,
            $this->oldTestNamespace . '\\' => $this->newTestNamespace . '\\',
            $this->oldTestNamespace . ';' => $this->newTestNamespace . ';',
            '@pekral' => '@' . $this->newVendor,
            'kral.petr.88@gmail.com' => 'your.email@example.com',
            'pekral' => $this->newVendor,
            'Pekral' => $this->toStudlyCase($this->newVendor),
            'Petr Král' => 'Your Name',
            str_replace('\\', '\\\\', $this->oldNamespace) . '\\\\' => str_replace('\\', '\\\\', $this->newNamespace) . '\\\\',
            str_replace('\\', '\\\\', $this->oldTestNamespace) . '\\\\' => str_replace('\\', '\\\\', $this->newTestNamespace) . '\\\\',
        ];

        uksort($replacements, static fn (string $a, string $b): int => strlen($b) <=> strlen($a));

        return $replacements;
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

    protected function configure(): void
    {
        $this->setDescription('Configure your new PHP package from the skeleton template');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->setupStyles($output);
        $this->symfonyStyle = new SymfonyStyle($input, $output);
        $this->registerSignalHandler();

        $this->printBanner();

        if (!$input->isInteractive()) {
            $this->symfonyStyle->warning('Non-interactive mode detected. Skipping configuration.');
            $this->symfonyStyle->text('Run this script manually to configure your package:');
            $this->symfonyStyle->text('  <accent>php post-create-project.php</accent>');

            return Command::SUCCESS;
        }

        $this->gatherInput($input);

        if (!$this->confirmChanges($input)) {
            $this->cleanupProjectDirectory('Configuration cancelled.');

            return Command::SUCCESS;
        }

        $this->performReplacements();
        $this->clearDocumentationFiles();
        $this->createSpecFile();
        $this->deleteExampleFiles();
        $this->removeGitDirectory();
        $this->cleanupComposerJson();
        $this->cleanupPhpstanNeon();
        $this->cleanupRectorPhp();
        $this->moveRequireToRequireDev();
        $this->updateDevDependenciesToStable();
        $this->deleteBuildPackageDirectory();
        $this->createExampleFile();
        $this->createExampleTest();
        $this->deleteSelf();
        $this->runComposerInstall();
        $this->installGitHubActions($input);
        $this->installCursorRules($input);
        $this->runComposerFix();

        $checkPassed = $this->runComposerCheck();
        $this->initializeGitRepository($input, $checkPassed);

        if ($checkPassed) {
            $this->printSuccess();

            return Command::SUCCESS;
        }

        $this->printFailure();

        return Command::FAILURE;
    }

    private function setupStyles(OutputInterface $output): void
    {
        ConsoleTheme::apply($output);
    }

    private function registerSignalHandler(): void
    {
        if (!function_exists('pcntl_signal')) {
            return;
        }

        pcntl_signal(SIGINT, function (): never {
            $this->cleanupProjectDirectory("\nInstallation cancelled by user (CTRL+C).");
            exit(0);
        });

        pcntl_signal(SIGTERM, function (): never {
            $this->cleanupProjectDirectory("\nInstallation terminated.");
            exit(0);
        });

        pcntl_async_signals(true);
    }

    private function cleanupProjectDirectory(string $message): void
    {
        ConsoleTheme::newLine();
        ConsoleTheme::warning($message);

        if (!is_dir($this->projectPath)) {
            return;
        }

        ConsoleTheme::warning('Cleaning up project directory...');
        $this->deleteDirectory($this->projectPath);
        ConsoleTheme::success('Project directory removed.');
    }

    private function printBanner(): void
    {
        ConsoleTheme::banner('PHP Skeleton', 'Project Configuration');
    }

    private function gatherInput(InputInterface $input): void
    {
        ConsoleTheme::newLine();
        ConsoleTheme::section('📝', 'Package Configuration');
        ConsoleTheme::thinDivider();

        $helper = $this->getQuestionHelper();

        $question = new Question('   <accent>Package name</accent> <muted>(vendor/package)</muted>: ', 'my-vendor/my-package');
        $this->newPackageName = $this->askQuestion($helper, $input, $question);

        $parts = explode('/', $this->newPackageName);

        if (count($parts) !== 2) {
            $this->symfonyStyle->error('Invalid package name format. Expected: vendor/package');
            exit(1);
        }

        $this->newVendor = $parts[0];
        $this->newPackage = $parts[1];

        $defaultNamespace = $this->toStudlyCase($this->newVendor) . '\\' . $this->toStudlyCase($this->newPackage);
        $question = new Question(
            sprintf('   <accent>Root namespace</accent> [<muted>%s</muted>]: ', $defaultNamespace),
            $defaultNamespace,
        );
        $this->newNamespace = $this->askQuestion($helper, $input, $question);

        $defaultTestNamespace = $this->newNamespace . 'Test';
        $question = new Question(
            sprintf('   <accent>Test namespace</accent> [<muted>%s</muted>]: ', $defaultTestNamespace),
            $defaultTestNamespace,
        );
        $this->newTestNamespace = $this->askQuestion($helper, $input, $question);

        $defaultDisplayName = $this->toDisplayName($this->newPackage);
        $question = new Question(
            sprintf('   <accent>Display name</accent> [<muted>%s</muted>]: ', $defaultDisplayName),
            $defaultDisplayName,
        );
        $this->newDisplayName = $this->askQuestion($helper, $input, $question);

        $defaultGithubUrl = 'https://github.com/' . $this->newPackageName;
        $question = new Question(
            sprintf('   <accent>GitHub URL</accent> [<muted>%s</muted>]: ', $defaultGithubUrl),
            $defaultGithubUrl,
        );
        $this->newGithubUrl = $this->askQuestion($helper, $input, $question);

        $this->checkAndCreateGithubRepository($input);

        ConsoleTheme::newLine();
        ConsoleTheme::info('Project spec', 'paste text, end with empty line (or skip with Enter)');

        $this->specContent = $this->readMultilineInput($input);

        if ($this->specContent !== '') {
            ConsoleTheme::success(sprintf('Spec received (%d chars)', strlen($this->specContent)));
        }
    }

    private function readMultilineInput(InputInterface $input): string
    {
        $stream = $input instanceof StreamableInputInterface ? $input->getStream() : null;

        if ($stream === null || $stream === false) {
            $stream = STDIN;
        }

        $lines = [];
        $consecutiveEmptyLines = 0;

        while (true) {
            $line = fgets($stream);

            if ($line === false) {
                break;
            }

            $trimmedLine = rtrim($line, "\r\n");

            if ($trimmedLine === '') {
                if ($lines === []) {
                    break;
                }

                $consecutiveEmptyLines++;

                if ($consecutiveEmptyLines >= 2) {
                    break;
                }

                $lines[] = '';

                continue;
            }

            $consecutiveEmptyLines = 0;
            $lines[] = $trimmedLine;
        }

        while ($lines !== [] && end($lines) === '') {
            array_pop($lines);
        }

        return implode("\n", $lines);
    }

    private function confirmChanges(InputInterface $input): bool
    {
        ConsoleTheme::summary('Review Configuration', [
            'GitHub' => $this->newGithubUrl,
            'Name' => $this->newDisplayName,
            'Namespace' => $this->newNamespace,
            'Package' => $this->newPackageName,
            'Tests' => $this->newTestNamespace,
        ]);

        $helper = $this->getQuestionHelper();
        $question = new ConfirmationQuestion('   <accent>Proceed?</accent> [<muted>yes</muted>]: ', true);

        return (bool) $helper->ask($input, $this->symfonyStyle, $question);
    }

    private function performReplacements(): void
    {
        ConsoleTheme::newLine();
        ConsoleTheme::section('⚡', 'Processing Files');

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

        if ($content === $originalContent) {
            return;
        }

        file_put_contents($filePath, $content);
        $relativePath = str_replace($this->projectPath . '/', '', $filePath);
        ConsoleTheme::success(sprintf('Updated: %s', $relativePath));
    }

    /**
     * @param array<string, string> $replacements
     */
    private function processDirectory(string $directory, array $replacements): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST,
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

    private function clearDocumentationFiles(): void
    {
        $files = [
            'CHANGELOG.md',
            'README.md',
        ];

        foreach ($files as $file) {
            $filePath = $this->projectPath . '/' . $file;

            if (!file_exists($filePath)) {
                continue;
            }

            file_put_contents($filePath, '');
            ConsoleTheme::success(sprintf('Cleared %s', $file));
        }
    }

    private function createSpecFile(): void
    {
        if ($this->specContent === '') {
            return;
        }

        $specPath = $this->projectPath . '/SPEC.md';
        file_put_contents($specPath, $this->specContent);
        ConsoleTheme::success('Created SPEC.md');
    }

    private function deleteExampleFiles(): void
    {
        ConsoleTheme::newLine();
        ConsoleTheme::section('🗑', 'Cleanup');

        foreach (self::EXAMPLE_FILES_TO_DELETE as $file) {
            $filePath = $this->projectPath . '/' . $file;

            if (!file_exists($filePath)) {
                continue;
            }

            unlink($filePath);
            ConsoleTheme::success(sprintf('Removed %s', $file));
        }

        $this->createGitkeepFiles();
    }

    private function createGitkeepFiles(): void
    {
        $directories = [
            'docs',
            'src',
            'tests',
            'tests/Unit',
        ];

        foreach ($directories as $dir) {
            $dirPath = $this->projectPath . '/' . $dir;

            if (is_dir($dirPath)) {
                continue;
            }

            mkdir($dirPath, 0755, true);
            ConsoleTheme::success(sprintf('Created: %s/', $dir));
        }

        foreach ($directories as $dir) {
            $dirPath = $this->projectPath . '/' . $dir;
            $gitkeepPath = $dirPath . '/.gitkeep';

            if (!$this->isDirectoryEmpty($dirPath)) {
                continue;
            }

            file_put_contents($gitkeepPath, '');
            ConsoleTheme::success(sprintf('Created: %s/.gitkeep', $dir));
        }
    }

    private function isDirectoryEmpty(string $directory): bool
    {
        $files = array_diff(scandir($directory) ?: [], ['.', '..']);

        return count($files) === 0;
    }

    private function removeGitDirectory(): void
    {
        $gitPath = $this->projectPath . '/.git';

        if (!is_dir($gitPath)) {
            return;
        }

        $this->deleteDirectory($gitPath);
        ConsoleTheme::success('Removed .git directory');
    }

    private function cleanupComposerJson(): void
    {

        $composerPath = $this->projectPath . '/composer.json';

        if (!file_exists($composerPath)) {
            return;
        }

        $content = file_get_contents($composerPath);

        if ($content === false) {
            return;
        }

        /** @var array<string, mixed>|null $composer */
        $composer = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($composer)) {
            return;
        }

        $modified = false;

        if (isset($composer['description'])) {
            $composer['description'] = '';
            $modified = true;
        }

        if (isset($composer['keywords'])) {
            $composer['keywords'] = [];
            $modified = true;
        }

        if (isset($composer['funding'])) {
            $composer['funding'] = [];
            $modified = true;
        }

        if (isset($composer['scripts'])
            && is_array($composer['scripts'])
            && array_key_exists('post-create-project-cmd', $composer['scripts'])
        ) {
            unset($composer['scripts']['post-create-project-cmd']);
            $modified = true;
        }

        if (isset($composer['autoload-dev'])
            && is_array($composer['autoload-dev'])
            && isset($composer['autoload-dev']['psr-4'])
            && is_array($composer['autoload-dev']['psr-4'])
        ) {
            $buildPackageKey = $this->toStudlyCase($this->newVendor) . '\\BuildPackage\\';

            if (array_key_exists($buildPackageKey, $composer['autoload-dev']['psr-4'])) {
                unset($composer['autoload-dev']['psr-4'][$buildPackageKey]);
                $modified = true;
            }
        }

        if (isset($composer['scripts']) && is_array($composer['scripts'])) {
            $scriptsToMakeOptional = [
                'analyse' => 'find src/ -name "*.php" 2>/dev/null | head -1 | grep -q . && vendor/bin/phpstan analyse --memory-limit=2G || echo "No PHP files to analyse"',
                'phpcs-check' => 'find src/ tests/ -name "*.php" 2>/dev/null | head -1 | grep -q . && vendor/bin/phpcs --standard=ruleset.xml src/ tests/ || echo "No PHP files to check"',
                'phpcs-fix' => 'find src/ tests/ -name "*.php" 2>/dev/null | head -1 | grep -q . && vendor/bin/phpcbf --standard=ruleset.xml src/ tests/ || true',
                'pint-check' => 'find src/ tests/ -name "*.php" 2>/dev/null | head -1 | grep -q . && vendor/bin/pint src/ tests/ --test || echo "No PHP files to check"',
                'pint-fix' => 'find src/ tests/ -name "*.php" 2>/dev/null | head -1 | grep -q . && vendor/bin/pint src/ tests/ || true',
                'rector-check' => 'find src/ tests/ -name "*.php" 2>/dev/null | head -1 | grep -q . && vendor/bin/rector process --dry-run --no-progress-bar || echo "No PHP files to check"',
                'rector-fix' => 'find src/ tests/ -name "*.php" 2>/dev/null | head -1 | grep -q . && vendor/bin/rector process --no-progress-bar || true',
                'test:coverage' => 'find tests/ -name "*.php" 2>/dev/null | head -1 | grep -q . && php -dpcov.enabled=1 -dpcov.directory=. -dxdebug.mode=off vendor/bin/pest --coverage --min=100 || echo "No tests to run"',
            ];

            foreach ($scriptsToMakeOptional as $scriptName => $newCommand) {
                if (array_key_exists($scriptName, $composer['scripts'])) {
                    $composer['scripts'][$scriptName] = [
                        sprintf('echo "Running %s..."', $scriptName),
                        $newCommand,
                    ];
                    $modified = true;
                }
            }
        }

        if ($modified) {
            ConsoleTheme::success('Updated composer.json');
        }

        if (!$modified) {
            return;
        }

        $json = json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($json !== false) {
            file_put_contents($composerPath, $json . "\n");
        }
    }

    private function cleanupPhpstanNeon(): void
    {
        $phpstanPath = $this->projectPath . '/phpstan.neon';

        if (!file_exists($phpstanPath)) {
            return;
        }

        $content = file_get_contents($phpstanPath);

        if ($content === false) {
            return;
        }

        $originalContent = $content;

        $content = preg_replace('/^(\s*)-\s*build-package\s*$/m', '', $content);

        if ($content === null || $content === $originalContent) {
            return;
        }

        $content = preg_replace('/\n{3,}/', "\n\n", $content);
        file_put_contents($phpstanPath, $content);
        ConsoleTheme::success('Updated phpstan.neon');
    }

    private function cleanupRectorPhp(): void
    {
        $rectorPath = $this->projectPath . '/rector.php';

        if (!file_exists($rectorPath)) {
            return;
        }

        $content = file_get_contents($rectorPath);

        if ($content === false) {
            return;
        }

        $originalContent = $content;

        $result = preg_replace("/[ \t]*__DIR__\s*\.\s*'\/build-package',?\s*\n?/m", '', $content);

        if ($result === null) {
            return;
        }

        $content = $result;

        $result = preg_replace('/\s*SimplifyQuoteEscapeRector::class\s*=>\s*\[[^\]]*\],?/s', '', $content);

        if ($result === null) {
            return;
        }

        $content = $result;

        $result = preg_replace('/use Rector\\\\CodingStyle\\\\Rector\\\\String_\\\\SimplifyQuoteEscapeRector;\s*\n?/', '', $content);

        if ($result === null) {
            return;
        }

        $content = $result;

        if ($content === $originalContent) {
            return;
        }

        file_put_contents($rectorPath, $content);
        ConsoleTheme::success('Updated rector.php');
    }

    private function createExampleFile(): void
    {
        $srcPath = $this->projectPath . '/src';

        if (!is_dir($srcPath)) {
            mkdir($srcPath, 0755, true);
        }

        $className = $this->toStudlyCase($this->newPackage);
        $filePath = $srcPath . '/' . $className . '.php';

        $content = <<<PHP
<?php

declare(strict_types=1);

namespace {$this->newNamespace};

final class {$className}
{
    public function greet(string \$name): string
    {
        return sprintf('Hello, %s!', \$name);
    }
}

PHP;

        file_put_contents($filePath, $content);
        ConsoleTheme::success(sprintf('Created src/%s.php', $className));

        $gitkeepPath = $srcPath . '/.gitkeep';

        if (file_exists($gitkeepPath)) {
            unlink($gitkeepPath);
        }
    }

    private function createExampleTest(): void
    {
        $testsPath = $this->projectPath . '/tests/Unit';

        if (!is_dir($testsPath)) {
            mkdir($testsPath, 0755, true);
        }

        $className = $this->toStudlyCase($this->newPackage);
        $filePath = $testsPath . '/' . $className . 'Test.php';

        $content = <<<PHP
<?php

declare(strict_types=1);

use {$this->newNamespace}\\{$className};

describe({$className}::class, function (): void {
    it('greets with name', function (): void {
        \$instance = new {$className}();

        expect(\$instance->greet('World'))->toBe('Hello, World!');
    });
});

PHP;

        file_put_contents($filePath, $content);
        ConsoleTheme::success(sprintf('Created tests/Unit/%sTest.php', $className));

        $gitkeepPath = $testsPath . '/.gitkeep';

        if (file_exists($gitkeepPath)) {
            unlink($gitkeepPath);
        }
    }

    private function moveRequireToRequireDev(): void
    {

        $composerPath = $this->projectPath . '/composer.json';

        if (!file_exists($composerPath)) {
            return;
        }

        $content = file_get_contents($composerPath);

        if ($content === false) {
            return;
        }

        /** @var array<string, mixed>|null $composer */
        $composer = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($composer)) {
            return;
        }

        if (!isset($composer['require']) || !is_array($composer['require'])) {
            return;
        }

        /** @var array<string, string> $require */
        $require = $composer['require'];

        /** @var array<string, string> $requireDev */
        $requireDev = isset($composer['require-dev']) && is_array($composer['require-dev'])
            ? $composer['require-dev']
            : [];

        $newRequire = [];
        $movedCount = 0;

        foreach ($require as $package => $version) {
            if ($package === 'php' || str_starts_with($package, 'ext-')) {
                $newRequire[$package] = $version;
            } else {
                $requireDev[$package] = $version;
                $movedCount++;
            }
        }

        $composer['require'] = $newRequire;
        $composer['require-dev'] = $requireDev;

        $json = json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($json === false) {
            return;
        }

        file_put_contents($composerPath, $json . "\n");

        if ($movedCount > 0) {
            ConsoleTheme::success(sprintf('Moved %d packages to require-dev', $movedCount));
        }
    }

    private function updateDevDependenciesToStable(): void
    {
        // All pekral/* packages stay as dev-master
    }

    private function deleteBuildPackageDirectory(): void
    {
        $buildPackagePath = $this->projectPath . '/' . self::BUILD_PACKAGE_DIRECTORY;

        if (!is_dir($buildPackagePath)) {
            return;
        }

        $this->deleteDirectory($buildPackagePath);
        ConsoleTheme::success('Removed build-package/');
    }

    private function deleteDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
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

    private function initializeGitRepository(InputInterface $input, bool $checkPassed): void
    {
        ConsoleTheme::newLine();
        ConsoleTheme::section('📦', 'Git Repository');

        if (!$checkPassed) {
            ConsoleTheme::warning('Skipped (quality checks failed)');

            return;
        }

        $helper = $this->getQuestionHelper();
        $question = new ConfirmationQuestion('   <accent>Initialize git?</accent> [<muted>yes</muted>]: ', true);

        if (!(bool) $helper->ask($input, $this->symfonyStyle, $question)) {
            return;
        }

        $branchQuestion = new Question('   <accent>Default branch</accent> [<muted>main</muted>]: ', 'main');
        $defaultBranch = $this->askQuestion($helper, $input, $branchQuestion);

        $currentDir = getcwd();
        chdir($this->projectPath);

        exec('git init 2>&1', $output, $returnCode);

        if ($returnCode !== 0) {
            ConsoleTheme::warning('Failed to initialize');

            if ($currentDir !== false) {
                chdir($currentDir);
            }

            return;
        }

        $this->setupDefaultBranch($defaultBranch);
        $this->updateGitignore();

        $sshUrl = $this->convertToSshUrl($this->newGithubUrl);
        exec(sprintf('git remote add origin %s 2>&1', escapeshellarg($sshUrl)), $remoteOutput, $remoteReturnCode);

        exec('git rm -r --cached . 2>&1');
        exec('git add -A 2>&1');
        exec('git commit -m "chore: initialize project from php-skeleton template" 2>&1', $commitOutput, $commitReturnCode);

        if ($commitReturnCode === 0) {
            ConsoleTheme::success('Initialized with initial commit');
        } else {
            ConsoleTheme::warning('Failed to create initial commit');
        }

        $this->pushToRemote($input, $helper, $defaultBranch);

        if ($currentDir !== false) {
            chdir($currentDir);
        }
    }

    private function updateGitignore(): void
    {
        $gitignorePath = $this->projectPath . '/.gitignore';

        if (!file_exists($gitignorePath)) {
            return;
        }

        $content = file_get_contents($gitignorePath);

        if ($content === false) {
            return;
        }

        $entriesToAdd = [
            'SPEC.md',
            '.idea/',
            '.cursor/',
        ];

        $added = [];

        foreach ($entriesToAdd as $entry) {
            if (!str_contains($content, $entry)) {
                $content = rtrim($content) . "\n" . $entry;
                $added[] = $entry;
            }
        }

        if ($added === []) {
            return;
        }

        $content = rtrim($content) . "\n";
        file_put_contents($gitignorePath, $content);
        ConsoleTheme::success('Updated .gitignore');
    }

    private function convertToSshUrl(string $httpsUrl): string
    {
        if (preg_match('#^https://github\.com/([^/]+)/([^/]+?)(?:\.git)?$#', $httpsUrl, $matches)) {
            return sprintf('git@github.com:%s/%s.git', $matches[1], $matches[2]);
        }

        return $httpsUrl;
    }

    private function setupDefaultBranch(string $branchName): void
    {
        exec('git symbolic-ref --short HEAD 2>&1', $currentBranchOutput, $currentBranchReturnCode);
        $currentBranch = $currentBranchReturnCode === 0 && $currentBranchOutput !== [] ? trim($currentBranchOutput[0]) : '';

        if ($currentBranch === $branchName) {
            return;
        }

        exec(sprintf('git branch -M %s 2>&1', escapeshellarg($branchName)), $renameOutput, $renameReturnCode);

        if ($renameReturnCode === 0) {
            ConsoleTheme::success(sprintf('Set default branch to %s', $branchName));
        } else {
            ConsoleTheme::warning(sprintf('Failed to set branch to %s', $branchName));
        }
    }

    private function pushToRemote(InputInterface $input, QuestionHelper $helper, string $branchName = 'main'): void
    {
        $question = new ConfirmationQuestion('   <accent>Push to GitHub?</accent> [<muted>no</muted>]: ', false);

        if (!(bool) $helper->ask($input, $this->symfonyStyle, $question)) {
            ConsoleTheme::info('Push later', sprintf('git push --force -u origin %s', $branchName));

            return;
        }

        exec(sprintf('git push --force -u origin %s 2>&1', escapeshellarg($branchName)), $pushOutput, $pushReturnCode);

        if ($pushReturnCode === 0) {
            ConsoleTheme::success(sprintf('Force pushed to origin/%s', $branchName));
        } else {
            ConsoleTheme::warning('Push failed - check SSH keys or create repo first');
            ConsoleTheme::info('Run', sprintf('git push --force -u origin %s', $branchName));
        }
    }

    private function runComposerInstall(): void
    {
        ConsoleTheme::newLine();
        ConsoleTheme::section('📦', 'Dependencies');

        $currentDir = getcwd();
        chdir($this->projectPath);

        $lockPath = $this->projectPath . '/composer.lock';

        if (file_exists($lockPath)) {
            unlink($lockPath);
        }

        exec('composer update --no-interaction 2>&1', $output, $returnCode);

        if ($returnCode === 0) {
            ConsoleTheme::success('Dependencies updated');
        } else {
            ConsoleTheme::warning('Update failed - regenerating autoload...');
            exec('composer dump-autoload --no-interaction 2>&1', $dumpOutput, $dumpReturnCode);

            if ($dumpReturnCode === 0) {
                ConsoleTheme::success('Autoload regenerated');
            } else {
                ConsoleTheme::warning('Autoload regeneration failed');
            }
        }

        if ($currentDir !== false) {
            chdir($currentDir);
        }
    }

    private function installGitHubActions(InputInterface $input): void
    {
        ConsoleTheme::newLine();
        ConsoleTheme::section('🚀', 'GitHub Actions');

        $githubPath = $this->projectPath . '/.github';

        if (!is_dir($githubPath)) {
            ConsoleTheme::warning('No .github directory found');

            return;
        }

        $helper = $this->getQuestionHelper();
        $question = new ConfirmationQuestion('   <accent>Install GitHub Actions?</accent> [<muted>yes</muted>]: ', true);

        if ((bool) $helper->ask($input, $this->symfonyStyle, $question)) {
            ConsoleTheme::success('GitHub Actions ready');

            return;
        }

        $this->deleteDirectory($githubPath);
        ConsoleTheme::success('Removed .github directory');
    }

    private function installCursorRules(InputInterface $input): void
    {
        ConsoleTheme::newLine();
        ConsoleTheme::section('📋', 'Cursor Rules');

        $helper = $this->getQuestionHelper();
        $question = new ConfirmationQuestion('   <accent>Install cursor rules?</accent> [<muted>yes</muted>]: ', true);

        if (!(bool) $helper->ask($input, $this->symfonyStyle, $question)) {
            ConsoleTheme::warning('Skipped');

            return;
        }

        $cursorPath = $this->projectPath . '/.cursor';

        if (is_dir($cursorPath)) {
            $this->deleteDirectory($cursorPath);
            ConsoleTheme::success('Removed old .cursor directory');
        }

        $currentDir = getcwd();
        chdir($this->projectPath);

        exec('vendor/bin/cursor-rules install --force 2>&1', $output, $returnCode);

        if ($returnCode === 0) {
            ConsoleTheme::success('Installed cursor rules');
        } else {
            ConsoleTheme::warning('Cursor rules installation failed');
        }

        if ($currentDir !== false) {
            chdir($currentDir);
        }
    }

    private function runComposerFix(): void
    {
        ConsoleTheme::newLine();
        ConsoleTheme::section('🔧', 'Code Quality Fixes');

        $currentDir = getcwd();
        chdir($this->projectPath);

        passthru('composer fix', $returnCode);

        if ($currentDir !== false) {
            chdir($currentDir);
        }
    }

    private function runComposerCheck(): bool
    {
        ConsoleTheme::newLine();
        ConsoleTheme::section('🔍', 'Quality Checks');

        $currentDir = getcwd();
        chdir($this->projectPath);

        passthru('composer check', $returnCode);

        if ($currentDir !== false) {
            chdir($currentDir);
        }

        return $returnCode === 0;
    }

    private function deleteSelf(): void
    {
        $scriptPath = $this->projectPath . '/post-create-project.php';

        if (!file_exists($scriptPath)) {
            return;
        }

        unlink($scriptPath);
    }

    private function printSuccess(): void
    {
        $projectDir = basename($this->projectPath);

        ConsoleTheme::newLine();
        ConsoleTheme::successBox('Done!', 'Your project is ready.');
        ConsoleTheme::newLine();
        ConsoleTheme::list('Next Steps', [
            'Update author info in <span class="text-orange-400">composer.json</span>',
            'Start coding in <span class="text-orange-400">src/</span>',
        ]);
        ConsoleTheme::newLine();
        ConsoleTheme::command(sprintf('cd %s', $projectDir));
    }

    private function printFailure(): void
    {
        $projectDir = basename($this->projectPath);

        ConsoleTheme::newLine();
        ConsoleTheme::warningBox('Done with issues', 'Quality checks failed.');
        ConsoleTheme::newLine();
        ConsoleTheme::list('Fix It', [
            'Run <span class="text-orange-400">composer fix</span>',
            'Run <span class="text-orange-400">composer check</span>',
            'Then <span class="text-orange-400">git init && git add -A && git commit</span>',
        ]);
        ConsoleTheme::newLine();
        ConsoleTheme::command(sprintf('cd %s', $projectDir));
    }

    private function getQuestionHelper(): QuestionHelper
    {
        $helper = $this->getHelper('question');
        assert($helper instanceof QuestionHelper);

        return $helper;
    }

    private function askQuestion(QuestionHelper $helper, InputInterface $input, Question $question): string
    {
        $answer = $helper->ask($input, $this->symfonyStyle, $question);

        if (is_string($answer)) {
            return $answer;
        }

        if (is_scalar($answer) || (is_object($answer) && method_exists($answer, '__toString'))) {
            return (string) $answer;
        }

        return '';
    }

    public function isGhCliInstalled(): bool
    {
        exec('which gh 2>/dev/null', $output, $returnCode);

        return $returnCode === 0;
    }

    public function githubRepositoryExists(string $githubUrl): bool
    {
        $repoName = $this->extractRepoNameFromUrl($githubUrl);

        if ($repoName === null) {
            return false;
        }

        exec(sprintf('gh repo view %s 2>/dev/null', escapeshellarg($repoName)), $output, $returnCode);

        return $returnCode === 0;
    }

    public function extractRepoNameFromUrl(string $githubUrl): ?string
    {
        if (preg_match('#^https://github\.com/([^/]+/[^/]+?)(?:\.git)?$#', $githubUrl, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function createGithubRepository(InputInterface $input, string $repoName): bool
    {
        $helper = $this->getQuestionHelper();

        $question = new ConfirmationQuestion('   <accent>Public repository?</accent> [<muted>yes</muted>]: ', true);
        $isPublic = (bool) $helper->ask($input, $this->symfonyStyle, $question);

        $visibility = $isPublic ? '--public' : '--private';
        $command = sprintf('gh repo create %s %s 2>&1', escapeshellarg($repoName), $visibility);

        exec($command, $output, $returnCode);

        return $returnCode === 0;
    }

    private function checkAndCreateGithubRepository(InputInterface $input): void
    {
        if (!$this->isGhCliInstalled()) {
            return;
        }

        $repoName = $this->extractRepoNameFromUrl($this->newGithubUrl);

        if ($repoName === null) {
            return;
        }

        ConsoleTheme::newLine();
        ConsoleTheme::section('🔍', 'GitHub Repository');

        if ($this->githubRepositoryExists($this->newGithubUrl)) {
            ConsoleTheme::success(sprintf('Repository %s exists', $repoName));

            return;
        }

        ConsoleTheme::warning(sprintf('Repository %s does not exist', $repoName));

        $helper = $this->getQuestionHelper();
        $question = new ConfirmationQuestion('   <accent>Create repository now?</accent> [<muted>yes</muted>]: ', true);

        if (!(bool) $helper->ask($input, $this->symfonyStyle, $question)) {
            $this->cleanupProjectDirectory('Repository does not exist and user declined to create it.');
            exit(1);
        }

        if ($this->createGithubRepository($input, $repoName)) {
            ConsoleTheme::success(sprintf('Created repository %s', $repoName));

            return;
        }

        ConsoleTheme::errorBox('Failed to create repository', 'Check your GitHub CLI authentication.');
        $this->cleanupProjectDirectory('Failed to create GitHub repository.');
        exit(1);
    }

}
