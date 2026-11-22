<?php

namespace Paymenter\Extensions\Gateways\MultiBankQR\Services;

use Paymenter\Extensions\Gateways\MultiBankQR\MultiBankQR;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use ZipArchive;

class GithubUpdateService
{
    public const GITHUB_REPO = 'Hasnain1385/Paymenter_Payment';
    public const USER_AGENT = 'MultiBankQR-Paymenter-Updater/1.2.0';

    /**
     * Get the extension base directory path.
     */
    public static function getExtensionPath(): string
    {
        return realpath(__DIR__ . '/..') ?: dirname(__DIR__);
    }

    /**
     * Check if the extension directory is a Git repository.
     */
    public static function isGitRepository(): bool
    {
        $path = self::getExtensionPath();
        return is_dir($path . '/.git');
    }

    /**
     * Check GitHub for available releases, tags, and commits.
     *
     * @return array [
     *     'has_update' => bool,
     *     'current_version' => string,
     *     'latest_version' => string,
     *     'release_name' => string,
     *     'release_notes' => string,
     *     'download_url' => string,
     *     'published_at' => string|null,
     *     'is_git_repo' => bool,
     *     'git_status' => array,
     *     'error' => string|null,
     * ]
     */
    public static function checkForUpdates(): array
    {
        $currentVersion = defined(MultiBankQR::class . '::VERSION') ? MultiBankQR::VERSION : '1.1.1';
        $gitStatus = self::getGitStatus();

        $result = [
            'has_update' => false,
            'current_version' => $currentVersion,
            'latest_version' => $currentVersion,
            'release_name' => 'v' . $currentVersion,
            'release_notes' => 'You are on the latest verified release.',
            'download_url' => 'https://github.com/' . self::GITHUB_REPO . '/archive/refs/heads/main.zip',
            'published_at' => null,
            'is_git_repo' => self::isGitRepository(),
            'git_status' => $gitStatus,
            'error' => null,
        ];

        try {
            // 1. Fetch latest release from GitHub
            $releaseData = self::fetchGithubApi('/releases/latest');

            if ($releaseData && isset($releaseData['tag_name'])) {
                $remoteTag = ltrim($releaseData['tag_name'], 'v');
                $result['latest_version'] = $remoteTag;
                $result['release_name'] = $releaseData['name'] ?? $releaseData['tag_name'];
                $result['release_notes'] = $releaseData['body'] ?? '';
                $result['published_at'] = $releaseData['published_at'] ?? null;
                $result['download_url'] = $releaseData['zipball_url'] ?? $result['download_url'];

                if (version_compare($remoteTag, $currentVersion, '>')) {
                    $result['has_update'] = true;
                }
            } else {
                // 2. Fallback to tags if no official release is published yet
                $tagsData = self::fetchGithubApi('/tags');
                if (is_array($tagsData) && !empty($tagsData)) {
                    $latestTag = $tagsData[0]['name'] ?? '';
                    $cleanTag = ltrim($latestTag, 'v');
                    $result['latest_version'] = $cleanTag;
                    $result['release_name'] = $latestTag;
                    $result['download_url'] = $tagsData[0]['zipball_url'] ?? $result['download_url'];

                    if (version_compare($cleanTag, $currentVersion, '>')) {
                        $result['has_update'] = true;
                        $result['release_notes'] = "New release {$latestTag} is available on GitHub.";
                    }
                }
            }

            // 3. Check commit difference if in Git repo
            if (!empty($gitStatus['local_commit'])) {
                $commitData = self::fetchGithubApi('/commits/main');
                if ($commitData && isset($commitData['sha'])) {
                    $remoteSha = substr($commitData['sha'], 0, 7);
                    $result['remote_commit'] = $remoteSha;
                    if ($remoteSha !== $gitStatus['local_commit']) {
                        $result['has_commit_update'] = true;
                        $result['commit_message'] = $commitData['commit']['message'] ?? '';
                    }
                }
            }
        } catch (\Throwable $e) {
            $result['error'] = 'Unable to check GitHub: ' . $e->getMessage();
            Log::warning('[MultiBankQR] GitHub update check error: ' . $e->getMessage());
        }

        return $result;
    }

