<?php

namespace App\Exceptions;

use Composer\InstalledVersions;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Jean85\PrettyVersions;
use PackageVersions\Versions;
use Throwable;

class Handler extends ExceptionHandler
{

    /**
     * @var array<string, string> The list of installed vendors
     */
    private static $packages = [];

    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            self::getComposerPackages();
        });
    }


    /**
     * @return array<string, string>
     */
    private static function getComposerPackages(): array
    {
        if (empty(self::$packages)) {
            foreach (self::getInstalledPackages() as $package) {
                try {
                    self::$packages[$package] = PrettyVersions::getVersion($package)->getPrettyVersion();
                } catch (\Throwable $exception) {
                    continue;
                }
            }
        }

        return self::$packages;
    }

    /**
     * @return string[]
     */
    private static function getInstalledPackages(): array
    {
        if (class_exists(InstalledVersions::class)) {
            return InstalledVersions::getInstalledPackages();
        }

        if (class_exists(Versions::class)) {
            // BC layer for Composer 1, using a transient dependency
            return array_keys(Versions::VERSIONS);
        }

        // this should not happen
        return ['sentry/sentry'];
    }
}
