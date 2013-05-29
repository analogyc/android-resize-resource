<?php

namespace Analogyc\AndroiDresourceResizer;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Console extends Command
{
	/**
	 * Configure the command line interface arguments
	 */
	protected function configure()
	{
		$this
			->setName('resize')
			->setDescription('Riempie il database di basi di dati con contenuto di test generato casualmente')
			->addOption(
				'source-dir',
				null,
				InputOption::VALUE_REQUIRED,
				'The directory where the media files are found'
			)
			->addOption(
				'dest-dir',
				null,
				InputOption::VALUE_REQUIRED,
				'The directory where the media files are to be saved (the res directory)'
			)
			->addOption(
				'resolutions',
				'mdpi|hdpi|xhdpi|xxhdpi',
				InputOption::VALUE_REQUIRED,
				'The Android resolutions to support'
			);
	}

	/**
	 * Manage the command line request
	 *
	 * @param InputInterface  $input
	 * @param OutputInterface $output
	 *
	 * @return int|null|void
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{

	}
}