    /**
     * Apply update in-place.
     * Uses `git pull` if running in a Git clone, or downloads and overwrites files via ZIP.
     *
     * @return array ['success' => bool, 'message' => string, 'output' => string|null]
     */
    public static function applyUpdateInPlace(): array
    {
        $extPath = self::getExtensionPath();

        // Method A: Git In-Place Pull
        if (self::isGitRepository() && self::isCommandAvailable('git')) {
            try {
                $cmd = 'cd ' . escapeshellarg($extPath) . ' && git fetch origin && git pull origin main 2>&1';
                $output = shell_exec($cmd);

                // Run post-update schema checks & cache clear
                MultiBankQR::ensureTablesExist();
                self::clearApplicationCache();

                return [
                    'success' => true,
                    'message' => 'Successfully pulled latest changes via Git in-place.',
                    'output' => trim($output ?? ''),
                ];
            } catch (\Throwable $e) {
                // If git fails, fall back to ZIP method
                Log::warning('[MultiBankQR] Git pull failed, falling back to ZIP update: ' . $e->getMessage());
            }
        }

        // Method B: Download and extract GitHub ZIP in-place
        return self::applyZipUpdateInPlace();
    }

    /**
     * Download and extract GitHub ZIP archive over the current extension directory.
     */
    protected static function applyZipUpdateInPlace(): array
    {
        $extPath = self::getExtensionPath();
        $zipUrl = 'https://github.com/' . self::GITHUB_REPO . '/archive/refs/heads/main.zip';

        $tempZip = sys_get_temp_dir() . '/multibankqr_update_' . uniqid() . '.zip';
        $tempExtract = sys_get_temp_dir() . '/multibankqr_ext_' . uniqid();

        try {
            // Download archive
            $ch = curl_init($zipUrl);
            $fp = fopen($tempZip, 'w+');
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_USERAGENT, self::USER_AGENT);
            $exec = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            fclose($fp);

            if (!$exec || $httpCode < 200 || $httpCode >= 400 || !file_exists($tempZip) || filesize($tempZip) === 0) {
                throw new \Exception("Failed to download update archive from GitHub (HTTP {$httpCode}).");
            }

            // Extract archive
            $zip = new ZipArchive();
            if ($zip->open($tempZip) !== true) {
                throw new \Exception('Failed to open downloaded update ZIP archive.');
            }

            mkdir($tempExtract, 0755, true);
            $zip->extractTo($tempExtract);
            $zip->close();
            @unlink($tempZip);

            // Locate source folder inside extracted archive
            $sourceDir = self::locateSourceDirectory($tempExtract);
            if (!$sourceDir) {
                throw new \Exception('No valid extension source files found inside the update archive.');
            }

            // Copy files in place (preserve storage/multibank_proofs)
            self::copyDirectoryInPlace($sourceDir, $extPath);

            // Cleanup temp extract folder
            self::deleteDirectoryRecursive($tempExtract);

            // Run migrations and clear cache
            MultiBankQR::ensureTablesExist();
            self::clearApplicationCache();

            return [
                'success' => true,
                'message' => 'Extension updated successfully in-place from GitHub main branch!',
                'output' => "Updated files copied to: {$extPath}",
            ];
        } catch (\Throwable $e) {
            if (file_exists($tempZip)) @unlink($tempZip);
            if (is_dir($tempExtract)) self::deleteDirectoryRecursive($tempExtract);

            Log::error('[MultiBankQR] In-place update failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Update failed: ' . $e->getMessage(),
                'output' => null,
            ];
        }
    }

    /**
     * Push local changes to GitHub in place.
     *
     * @param string $commitMessage
     * @return array ['success' => bool, 'message' => string, 'output' => string]
     */
    public static function pushGitUpdates(string $commitMessage = 'Update from Paymenter Admin'): array
    {
        $extPath = self::getExtensionPath();

        if (!self::isGitRepository()) {
            return [
                'success' => false,
                'message' => 'This installation is not a Git clone. To push updates, the extension must be cloned via Git.',
                'output' => null,
            ];
        }

        if (!self::isCommandAvailable('git')) {
            return [
                'success' => false,
                'message' => 'The `git` command is not available or executable on this server environment.',
                'output' => null,
            ];
        }

        // Sanitize commit message
        $sanitizedMessage = preg_replace('/[^\w\s\-\.\,\:\(\)]/u', '', $commitMessage);
        if (empty(trim($sanitizedMessage))) {
            $sanitizedMessage = 'Update from Paymenter Admin Panel at ' . date('Y-m-d H:i:s');
        }

        // Check git status
        $statusCmd = 'cd ' . escapeshellarg($extPath) . ' && git status --porcelain 2>&1';
        $statusOut = trim(shell_exec($statusCmd) ?? '');

        if (empty($statusOut)) {
            return [
                'success' => true,
                'message' => 'Working tree is already clean. Nothing new to commit or push.',
                'output' => 'Clean working directory.',
            ];
        }

        // Stage, commit, and push
        $pushCmd = 'cd ' . escapeshellarg($extPath)
            . ' && git add -A'
            . ' && git commit -m ' . escapeshellarg($sanitizedMessage)
            . ' && git push origin main 2>&1';

        $pushOutput = shell_exec($pushCmd);
        $isSuccess = str_contains($pushOutput ?? '', 'main -> main') || !str_contains($pushOutput ?? '', 'error:');

        return [
            'success' => $isSuccess,
            'message' => $isSuccess ? 'Changes committed and pushed to GitHub main branch!' : 'Git push command executed with warnings.',
            'output' => trim($pushOutput ?? ''),
        ];
    }

    /**
     * Get detailed local Git status.
     */
    public static function getGitStatus(): array
    {
        $extPath = self::getExtensionPath();
        $status = [
            'is_git' => self::isGitRepository(),
            'branch' => 'unknown',
            'local_commit' => null,
            'modified_count' => 0,
            'remote_url' => null,
        ];

        if (!self::isGitRepository() || !self::isCommandAvailable('git')) {
            return $status;
        }

        try {
            $branch = trim(shell_exec('cd ' . escapeshellarg($extPath) . ' && git branch --show-current 2>/dev/null') ?? '');
            $commit = trim(shell_exec('cd ' . escapeshellarg($extPath) . ' && git rev-parse --short HEAD 2>/dev/null') ?? '');
            $remote = trim(shell_exec('cd ' . escapeshellarg($extPath) . ' && git remote get-url origin 2>/dev/null') ?? '');
            $dirty = trim(shell_exec('cd ' . escapeshellarg($extPath) . ' && git status --porcelain 2>/dev/null') ?? '');

            $status['branch'] = $branch ?: 'main';
            $status['local_commit'] = $commit ?: null;
            $status['remote_url'] = $remote ?: 'https://github.com/' . self::GITHUB_REPO;
            $status['modified_count'] = !empty($dirty) ? count(explode("\n", trim($dirty))) : 0;
        } catch (\Throwable $e) {
            // Ignore Git read errors
        }

        return $status;
    }

    /**
     * Locate the root folder containing MultiBankQR.php inside extracted archive.
     */
    protected static function locateSourceDirectory(string $extractPath): ?string
    {
        if (file_exists($extractPath . '/MultiBankQR.php')) {
            return $extractPath;
        }

        $subDirs = glob($extractPath . '/*', GLOB_ONLYDIR);
        foreach ($subDirs as $dir) {
            if (basename($dir) === '__MACOSX') continue;
            if (file_exists($dir . '/MultiBankQR.php')) {
                return $dir;
            }
        }

        return null;
    }

    /**
     * Copy all files from source to destination in place.
     * Skips user uploaded files (storage/multibank_proofs).
     */
    protected static function copyDirectoryInPlace(string $source, string $destination): void
    {
        $dir = opendir($source);
        if (!$dir) return;

        @mkdir($destination, 0755, true);

        while (($file = readdir($dir)) !== false) {
            if ($file === '.' || $file === '..') continue;

            $srcFile = $source . '/' . $file;
            $destFile = $destination . '/' . $file;

            // Never overwrite customer uploaded transaction proofs
            if ($file === 'storage' && file_exists($destFile . '/multibank_proofs')) {
                continue;
            }

            if (is_dir($srcFile)) {
                self::copyDirectoryInPlace($srcFile, $destFile);
            } else {
                copy($srcFile, $destFile);
            }
        }

        closedir($dir);
    }

    /**
     * Recursively delete a directory.
     */
    protected static function deleteDirectoryRecursive(string $dir): void
    {
        if (!is_dir($dir)) return;

        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $path = $dir . '/' . $item;
            if (is_dir($path)) {
                self::deleteDirectoryRecursive($path);
            } else {
                @unlink($path);
            }
        }
        @rmdir($dir);
    }

    /**
     * Clear application cache using Artisan if available.
     */
    protected static function clearApplicationCache(): void
    {
        try {
            if (class_exists(Artisan::class)) {
                Artisan::call('view:clear');
                Artisan::call('optimize:clear');
            }
        } catch (\Throwable $e) {
            // Non-critical if running in test environment
        }
    }

    /**
     * Check if a shell command is available and executable.
     */
    protected static function isCommandAvailable(string $cmd): bool
    {
        if (!function_exists('shell_exec') || !function_exists('exec')) {
            return false;
        }

        $which = trim(shell_exec('which ' . escapeshellarg($cmd) . ' 2>/dev/null') ?? '');
        return !empty($which);
    }

    /**
     * Make a request to the GitHub API.
     */
    protected static function fetchGithubApi(string $endpoint): ?array
    {
        $url = 'https://api.github.com/repos/' . self::GITHUB_REPO . $endpoint;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, self::USER_AGENT);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/vnd.github.v3+json',
        ]);

        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code >= 200 && $code < 300 && $response) {
            return json_decode($response, true);
        }

        return null;
    }
}
