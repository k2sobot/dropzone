<?php

namespace App\Console\Commands;

use App\Services\ExtensionLicense;
use Illuminate\Console\Command;

class LicenseIssueCommand extends Command
{
    protected $signature = 'dropzone:license-issue {package : Official package, e.g. dropzone/s3}
                            {--domain= : Licensed host; empty means any}
                            {--years=1 : Years of updates included}';

    protected $description = 'Issue a Dropzone extension license key (FreeScout-style: pay once, updates for N years)';

    public function handle(): int
    {
        $package = $this->argument('package');
        $catalog = ExtensionLicense::catalog();

        if (! isset($catalog[$package]) || empty($catalog[$package]['paid'])) {
            $this->error('Not a paid official package: '.$package);
            $this->line('Paid packages: '.implode(', ', array_keys(array_filter($catalog, fn ($m) => ! empty($m['paid'])))));

            return self::FAILURE;
        }

        $domain = trim((string) $this->option('domain'));
        if ($domain === '') {
            $domain = '*';
        }

        $key = ExtensionLicense::issue(
            $package,
            $domain,
            max(1, (int) $this->option('years'))
        );

        $this->info($key);

        return self::SUCCESS;
    }
}
