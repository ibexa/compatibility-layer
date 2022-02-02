<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\CompatibilityLayer\Command;

use Ibexa\CompatibilityLayer\Rebranding\ComposerRebranding;
use Ibexa\CompatibilityLayer\Rebranding\CssRebranding;
use Ibexa\CompatibilityLayer\Rebranding\JsRebranding;
use Ibexa\CompatibilityLayer\Rebranding\PhpRebranding;
use Ibexa\CompatibilityLayer\Rebranding\RebrandingInterface;
use Ibexa\CompatibilityLayer\Rebranding\TwigRebranding;
use Ibexa\CompatibilityLayer\Rebranding\XmlRebranding;
use Ibexa\CompatibilityLayer\Rebranding\YamlRebranding;
use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class IbexaRebrandingCommand extends Command
{
    protected static $defaultName = 'ibexa:rebranding';

    private SymfonyStyle $style;

    protected function configure()
    {
        $this->addArgument('sourcePath', InputArgument::REQUIRED);
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $this->style = new SymfonyStyle($input, $output);

        $sourcePath = $input->getArgument('sourcePath');

        $filesystem = new Filesystem();
        if (!$filesystem->exists($sourcePath)) {
            throw new InvalidArgumentException("Path ${sourcePath} does not exist or is not readable.");
        }

        $this->process(new PhpRebranding(), $sourcePath);
        $this->process(new YamlRebranding(), $sourcePath);
        $this->process(new XmlRebranding(), $sourcePath);
        $this->process(new TwigRebranding(), $sourcePath);
        $this->process(new JsRebranding(), $sourcePath);
        $this->process(new CssRebranding(), $sourcePath);
        $this->process(new ComposerRebranding(), $sourcePath);

        $this->style->success('Done.');

        return Command::SUCCESS;
    }

    private function process(RebrandingInterface $rebranding, string $sourcePath, bool $dryRun = false): void
    {
        $this->style->info('Rebranding ' . implode(', ', $rebranding->getFileNamePatterns()) . ':');

        $finder = new Finder();
        $files = $finder->files()->in($sourcePath)->name($rebranding->getFileNamePatterns());
        $filesCount = iterator_count($files);

        $progressBar = new ProgressBar($this->style, $filesCount);
        $progressBar->display();

        foreach ($files as $file) {
            if (strpos($file->getPathname(), 'vendor/ibexa/compatibility-layer/') === 0
                || strpos($file->getPathname(), 'vendor/ibexa/admin-ui-assets/') === 0) {
                $progressBar->advance();
                continue;
            }
            $input = file_get_contents($file->getPathname());
            $output = $rebranding->rebrand($input);

            if (!$dryRun) {
                file_put_contents($file->getPathname(), $output);
            }

            $progressBar->advance();
        }
    }
}
