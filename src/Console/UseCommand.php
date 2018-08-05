<?php

namespace TightenCo\Jigsaw\Console;

use TightenCo\Jigsaw\File\Filesystem;
use Symfony\Component\Console\Input\InputArgument;

class UseCommand extends Command
{
    private $files;
    private $base;
    private $elixirFiles = [
        'gulpfile.js',
        'package.json',
        'package-lock.json',
        'tasks/bin.js',
    ];
    private $mixFiles = [
        'webpack.mix.js',
        'package.json',
        'package-lock.json',
        'tasks/bin.js',
    ];

    public function __construct(Filesystem $files)
    {
        $this->files = $files;
        $this->base = getcwd();
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('use')
            ->setDescription('Switch between using Mix (with Webpack) and Elixir (with Gulp) for compiling assets.')
            ->addArgument(
                'tool',
                InputArgument::OPTIONAL,
                'What tool should we use to compile assets for this project?'
            );
    }

    protected function fire()
    {
        $this->comment("\nThis will replace your current `package.json` file, and any existing Gulp or Webpack configurations.");

        if (!$this->confirm('Do you wish to continue? ')) {
            $this->info("\nNo changes were made.\n");

            return;
        }

        $tool = $this->input->getArgument('tool');

        if ('mix' == $tool || 'webpack' == $tool) {
            $this->scaffoldMix();
        } elseif ('elixir' == $tool || 'gulp' == $tool) {
            $this->scaffoldElixir();
        }

        $this->info("Run `npm install` to update your Node.js dependencies.\n");
    }

    private function scaffoldMix()
    {
        $this->deleteFiles($this->elixirFiles);
        $this->files->copyDirectory(__DIR__ . '/../../stubs/mix', $this->base);
        $this->info("\nNow using Laravel Mix and Webpack to compile assets.");
    }

    private function scaffoldElixir()
    {
        $this->deleteFiles($this->mixFiles);
        $this->files->copyDirectory(__DIR__ . '/../../stubs/elixir', $this->base);
        $this->info("\nNow using Laravel Elixir and Gulp to compile assets.");
    }

    private function deleteFiles($files)
    {
        collect($files)->each(function ($file) {
            $this->files->delete($this->base . '/' . $file);
        });
    }
}
